<?php

namespace zhqing\module;

use zhqing\extend\Curl;
use zhqing\web\Request;
use zhqing\web\Response;
use zhqing\web\Cookie;

class ProxyUrl {
    public static function run($url, $referer, $head = []) {
        ini_set('date.timezone', 'Asia/Shanghai');
        $curl = Curl::url($url, Request::getMethod(), Request::post())
            ->path(Request::getUri())
            ->setHead($head)
            ->reqIp(Request::getIp())
            ->cookie(Cookie::get())
            ->referer($referer)
            ->encoding('gzip, deflate')
            ->timeConnect(10)
            ->timeOut(10)
            ->exec();
        if ($curl->code() == 200) {
            $header = $curl->getHeadArr(['Content-Type', 'Content-Disposition']);
            foreach ($header as $k => $v) {
                header($k . ':' . $v);
            }
            $getCookie = $curl->getCookie();
            foreach ($getCookie as $k => $v) {
                Cookie::set($k, $v);
            }
            echo $curl->body();
        } else {
            Response::status(404);
            echo '404 Not Found';
        }
    }
}
