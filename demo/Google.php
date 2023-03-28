<?php
require __DIR__ . '/../vendor/autoload.php';

use zhqing\module\GoogleAuth;

$data = GoogleAuth::create(32);
var_dump($data);
$code = GoogleAuth::url($data, 'admin888', '测试');
var_dump($code);
$code = GoogleAuth::getCode($data);
var_dump($code);
$code = GoogleAuth::check($data, $code, true);
var_dump($code);
