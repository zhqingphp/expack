<?php

namespace zhqing\extend\frame;

trait Acme {

    /**
     * 生成证书
     * @param $acme //acme.sh  绝对路径
     * @param $domain //域名 (可以加-d添加多域名)
     * @param $path //网站目录 绝对路径
     * @param $sslFile //证书保存路径
     * @return bool
     * ./acme.sh --issue -d ws.aabb999.net -w /home/wwwroot/ws.aadd999.net/ --config-home /home/ssl/ --force --debug 2
     */
    public static function acmeInsert($acme, $domain, $path, $sslFile): bool {
        \exec("{$acme} --issue -d {$domain} -w {$path} --config-home {$sslFile} --force", $K, $v);
        return $v == 0;
    }

    /**
     * 更新
     * @param $acme //acme.sh  绝对路径
     * @param $domain //域名 (可以加-d添加多域名)
     * @param $sslFile //证书保存路径
     * @return bool
     */
    public static function acmeUpdate($acme, $domain, $sslFile): bool {
        \exec("{$acme} --renew -d {$domain} --config-home {$sslFile} --force", $K, $v);
        return $v == 0;
    }

    /**
     * @param $cerPem //检查的cer或者pem文件
     * @param string $domain //all=更新全部,[域名单个逗号分开]
     * @param string $acme //acme 路径
     * @return mixed
     */
    public static function updateSsl($cerPem, string $domain = 'all', string $acme = '/usr/local/acme.sh/acme.sh'): mixed {
        \date_default_timezone_set('Asia/Shanghai');
        if (empty(is_file($cerPem))) {
            return $cerPem . '[does not exist]';
        }
        $cert = \openssl_x509_parse(file_get_contents($cerPem));
        $time = $cert['validTo_time_t'] ?? 0;
        if (empty($time)) {
            return 'validTo_time_t error';
        }
        //7天内更新SSL
        if (($time - time()) <= 604800) {
            if ($domain == 'all') {
                \exec($acme . " --renew-all --force", $arr, $is);
            } else {
                $dArr = explode(',', $domain);
                $d = '';
                foreach ($dArr as $v) {
                    $d .= '-d ' . $v;
                }
                \exec($acme . " --renew " . $d . " --force", $arr, $is);
            }
            return ($is == 0) ? join("\r\n", $arr) : 'acme error';
        } else {
            return ($time - time());
        }
    }
}