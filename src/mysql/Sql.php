<?php

namespace zhqing\mysql;

trait Sql {

    /**
     * where 多条件统计
     * [['bet_amount', 'id = 1 || id = 2'], ['bet_amount@aa', 'id = 1 || id = 2'], ['bet_amount@bet'], ['bet_amount']]
     * @param string|array $field
     * @return string
     */
    public static function sum(string|array $field): string {
        $sumWay = function ($field, $alias = '', $where = '') {
            $alias = (!empty($alias) ? $alias : $field);
            if (!empty($where)) {
                $sum = "SUM(CASE WHEN " . $where . " THEN " . $field . " ELSE 0 END) AS " . $alias . ",";
            } else {
                $sum = "SUM(" . $field . ") AS " . $alias . ",";
            }
            return $sum;
        };
        if (is_array($field)) {
            $sum = '';
            $data = (static::arrLevel($field) == 1) ? [$field] : $field;
            foreach ($data as $v) {
                if (!empty($key = ($v[0] ?? ''))) {
                    $arr = explode('@', $key);
                    $sum .= $sumWay(($arr[0] ?? ''), ($arr[1] ?? ''), ($v[1] ?? ''));
                }
            }
        } else {
            $sum = $sumWay($field);
        }
        return trim($sum, ',');
    }

    /**
     * @param string $table 表单名
     * @param array $array
     * @return string
     */
    public function add(string $table, array $array): string {
        $demo = [
            'id' => ['name' => 'id', 'type' => 'int(11)'],//主键
            'engine' => 'InnoDB',//引擎
            'auto' => 1,//自动递增
            'charset' => 'utf8mb4',//字符集
            'comment' => 'demo table',//表单备注
            'list' => [
                'title' => [
                    'type' => 'varchar(200)', //字段类型
                    'charset' => 'utf8mb4', //字符集
                    'key' => false, //是否键
                    'null' => false, //不是null
                    'default' => 0, //字段默认值
                    'time' => false, //根据当前时间戳更新
                    'comment' => 'demo field'//字段备注
                ],
                'content' => [
                    'type' => 'blob', //字段类型
                    'charset' => 'utf8mb4', //字符集
                    'key' => false, //是否键
                    'null' => false, //不是null
                    'default' => "NULL", //字段默认值
                    'time' => false, //根据当前时间戳更新
                    'comment' => 'demo field'//字段备注
                ]
            ]
        ];
        $tables = $this->getFullTable($table);
        $sql = "DROP TABLE IF EXISTS `{$tables}`;\r\n";
        $sql .= "CREATE TABLE `{$tables}` (\r\n";
        $sql .= "  `" . ($array['id']['name'] ?? 'id') . "` " . ($array['id']['type'] ?? 'int(11)') . " NOT NULL AUTO_INCREMENT,\r\n";
        $primary = "`" . ($array['id']['name'] ?? 'id') . "`";
        foreach ($array['list'] as $k => $v) {
            $null = $v['null'] ?? false;
            if (!empty($key = ($v['key'] ?? ''))) {
                $null = true;
                $primary .= ",`" . $key . "`";
            }
            $sql .= "  `" . $k . "`";
            $sql .= (!empty($type = ($v['type'] ?? '')) ? (" " . $type) : "");//int(11)
            $sql .= (!empty($charset = ($v['charset'] ?? ($this->config['charset'] ?? ''))) ? (" CHARACTER SET " . $charset) : " CHARACTER SET utf8mb4");//字符集
            $sql .= (!empty($null) ? (" NOT NULL") : "");//是否能为空
            $sql .= (!empty($default = ($v['default'] ?? '')) ? (" DEFAULT " . $default) : "");//默认值
            $sql .= !empty(($v['time'] ?? '')) ? " ON UPDATE CURRENT_TIMESTAMP" : "";//根据当前时间戳更新
            $sql .= (!empty($comment = ($v['comment'] ?? '')) ? (" COMMENT '" . $comment . "'") : "");//备注
            $sql .= ",\r\n";
        }
        $sql .= "  PRIMARY KEY (" . trim($primary, ",") . ") USING BTREE\r\n";
        $sql .= ") ENGINE=" . ($array['engine'] ?? 'InnoDB') . " AUTO_INCREMENT=" . ($array['auto'] ?? 1) . " DEFAULT CHARSET=" . ($array['charset'] ?? $this->config['charset'] ?? '') . " COMMENT='" . ($array['comment'] ?? '') . "';";
        return $sql;
    }

    /**
     * 删除表单
     * @param string|array $table 表单名
     * @return string
     */
    public function delete(string|array $table): string {
        if (is_array($table)) {
            $sql = "";
            foreach ($table as $v) {
                $sql .= "DROP TABLE IF EXISTS `" . $this->getFullTable($v) . "`;";
                $sql .= "\r\n";
            }
        } else {
            $sql = "DROP TABLE IF EXISTS `" . $this->getFullTable($table) . "`;";
        }
        return $sql;
    }

    /**
     * 添加表单列
     * @param string $table 表单名
     * @param array $array
     * @return string
     */
    public function row(string $table, array $array): string {
        $demo = [
            'field' => [
                'type' => 'int(11)', //字段类型
                'charset' => 'utf8mb4', //字符集
                'null' => false, //不是null
                'default' => 0, //字段默认值
                'time' => false, //根据当前时间戳更新
                'comment' => 'demo field'//字段备注
            ]
        ];
        $sql = "ALTER TABLE `" . $this->getFullTable($table) . "`";
        foreach ($array as $k => $v) {
            $sql .= " ADD `" . $k . "`";
            //
            $sql .= (!empty($type = ($v['type'] ?? '')) ? (" " . $type) : "");//int(11)
            $sql .= (!empty($charset = ($v['charset'] ?? '')) ? (" CHARACTER SET " . $charset) : " CHARACTER SET utf8mb4");//字符集
            $sql .= (!empty(($v['null'] ?? '')) ? (" NOT NULL") : "");//是否能为空
            $sql .= (!empty($default = ($v['default'] ?? '')) ? (" DEFAULT " . $default) : "");//默认值
            $sql .= !empty(($v['time'] ?? '')) ? " ON UPDATE CURRENT_TIMESTAMP" : "";//根据当前时间戳更新
            $sql .= (!empty($comment = ($v['comment'] ?? '')) ? (" COMMENT '" . $comment . "'") : "");//备注
            $sql .= ",";
        }
        return trim(trim($sql), ",") . ";";
    }

    /**
     * 删除表单列
     * @param string $table 表单名
     * @param array|string $array ['field1','field2'],field1,field2
     * @return string
     */
    public function del(string $table, array|string $array): string {
        $sql = "ALTER TABLE `" . $this->getFullTable($table) . "`";
        $array = is_array($array) ? $array : explode(',', $array);
        foreach ($array as $v) {
            $sql .= " DROP COLUMN `" . $v . "`,";
        }
        return trim(trim($sql), ",") . ";";
    }
}