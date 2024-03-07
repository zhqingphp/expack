<?php

use zhqing\module\EnvHelper;

if (!function_exists('set_env')) {
    /**
     * 保存env
     * @param string|array $key
     * @param mixed $data
     * @return int
     */
    function set_env(string|array $key, mixed $data = ""): int {
        return EnvHelper::set($key, $data);
    }
}

if (!function_exists('get_env')) {
    /**
     * 获取env
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    function get_env(string $key = '', mixed $default = null): mixed {
        return EnvHelper::get($key, $default);
    }
}

if (!function_exists('del_env')) {
    /**
     * 删除env
     * @param string $key
     * @return int
     */
    function del_env(string $key): int {
        return EnvHelper::del($key);
    }
}

if (!function_exists('getProtocol')) {
    function getProtocol($domain = ''): string {
        $domain = !empty($domain) ? $domain : request()->header('referer', getenv('APP_HTTP') . '://');
        return join(array_slice(explode('://', $domain), 0, 1));
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
