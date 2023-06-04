<?php

namespace zhqing\module;

use zhqing\extend\Frame;

class ipIdea {
    public static array $arr = [];

    /**
     * @return array
     */
    public static function getArr(): array {
        $data = Frame::getStrArr(Frame::isJson(@file_get_contents(__DIR__ . '/../../file/ipIdea.json')), 'ret_data', []);
        self::$arr = [];
        foreach ($data as $v) {
            self::$arr[$v['country_code']] = $v['country_name'];
        }
        return self::$arr;
    }

}