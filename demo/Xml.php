<?php
require __DIR__ . '/../vendor/autoload.php';

use zhqing\extend\Xml;

$xml = '<?xml version="1.0" encoding="UTF-8"?>
<serverresponse>
    <code>607</code>
    <title>测试</title>
    <history id="1234">
        <date>内容</date>
        <date_name>名称</date_name>
    </history>
</serverresponse>';

$arr = Xml::toArr($xml);
$array = Xml::toArray($xml);
$x = Xml::toXml($arr);
$xx = Xml::toXml($array);
ts($x);
ts($xx);
ps($arr);
ps($array);
