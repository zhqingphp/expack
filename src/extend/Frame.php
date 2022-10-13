<?php

namespace zhqing\extend;

use zhqing\extend\frame\Decompression;
use zhqing\extend\frame\Mime;
use zhqing\extend\frame\Browser;
use zhqing\extend\frame\Sort;
use zhqing\extend\frame\Upload;
use zhqing\extend\frame\Arrays;
use zhqing\extend\frame\File;
use zhqing\extend\frame\Acme;
use zhqing\extend\frame\Wait;
use zhqing\extend\frame\Xml;
use zhqing\extend\frame\Tool;

class Frame {
    use Acme, File, Upload, Browser, Arrays, Decompression, Mime, Wait, Sort, Xml, Tool;

    /**
     * 获取url详细
     * @param $url
     * @return array
     */
    public static function getUrlArr($url): array {
        $parse = \parse_url($url);
        $array['get'] = [];
        if (!empty($array['query'] = $parse['query'] ?? '')) {
            \parse_str($array['query'], $data);
            $array['get'] = $data;
        }
        $array['url'] = ($parse['scheme'] ?? 'https') . '://' . ($parse['host'] ?? '') . '/';
        $array['path'] = ($parse['path'] ?? '');
        $array['fragment'] = ($parse['fragment'] ?? '');
        return $array;
    }

    /**
     * 获取本周星期(1-7)的日期
     * @param int $s 要获取的星期(1-7)
     * @param int $data //当前时间
     * @return int
     */
    public static function getWDate(int $s = 1, int $data = 0): int {
        return ($data > 0 ? $data : time()) - (60 * 60 * 24 * ((date('w', ($data > 0 ? $data : time())) ?: 7) - $s));
    }

    /**
     * 金额转换
     * 转换回来第三个为空
     * @param $int
     * @param int $decimals
     * @param string $separator
     * @param string $thousands
     * @return string
     */
    public static function money($int, int $decimals = 2, string $thousands = ',', string $separator = '.'): string {
        return number_format($int, $decimals, $separator, $thousands);
    }

    /**
     * 金额转换
     * 强制加小数点：sprintf("%01.2f", 0) 显示0.00
     * 补够4位：sprintf("%04d", 2) 显示0002
     * @param $int
     * @return string
     */
    public static function amount($int): string {
        return sprintf("%01.2f", round($int, 2));
    }

    /**
     * 读取数组
     * @param array $data
     * @param null|string|int $key
     * @param mixed $default
     * @return mixed
     */
    public static function getArr(array $data, null|string|int $key = null, mixed $default = ''): mixed {
        return empty(isset($key)) ? $data : (isset($data[$key]) ? ($data[$key] ?: $default) : $default);
    }

    /**
     * 数组转Json
     * @param $data
     * @param bool $type
     * @return false|string
     */
    public static function json($data, bool $type = true): bool|string {
        return $type ? \json_encode($data, JSON_NUMERIC_CHECK + JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES) : \json_encode($data, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
    }

    /**
     * 判断字符串是否json,返回array
     * @param mixed $data
     * @param bool $type
     * @return mixed
     */
    public static function isJson(mixed $data, bool $type = true): mixed {
        $data = \json_decode((is_string($data) ? ($data ?: '') : ''), $type);
        return (($data && \is_object($data)) || (\is_array($data) && $data)) ? $data : [];
    }

    /**
     * 替换内容
     * @param string $str
     * @param string $old
     * @param string $new
     * @return string
     */
    public static function strRep(string $str, string $old, string $new = ''): string {
        return \str_replace($old, $new, $str);
    }

    /**
     * 获取格式
     * @param $str
     * @return string|array
     */
    public static function getPath($str): string|array {
        return \pathinfo($str, PATHINFO_EXTENSION);
    }

    /**
     * 删除格式
     * @param string $str
     * @param null $format
     * @return string
     */
    public static function delPath(string $str, $format = null): string {
        $format = !isset($format) ? self::getPath($str) : $format;
        return \trim(($format ? \substr($str, 0, (\strlen($str) - \strlen($format) - 1)) : $str), '.');
    }

    /**
     * 获取文件名
     * @param string $str 字符串
     * @param string|null $format 是否删除格式(结尾要删除的内容)
     * @return string
     */
    public static function baseName(string $str, string $format = null): string {
        return \basename($str, $format);
    }

    /**
     * 异常处理
     * @param $method
     * @param mixed $Error
     * @return mixed
     */
    public static function tryCatch($method, mixed $Error = ''): mixed {
        try {
            return $method();
        } catch (\Exception | \Error $E) {
            return $Error ? ($Error()) : $E;
        }
    }

    /**
     * 获取缓冲区内容
     * @param $data //闭包
     * @return mixed
     */
    public static function obCache($data): mixed {
        \ob_start();
        if (\is_callable($data)) {
            $data();
        } else {
            echo $data;
        }
        $content = \ob_get_contents();
        \ob_end_clean();
        return $content;
    }


    /**
     * @param $file
     * @return false|string
     */
    public static function isFile($file): bool|string {
        return realpath($file);
    }

    /**
     * @param $file
     * @return mixed
     */
    public static function retFile($file): mixed {
        if (!empty($file = self::isFile($file))) {
            return require $file;
        }
        return [];
    }

    /**
     * 替换内容
     * @param $str
     * @param $old
     * @param $new
     * @return string
     */
    public static function strTr($str, $old, $new): string {
        return \strtr($str, $old, $new);
    }


    /**
     * 是否包含
     * @param $str
     * @param $in
     * @param false $type //是否使用逗号
     * @return bool
     */
    public static function strIn($str, $in, bool $type = false): bool {
        $str = !empty($type) ? "," . $str . "," : $str;
        $in = !empty($type) ? "," . $in . "," : $in;
        if (str_contains($str, $in)) {
            return true;
        }
        return false;
    }


    /**
     * 将字符串中的连续多个空格转换为一个空格
     * @param $str
     * @return string
     */
    public static function mergeSpaces($str): string {
        return \preg_replace("/\s(?=\s)/", "\\1", $str);
    }

    /**
     * @param $str
     * @return array|string
     */
    public static function trim($str): array|string {
        return str_replace([" ", "　", "\t", "\n", "\r"], ["", "", "", "", ""], $str);
    }

    /**
     * 数组转Json格式化
     * @param $data
     * @param bool $type 是否强制int
     * @return string
     */
    public static function jsonFormat($data, bool $type = true): string {
        \array_walk_recursive($data, function (&$val) {
            if (!empty($val) && $val !== true && !\is_numeric($val)) {
                $val = \urlencode($val);
            }
        });
        $data = (empty($type)) ? \json_encode($data, JSON_PRETTY_PRINT) : \json_encode($data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
        return \urldecode($data);
    }

    /**
     * 获取解析IP
     * @param $data
     * @return string
     */
    public static function getHostIp($data): string {
        return \gethostbyname($data);
    }

    /**
     * 生成Token
     * @return string
     */
    public static function getToken(): string {
        return \sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', \mt_rand(0, 65535), \mt_rand(0, 65535), \mt_rand(0, 65535), \mt_rand(16384, 20479), \mt_rand(32768, 49151), \mt_rand(0, 65535), \mt_rand(0, 65535), \mt_rand(0, 65535));
    }


    /**
     * 文件夹不存在创建文件夹(无限级)
     * @param $dir
     * @return bool
     */
    public static function mkDir($dir): bool {
        return (!empty(\is_dir($dir)) || \mkdir($dir, 0777, true));
    }


    /**
     * 计算crc32
     * hash('crc32b', $str)
     * @param $str
     * @return string
     */
    public static function strCrc($str): string {
        return \dechex(\crc32($str));
    }


    /**
     * 获取二个符号之间的内容
     * @param $str
     * @param string $one
     * @param string $two
     * @return bool|false|string
     */
    public static function signData($str, string $one = '(', string $two = ')'): bool|string {
        $onePos = \stripos($str, $one);
        $twoPos = \stripos($str, $two);
        if (($onePos === false || $twoPos === false) || $onePos >= $twoPos) {
            return false;
        }
        return \substr($str, ($onePos + 1), ($twoPos - $onePos - 1));
    }


    /**
     * 是否cli
     * @return bool
     */
    public static function isCli(): bool {
        return (bool)\preg_match("/cli/i", \php_sapi_name());
    }

    /**
     * 随机生成ip
     * @return bool|string
     */
    public static function RandIp(): bool|string {
        $ip_long = array(
            array('607649792', '608174079'), // 36.56.0.0-36.63.255.255
            array('1038614528', '1039007743'), // 61.232.0.0-61.237.255.255
            array('1783627776', '1784676351'), // 106.80.0.0-106.95.255.255
            array('2035023872', '2035154943'), // 121.76.0.0-121.77.255.255
            array('2078801920', '2079064063'), // 123.232.0.0-123.235.255.255
            array('-1950089216', '-1948778497'), // 139.196.0.0-139.215.255.255
            array('-1425539072', '-1425014785'), // 171.8.0.0-171.15.255.255
            array('-1236271104', '-1235419137'), // 182.80.0.0-182.92.255.255
            array('-770113536', '-768606209'), // 210.25.0.0-210.47.255.255
            array('-569376768', '-564133889'), // 222.16.0.0-222.95.255.255
        );
        $rand_key = mt_rand(0, 9);
        return long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
    }

    /**
     * 删除url参数
     * @param $url
     * @param string $default
     * @return string
     */
    public static function delUrlGet($url, string $default = 'index.php'): string {
        $url = \str_contains($url, '?') ? \substr($url, 0, \strpos($url, '?')) : $url;
        $url = \str_contains($url, '&') ? \substr($url, 0, \strpos($url, '&')) : $url;
        $url = \str_contains($url, '#') ? \substr($url, 0, \strpos($url, '#')) : $url;
        $url = trim(trim($url, '/'));
        return (!empty($url) ? ($url != $default ? Frame::strRep($url, '//', '/') : '/') : $default);
    }

    /**
     * 对比时间
     * @param $start
     * @param $end
     * @return \DateInterval
     */
    public static function diffTime($start, $end): \DateInterval {
        return date_diff(date_create(trim($start)), date_create(trim($end)));
    }

    /**
     * 生成随机字符串
     * @param $length
     * @return string
     */
    public static function randStr($length): string {
        return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890'), 0, $length);
    }

    /**
     * 通过url获取get参数
     * @param $url
     * @return array
     */
    public static function urlToArr($url): array {
        $url = \parse_url($url);
        $query = $url['query'] ?? '';
        \parse_str($query, $data);
        return $data;
    }
}