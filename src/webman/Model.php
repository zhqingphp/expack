<?php

namespace zhqing\webman;

use zhqing\extend\Safe;
use zhqing\extend\Frame;
use support\Response;
use support\Db;

class Model extends \support\Model {
    /**
     * 获取表单信息
     * @param string|null $default //数据库配置
     * @param string|null $table //表单名称
     * @return array
     */
    public static function getTab(string|null $default = null, string|null $table = null): array {
        $data = [];
        $self = new static();
        $arr = config('database.connections');
        $connection = ((!empty($default) && isset($arr[$default])) ? $default : $self->connection);
        $table = ($arr[$connection]['prefix'] . (!empty($table) ? $table : $self->getTable()));
        $array = Db::connection($connection)->select('SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name="' . $table . '"');
        foreach ($array as $v) {
            $data[$v->COLUMN_NAME] = $v;
        }
        return $data;
    }

    /**
     * 获取数据库信息
     * @param string|null $default //数据库配置
     * @return array
     */
    public static function getBase(string|null $default = null): array {
        $data = [];
        $self = new static();
        $arr = config('database.connections');
        $connection = ((!empty($default) && isset($arr[$default])) ? $default : $self->connection);
        $table = $arr[$connection]['database'];
        $array = Db::connection($connection)->select('SELECT * FROM information_schema.TABLES WHERE table_schema="' . $table . '"');
        $prefix = $arr[$connection]['prefix'];
        foreach ($array as $v) {
            $data[substr($v->TABLE_NAME, strlen($prefix))] = $v;
        }
        return $data;
    }

    /**
     * 获取表单名称
     * @param string|null $default //数据库配置
     * @return mixed
     */
    public static function getTabName(string|null $default = null): mixed {
        $self = new static();
        $base = self::getBase($default);
        return (!empty($name = ($base[$self->getTable()]->TABLE_COMMENT ?? '')) ? $name : $self->getTable());
    }

    /**
     * 获取表单字段信息并生成
     * @param string|null $table
     * @return string
     */
    public static function getTabField(string $table = null): string {
        $self = new static();
        $base = self::getBase();
        $table = (!empty($table) ? $table : $self->getTable());
        $arr = self::getTab(null, $table);
        $html = "/**\r\n";
        $html .= " * " . ($base[$table]->TABLE_COMMENT ?? $table) . "\r\n";
        $data = "";
        foreach ($arr as $k => $v) {
            $html .= " * @property " . ($v->DATA_TYPE == 'int' ? 'int' : 'string') . " \$" . $k . " " . $v->COLUMN_COMMENT . "(" . $v->COLUMN_TYPE . ")\r\n";
            $data .= "\$data[\"{$k}\"]=\$v[\"{$k}\"];//" . $v->COLUMN_COMMENT . "\r\n";
        }
        $html .= " */\r\n/*\r\n" . $data . "*/\r\n";
        return $html;
    }
}