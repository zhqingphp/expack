<?php

namespace zhqing\webman;

use zhqing\extend\Frame;
use zhqing\extend\IpAdder;
use support\view\ThinkPHP;
use support\Response;
use Phar;
use Webman\Route;
use support\Request;
use zhqing\extend\Curl;
use develop\PceLogin;
use zhqing\mysql\PdoHelper;
use Workerman\Protocols\Http\Chunk;

class Way {

    /**
     * 发送http chunk数据
     * https://www.workerman.net/doc/workerman/http/response.html
     * @param callable $callable
     * @param string $top
     * @param string $end
     * @return \support\Response
     */
    public static function obFlush(callable $callable, string $top = "", string $end = ""): \support\Response {
        $connection = \request()->connection;
        $connection->send((new \support\Response(200, ['Transfer-Encoding' => 'chunked'], $top)));
        $callable(function ($data) use ($connection) {
            return $connection->send(new \Workerman\Protocols\Http\Chunk($data));
        });
        $connection->send(new \Workerman\Protocols\Http\Chunk($end));
        $connection->send(new \Workerman\Protocols\Http\Chunk(''));
        return \response($end)->withHeaders([
            "Content-Type" => "application/octet-stream",
            "Transfer-Encoding" => "chunked"
        ]);
        /**
         * return self::obFlush(function (callable $callable) {
         * for ($i = 1; $i <= 30; $i++) {
         * $callable('第' . $i . '段数据');
         * sleep(1);
         * }
         * }, 'top', 'end');
         */
    }

    /**
     * 还原phar文件
     * @param string $file
     * @param string $dir
     * @return bool
     */
    public function pharFile(string $file, string $dir = ''): bool {
        $dir = (!empty($dir) ? $dir : Frame::delPath($file));
        Frame::mkDir($dir);
        return (new Phar($file))->extractTo($dir, null, true);
    }

    /**
     * 获取ip
     * @return string
     */
    public static function getIp(): string {
        return \request()->getRealIp();
    }

    /**
     * 目录静态文件可以被访问
     * @param $path //访问路径
     * @param $dir //目录
     */
    public static function fileRoute($path, $dir) {
        \Webman\Route::any($path . '/[{name:.+}]', function (\support\Request $request, $name = '') use ($dir) {
            $file = rtrim($dir, '/') . "/" . trim($name, '/');
            if (!str_contains($name, '..') && !empty(is_file($file))) {
                return response('')->withFile($file);
            }
            return workError();
        });
    }

    /**
     * @param string $plugin
     * @param array|string $template
     * @param array $vars
     * @param string|null $app
     * @return Response
     */
    public static function view(string $plugin, array|string $template, array $vars = [], string|null $app = null): Response {
        $handler = \config("plugin.{$plugin}.view.handler", ThinkPHP::class);
        $dir = rtrim(config("plugin.{$plugin}.view.options.view_path", base_path() . "/plugin/{$plugin}/app/view/"), '/');
        $suffix = '.' . config("plugin.{$plugin}.view.options.view_suffix", "html");
        if (is_array($template)) {
            $html = '';
            foreach ($template as $v) {
                $html .= $handler::render($dir . '/' . trim($v, '/') . $suffix, $vars, $app);
            }
        } else {
            $html = $handler::render($dir . '/' . trim($template, '/') . $suffix, $vars, $app);
        }
        return new Response(200, [], $html);
    }

    /**
     * 获取地区
     * @return string
     */
    public static function getAdder(): string {
        return IpAdder::getAdder(self::getIp());
    }

    /**
     * 多个端口提供http服务
     * @param string|int $port
     * @param int|null $count
     * @return array
     */
    public static function httpServe(string|int $port, null|int $count = null) {
        return [
            'handler' => \Webman\App::class,
            'listen' => 'http://0.0.0.0:' . $port,
            'count' => ($count ?: cpu_count() * 4), // 进程数
            'constructor' => [
                'request_class' => \support\Request::class, // request类设置
                'logger' => \support\Log::channel('default'), // 日志实例
                'app_path' => app_path(), // app目录位置
                'public_path' => public_path() // public目录位置
            ]
        ];
    }

    /**
     * @param array $list //追加js
     * @param bool $type /是否更新
     * @return mixed
     */
    public static function cacheJs(array $list = [], bool $type = false) {
        $dir = __DIR__ . '/../../file';
        $file = $dir . '/cache.js';
        if (is_file($file) && empty($type)) {
            return \response()->file($file);
        }
        $arr = array_merge([
            $dir . '/js/jquery.min.js',
            $dir . '/js/clipboard.js',
            $dir . '/js/jquery.qrcode.min.js',
            $dir . '/js/CryptoJS.js',
            $dir . '/js/jsencrypt.js',
            $dir . '/js/axios.min.js',
            $dir . '/js/Safety.js',
            $dir . '/js/browser.js',
            $dir . '/js/PassEn.js',
            $dir . '/js/Frame.js',
        ], $list);
        $body = '';
        foreach ($arr as $v) {
            if (is_file($v)) {
                $body .= @file_get_contents($v) . "\r\n";
            }
        }
        file_put_contents($file, $body);
        return \response($body, 200, ['Content-Type' => 'application/javascript']);
    }
}