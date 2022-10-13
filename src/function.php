<?php
function ps($data) {
    echo '<pre>' . print_r($data, true) . '</pre>';
}

function ts(mixed $data = null, int $row = 30, int $cols = 200) {
    echo '<textarea rows="' . $row . '" cols="' . $cols . '">' . $data . '</textarea>';
}

function rs($data) {
    return response('<pre>' . print_r($data, true) . '</pre>');
}

function es(mixed $data = null, int $row = 30, int $cols = 200) {
    return '<textarea rows="' . $row . '" cols="' . $cols . '">' . $data . '</textarea>';
}

function load($title, $url, $time = 15) {
    require __DIR__ . '/../file/load.php';
}