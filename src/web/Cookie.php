<?php

namespace zhqing\web;

use zhqing\extend\Frame;

class Cookie {

    /**
     * 单个存储
     * @param mixed $key cookie名称
     * @param mixed $value cookie值
     * @param int $maxAge cookie过期时间
     * @param string $path 有效的服务器路径
     * @param string $domain 有效域名/子域名
     * @param bool $secure 是否仅仅通过HTTPS
     * @param bool $only 仅可通过HTTP访问
     */
    public static function set(mixed $key, mixed $value = '', int $maxAge = 0, string $path = '/', string $domain = '', bool $secure = false, bool $only = false) {
        if (empty(\is_array($key))) {
            \setcookie($key, $value, ($maxAge > 0 ? \time() + $maxAge : $maxAge), $path, $domain, $secure, $only);
        } else if (!empty($key)) {
            foreach ($key as $k => $v) {
                \call_user_func_array('self::set', $v);
            }
        }
    }

    /**
     * 读取
     * @param array|string|int|null $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(array|string|int|null $key = null, mixed $default = ''): mixed {
        return Frame::getArr($_COOKIE, $key, $default);
    }

    /**
     * 单个、多个、删除数据
     * @param array|string|int|null $key
     */
    public static function delete(array|string|int|null $key = null) {
        if (!isset($key)) {
            if (!empty($_COOKIE)) {
                foreach ($_COOKIE as $k => $v) {
                    self::set($k, '', -10);
                }
            }
        } elseif (empty(\is_array($key))) {
            if (isset($_COOKIE[$key])) {
                self::set($key, '', -10);
            }
        } else if (!empty($key)) {
            foreach ($key as $v) {
                self::set($v, '', -10);
            }
        }
    }

    /**
     * 获取并删除
     * @param array|string|int|null $key
     * @param mixed $default
     * @return mixed
     */
    public static function pull(array|string|int|null $key, mixed $default = ''): mixed {
        $cookie = self::get($key, $default);
        self::delete($key);
        return $cookie;
    }

    /**
     * 判断是否存在
     * @param array|string|int|null $key
     * @return bool
     */
    public static function has(array|string|int|null $key): bool {
        return isset($_COOKIE[$key]);
    }
}