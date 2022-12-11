<?php

namespace zhqing\workerman;
class Way {
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