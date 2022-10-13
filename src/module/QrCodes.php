<?php

namespace zhqing\module;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodes {

    /**
     * @param string $str
     * @param string $format
     * @param int $version
     * @param int $scale
     * @return mixed
     */
    public static function getBase(string $str, string $format = 'jpg', int $version = 5, int $scale = 5): mixed {
        $qro = new QROptions;
        $qro->version = $version;
        $qro->outputType = $format;
        $qro->addQuietzone = true;
        $qro->quietzoneSize = 1;
        $qro->scale = $scale;
        return (new QRCode($qro))->render($str);
    }
}