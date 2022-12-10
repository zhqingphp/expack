<?php

namespace zhqing\workerman;
class Way {
    public static function cacheJs() {
        $dir = __DIR__ . '/../../file';
        $file = $dir . '/cache.js';
        if (is_file($file)) {
            return \response()->file($file);
        }
        $arr = [
            $dir . '/js/jquery.min.js',
            $dir . '/js/clipboard.js',
            $dir . '/js/jquery.qrcode.min.js',
            $dir . '/js/CryptoJS.js',
            $dir . '/js/jsencrypt.js',
            $dir . '/js/axios.min.js',
        ];
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