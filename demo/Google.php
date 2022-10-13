<?php
require __DIR__ . '/../vendor/autoload.php';

use zhqing\module\GoogleAuthenticator;

$ga = new GoogleAuthenticator();


$secret = $ga->createSecret();
//这是生成的密钥，每个用户唯一一个，为用户保存起来用于验证
ps($secret);

//下面为生成二维码，内容是一个URI地址(otpauth://totp/账号?secret=密钥&issuer=标题)
$qrCodeUrl = $ga->getQRCodeGoogleUrl('Blog', $secret);

ps($qrCodeUrl);


ps($ga->getQRCode('Blog', $secret, '111'));

//验证
$oneCode = $ga->getCode($secret);
ps($oneCode);
$checkResult = $ga->verifyCode($secret, $oneCode);    // 2 = 2*30sec clock tolerance
if ($checkResult) {
    echo 'OK';
} else {
    echo 'FAILED';
}
