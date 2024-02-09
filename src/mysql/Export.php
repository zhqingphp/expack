<?php

namespace zhqing\mysql;

use Exception;

trait Export {
    //mysql字段类型分类
    public array $MysqlType = [
        //数值类型
        'int' => [
            'tinyint',
            'smallint',
            'mediumint',
            'int',
            'bigint',
            'float',
            'double',
            'decimal'
        ],
        //二进制类型
        'hex' => [
            'tinyblob',
            'blob',
            'mediumblob',
            'longblob',
            'binary',
            'varbinary'
        ],
        //空间类型
        'room' => [
            'geometry',
            'point',
            'linestring',
            'polygon',
            'multipoint',
            'multilinestring',
            'multipolygon',
            'geometrycollection'
        ],
        //JSON类型
        'json' => [
            'json'
        ],
        //字符串类型
        'string' => [
            'char',
            'varchar',
            'tinytext',
            'text',
            'mediumtext',
            'longtext'
        ],
        //枚举与集合类型
        'enum' => [
            'enum',
            'set'
        ],
        //日期与时间类型
        'time' => [
            'time',
            'date',
            'datetime',
            'timestamp',
            'year'
        ]
    ];

    /**
     * 导出sql文件
     * @param string $FilePath 导出的.sql文件路径,(为空不保存,可通过sql()获取生成的sql)
     * @param bool $isData 是否导出表数据(默认为true)
     * @param array $isTable 要导出的表名数组(默认为空，即导出所有表)
     * @return array
     */
    public function export(string $FilePath = '', bool $isData = true, array $isTable = []): array {
        try {
            $content = $this->remark("Date: " . date("Y-m-d H:i:s")) . "\r\n";
            $content .= "SET NAMES " . $this->config['charset'] . ";\r\n";
            $content .= "SET FOREIGN_KEY_CHECKS = 0;\r\n";
            $ss = 2;
            $ds = 0;
            $cs = 0;
            $is = 0;
            $tabNameArray = $this->getAllTabName();//获取所有表名
            if (!empty($tabNameArray)) {
                if (!empty($isTable)) {
                    foreach ($isTable as $k => $v) {
                        $isTable[$k] = $this->getFullTable($v);
                    }
                }
                foreach ($tabNameArray as $table) {
                    //如果设置了表前缀,且传入的表名不包含表前缀,则补上
                    $table = $this->getFullTable($table);
                    //要导出的表名数组
                    if (!empty($isTable) && !in_array($table, $isTable)) {
                        continue;
                    }
                    $content .= "\r\n";
                    $content .= $this->remark("Table structure for " . $table);
                    $tabSql = $this->getTabSql($table);//获取表单sql
                    $content .= "DROP TABLE IF EXISTS `{$table}`;\r\n";
                    ++$ds;
                    $content .= $tabSql . ";\r\n\r\n";
                    ++$cs;
                    if (!empty($isData)) {
                        $from = "*";
                        $content .= $this->remark("Records of " . $table);
                        $tabArr = $this->getTabInfo($table);//获取表单字段信息
                        foreach ($tabArr as $k => $v) {
                            if (in_array(strtolower(($v['DATA_TYPE'] ?? '')), $this->MysqlType['room'])) {
                                $from .= ",ST_AsText(" . $k . ") as " . $k;
                            }
                        }
                        $tabData = $this->getTabData($table, trim($from, ",")); //导出表的结构
                        if (!empty($tabData)) {
                            foreach ($tabData as $row) {
                                $field = "";
                                $values = "";
                                foreach ($row as $key => $value) {
                                    $value = !empty($value) ? $value : ($tabArr[$key]['COLUMN_DEFAULT'] ?? NULL);
                                    $dataType = strtolower($tabArr[$key]['DATA_TYPE'] ?? '');//类型
                                    if (in_array($dataType, $this->MysqlType['int'])) {
                                        $values .= (!empty($value) ? $value : 0) . ", ";
                                    } else if ($value == null) {
                                        $values .= "NULL, ";
                                    } else if (empty($value)) {
                                        $values .= "'', ";
                                    } else if (in_array($dataType, $this->MysqlType['hex'])) {
                                        $value = "0x" . bin2hex($value);
                                        $values .= $value . ", ";
                                    } else if (in_array($dataType, $this->MysqlType['room'])) {
                                        $values .= "ST_GeomFromText(\"" . $value . "\"), ";
                                    } else {
                                        if (!empty($jsonArr = static::isJson($value)) || in_array($dataType, $this->MysqlType['json'])) {
                                            $formattedJson = json_encode($jsonArr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                            $formattedJson = preg_replace('/^\s+|\s+$/m', '', $formattedJson);
                                            $value = preg_replace('/\s+/', '', $formattedJson);
                                        }
                                        $values .= $this->quote($value) . ", ";
                                    }
                                    $field .= "`{$key}`, ";
                                }
                                $field = trim($field, ", ");
                                $values = trim($values, ", ");
                                $content .= "INSERT INTO `{$table}` ({$field}) VALUES ({$values});\r\n";
                                ++$is;
                            }
                        }
                    }
                }
            }
            $content .= "\r\nSET FOREIGN_KEY_CHECKS = 1;";
            ++$ss;
            $data['code'] = 200;
            if (!empty($FilePath)) {
                $data['data'] = (@file_put_contents($this->mkDir($FilePath), $content));
            } else {
                $data['data'] = $content;
            }
            $data['count'] = [
                'count' => ($ds + $cs + $is + $ss),//执行总数
                'drop_success' => $ds,//删除表单数量
                'create_success' => $cs,//创建表单数量
                'insert_success' => $is,//添加记录数量
                'set_success' => $ss,//其它数量
            ];
        } catch (Exception $e) {
            $data['code'] = 400;
            $data['data'] = $e->getMessage();
        }
        $this->close();
        return $data;
    }
}