<?php

namespace zhqing\module;

/**
 * 谷哥验证器类
 */
class GoogleAuth {
    protected static array $BaseArr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '2', '3', '4', '5', '6', '7', '='];

    /**
     * 检查验证码
     * @param string|int $secret //验证地址
     * @param string|int $code //验证码
     * @param bool $type //是否开启2个30秒验证
     * @param int $discrepancy
     * @param mixed|null $currentTime
     * @return bool
     */
    public static function check(string|int $secret, string|int $code, bool $type = true, int $discrepancy = 1, mixed $currentTime = null): bool {
        $currentTime = (($currentTime === null) ? floor(time() / 30) : $currentTime);
        if (strlen($code) == 6) {
            if ($type) {
                for ($i = -$discrepancy; $i <= $discrepancy; ++$i) {
                    $calculatedCode = self::getCode($secret, $currentTime + $i);
                    if (self::safeCode($calculatedCode, $code)) {
                        return true;
                    }
                }
            } else {
                return !empty(self::getCode($secret) == $code);
            }
        }
        return false;
    }

    /**
     * 获取验证码
     * @param $secret //验证地址
     * @param null $timeSlice
     * @return string
     */
    public static function getCode($secret, $timeSlice = null): string {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }
        $secretKey = self::baseDecode($secret);
        $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timeSlice);
        $hm = hash_hmac('SHA1', $time, $secretKey, true);
        $offset = ord(substr($hm, -1)) & 0x0F;
        $hashpart = substr($hm, $offset, 4);
        $value = unpack('N', $hashpart);
        $value = $value[1];
        $value = $value & 0x7FFFFFFF;
        $modulo = pow(10, 6);
        return str_pad($value % $modulo, 6, '0', STR_PAD_LEFT);
    }

    /**
     * 链接生成二维码
     * @param string $secret //验证地址
     * @param string $user //帐号
     * @param string $title //标题
     * @return string
     */
    public static function qrcode(string $secret, string $user, string $title = ''): string {
        return QrCodes::getBase(self::url($secret, $user, $title));
    }

    /**
     * 生成链接
     * @param string $secret //验证地址
     * @param string $user //帐号
     * @param string $title //标题
     * @return string
     */
    public static function url(string $secret, string $user, string $title = ''): string {
        return 'otpauth://totp/' . $user . '?secret=' . $secret . (!empty($title) ? ('&issuer=' . urlencode($title)) : '');
    }

    /**
     * 生成谷哥验证地址
     * @param int $length
     * @param mixed|false $rnd
     * @param mixed|string $secret
     * @return bool|string
     */
    public static function create(int $length = 16, mixed $rnd = false, mixed $secret = ''): bool|string {
        if ($length >= 16 && $length <= 128) {
            if (function_exists('random_bytes')) {
                $rnd = random_bytes($length);
            } elseif (function_exists('mcrypt_create_iv')) {
                $rnd = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            } elseif (function_exists('openssl_random_pseudo_bytes')) {
                $rnd = openssl_random_pseudo_bytes($length, $cryptoStrong);
                $rnd = (empty($cryptoStrong) ? false : $rnd);
            }
            if ($rnd !== false) {
                for ($i = 0; $i < $length; ++$i) {
                    $secret .= self::$BaseArr[ord($rnd[$i]) & 31];
                }
                return $secret;
            }
        }
        return false;
    }

    /**
     * 定时安全等于比较
     * @param $safeString
     * @param $userString
     * @return bool
     */
    protected static function safeCode($safeString, $userString): bool {
        if (function_exists('hash_equals')) {
            return hash_equals($safeString, $userString);
        }
        $safeLen = strlen($safeString);
        $userLen = strlen($userString);
        if ($userLen == $safeLen) {
            $result = 0;
            for ($i = 0; $i < $userLen; ++$i) {
                $result |= (ord($safeString[$i]) ^ ord($userString[$i]));
            }
            return $result === 0;
        }
        return false;
    }

    /**
     * 用于解码 base32 的辅助类
     * @param string $secret
     * @param string $binaryString
     * @return bool|string
     */
    protected static function baseDecode(string $secret, string $binaryString = ''): bool|string {
        if (!empty($secret)) {
            $base32chars = self::$BaseArr;
            $base32charsFlipped = array_flip($base32chars);
            $paddingCharCount = substr_count($secret, $base32chars[32]);
            $allowedValues = array(6, 4, 3, 1, 0);
            if (!in_array($paddingCharCount, $allowedValues)) {
                return false;
            }
            for ($i = 0; $i < 4; ++$i) {
                if ($paddingCharCount == $allowedValues[$i] &&
                    substr($secret, -($allowedValues[$i])) != str_repeat($base32chars[32], $allowedValues[$i])) {
                    return false;
                }
            }
            $secret = str_replace('=', '', $secret);
            $secret = str_split($secret);
            for ($i = 0; $i < count($secret); $i = $i + 8) {
                $x = '';
                if (!in_array($secret[$i], $base32chars)) {
                    return false;
                }
                for ($j = 0; $j < 8; ++$j) {
                    $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
                }
                $eightBits = str_split($x, 8);
                for ($z = 0; $z < count($eightBits); ++$z) {
                    $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : '';
                }
            }
        }
        return $binaryString;
    }
}