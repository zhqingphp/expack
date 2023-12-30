<?php

namespace zhqing\extend;

use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\DES;
use zhqing\extend\safe\Moving;
use zhqing\extend\safe\Openssl;
use phpseclib3\Crypt\TripleDES as DES3;

/**
 * https://github.com/phpseclib/phpseclib
 * composer require phpseclib/phpseclib:~3.0
 */
class Safe {
    use Moving;
    use Openssl;

    public array $data = [];

    public array $list = [
        'AES' => [
            'class' => AES::class,
            'length' => [128, 192, 256],
            'key' => [16, 24, 32],
            'iv' => [16]
        ],
        'DES' => [
            'class' => DES::class,
            'length' => false,
            'key' => [8],
            'iv' => [8]
        ],
        'DES3' => [
            'class' => DES3::class,
            'length' => [192],
            'key' => [16, 24],
            'iv' => [8]
        ],
    ];

    /**
     * @param string $type //加密类型aes,des,des3
     * @param int $length
     * @return static
     */
    public static function set(string $type, int $length = 0): static {
        return (new self($type, $length));
    }

    /**
     * aes加密
     * @param int $length
     * @return static
     */
    public static function aes(int $length = 128): static {
        return self::set('aes', $length);
    }

    /**
     * des加密
     * @return static
     */
    public static function des(): static {
        return self::set('des');
    }

    /**
     * des3加密
     * @param int $length
     * @return static
     */
    public static function des3(int $length = 192): static {
        return self::set('des3', $length);
    }

    /**
     * 设置加密模式
     * @param string $mode
     * @return $this
     */
    public function mode(string $mode): static {
        $this->data['mode'] = strtoupper($mode);
        return $this;
    }

    /**
     * 设置key和iv
     * @param string $key
     * @param string $iv
     * @return $this
     */
    public function setKeyIv(string $key, string $iv = ''): static {
        $this->data['key'] = $key;
        $this->data['iv'] = $iv;
        return $this;
    }

    /**
     * 加密
     * @param mixed $data
     * @param bool $url
     * @return array|string
     */
    public function encrypt(mixed $data, bool $url = false): array|string {
        if (!empty($safe = $this->verify()) && is_array($safe))
            return $safe;
        $content = base64_encode($safe->encrypt($data));
        return (!empty($url) ? (urlencode($content)) : $content);
    }

    /**
     * 解密
     * @param mixed $data
     * @param bool $url
     * @return array|string
     */
    public function decrypt(mixed $data, bool $url = false): array|string {
        if (!empty($safe = $this->verify()) && is_array($safe))
            return $safe;
        $content = base64_decode($data);
        return $safe->decrypt((!empty($url) ? urldecode($content) : $content));
    }

    /**
     * 验证
     * @return mixed
     */
    protected function verify(): mixed {
        $class = $this->list('class');
        $key = $this->data('key');
        $key_ = $this->list('key');
        $iv = $this->data('iv');
        $iv_ = $this->list('iv');
        $length = $this->data('length');
        $length_ = $this->list('length');
        $mode = '';
        if (empty($class)) {
            return $this->error('The encryption type is incorrect', 401);
        }
        if (!empty($key_)) {
            if (empty($key))
                return $this->error('this algorithm. Only keys of sizes ' . join(',', $key_) . ' supported', 402);
            if (empty(in_array(strlen($key), $key_)))
                return $this->error('Key of size ' . strlen($key) . ' not supported by this algorithm. Only keys of sizes ' . join(',', $key_) . ' supported', 403);
            $mode = 'ECB';
        }
        if (!empty($iv) && !empty($iv_)) {
            if (empty(in_array(strlen($iv), $iv_)))
                return $this->error('Received initialization vector of size ' . strlen($iv) . ', but size ' . join(',', $iv_) . ' is required', 404);
            $mode = 'CBC';
        }
        if (!empty($length_)) {
            if (empty($length))
                return $this->error('this algorithm. Only keys of sizes ' . join(',', $length_) . ' supported', 405);
            if (empty(in_array($length, $length_)))
                return $this->error('Key of size ' . $length . ' not supported by this algorithm. Only keys of sizes ' . join(',', $length_) . ' supported', 406);
        }
        $mode = $this->data('mode') ?: $mode;
        $safe = (new $class(strtoupper($mode)));
        $safe->setKey($key);
        if (!empty($iv))
            $safe->setIV($iv);
        if ($length > 0)
            $safe->setKeyLength($length);
        return $safe;
    }

    /**
     * @param string $type //加密类型aes,des,des3
     * @param int $length
     */
    public function __construct(string $type, int $length = 0) {
        $this->data['type'] = strtoupper($type);
        $this->data['length'] = $length;
    }

    /**
     * 获取设置数据
     * @param string $key
     * @param mixed|string $default
     * @return mixed
     */
    public function data(string $key, mixed $default = ''): mixed {
        return Frame::getStrArr($this->data, $key, $default);
    }

    /**
     * 获取配置
     * @param string $key
     * @param mixed|string $default
     * @return mixed
     */
    public function list(string $key, mixed $default = ''): mixed {
        return Frame::getStrArr($this->list, strtoupper($this->data('type')) . '.' . $key, $default);
    }

    /**
     * 报错
     * @param string $msg
     * @param int $code
     * @return array
     */
    public function error(string $msg, int $code = 400): array {
        $data['msg'] = $msg;
        $data['code'] = $code;
        return $data;
    }

    /**
     * @param string $data
     * @param string $key
     * @param string $iv
     * @return string
     */
    public static function aesEncrypt(string $data, string $key, string $iv = ''): string {
        $data = self::aes()
            ->setKeyIv((strlen($key) > 16 ? substr($key, 0, 16) : $key), (strlen($iv) > 16 ? substr($iv, 0, 16) : $iv))
            ->encrypt($data);
        return is_array($data) ? $data['msg'] : $data;
    }

    /**
     * @param string $data
     * @param string $key
     * @param string $iv
     * @return string
     */
    public static function aesDecrypt(string $data, string $key, string $iv = ''): string {
        $data = self::aes()
            ->setKeyIv((strlen($key) > 16 ? substr($key, 0, 16) : $key), (strlen($iv) > 16 ? substr($iv, 0, 16) : $iv))
            ->decrypt($data);
        return is_array($data) ? $data['msg'] : $data;
    }

    /**
     * @param string $data
     * @param string $key
     * @param string $iv
     * @return string
     */
    public static function desEncrypt(string $data, string $key, string $iv = ''): string {
        $data = self::des()
            ->setKeyIv((strlen($key) > 8 ? substr($key, 0, 8) : $key), (strlen($iv) > 8 ? substr($iv, 0, 8) : $iv))
            ->encrypt($data);
        return is_array($data) ? $data['msg'] : $data;
    }

    /**
     * @param string $data
     * @param string $key
     * @param string $iv
     * @return string
     */
    public static function desDecrypt(string $data, string $key, string $iv = ''): string {
        $data = self::des()
            ->setKeyIv((strlen($key) > 8 ? substr($key, 0, 8) : $key), (strlen($iv) > 8 ? substr($iv, 0, 8) : $iv))
            ->decrypt($data);
        return is_array($data) ? $data['msg'] : $data;
    }

    /**
     * @param string $data
     * @param string $key
     * @param string $iv
     * @return string
     */
    public static function des3Encrypt(string $data, string $key, string $iv = ''): string {
        $data = self::des3()
            ->setKeyIv((strlen($key) > 24 ? substr($key, 0, 24) : $key), (strlen($iv) > 8 ? substr($iv, 0, 8) : $iv))
            ->encrypt($data);
        return is_array($data) ? $data['msg'] : $data;
    }

    /**
     * @param string $data
     * @param string $key
     * @param string $iv
     * @return string
     */
    public static function des3Decrypt(string $data, string $key, string $iv = ''): string {
        $data = self::des3()
            ->setKeyIv((strlen($key) > 24 ? substr($key, 0, 24) : $key), (strlen($iv) > 8 ? substr($iv, 0, 8) : $iv))
            ->decrypt($data);
        return is_array($data) ? $data['msg'] : $data;
    }
}