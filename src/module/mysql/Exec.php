<?php

namespace zhqing\module\mysql;

use zhqing\extend\Frame;

class Exec extends Common {

    /**
     * @return $this
     */
    public function setThis(): static {
        $this->mysql->Execs = $this;
        return $this;
    }

    /**
     * 生成表单
     * @return string
     */
    public function exec(): string {
        if (!empty($this->mysql->config)) {
            foreach ($this->mysql->config as $v) {
                $mode = Frame::getStrArr($v, 'mode');
                $table = $this->mysql->prefix . trim(Frame::getStrArr($v, 'table'));
                if ($mode == 'create') {
                    $this->mysql->sql .= "\r\n" . self::setCome('创造表单') . "\r\n" . $this->execCreate($table, $v) . "\r\n";
                } else if ($mode == 'row') {
                    $this->mysql->sql .= "\r\n" . self::setCome('表单添加列') . "\r\n" . $this->execRow($table, $v) . "\r\n";
                }
            }
            $this->mysql->config = [];
        }
        return $this->mysql->sql;
    }

    /**
     * 生成表单添加列SQL
     * ALTER TABLE back_demo ADD `aa1` int(11),  ADD `bb1` int(11);
     * @param $table
     * @param $v
     * @return string
     */
    protected function execRow($table, $v): string {
        $sql = "ALTER TABLE `{$table}`";
        $field = Frame::getStrArr($v, 'field', []);
        foreach ($field as $key => $val) {
            $sql .= " ADD " . self::fieldSql($key, $val['type'], $val['comment'], $val['def'], $val['null']) . ", ";
        }
        return trim(trim($sql), ",") . ";";
    }

    /**
     * 生成创造表单SQL
     * CREATE TABLE `back_demo` (
     * `id` int(11) NOT NULL AUTO_INCREMENT,
     * `aa` int(11),
     * `bb` int(11) NOT NULL DEFAULT 0 COMMENT '数字',
     * `cc` int(11),
     * PRIMARY KEY (`id`,`bb`) USING BTREE
     * ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
     * @param $table
     * @param $v
     * @return string
     */
    protected function execCreate($table, $v): string {
        $id = Frame::getStrArr($v, 'id');
        $type = Frame::getStrArr($v, 'type');
        $charset = Frame::getStrArr($v, 'charset');
        $engine = Frame::getStrArr($v, 'engine');
        $comment = Frame::getStrArr($v, 'comment');
        $auto = Frame::getStrArr($v, 'auto', 1);
        $field = Frame::getStrArr($v, 'field', []);
        $sql = "CREATE TABLE `{$table}` (\r\n";
        $sql .= "  `{$id}` {$type} NOT NULL AUTO_INCREMENT,\r\n";
        $primary = "`{$id}`";
        foreach ($field as $key => $val) {
            $null = $val['null'];
            if (!empty(Frame::getStrArr($val, 'key'))) {
                $null = true;
                $primary .= ",`{$key}`";
            }
            $sql .= "  " . self::fieldSql($key, $val['type'], $val['comment'], $val['def'], $null) . ",\r\n";
        }
        $primary = trim($primary, ",");
        $sql .= "  PRIMARY KEY ({$primary}) USING BTREE\r\n";
        $sql .= ") ENGINE={$engine} AUTO_INCREMENT={$auto} DEFAULT CHARSET={$charset}" . (!empty($comment) ? "COMMENT='{$comment}'" : "");
        return $sql . ";";
    }

    /**
     * 设置字段
     * @param string $field 字段
     * @param string $type 类型(长度)
     * @param string|null|int $comment 注释
     * @param string|null|int $def 默认值
     * @param bool $null 不为空?  true=必须有值,false=允许空值
     * @return string
     */
    protected static function fieldSql(string $field, string $type, string|null|int $comment, string|null|int $def, bool $null): string {
        if (is_null($def)) {
            //数字默认值为空自动设0
            $intArr = ['tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'float', 'double', 'decimal'];
            foreach ($intArr as $v) {
                if (str_starts_with($type, $v)) {
                    $def = 0;
                    break;
                }
            }
        }
        return "`{$field}` {$type}" . (!empty($null) ? " NOT NULL" : "") . (is_null($def) ? "" : " DEFAULT {$def}") . (!empty($comment) ? " COMMENT '{$comment}'" : "");
    }
}