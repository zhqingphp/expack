<?php

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
if (!function_exists('rs')) {
    function rs($data) {
        return response('<pre>' . print_r($data, true) . '</pre>');
    }
}
if (!function_exists('workError')) {
    function workError() {
        $html = '<html><head><title>404 Not Found</title></head><body><center><h1>404 Not Found</h1></center><hr></body></html>';
        return \response($html, 404);
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
    function timeToDate($time): float|int {
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
 * 设置cli颜色
 * @param $data
 * @param int $type 1-9不同色彩
 * @return string
 */

if (!function_exists('cliColor')) {
    function cliColor($data, int $type = 1): string {
        $req = function_exists('request');
        return ((isCli() && (!empty($req) && empty(request()))) ? ("\033[38;5;" . $type . ";1m" . $data . "\033[0m") : ($data));
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
        $arr = \array_merge(\explode('.', $key), [$data]);
        while (\count($arr) > 1) {
            $v = \array_pop($arr);
            $k = \array_pop($arr);
            $arr[] = [$k => $v];
        }
        return $arr[\key($arr)];
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
        if (isset($key)) {
            $arr = \explode('.', $key);
            foreach ($arr as $v) {
                if (isset($data[$v])) {
                    $data = ($data[$v] ?: $default);
                } else {
                    $data = $default;
                    break;
                }
            }
        }
        return $data;
    }
}

//开启跨域
if (!function_exists('resCross')) {
    function resCross(array $header = [], $type = ''): array {
        $mime = ['xml' => ['Content-Type' => 'text/xml'], 'json' => ['Content-Type' => 'application/json']];
        return array_merge((!empty($type) ? array_merge($header, ($mime[$type] ?? [])) : $header), [
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type,Authorization,X-Requested-With,Accept,Origin,requesttype'
        ]);
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

if (!function_exists('getProtocol')) {
    function getProtocol($domain = ''): string {
        $domain = !empty($domain) ? $domain : request()->header('referer', getenv('APP_HTTP') . '://');
        return join(array_slice(explode('://', $domain), 0, 1));
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
        $time = (!empty($format) && is_numeric($format)) ? $format : (is_string($time) ? strtotime($time) : ($time > 0 ? $time : time()));
        switch ($type) {
            case 1:
                //今天
                $data['top'] = date('Y-m-d 00:00:00', $time);
                $data['end'] = date('Y-m-d 23:59:59', $time);
                break;
            case 2:
                //昨天
                $time = strtotime('-1 day', $time);
                $data['top'] = date('Y-m-d 00:00:00', $time);
                $data['end'] = date('Y-m-d 23:59:59', $time);
                break;
            case 3:
                //本周
                $data['top'] = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $time), date('d', $time) - date('w', $time) + 1, date('Y', $time)));
                $data['end'] = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', $time), date('d', $time) - date('w', $time) + 7, date('Y', $time)));
                break;
            case 4:
                //上周
                $time = strtotime('-1 week', $time);
                $data['top'] = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $time), date('d', $time) - date('w', $time) + 1, date('Y', $time)));
                $data['end'] = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', $time), date('d', $time) - date('w', $time) + 7, date('Y', $time)));
                break;
            case 5:
                //近一周
                $data['top'] = date('Y-m-d H:i:s', strtotime('-7 day', $time));
                $data['end'] = date('Y-m-d H:i:s', $time);
                break;
            case 6:
                //本月
                $data['top'] = date('Y-m-01 00:00:00', $time);
                $data['end'] = date('Y-m-t 23:59:59', $time);
                break;
            case 7:
                //上月
                $time = strtotime('-1 month', $time);
                $data['top'] = date('Y-m-01 00:00:00', $time);
                $data['end'] = date('Y-m-t 23:59:59', $time);
                break;
            case 8:
                //近一月
                $data['top'] = date('Y-m-d H:i:s', strtotime('-1 month', $time));
                $data['end'] = date('Y-m-d H:i:s', $time);
                break;
            case 9:
                //近三月
                $data['top'] = date('Y-m-d H:i:s', strtotime('-3 month', $time));
                $data['end'] = date('Y-m-d H:i:s', $time);
                break;
            case 10:
                //本季度
                $quarter = ceil((date('n', $time)) / 3);//当月是第几季度
                $data['top'] = date('Y-m-d H:i:s', mktime(0, 0, 0, $quarter * 3 - 3 + 1, 1, date('Y', $time)));
                $data['end'] = date('Y-m-d H:i:s', $time);
                break;
            case 11:
                //上季度
                $y = date('Y', $time);
                $quarter = (ceil((date('n', $time)) / 3) - 1) * 3;//上季度是第几季度
                $data['top'] = date('Y-m-d H:i:s', mktime(0, 0, 0, $quarter - 3 + 1, 1, $y));
                $data['end'] = date('Y-m-d H:i:s', mktime(23, 59, 59, $quarter, date('t', mktime(0, 0, 0, $quarter, 1, $y)), $y));
                break;
            case 12:
                //第1季度
                $y = date('Y', $time);
                $quarter = 1 * 3;//上季度是第几季度
                $data['top'] = date('Y-m-d H:i:s', mktime(0, 0, 0, $quarter - 3 + 1, 1, $y));
                $data['end'] = date('Y-m-d H:i:s', mktime(23, 59, 59, $quarter, date('t', mktime(0, 0, 0, $quarter, 1, $y)), $y));
                break;
            case 13:
                //第2季度
                $y = date('Y', $time);
                $quarter = 2 * 3;//上季度是第几季度
                $data['top'] = date('Y-m-d H:i:s', mktime(0, 0, 0, $quarter - 3 + 1, 1, $y));
                $data['end'] = date('Y-m-d H:i:s', mktime(23, 59, 59, $quarter, date('t', mktime(0, 0, 0, $quarter, 1, $y)), $y));
                break;
            case 14:
                //第3季度
                $y = date('Y', $time);
                $quarter = 3 * 3;//上季度是第几季度
                $data['top'] = date('Y-m-d H:i:s', mktime(0, 0, 0, $quarter - 3 + 1, 1, $y));
                $data['end'] = date('Y-m-d H:i:s', mktime(23, 59, 59, $quarter, date('t', mktime(0, 0, 0, $quarter, 1, $y)), $y));
                break;
            case 15:
                //第4季度
                $y = date('Y', $time);
                $quarter = 4 * 3;//上季度是第几季度
                $data['top'] = date('Y-m-d H:i:s', mktime(0, 0, 0, $quarter - 3 + 1, 1, $y));
                $data['end'] = date('Y-m-d H:i:s', mktime(23, 59, 59, $quarter, date('t', mktime(0, 0, 0, $quarter, 1, $y)), $y));
                break;
            case 16:
                //当年
                $data['top'] = date('Y-01-01 00:00:00', $time);
                $data['end'] = date('Y-m-d H:i:s', $time);
                break;
            case 17:
                //去年
                $data['top'] = date('Y-01-01 00:00:00', strtotime('-1 year', $time));
                $data['end'] = date('Y-12-31 23:59:59', strtotime('-1 year', $time));
                break;
            case 18:
                //近一年
                $data['top'] = date('Y-m-d H:i:s', strtotime('-1 year', $time));
                $data['end'] = date('Y-m-d H:i:s', $time);
                break;
            default:
                //时间
                list($t1, $t2) = explode(" ", (!empty($time) ? $time : microtime()));
                $data['top'] = (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
                $data['end'] = date(((!empty($type) && is_string($type)) ? $type : 'Y-m-d H:i:s'), $time);
                break;
        }
        if (!empty($format)) {
            if (is_array($format)) {
                $data['top'] = date($format[0], strtotime($data['top']));
                $data['end'] = date($format[1], strtotime($data['end']));
            } else if (is_string($format)) {
                $data['top'] = date($format, strtotime($data['top']));
                $data['end'] = date($format, strtotime($data['end']));
            }
        }
        return $data;
    }
}