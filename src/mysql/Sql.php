<?php

namespace zhqing\mysql;

trait Sql {
    /**
     * where 多条件统计
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
            foreach ($field as $v) {
                $sum .= $sumWay(($v['name'] ?? ''), ($v['as'] ?? ''), ($v['where'] ?? ''));
            }
        } else {
            $sum = $sumWay($field);
        }
        return trim($sum, ',');
    }

    /**
     * 重设AUTO_INCREMENT=1
     * @param array|string $table
     * @param bool $base 是否添加数据库名
     * @param int $auto
     * @return string
     */
    public function auto(array|string $table, bool $base = false, int $auto = 1): string {
        return "ALTER TABLE " . $this->getFullTable($table, $base) . " AUTO_INCREMENT = " . $auto . ";";
    }

    /**
     * 添加表单
     * @param string $table 表单名
     * @param array $array
     * @param bool $base 是否添加数据库名
     * @return string
     */
    public function add(string $table, array $array, bool $base = false): string {
        $tables = $this->getFullTable($table, $base);
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
            $default = ($v['default'] ?? '');
            if ($default == null || strtolower($default) == 'null') {
                $default = "NULL";
            } else if (!empty($default)) {
                $default = "'" . $default . "'";
            }
            $sql .= "  `" . $k . "`";
            $sql .= (!empty($type = ($v['type'] ?? '')) ? (" " . $type) : "");//int(11)
            $sql .= (!empty($charset = ($v['charset'] ?? ($this->config['charset'] ?? ''))) ? (" CHARACTER SET " . $charset) : " CHARACTER SET utf8mb4");//字符集
            $sql .= (!empty($null) ? (" NOT NULL") : "");//是否能为空
            $sql .= (!empty($default) ? (" DEFAULT " . $default) : "");//默认值
            $sql .= !empty(($v['time'] ?? '')) ? " ON UPDATE CURRENT_TIMESTAMP" : "";//根据当前时间戳更新
            $sql .= (!empty($comment = ($v['comment'] ?? '')) ? (" COMMENT '" . $comment . "'") : "");//备注
            $sql .= ",\r\n";
        }
        $sql .= "  PRIMARY KEY (" . trim($primary, ",") . ") USING BTREE\r\n";
        $sql .= ") ENGINE=" . ($array['engine'] ?? 'InnoDB') . " AUTO_INCREMENT=" . ($array['auto'] ?? 1) . " DEFAULT CHARSET=" . ($array['charset'] ?? $this->config['charset'] ?? '') . " COMMENT='" . ($array['comment'] ?? '') . "';";
        return $sql;
    }

    /**
     * 添加列
     * @param string $table 表单名
     * @param array $array
     * @param bool $base 是否添加数据库名
     * @return string
     */
    public function row(string $table, array $array, bool $base = false): string {
        $sql = "ALTER TABLE `" . $this->getFullTable($table, $base) . "`";
        foreach ($array as $k => $v) {
            $default = ($v['default'] ?? '');
            if ($default == null || strtolower($default) == 'null') {
                $default = "NULL";
            } else if (!empty($default)) {
                $default = "'" . $default . "'";
            }
            $sql .= " ADD `" . $k . "`";
            $sql .= (!empty($type = ($v['type'] ?? '')) ? (" " . $type) : "");//int(11)
            $sql .= (!empty($charset = ($v['charset'] ?? '')) ? (" CHARACTER SET " . $charset) : " CHARACTER SET utf8mb4");//字符集
            $sql .= (!empty(($v['null'] ?? '')) ? (" NOT NULL") : "");//是否能为空
            $sql .= (!empty($default) ? (" DEFAULT " . $default) : "");//默认值
            $sql .= !empty(($v['time'] ?? '')) ? " ON UPDATE CURRENT_TIMESTAMP" : "";//根据当前时间戳更新
            $sql .= (!empty($comment = ($v['comment'] ?? '')) ? (" COMMENT '" . $comment . "'") : "");//备注
            $sql .= ",";
        }
        return trim(trim($sql), ",") . ";";
    }

    /**
     * 修改字段
     * @param string $table 表单名
     * @param array $array
     * @param bool $base 是否添加数据库名
     * @return string
     */
    public function edits(string $table, array $array, bool $base = false): string {
        $sql = "ALTER TABLE `" . $this->getFullTable($table, $base) . "`";
        foreach ($array as $k => $v) {
            $sql .= ($this->edit('', [$k => ($v['name'] ?? '')], [
                    'type' => ($v['type'] ?? ''),
                    'default' => ($v['default'] ?? ''),
                    'comment' => ($v['comment'] ?? '')
                ])) . ",";
        }
        return trim(trim($sql, ","));
    }

    /**
     * 修改单个字段
     * @param string $table 表单名
     * @param array|string $array string=字段,array=['旧名字'=>'新的名字']
     * @param array|string $type string=字段类型,array=['type' => 'int(11)','default' => 0,'comment' => 'demo field']
     * @param bool $base 是否添加数据库名
     * @return string
     */
    public function edit(string $table, array|string $array, array|string $type, bool $base = false): string {
        $sql = !empty($table) ? "ALTER TABLE `" . $this->getFullTable($table, $base) . "`" : "";
        if (is_array($array)) {
            $field = key($array);
            $new_field = $array[$field];
        } else {
            $field = $array;
        }
        if (!empty($new_field)) {
            $sql .= " CHANGE COLUMN `" . $field . "` `" . $new_field . "`";
        } else {
            $sql .= " MODIFY COLUMN `" . $field . "`";
        }
        if (is_array($type)) {
            $default = $type['default'] ?? '';
            if ($default == null || strtolower($default) == 'null') {
                $default = "NULL";
            } else if (!empty($default)) {
                $default = "'" . $default . "'";
            }
            $comment = $type['comment'] ?? '';
            $type = $type['type'] ?? '';
            $sql .= " " . $type . "";
            $sql .= !empty($default) ? " DEFAULT " . $default . "" : "";
            $sql .= !empty($comment) ? " COMMENT '" . $comment . "'" : "";
        } else {
            $sql .= " " . $type;
        }
        return $sql;
    }

    /**
     * 删除列
     * @param string $table 表单名
     * @param array|string $array ['field1','field2'],field1,field2
     * @param bool $base 是否添加数据库名
     * @return string
     */
    public function del(string $table, array|string $array, bool $base = false): string {
        $sql = "ALTER TABLE `" . $this->getFullTable($table, $base) . "`";
        $array = is_array($array) ? $array : explode(',', $array);
        foreach ($array as $v) {
            $sql .= " DROP COLUMN `" . $v . "`,";
        }
        return trim(trim($sql), ",") . ";";
    }

    /**
     * 删除表单
     * @param string|array $table 表单名
     * @param bool $base 是否添加数据库名
     * @return string
     */
    public function delete(string|array $table, bool $base = false): string {
        if (is_array($table)) {
            $sql = "";
            foreach ($table as $v) {
                $sql .= "DROP TABLE IF EXISTS `" . $this->getFullTable($v, $base) . "`;";
                $sql .= "\r\n";
            }
        } else {
            $sql = "DROP TABLE IF EXISTS `" . $this->getFullTable($table, $base) . "`;";
        }
        return $sql;
    }

    /**
     * 获取表单字段信息
     * @param string $table 表单
     * @param string $base 数据库名
     * @param string $columns
     * @return string
     */
    public function getTabInfoSql(string $table, string $base = "", string $columns = "*"): string {
        $sql = "SELECT {$columns} FROM INFORMATION_SCHEMA.COLUMNS";
        if (!empty($base)) {
            $sql .= " WHERE table_schema='" . $base . "' AND table_name='" . $this->getFullTable($table) . "'";
        } else {
            $sql .= " WHERE table_name='" . $this->getFullTable($table) . "'";
        }
        return trim($sql) . ";";
    }

    /**
     * 获取数据库全部表单信息
     * @param string|bool $base 数据库名称,true=全部,false=当前,string=指定
     * @return string
     */
    public function getBaseSql(string|bool $base = false): string {
        $sql = "SELECT * FROM information_schema.TABLES";
        if ($base === false) {
            $sql .= " WHERE table_schema='" . $this->config['database'] . "'";
        } else if (!empty($base) && !empty(is_string($base))) {
            $sql .= " WHERE table_schema='" . $base . "'";
        }
        return trim($sql) . ";";
    }
}