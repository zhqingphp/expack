<?php

namespace zhqing\extend;

use zhqing\extend\safe\Moving;
use zhqing\extend\safe\Openssl;

class Safety {
    use Moving;
    use Openssl;

    /**
     * aes加密
     * @param string $data
     * @param string $key //最长16位
     * @param string $iv //长度16位
     * @return string
     */
    public static function aesEncrypt(string $data, string $key, string $iv = ''): string {
        return \base64_encode(\openssl_encrypt($data
            , (!empty($iv) ? "AES-128-CBC" : "AES-128-ECB")
            , (\strlen($key) > 16 ? \substr($key, 0, 16) : $key)
            , OPENSSL_RAW_DATA
            , (\strlen($iv) > 16 ? \substr($iv, 0, 16) : $iv)
        ));
    }

    /**
     * aes解密
     * @param string $data
     * @param string $key //最长16位
     * @param string $iv //长度16位
     * @return string
     */
    public static function aesDecrypt(string $data, string $key, string $iv = ''): string {
        return \openssl_decrypt(\base64_decode($data)
            , (!empty($iv) ? "AES-128-CBC" : "AES-128-ECB")
            , (\strlen($key) > 16 ? \substr($key, 0, 16) : $key)
            , OPENSSL_RAW_DATA
            , (\strlen($iv) > 16 ? \substr($iv, 0, 16) : $iv)
        );
    }

    /**
     * des加密
     * @param string $data
     * @param string $key //最长24位
     * @param string $iv //长度8位
     * @return string
     */
    public static function desEncrypt(string $data, string $key, string $iv = ''): string {
        return \base64_encode(\openssl_encrypt($data
            , (!empty($iv) ? "DES-CBC" : "DES-ECB")
            , (\strlen($key) > 8 ? \substr($key, 0, 8) : $key)
            , OPENSSL_RAW_DATA
            , (\strlen($iv) > 8 ? \substr($iv, 0, 8) : $iv)
        ));
    }

    /**
     * des解密
     * @param string $data
     * @param string $key //最长24位
     * @param string $iv //长度8位
     * @return string
     */
    public static function desDecrypt(string $data, string $key, string $iv = ''): string {
        return \openssl_decrypt(\base64_decode($data)
            , (!empty($iv) ? "DES-CBC" : "DES-ECB")
            , (\strlen($key) > 8 ? \substr($key, 0, 8) : $key)
            , OPENSSL_RAW_DATA
            , (\strlen($iv) > 8 ? \substr($iv, 0, 8) : $iv));
    }

    /**
     * des3加密
     * @param string $data
     * @param string $key //最长24位
     * @param string $iv //长度8位
     * @return string
     */
    public static function des3Encrypt(string $data, string $key, string $iv = ''): string {
        return \base64_encode(\openssl_encrypt($data
            , (!empty($iv) ? "DES-EDE3-CBC" : "DES-EDE3")
            , (\strlen($key) > 24 ? \substr($key, 0, 24) : $key)
            , OPENSSL_RAW_DATA
            , (\strlen($iv) > 8 ? \substr($iv, 0, 8) : $iv)
        ));
    }

    /**
     * des3解密
     * @param string $data
     * @param string $key //最长24位
     * @param string $iv //长度8位
     * @return string
     */
    public static function des3Decrypt(string $data, string $key, string $iv = ''): string {
        return \openssl_decrypt(\base64_decode($data)
            , (!empty($iv) ? "DES-EDE3-CBC" : "DES-EDE3")
            , (\strlen($key) > 24 ? \substr($key, 0, 24) : $key)
            , OPENSSL_RAW_DATA
            , (\strlen($iv) > 8 ? \substr($iv, 0, 8) : $iv)
        );
    }
}