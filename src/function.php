<?php
if (!function_exists('ps')) {
    function ps($data) {
        echo '<pre>' . print_r($data, true) . '</pre>';
    }
}

if (!function_exists('ts')) {
    function ts(mixed $data = null, int $row = 30, int $cols = 200) {
        echo '<textarea rows="' . $row . '" cols="' . $cols . '">' . $data . '</textarea>';
    }
}
if (!function_exists('rs')) {
    function rs($data) {
        return response('<pre>' . print_r($data, true) . '</pre>');
    }

}
if (!function_exists('es')) {
    function es(mixed $data = null, int $row = 30, int $cols = 200) {
        return '<textarea rows="' . $row . '" cols="' . $cols . '">' . $data . '</textarea>';
    }

}
if (!function_exists('loadJump')) {
    function loadJump($title, $url, $time = 15) {
        require __DIR__ . '/../file/load.php';
    }

}
if (!function_exists('workError')) {
    function workError() {
        $html = '<html><head><title>404 Not Found</title></head><body><center><h1>404 Not Found</h1></center><hr></body></html>';
        return response($html, 404);
    }

}
if (!function_exists('toArr')) {
    function toArr($data) {
        return !empty($data) ? $data->toArray() : [];
    }
}