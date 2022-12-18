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
 * 设置cli颜色
 * @param $data
 * @param int $type 1-9不同色彩
 * @return string
 */

if (!function_exists('cliColor')) {
    function cliColor($data, int $type = 1): string {
        return (isCli() ? ("\033[38;5;" . $type . ";1m" . $data . "\033[0m") : ($data));
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