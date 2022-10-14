<?php

namespace zhqing\web;

use zhqing\extend\Frame;

class Session {
    /**
     * 单个设置Session
     * @param array|string|int $key
     * @param mixed $value
     */
    public static function set(array|string|int $key, mixed $value = null) {
        self::config();
        if (empty(\is_array($key))) {
            $_SESSION[$key] = $value;
        } else if (!empty($key)) {
            foreach ($key as $k => $v) {
                $_SESSION[$k] = $v;
            }
        }
    }

    /**
     * 读取Session
     * @param array|string|int|null $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(array|string|int|null $key = null, mixed $default = ''): mixed {
        self::config();
        return Frame::getArr($_SESSION, $key, $default);
    }

    /**
     * 删除Session
     * @param array|string|int|null $key
     */
    public static function delete(array|string|int|null $key = null) {
        self::config();
        if (!isset($key)) {
            $_SESSION = [];
        } elseif (empty(\is_array($key))) {
            if (isset($_SESSION[$key])) {
                unset($_SESSION[$key]);
            }
        } else if (!empty($key)) {
            foreach ($key as $v) {
                unset($_SESSION[$v]);
            }
        }
    }

    /**
     * 获取并删除某个值
     * @param array|string|int|null $key
     * @param mixed $default
     * @return mixed
     */
    public static function pull(array|string|int|null $key, mixed $default = ''): mixed {
        $GetSession = self::get($key, $default);
        self::delete($key);
        return $GetSession;
    }

    /**
     * 判断Session数据是否存在
     * @param array|string|int|null $key
     * @return bool
     */
    public static function has(array|string|int|null $key): bool {
        self::config();
        return isset($_SESSION[$key]);
    }


    /**
     * 获取SessionId
     * @return string|bool
     */
    public static function sessionId(): string|bool {
        self::config();
        return \session_id();
    }

    private static function config() {
        if (\session_status() !== PHP_SESSION_ACTIVE) {
            \session_start();
        }
    }
}