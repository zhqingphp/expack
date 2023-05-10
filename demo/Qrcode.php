<?php
require __DIR__ . '/../vendor/autoload.php';

use zhqing\module\QrCodes;

echo QrCodes::imgBase(123, __DIR__ . '/1.png');