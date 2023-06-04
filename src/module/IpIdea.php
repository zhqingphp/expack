<?php

namespace zhqing\module;

use zhqing\extend\Frame;

/**
 * https://www.ipidea.net/
 */
class IpIdea {
    /**
     * 动态数据中心
     * @param string $user //帐号
     * @param string $pass //密码
     * @param string $region //选择地区
     * @param string $ip //ip
     * @param string $port //端口
     * @return array
     */
    public static function static(string $user, string $pass, string $region = '', string $ip = '', string $port = ''): array {
        $data['ip'] = 'na.ipidea.io';
        $data['port'] = '2336';
        $data['userPass'] = $user . '-zone-static' . (!empty($region) ? ('-region-' . $region) : '') . ':' . $pass;
        $data['type'] = 'http';
        $data['auth'] = 'basic';
        return $data;
    }

    /**
     * 全球动态
     * @param string $user //帐号
     * @param string $pass //密码
     * @param string $region //选择地区
     * @param string $ip //ip
     * @param string $port //端口
     * @return array
     */
    public static function custom(string $user, string $pass, string $region = '', string $ip = '', string $port = ''): array {
        $data['ip'] = $ip;
        $data['port'] = $port;
        $data['userPass'] = $user . '-zone-custom' . (!empty($region) ? ('-region-' . $region) : '') . ':' . $pass;
        $data['type'] = 'http';
        $data['auth'] = 'basic';
        return $data;
    }

    /**
     * 获取地区列表
     * @param array $arr
     * @return array
     */
    public static function getArr(array $arr = []): array {
        $arr = [];
        $dir = __DIR__ . '/../../file/';
        $file = rtrim($dir, '/') . '/IpIdea_code.json';
        if (empty(is_file($file))) {
            $data = Frame::getStrArr(Frame::isJson(@file_get_contents(rtrim($dir, '/') . '/IpIdea.json')), 'ret_data', []);
            foreach ($data as $v) {
                $arr = [$v['country_code']] = $v['country_name'];
            }
            file_put_contents($file, Frame::json($arr));
        } else {
            $arr = Frame::isJson(@file_get_contents($file));
        }
        return $arr;
    }
}