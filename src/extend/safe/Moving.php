<?php

namespace zhqing\extend\safe;

trait Moving {
    public static array $setMov = [
        'aes' => [0, 1, 2, 3],
        'des' => [4, 5],
        'des3' => [6, 7, 8, 9],
    ];

    /**
     * 动态加密
     * @param $data
     * @param array $mode
     * @return array
     */
    public static function movEn($data, array $mode = ['aes', 'des', 'des3']): array {
        return call_user_func_array([self::class, ($mode[rand(0, (count($mode) - 1))] ?? 'des3') . 'En'], [$data]);
    }

    /**
     * @param $data
     * @param $random
     * @return string
     */
    public static function movDe($data, $random = null): string {
        if (empty($random)) {
            $random = $data['random'] ?? $random;
            $data = $data['data'] ?? $data;
        }
        $type = substr($random, 0, 1);
        if (in_array($type, self::$setMov['aes'])) {
            return self::aesDe($data, $random);
        } else if (in_array($type, self::$setMov['des'])) {
            return self::desDe($data, $random);
        }
        return self::des3De($data, $random);
    }

    /**
     * aes动态加密
     * @param $data
     * @return array
     */
    public static function aesEn($data): array {
        $rand = self::rand();
        $iv = rand(1, 16);
        $md5 = md5($rand);
        $type = rand(1, 2);
        return [
            'random' => self::randInt('aes') . $type . $rand . (strlen($iv) == 1 ? '0' . $iv : $iv),
            'data' => self::aesEncrypt((is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES) : $data), substr($md5, $iv, 16), ($type == 2 ? substr(md5($md5), $iv, 16) : ''))
        ];
    }

    /**
     * aes动态解密
     * @param $data
     * @param $random
     * @return string
     */
    public static function aesDe($data, $random = null): string {
        if (empty($random)) {
            $random = $data['random'] ?? $random;
            $data = $data['data'] ?? $data;
        }
        $md5 = md5(substr($random, 2, strlen($random) - 4));
        $iv = substr($random, -2);
        $type = substr($random, 1, 1);
        return self::aesDecrypt($data, substr($md5, $iv, 16), ($type == 2 ? substr(md5($md5), $iv, 16) : ''));
    }

    /**
     * des动态加密
     * @param $data
     * @return array
     */
    public static function desEn($data): array {
        $rand = self::rand();
        $iv = rand(1, 8);
        $md5 = md5($rand);
        $type = rand(1, 2);
        return [
            'random' => self::randInt('des') . $type . $rand . (strlen($iv) == 1 ? '0' . $iv : $iv),
            'data' => self::desEncrypt((is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES) : $data), substr($md5, $iv, 8), ($type == 2 ? substr(md5($md5), $iv, 8) : ''))
        ];
    }

    /**
     * des动态解密
     * @param $data
     * @param $random
     * @return string
     */
    public static function desDe($data, $random = null): string {
        if (empty($random)) {
            $random = $data['random'] ?? $random;
            $data = $data['data'] ?? $data;
        }
        $md5 = md5(substr($random, 2, strlen($random) - 4));
        $iv = substr($random, -2);
        $type = substr($random, 1, 1);
        return self::desDecrypt($data, substr($md5, $iv, 8), ($type == 2 ? substr(md5($md5), $iv, 8) : ''));
    }

    /**
     * des3动态加密
     * @param $data
     * @return array
     */
    public static function des3En($data): array {
        $rand = self::rand();
        $iv = rand(1, 8);
        $md5 = md5($rand);
        $type = rand(1, 2);
        return [
            'random' => self::randInt('des3') . $type . $rand . (strlen($iv) == 1 ? '0' . $iv : $iv),
            'data' => self::des3Encrypt((is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES) : $data), substr($md5, $iv, 24), ($type == 2 ? substr(md5($md5), $iv, 8) : ''))
        ];
    }

    /**
     * des3动态解密
     * @param $data
     * @param $random
     * @return string
     */
    public static function des3De($data, $random = null): string {
        if (empty($random)) {
            $random = $data['random'] ?? $random;
            $data = $data['data'] ?? $data;
        }
        $md5 = md5(substr($random, 2, strlen($random) - 4));
        $iv = substr($random, -2);
        $type = substr($random, 1, 1);
        return self::des3Decrypt($data, substr($md5, $iv, 24), ($type == 2 ? substr(md5($md5), $iv, 8) : ''));
    }


    /**
     * 生成随机
     * @param int $length
     * @return string
     */
    public static function rand(int $length = 32): string {
        return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890'), 0, $length);
    }

    /**
     * @param $mode
     * @return int
     */
    public static function randInt($mode): int {
        $arr = self::$setMov[$mode];
        return $arr[rand(0, (count($arr) - 1))];
    }
}