<?php

namespace zhqing\web;

use zhqing\extend\Frame;

class Response {
    /**
     * 发送文件流
     * @param array|string $localFile
     * @param string|null $mime
     */
    public static function sendFileStream(array|string $localFile, string $mime = null) {
        if (\is_array($localFile)) {
            $downFile = Frame::getArr($localFile, 1);
            $localFile = Frame::getArr($localFile, 0);
        }
        if (empty($localFile = self::modifiedSince($localFile))) {
            return;
        }
        $size = \filesize($localFile);
        $start = 0;
        $end = $size - 1;
        $length = $size;
        $head['Accept-Ranges'] = '0-' . $size;
        $head['Content-Type'] = $mime ? (Frame::getMime($mime) ?: $mime) : Frame::getMime(Frame::getPath($localFile));
        $head['Last-Modified'] = \gmdate('D, d M Y H:i:s', \filemtime($localFile)) . " " . \date_default_timezone_get();
        if (!empty($downFile)) {
            $head['Content-Disposition'] = 'attachment;filename=' . $downFile;
        }
        $ranges_arr = array();
        if (!empty($http_range = Frame::getArr($_SERVER, 'HTTP_RANGE'))) {
            if (!\preg_match('/^bytes=\d*-\d*(,\d*-\d*)*$/i', $http_range)) {
                self::status(416);
            }
            $ranges = \explode(',', \substr($http_range, 6));
            foreach ($ranges as $range) {
                $parts = \explode('-', $range);
                $ranges_arr[] = array($parts[0], $parts[1]);
            }
            $ranges = $ranges_arr[0];
            if ($ranges[0] == '') {
                if ($ranges[1] != '') {
                    $length = (int)$ranges[1];
                    $start = $size - $length;
                } else {
                    self::status(416);
                }
            } else {
                $start = (int)$ranges[0];
                if ($ranges[1] != '') {
                    $end = (int)$ranges[1];
                }
                $length = $end - $start + 1;
            }
            self::status(206);
        }
        $head['Content-Range'] = "bytes {$start}-{$end}/{$size}";
        $head['Content-Length'] = $length;
        foreach ($head as $k => $v) {
            \header($k . ':' . $v);
        }
        $buffer = 1024;
        $file = \fopen($localFile, 'rb');
        if ($file) {
            \set_time_limit(0);
            \fseek($file, $start);
            while (!\feof($file) && ($p = \ftell($file)) <= $end) {
                if ($p + $buffer > $end) {
                    $buffer = $end - $p + 1;
                }
                echo \fread($file, $buffer);
                \flush();
            }
            \fclose($file);
        }
    }

    /**
     * 发送文件
     * @param array|string $localFile
     * @param string|null $mime
     */
    public static function sendFile(array|string $localFile, string $mime = null) {
        if (\is_array($localFile)) {
            $downFile = Frame::getArr($localFile, 1);
            $localFile = Frame::getArr($localFile, 0);
        }
        if (empty($localFile = self::modifiedSince($localFile))) {
            return;
        }
        $head = [
            'Content-Length' => \filesize($localFile),
            'Content-Type' => $mime ? (Frame::getMime($mime) ?: $mime) : Frame::getMime(Frame::getPath($localFile)),
            'Last-Modified' => \gmdate('D, d M Y H:i:s', \filemtime($localFile)) . " " . \date_default_timezone_get(),
        ];
        if (!empty($downFile)) {
            $head['Content-Disposition'] = 'attachment;filename=' . $downFile;
        }
        foreach ($head as $k => $v) {
            \header($k . ':' . $v);
        }
        echo \readfile($localFile);
    }


    /**
     * 304
     * @param $file
     * @return string|bool
     */
    private static function modifiedSince($file): string|bool {
        if (empty($file = realpath($file))) {
            self::come('<h3>404 Not Found</h3>', 404);
        }
        if (!empty($if_modified_since = Frame::getArr($_SERVER, 'HTTP_IF_MODIFIED_SINCE'))) {
            if ($if_modified_since === \date('D, d M Y H:i:s', \filemtime($file)) . ' ' . \date_default_timezone_get()) {
                self::status(304);
                return false;
            }
        }
        return $file;
    }

    /**
     * 发送内容
     * @param array|string $data
     * @param string $mime
     */
    public static function sendData(array|string $data, string $mime) {
        if (\is_array($data)) {
            $downFile = Frame::getArr($data, 1);
            $data = Frame::getArr($data, 0);
        }
        $head = [
            'Content-Length' => \strlen($data),
            'Content-Type' => (Frame::getMime($mime) ?: $mime),
        ];
        if (!empty($downFile)) {
            $head['Content-Disposition'] = 'attachment;filename=' . $downFile;
        }
        foreach ($head as $k => $v) {
            \header($k . ':' . $v);
        }
        echo $data;
    }

    /**
     * 发送Http跳转
     * @param $url
     * @param int $code
     */
    public static function redirect($url, int $code = 302) {
        self::status($code);
        \header('Location:' . $url);
    }


    /**
     * 设置头部信息
     * @param $name
     * @param null $data
     * @param array $head
     */
    public static function head($name, $data = null, array $head = []) {
        if (empty(\is_array($name))) {
            $head[$name] = $data;
        } else if (!empty($name)) {
            foreach ($name as $k => $v) {
                $head[$k] = $v;
            }
        }
        if (!empty($head)) {
            foreach ($head as $k => $v) {
                if ($k == 'mime') {
                    $k = 'Content-type';
                    $v = Frame::getMime($v);
                }
                \header($k . ':' . $v);
            }
        }
    }


    /**
     * 设置状态
     * @param int $key
     */
    public static function status(int $key = 200) {
        \header(self::headStatus($key));
    }


    /**
     * @param string $data
     * @param int $status
     * @param array $header
     * @throws \Exception
     */
    public static function come(string $data = '', int $status = 200, array $header = []) {
        if ($status > 0) {
            self::status($status);
        }
        self::head($header);
        echo $data;
        exit;
    }

    /**
     * 头部信息状态
     * @param int $key
     * @return string
     */
    private static function headStatus(int $key = 200): string {
        $header = [
            100 => "HTTP/1.1 100 Continue",
            101 => "HTTP/1.1 101 Switching Protocols",
            200 => "HTTP/1.1 200 OK",
            201 => "HTTP/1.1 201 Created",
            202 => "HTTP/1.1 202 Accepted",
            203 => "HTTP/1.1 203 Non-Authoritative Information",
            204 => "HTTP/1.1 204 No Content",
            205 => "HTTP/1.1 205 Reset Content",
            206 => "HTTP/1.1 206 Partial Content",
            300 => "HTTP/1.1 300 Multiple Choices",
            301 => "HTTP/1.1 301 Moved Permanently",
            302 => "HTTP/1.1 302 Found",
            303 => "HTTP/1.1 303 See Other",
            304 => "HTTP/1.1 304 Not Modified",
            305 => "HTTP/1.1 305 Use Proxy",
            307 => "HTTP/1.1 307 Temporary Redirect",
            400 => "HTTP/1.1 400 Bad Request",
            401 => "HTTP/1.1 401 Unauthorized",
            402 => "HTTP/1.1 402 Payment Required",
            403 => "HTTP/1.1 403 Forbidden",
            404 => "HTTP/1.1 404 Not Found",
            405 => "HTTP/1.1 405 Method Not Allowed",
            406 => "HTTP/1.1 406 Not Acceptable",
            407 => "HTTP/1.1 407 Proxy Authentication Required",
            408 => "HTTP/1.1 408 Request Time-out",
            409 => "HTTP/1.1 409 Conflict",
            410 => "HTTP/1.1 410 Gone",
            411 => "HTTP/1.1 411 Length Required",
            412 => "HTTP/1.1 412 Precondition Failed",
            413 => "HTTP/1.1 413 Request Entity Too Large",
            414 => "HTTP/1.1 414 Request-URI Too Large",
            415 => "HTTP/1.1 415 Unsupported Media Type",
            416 => "HTTP/1.1 416 Requested range not satisfiable",
            417 => "HTTP/1.1 417 Expectation Failed",
            500 => "HTTP/1.1 500 Internal Server Error",
            501 => "HTTP/1.1 501 Not Implemented",
            502 => "HTTP/1.1 502 Bad Gateway",
            503 => "HTTP/1.1 503 Service Unavailable",
            504 => "HTTP/1.1 504 Gateway Time-out"
        ];
        return Frame::getArr($header, $key, $header[200]);
    }
}