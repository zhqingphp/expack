<?php

namespace zhqing\module;

use zhqing\extend\Frame;

/**
 * 银行列表
 */
class Bank {
    public static array $data = [];

    /**
     * 获取银行列表
     * @return array
     */
    public static function get(): array {
        if (empty(self::$data)) {
            self::$data = Frame::isJson(@file_get_contents(__DIR__ . '/../../file/bank.json'));
        }
        return self::$data;
    }

    /**
     * 通过名称获取代码
     * @param string $title
     * @param string $default
     * @return mixed
     */
    public static function getCode(string $title, string $default = ''): mixed {
        $data = self::get();
        foreach ($data as $k => $v) {
            if ($v['title'] == trim($title)) {
                return $v['code'];
            }
        }
        return $default;
    }

    /**
     * 通过代码获取名称
     * @param string $code
     * @param string $default
     * @return mixed
     */
    public static function getTitle(string $code, string $default = ''): mixed {
        $data = self::get();
        foreach ($data as $k => $v) {
            if ($v['code'] == trim($code)) {
                return $v['title'];
            }
        }
        return $default;
    }
}