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
}