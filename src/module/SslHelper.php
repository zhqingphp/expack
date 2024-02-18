<?php

namespace zhqing\module;

class SslHelper {

    public array $data = [];

    /**
     * @param string $path acme 证书位置
     * @param string $save 保存路径
     * @return string|array,success=成功,array=失败信息
     */
    public static function acme(string $path, string $save): string|array {
        return (new static())->acmeHandle($path, $save);
    }

    /**
     * 通过完整的证书链 获取 证书开始到结束时间
     * @param string $full 完整的证书链
     * @param bool $type 是否返回全部信息
     * @return array
     */
    public static function getTime(string $full, bool $type = false): array {
        $resource = openssl_x509_read((@file_get_contents($full)));
        $res = openssl_x509_parse($resource);
        $data['top_time'] = $res['validFrom_time_t'] ?? 0;
        $data['end_time'] = $res['validTo_time_t'] ?? 0;
        $data['top'] = date('Y-m-d H:i:s', $data['top_time']);
        $data['end'] = date('Y-m-d H:i:s', $data['end_time']);
        if (!empty($type)) {
            $data['data'] = $res;
        }
        return $data;
    }

    /**
     * 通过完整的证书链 获取 PFX类型证书，一般IIS和Tomcat使用
     * @param string $full 完整的证书链
     * @param string $private 证书私钥
     * @param string $pfx 保存路径和文件名
     * @param string $pass 密码
     * @return bool|int
     */
    public static function getPfx(string $full, string $private, string $pfx, string $pass = ''): bool|int {
        $res = openssl_pkcs12_export((@file_get_contents($full)), $data, (@file_get_contents($private)), $pass);
        return !empty($res) ? static::saveFile($pfx, $data) : 0;
    }

    /**
     * 通过证书私钥获取证书公钥
     * @param string $private 完整的证书链
     * @param string $public 保存路径和文件名
     * @return bool|int
     */
    public static function getPublic(string $private, string $public): bool|int {
        $res = openssl_pkey_get_details(openssl_pkey_get_private((@file_get_contents($private))));
        return static::saveFile($public, ($res['key'] ?? ''));
    }

    /**
     * @param string $file
     * @param string $data
     * @return bool|int
     */
    public static function saveFile(string $file, string $data): bool|int {
        return @file_put_contents(static::mkDir($file), $data);
    }

    /**
     * 创造文件夹
     * @param string $filePath 文件名
     * @return string
     */
    public static function mkDir(string $filePath): string {
        $path = dirname($filePath);
        if (empty(is_dir($path))) {
            mkdir($path, 0777, true);
        }
        return $filePath;
    }

    /**
     * 获取目录下全部文件列表
     * @param $path
     * @param array $result
     * @return mixed
     */
    public static function getDirFile($path, array $result = []): mixed {
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                if (is_dir($path . '/' . $file)) {
                    $result = static::getDirFile($path . '/' . $file, $result);
                } else {
                    $result[$file] = $path . '/' . $file;
                }
            }
        }
        return $result;
    }

    /**
     * 复制文件
     * @param string $filePath 文件
     * @param string $newFilePath 复制到的位置
     * @return bool|int
     */
    public static function copyFile(string $filePath, string $newFilePath): bool|int {
        if (is_readable($filePath)) {
            static::mkDir($newFilePath);
            if (($handle1 = fopen($filePath, 'r')) && ($handle2 = fopen($newFilePath, 'w'))) {
                $type = stream_copy_to_stream($handle1, $handle2);
                fclose($handle1);
                fclose($handle2);
            }
        }
        clearstatcache();
        return ($type ?? false);
    }

    /**
     * @param string|array $data
     * @param string|bool $info
     * @param bool $type
     * @return mixed
     */
    private static function helpTxt(string|array $data, string|bool $info = '', bool $type = false): mixed {
        $way = function ($name, $content) {
            return $name . "\r\n" . $content . "\r\n------------------------------------------------------------------\r\n\r\n";
        };
        $content = '';
        if (is_array($data)) {
            $i = 0;
            $type = is_bool($info) ? $info : $type;
            foreach ($data as $k => $v) {
                ++$i;
                $content .= $way((!empty($type) ? ($i . '.' . $k) : $k), $v);
            }
        } else if (is_string($info)) {
            $content = $way($data, $info);
        }
        return $content;
    }

    /**
     * @param string $path 证书位置
     * @param string $save 保存路径
     * @return string|array,success=成功,array=失败信息
     */
    private function acmeHandle(string $path, string $save): string|array {
        $path = rtrim($path, '/');
        $this->data['list'] = static::getDirFile($path);
        $this->data['save'] = rtrim($save, '/');
        foreach ($this->data['list'] as $k => $v) {
            $key = strtolower($k);
            $format = pathinfo($key, PATHINFO_EXTENSION);
            if ($key == 'fullchain.cer') {
                $this->data['full'] = $v;//完整的证书链，可更改后缀为pem。文件里一般有两段证书(也会有三张)，一张是你的域名证书，另一张是所依赖的证书链(可能会有两张证书链)
            } else if ($key == 'ca.cer') {
                $this->data['chain'] = $v;//依赖的证书链，里面内容同时存在于fullchain.crt文件中。
            } else if ($format == 'cer') {
                $this->data['domain'] = $v;//域名证书，里面内容同时存在于fullchain.crt文件中。
            } else if ($format == 'key') {
                $this->data['private'] = $v;//证书私钥，可更改后缀为key。如果使用的是自己上传的CSR文件，将不包含该文件。
            } else if ($format == 'conf') {
                $this->data['conf'] = $v;//配置
            }
        }
        $pass = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz1234567890'), 0, 6);
        $save = $this->data['save'];
        $apache = $save . '/Apache';
        $bt = $save . '/Bt';
        $nginx = $save . '/Nginx';
        $iis = $save . '/IIS';
        $all = $save . '/All';
        $tomcat = $save . '/Tomcat';
        //apache
        $data['apache_domain'] = static::copyFile($this->data['domain'], $apache . '/domain.crt');//域名证书
        $data['apache_private'] = static::copyFile($this->data['private'], $apache . '/private.key');//证书私钥
        $data['apache_chain'] = static::copyFile($this->data['chain'], $apache . '/chain.crt');//依赖的证书链
        //bt
        $data['bt_fullchain'] = static::copyFile($this->data['full'], $bt . '/fullchain.pem');//完整的证书链
        $data['bt_private'] = static::copyFile($this->data['private'], $bt . '/private.key');//证书私钥
        //iis
        $pfx = $iis . '/fullchain.pfx';
        $data['iis_fullchain'] = static::getPfx($this->data['full'], $this->data['private'], $pfx, $pass);//PFX类型证书
        $data['iis_password'] = static::saveFile($iis . '/password.txt', $pass);//PFX密码
        //nginx
        $data['nginx_fullchain'] = static::copyFile($this->data['full'], $nginx . '/fullchain.pem');//完整的证书链
        $data['nginx_private'] = static::copyFile($this->data['private'], $nginx . '/private.key');//证书私钥
        //tomcat
        $data['tomcat_public'] = static::copyFile($pfx, $tomcat . '/fullchain.pfx');//PFX类型证书
        $data['tomcat_password'] = static::saveFile($tomcat . '/password.txt', $pass);//PFX密码
        //all
        $data['all_fullchain'] = static::copyFile($this->data['full'], $all . '/fullchain.pem');//完整的证书链
        $data['all_domain'] = static::copyFile($this->data['domain'], $all . '/domain.crt');//域名证书
        $data['all_chain'] = static::copyFile($this->data['chain'], $all . '/chain.crt');//依赖的证书链
        $data['all_private'] = static::copyFile($this->data['private'], $all . '/private.key');//证书私钥
        $data['all_public'] = static::getPublic($this->data['private'], $all . '/public.pem');//证书公钥
        $data['all_public'] = static::copyFile($pfx, $all . '/fullchain.pfx');//PFX类型证书
        $data['all_password'] = static::saveFile($all . '/password.txt', $pass);//PFX密码
        $time = static::getTime($this->data['full']);
        $arr = array_slice(explode("DNS:", (@file_get_contents(($this->data['conf'] ?? '')))), 1);
        $domain = [];
        foreach ($arr as $v) {
            $domain[] = trim(trim($v), ',');
        }
        $data['help'] = static::saveFile($all . '/help.txt',
            static::helpTxt([
                'chain.crt' => '依赖的证书链，里面内容同时存在于fullchain.pem文件中',
                'domain.crt' => '域名证书，里面内容同时存在于fullchain.pem文件中',
                'fullchain.pem' => '完整的证书链，文件里一般有两段证书(也会有三张)，一张是你的域名证书，另一张是所依赖的证书链(可能会有两张证书链)',
                'fullchain.pfx' => 'PFX类型证书，一般IIS和Tomcat使用',
                'password.txt' => 'PFX证书密码',
                'private.key' => '证书私钥',
                'public.pem' => '证书公钥',
                '证书密码' => $pass,
                '证书域名' => join(",", $domain),
                '证书时间' => $time['top'] . " - " . $time['end']
            ], true));
        $error = [];
        foreach ($data as $k => $v) {
            if ($v === 0 || empty($v)) {
                $error[] = $k;
            }
        }
        return (!empty($error) ? $error : 'success');
    }
}