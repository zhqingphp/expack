<?php

namespace zhqing\web;

use zhqing\extend\Frame;

class Request {
    /**
     * 获取ip
     * @return string
     */
    public static function getIp(): string {
        $header = [
            'HTTP_CF_CONNECTING_IP',
            'REMOTE_ADDR',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CDN_SRC_IP',
            'HTTP_PROXY_CLIENT_IP',
            'HTTP_WL_PROXY_CLIENT_IP',
            'HTTP_CLIENT_IP',
        ];
        $client_ip = 'unknown';
        foreach ($header as $key) {
            $ip = Frame::getArr($_SERVER, $key);
            if ($ip && preg_match("/^[\d]+\.[\d]+\.[\d]+\.[\d]+$/isU", $ip)) {
                $client_ip = $ip;
                break;
            }
        }
        return $client_ip;
    }

    /**
     * 获取当前域名
     * @param bool $type //true不带端口
     * @return mixed
     */
    public static function getHost(bool $type = false): mixed {
        return $type ? Frame::getArr($_SERVER, 'SERVER_NAME', false) : Frame::getArr($_SERVER, 'HTTP_HOST', false);
    }

    /**
     * 获取GET
     * @param string|int|null $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string|int|null $key = null, mixed $default = ''): mixed {
        return Frame::getArr($_GET, $key, $default);
    }

    /**
     * 获取POST
     * @param string|int|null $key
     * @param mixed $default
     * @return mixed
     */
    public static function post(string|int|null $key = null, mixed $default = ''): mixed {
        return Frame::getArr($_POST, $key, $default);
    }

    /**
     * 获取头部信息
     * @param string|int|null $key
     * @param mixed $default
     * @param array $headData
     * @return mixed
     */
    public static function getHeader(string|int|null $key = null, mixed $default = '', array $headData = []): mixed {
        foreach ($_SERVER as $k => $v) {
            if (\str_starts_with($k, 'HTTP_')) {
                $headData[\strtolower(Frame::strRep(Frame::strRep(\substr($k, 5), '_', ' '), ' ', '-'))] = $v;
            }
        }
        return Frame::getArr($headData, ($key ? \strtolower(Frame::strRep($key, '_', '-')) : $key), $default);
    }

    /**
     * 获取当前URl get.php?uid=10&type=2时将返回get.php?uid=10&type=2
     * @return mixed
     */
    public static function getUri(): mixed {
        return Frame::getArr($_SERVER, 'REQUEST_URI', '');
    }

    /**
     * 获取当前URl 请求路径 get.php?uid=10&type=2时将返回/user/get.php
     * @return string
     */
    public static function getPath(): string {
        if (isset($_SERVER['REDIRECT_URL'])) {
            return $_SERVER['REDIRECT_URL'];
        } else if (isset($_SERVER['PATH_INFO'])) {
            return $_SERVER['PATH_INFO'];
        }
        return '/';
    }

    /**
     * 获取当前URl get.php?uid=10&type=2时将返回uid=10&type=2
     * @return mixed
     */
    public static function getQueryString(): mixed {
        return Frame::getArr($_SERVER, 'REDIRECT_QUERY_STRING', Frame::getArr($_SERVER, 'QUERY_STRING', ''));
    }

    /**
     * 获取请求方法 返回值可能是GET、POST、PUT、DELETE、OPTIONS、HEAD中的一个
     * @return mixed
     */
    public static function getMethod(): mixed {
        return Frame::getArr($_SERVER, 'REQUEST_METHOD', 'GET');
    }

    /**
     * 获取 Http Body
     * @return mixed
     */
    public static function getBody(): mixed {
        return \file_get_contents("php://input");
    }

    /**
     * 获取上传文件
     * @param mixed $key
     * @param mixed $default
     * @return array|string
     */
    public static function getFile(mixed $key = null, mixed $default = ''): array|string {
        return Frame::getArr($_FILES, $key, $default);
    }

    /**
     * 获取本地ip
     * @return array|string
     */
    public static function getLocalIp(): array|string {
        return Frame::getArr($_SERVER, 'SERVER_ADDR');
    }

    /**
     * 获取协议
     * @return array|string
     */
    public static function getScheme(): array|string {
        return Frame::getArr($_SERVER, 'REQUEST_SCHEME');
    }

    /**
     * 获取请求HTTP版本
     */
    public static function getProtocolVersion(): string {
        $version = Frame::getArr($_SERVER, 'SERVER_PROTOCOL');
        $versionData = \substr(\strstr($version, 'HTTP/'), 5);
        return $versionData ?: '1.0';
    }

    /**
     *
     * @return string
     */
    public static function getDomain(): string {
        return self::getScheme() . '://' . self::getHost() . '/';
    }

    /**
     * 获取来源
     * @return mixed
     */
    public static function getRefer(): mixed {
        $referer = self::getHeader('referer');
        return (!empty($referer) ? $referer : self::getHeader('origin'));
    }

    /**
     * @return array
     */
    public static function getServer(): array {
        return $_SERVER;
    }

    /**
     * 判断是否是ajax请求
     * @return bool
     */
    public static function isAjax(): bool {
        return self::getHeader('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * 判断是否是pjax请求
     * @return bool
     */
    public static function isPjax(): bool {
        return (bool)self::getHeader('X-PJAX');
    }
}