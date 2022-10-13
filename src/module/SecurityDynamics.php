<?php

namespace zhqing\module;

use zhqing\extend\Ciphertext;
use zhqing\extend\Frame;

/**
 * 超级动态加密
 */
class SecurityDynamics {
    private static array $EncryptArr = [
        ['key' => 'BC195B9DDA6E647D', 'iv' => '128F8A0C9252F5B1', 'type' => 'aes'],
        ['key' => '128F8A0C9252F5B1', 'iv' => '34EE884D6FAEBBE2', 'type' => 'aes'],
        ['key' => '1E20194530FA834D', 'iv' => 'DC01D34C4DCE7967', 'type' => 'aes'],
        ['key' => '6FD8F466A6BEA828', 'iv' => 'FB721663AE14E025', 'type' => 'aes'],
        ['key' => 'B8E23410138BF619258FDAA7', 'iv' => 'DC01D34C'],
        ['key' => 'E37734FC7E94AEC58CEA55DF', 'iv' => 'FB721663'],
        ['key' => '8771C94C5721F2B1534DC077', 'iv' => '548E3484'],
        ['key' => '669498AC544127D5A2D6DFFD', 'iv' => '281E1FA4'],
        ['key' => '7E94AEC58CEA55DF', 'type' => 'aes'],
        ['key' => 'EFCE772EE1F14E4C', 'type' => 'aes'],
        ['key' => '1E87358D2B30E9DD', 'type' => 'aes'],
        ['key' => '5D7938CEA06AC875', 'type' => 'aes'],
        ['key' => '7E72FDBC0A5E86FE485C0BAF'],
        ['key' => 'EACEF77EE4AE160262DC610F'],
        ['key' => 'F493CD957866C4A4D2F00962'],
        ['key' => '637CA7D3CC96C84283D63430']
    ];
    private static array $RandStr = ['\Ky@Q', '/hsQ@W', '\qB6JM#0', '/qR9p#3', '\Knh+I8h/', '/BcQ+Pi8-qR', '\Kny-OL(H', '/Bc-Ju)Tq4', '\sPqk#Mn'];

    /**
     * 加密数据
     * @param mixed $content
     * @param int $time //有效时间(秒)
     * @return string
     */
    public static function Encrypt(mixed $content, int $time = 0): string {
        $keyArr = self::$EncryptArr;
        $keyField = array_rand($keyArr);
        $kRand = rand(0, 9);
        $key = substr($keyArr[$keyField]['key'], 0, -1) . $kRand;
        $iv = $keyArr[$keyField]['iv'] ?? '';
        $info['data'] = $content;
        $info['time'] = time();
        $info['valid'] = $time;
        if (($keyArr[$keyField]['type'] ?? 'des') == 'des') {
            $Encrypt = Ciphertext::desEncrypt(Frame::json($info), $key, $iv);
        } else {
            $Encrypt = Ciphertext::aesEncrypt(Frame::json($info), $key, $iv);
        }
        $random = Frame::randStr(32);
        $a = rand(0, 9);
        $b = rand(10, 16);
        $k = substr($random, $a, 16);
        $i = substr($random, $b, 16);
        $EncryptData = Ciphertext::aesEncrypt($Encrypt, $k, $i);
        $rand = rand(0, strlen($EncryptData));
        return substr($EncryptData, 0, $rand) . (self::$RandStr[array_rand(self::$RandStr)]) . $b . $random . $a . (strlen($keyField) == 1 ? '0' . $keyField : $keyField) . $kRand . substr($EncryptData, $rand);
    }

    /**
     * 解密数据
     * @param string $data
     * @param string $content
     * @return mixed
     */
    public static function Decrypt(string $data, string $content = 'Error'): mixed {
        foreach (self::$RandStr as $v) $data = Frame::strRep($data, $v, '\@');
        $arr = explode('\@', $data);
        $end = end($arr);
        $random = substr($end, 2, 32);
        $Decrypt = Ciphertext::aesDecrypt(Frame::strRep($data, '\@' . substr($end, 0, 38)), substr($random, substr($end, 34, 1), 16), substr($random, substr($end, 0, 2), 16));
        if (!empty($keyArr = (self::$EncryptArr[((int)substr($end, 35, 2))] ?? []))) {
            if (($keyArr['type'] ?? 'des') == 'des') {
                $Encrypt = Ciphertext::desDecrypt($Decrypt, (substr($keyArr['key'], 0, -1) . substr($end, 37, 1)), ($keyArr['iv'] ?? ''));
            } else {
                $Encrypt = Ciphertext::aesDecrypt($Decrypt, (substr($keyArr['key'], 0, -1) . substr($end, 37, 1)), ($keyArr['iv'] ?? ''));
            }
            $DataArr = Frame::isJson($Encrypt);
            if (!empty($time = ($DataArr['time'] ?? '')) && !empty($valid = ($DataArr['valid'] ?? '')) && (($time + $valid) <= time())) {
                $content = 'Decrypt Error';
            } else {
                $content = $DataArr['data'] ?? $content;
            }
        }
        return $content;
    }
}