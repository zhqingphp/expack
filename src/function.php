<?php

use zhqing\extend\Frame;
use zhqing\module\EnvHelper;

if (!function_exists('ps')) {
    function ps($data, $type = true) {
        $content = '<pre>' . print_r($data, true) . '</pre>';
        if (empty($type)) {
            return $content;
        }
        echo $content;
    }
}

if (!function_exists('ts')) {
    function ts(mixed $data = null, int $row = 30, int $cols = 200) {
        echo '<textarea rows="' . $row . '" cols="' . $cols . '">' . $data . '</textarea>';
    }
}
if (!function_exists('es')) {
    function es(mixed $data = null, int $row = 30, int $cols = 200) {
        return '<textarea rows="' . $row . '" cols="' . $cols . '">' . $data . '</textarea>';
    }

}
if (!function_exists('loadJump')) {
    function loadJump($title, $url, $time = 15) {
        require __DIR__ . '/../file/load.php';
    }

}

if (!function_exists('toArr')) {
    function toArr($data) {
        return !empty($data) ? $data->toArray() : [];
    }
}

/**
 * 生成12/13位时间
 * @param null|string|int|float $time
 * @return string
 */

if (!function_exists('getTime')) {
    function getTime(null|string|int|float $time = null): string {
        list($t1, $t2) = explode(" ", (!empty($time) ? $time : microtime()));
        $timeArr = explode(".", $t2 . ($t1 * 1000));
        return $timeArr[key($timeArr)];
    }
}

/**
 * 生成13位时间
 * @param string|int|float|null $time
 * @return float
 */

if (!function_exists('seekTime')) {
    function seekTime(null|string|int|float $time = null): float {
        list($t1, $t2) = explode(" ", (!empty($time) ? $time : microtime()));
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }
}

/**
 * 13位时间转10位
 * @param $time
 * @return float|int
 */
if (!function_exists('strToDate')) {
    function strToDate($time): float|int {
        return (ceil($time / 1000));
    }
}

/**
 * 13位转时间
 */
if (!function_exists('timeToDate')) {
    function timeToDate($time): string {
        return seekDate(strToDate($time));
    }
}
/**
 * @param $data
 * @return string
 */
if (!function_exists('packNum')) {
    function packNum($data) {
        return \bin2hex(\pack('N', $data));
    }
}
/**
 * 获取当前时间
 * @param string|int $time
 * @return string
 */

if (!function_exists('seekDate')) {
    function seekDate(string|int $time = ''): string {
        return date("Y-m-d H:i:s", (!empty($time) ? $time : time()));
    }
}

/**
 * 当天时间
 * @param int|string $time
 * @return array
 */
if (!function_exists('dayTime')) {
    function dayTime(int|string $time = 0): array {
        $time = is_string($time) ? strtotime($time) : ($time > 0 ? $time : time());
        $data['top'] = date('Y-m-d 00:00:00', $time);
        $data['end'] = date('Y-m-d 23:59:59', $time);
        return $data;
    }
}
/**
 * 本周时间
 * @param int|string $time
 * @return array
 */
if (!function_exists('weekTime')) {
    function weekTime(int|string $time = 0): array {
        $time = is_string($time) ? strtotime($time) : ($time > 0 ? $time : time());
        $data['top'] = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $time), date('d', $time) - date('w', $time) + 1, date('Y', $time)));
        $data['end'] = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', $time), date('d', $time) - date('w', $time) + 7, date('Y', $time)));
        return $data;
    }
}

/**
 * 本月时间
 * @param int|string $time
 * @return array
 */
if (!function_exists('monthTime')) {
    function monthTime(int|string $time = 0): array {
        $time = is_string($time) ? strtotime($time) : ($time > 0 ? $time : time());
        $data['top'] = date('Y-m-01 00:00:00', $time);
        $data['end'] = date('Y-m-t 23:59:59', $time);
        return $data;
    }
}
/**
 * 判断是否Cli
 */
if (!function_exists('isCli')) {
    function isCli(): bool|int {
        return preg_match("/cli/i", php_sapi_name());
    }
}

/**
 * 通过a.b.c.d生成多维数组
 * @param $key //名字
 * @param $data //内容
 * @return mixed
 */

if (!function_exists('setArr')) {
    function setArr($key, $data): mixed {
        return Frame::setStrArr($key, $data);
    }
}
/**
 * 通过a.b.c.d获取数组内容
 * @param array $data //要取值的数组
 * @param string|int|null $key //支持aa.bb.cc.dd这样获取数组内容
 * @param $default //默认值
 * @return mixed
 */
if (!function_exists('getArr')) {
    function getArr(array $data, string|int|null $key = null, $default = null): mixed {
        return Frame::getStrArr($data, $key, $default);
    }
}
if (!function_exists('resCome')) {
    function resCome($data, $msg = ''): array {
        return ['code' => 200, 'data' => $data, 'msg' => $msg];
    }
}

if (!function_exists('resErr')) {
    function resErr($msg, $code = 400, $data = []): array {
        return ['code' => $code, 'data' => $data, 'msg' => $msg];
    }
}

if (!function_exists('getOrderId')) {
    function getOrderId(string $prefix = ''): string {
        list($m, $s) = explode(' ', microtime());
        return strtoupper($prefix . \chr(\rand(65, 90)) . \bin2hex(\chr(\rand(65, 90)) . \pack('N', substr(((float)sprintf('%.0f', (floatval($m) + floatval($s)) * 1000)), 0, 13))));
    }
}


if (!function_exists('getNowTime')) {
    /**
     * @param $type //格式
     * @param string|array|int $format string|array=返回格式,int=设置时间
     * @param int|string $time 设置时间
     * @return array
     */
    function getNowTime($type, string|array|int $format = '', int|string $time = 0): array {
        return Frame::getDateTime($type, $format, $time);
    }
}

if (!function_exists('repBody')) {
    /**
     * 替换内容
     * @param string $body //内容
     * @param array $preg //要替换的内容['旧内容'=>'新内容']
     * @return string
     */
    function repBody(string $body, array $preg): string {
        return Frame::repBody($body, $preg);
    }
}

if (!function_exists('newEnv')) {
    /**
     * 实例env
     * @param string $env_file
     * @return EnvHelper
     */
    function newEnv(string $env_file): EnvHelper {
        return (new EnvHelper($env_file));
    }
}