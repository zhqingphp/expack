<?php


namespace zhqing\module;

use zhqing\extend\Frame;
use zhqing\extend\Curl;
use zhqing\webman\Way;
use Webman\Route;

class CopyWeb {
    /**
     * @param string $url
     * @param string $dir
     */
    public static function Route(string $url, string $dir = '') {
        Route::fallback(function () use ($url, $dir) {
            $data = [
                'dir' => (!empty($dir) ? $dir : (__DIR__ . '/../../file/copy/web')),//保存路径
                'url' => $url,
                'uri' => request()->uri(),//请求uri
                'method' => request()->method(),//请求方法
                'post' => request()->post(),//post参数
                'path' => request()->path(),//请求路径
                'ip' => Way::getIp(),//请求ip
                'referer' => '',//来路
                'ajax' => request()->isAjax(),//是否ahax
                'json' => Frame::strIn(strtolower(request()->header('content-type', '')), 'json'),//是否json
                'follow' => false,//是否跳转
                'save' => function ($path, $file, $code, $body) {
                    return false;//返回false保存
                },
            ];
            $rs = self::copyWeb($data);
            return \response($rs['body'], $rs['code'])->withHeaders($rs['header']);
        });
    }

    /**
     * @param $arr
     * @return array
     */
    public static function copyWeb($arr): array {
        $path = ltrim(Frame::getStrArr($arr, 'path'), '/');
        $path = ((!empty($path)) ? ($path) : ($path . '/index.html'));
        $format = Frame::getPath($path);
        $file = rtrim(Frame::getStrArr($arr, 'dir'), '/') . (!empty($format) ? ('/' . $path) : '/' . $path . '/index.html');
        if (!empty(is_file($file))) {
            $data['code'] = 200;
            $data['header'] = ['Content-Type' => Frame::getMime(Frame::getPath($path))];
            $data['body'] = @file_get_contents($file);
        } else {
            $curl = Curl::url(Frame::getStrArr($arr, 'url'), Frame::getStrArr($arr, 'method'), Frame::getStrArr($arr, 'post', []))
                ->path(Frame::getStrArr($arr, 'uri'))
                ->timeConnect(15)
                ->timeOut(15)
                ->reqIp(Frame::getStrArr($arr, 'ip'))
                ->referer(Frame::getStrArr($arr, 'referer', ''));
            if (!empty(Frame::getStrArr($arr, 'follow', false))) {
                $curl->follow(true);
            }
            if (!empty(Frame::getStrArr($arr, 'ajax'))) {
                $curl->ajax();
            }
            if (!empty(Frame::getStrArr($arr, 'json'))) {
                $curl->json();
            }
            if (!empty($proxy = Frame::getStrArr($arr, 'proxy'))) {
                $curl->proxy($proxy, true);
            }
            $curl = $curl->exec();
            $code = $curl->code();
            $body = $curl->body();
            if ($code == 200 && !empty($body)) {
                $is = false;
                $save = Frame::getStrArr($arr, 'save');
                if (is_callable($save)) {
                    $is = $save($path, $file, $code, $body);
                }
                if (empty($is)) {
                    Frame::mkDir(dirname($file));
                    file_put_contents($file, $body);
                }
            }
            $data['code'] = $code;
            $header = ['Content-Type' => ($curl->type() ?: Frame::getMime(Frame::getPath($path)))];
            if (!empty($dis = $curl->dis())) {
                $header = array_merge($header, ['Content-Disposition' => $dis]);
            }
            $data['header'] = $header;
            $data['body'] = $body;
        }
        return $data;
    }
}
