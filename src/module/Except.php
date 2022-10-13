<?php

namespace zhqing\module;

use zhqing\extend\Frame;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Exception;

class Except {


    /**
     * 获取文件内容
     * @param $file
     * @param string $format //Xlsx|Xls|Xml|Ods|Slk|Gnumeric|Html|Csv
     * @return array
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public static function getArray($file, string $format = ''): array {
        if (empty($format)) {
            $format = Frame::getPath($file);
        }
        return (IOFactory::createReader(ucfirst($format)))->setReadDataOnly(true)->load($file)->getSheet(0)->toArray();
    }
}