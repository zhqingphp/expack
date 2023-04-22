<?php

namespace zhqing\webman\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class Port implements MiddlewareInterface {
    public static array $Plugin = [];

    public function process(Request $request, callable $handler): Response {
        $port = $request->getLocalPort();
        if (empty($request->plugin) && !empty($plugin = (self::$Plugin[$port] ?? ''))) {
            $request->plugin = $plugin;
            $controller = "\\plugin\\{$plugin}\\app\\controller\\" . trim((explode('app\controller\\', $request->controller)[1] ?? ''), '\\');
            return call_user_func_array([(new $controller()), $request->action], [$request]);
        }
        return $handler($request);
    }

    /**
     * @param string|int $port
     * @param string $plugin
     * @param int|null $count
     * @return array
     */
    public static function set(string|int $port, string $plugin = '', null|int $count = null) {
        self::$Plugin[$port] = $plugin;
        return [
            'handler' => \Webman\App::class,
            'listen' => 'http://0.0.0.0:' . $port,
            'count' => ($count ?: cpu_count() * 4), // 进程数
            'constructor' => [
                'request_class' => \support\Request::class, // request类设置
                'logger' => \support\Log::channel('default'), // 日志实例
                'app_path' => (!empty($plugin) ? app_path() : (run_path() . '/plugin/' . $plugin . '/app')), // app目录位置
                'public_path' => (!empty($plugin) ? public_path() : (run_path() . '/plugin/' . $plugin . '/public')) // public目录位置
            ]
        ];
    }
}