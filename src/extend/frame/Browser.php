<?php

namespace zhqing\extend\frame;

trait Browser {
    /**
     * 获取顶级域名
     * @param string $url
     * @return string
     */
    public static function getOneDomain(string $url): string {
        $arr = explode('.', $url);
        return join('.', array_splice($arr, -2));
    }

    /**
     * 获取字符串当前域名
     * @param $url //字符串
     * @param false $type //是否返回string
     * @return array|string|false //默认返回array
     */
    public static function getDomain($url, bool $type = false): bool|array|string {
        $web = \explode('//', $url);
        if (!isset($web[1])) {
            return false;
        }
        $dom = \explode('/', $web[1]);
        if (!isset($dom[key($dom)])) {
            return false;
        }
        return ($type ? ($web[key($web)] . '//' . $dom[key($dom)]) : ['http' => $web[key($web)], 'domain' => $dom[key($dom)]]);
    }


    /**
     * 限制来源
     * @param array|string $list *=全部开放 支持*.域名
     * @param string $referer //来源域名
     * @param int $i
     * @return bool false=允许访问，true=禁止访问
     */
    public static function checkOrigin(array|string $list, string $referer, int $i = 0): bool {
        if ($list != '*') {
            if (!empty($referer)) {
                $Restricted = $list;
                if (empty(is_array($list))) {
                    $Restricted = explode(',', $list);
                }
                $arr = explode("://", $referer);
                $array = explode("/", end($arr));
                $origin = $array[key($array)];
                $originArr = explode('.', $origin);
                foreach ($Restricted as $v) {
                    if (str_starts_with($v, '*.')) {
                        if (join('.', array_slice($originArr, '-' . count(explode(".", substr($v, 2))))) == substr($v, 2)) {
                            $i++;
                        }
                    } else if ($origin == $v) {
                        $i++;
                    }
                }
            }
            return empty($i > 0);
        }
        return false;
    }
}