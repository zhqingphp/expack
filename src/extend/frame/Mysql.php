<?php

namespace zhqing\extend\frame;

use zhqing\extend\Frame;

trait Mysql {
    /**
     * 通过表单代码生成数据
     * 使用 self::codeToData(['orderStatus' => 2], self::tableToCode($tableData ?? [], ['id'])['data']);
     * @param array $data 传入的数据
     * @param array $code 表单信息(通过表单信息生成相关array code代码)
     * @param array $arr 生成写入数据信息
     * @return array
     */
    public static function codeToData(array $data, array $code, array $arr = []): array {
        $date = function ($content, $format) {
            $content = ($content ?: time());
            return (is_numeric($content) ? date($format, (strlen($content) > 10 ? ceil($content / 1000) : $content)) : $content);
        };
        foreach ($code as $key => $val) {
            foreach ($val as $k => $v) {
                $name = trim($k);
                $content = self::getStrArr($data, $name, self::getStrArr($v, 'default', ''));
                if (!empty($content) || Frame::getStrArr($v, 'nullable') == 'no') {
                    if ($key == 'decimal') {
                        $content = self::money(($content ?: 0), self::getStrArr($v, 'scale', 2), '');
                    } else if ($key == 'int') {
                        $content = ($content ?: 0);
                    } else if ($key == 'blob' || $key == 'json') {
                        $content = (is_array($content) ? self::json($content) : $content);
                    } else if ($key == 'date') {
                        $content = $date($content, 'Y-m-d');
                    } else if ($key == 'year') {
                        $content = $date($content, 'Y');
                    } else if ($key == 'time') {
                        $content = $date($content, 'H:i:s');
                    } else if ($key == 'datetime' || $key == 'timestamp') {
                        $content = $date($content, 'Y-m-d H:i:s');
                    }
                    $arr[$name] = $content;
                }
            }
        }
        return $arr;
    }

    /**
     * 通过表单信息生成相关array code代码
     * @param array $data 表单信息
     * @param array $delete 排除
     * @param array $config 配置['字段类型'=>'组名']
     * @param array $arr
     * @param int $i
     * @return array
     */
    public static function tableToCode(array $data, array $delete = [], array $config = [], array $arr = [], int $i = 0): array {
        $conf = [
            'int' => ['tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'float', 'double'],
            'decimal' => ['decimal'],
            'blob' => ['blob'],
            'json' => ['json'],
            'date' => ['date'],//‘1000-01-01’ to ‘9999-12-31’
            'time' => ['time'],//‘-838:59:59’ to ‘838:59:59’
            'year' => ['year'],//1901 to 2155
            'datetime' => ['datetime'],//‘1000-01-01 00:00:00’ to ‘9999-12-31 23:59:59’
            'timestamp' => ['timestamp']//‘1970-01-01 00:00:01’ UTC to ‘2038-01-19 03:14:07’ UTC
        ];
        foreach ($conf as $key => $val) {
            foreach ($val as $v) {
                $keys = strtolower($v);
                if (empty(isset($config[$keys]))) {
                    $config[$keys] = strtolower($key);
                }
            }
        }
        foreach ($data as $k => $v) {
            if (empty($delete) || empty(in_array($k, $delete))) {
                ++$i;
                $array = [];
                $type = strtolower(($v->DATA_TYPE ?? ''));
                $array['remark'] = ($v->COLUMN_COMMENT ?? '未知');
                $array['type'] = $type;
                if (!empty($precision = ($v->NUMERIC_PRECISION ?? ''))) {
                    $array['precision'] = $precision;
                }
                if (!empty($scale = ($v->NUMERIC_SCALE ?? ''))) {
                    $array['scale'] = $scale;
                }
                if (!empty($default = ($v->COLUMN_DEFAULT ?? ''))) {
                    $array['default'] = $default;
                }
                if (!empty($nullable = ($v->IS_NULLABLE ?? ''))) {
                    $array['nullable'] = strtolower($nullable);
                }
                if (!empty($key = ($v->COLUMN_KEY ?? ''))) {
                    $array['key'] = strtolower($key);
                }
                $arr[self::getStrArr($config, $type, 'string')][$k] = $array;
            }
        }
        $content['count'] = $i;
        $content['data'] = $arr;
        $content['php'] = self::arrayToString($arr);
        return $content;
    }

    /**
     * array生成字符串array
     * @param array $array //要转换的array
     * @param bool $type //是否使用var_export, array()
     * @param int $i
     * @return string
     */
    public static function arrayToString(array $array, bool $type = false, int $i = 0): string {
        if (!empty($type)) {
            return var_export($array, true);
        }
        ++$i;
        $branch = "\r\n";
        $string = "[" . $branch;
        $symbol = str_repeat('  ', $i);
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $string .= "{$symbol}'" . $key . "' => " . self::arrayToString($value, $type, $i) . "," . $branch;
            } else {
                if (is_numeric($value)) {
                    $string .= "{$symbol}'" . $key . "' => " . $value . "," . $branch;
                } else {
                    $string .= "{$symbol}'" . $key . "' => '" . addslashes($value) . "'," . $branch;
                }
            }
        }
        return rtrim($string, "," . $branch) . $branch . $symbol . ']';
    }
}