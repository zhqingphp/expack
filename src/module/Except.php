<?php

namespace zhqing\module;

use zhqing\extend\Frame;
use zhqing\extend\StrArrange;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

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

    /**
     * @param $data
     * @param $array
     * @param $file
     */
    public static function save($data, $array, $file) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $i = 0;
        $arr = [];
        foreach ($data as $k => $v) {
            ++$i;
            $arr[$k] = StrArrange::aZ($i);
            $sheet->setCellValue($arr[$k] . '1', $v);
        }
        $i = 1;
        foreach ($array as $v) {
            ++$i;
            foreach ($data as $k => $c) {
                $sheet->setCellValue($arr[$k] . $i, $v[$k]);
            }
        }
        $format = ucfirst(Frame::getPath($file));
        if ($format == 'Xls') {
            $writer = new Xls($spreadsheet);
        } elseif ($format == 'Xlsx') {
            $writer = new Xlsx($spreadsheet);
        } else {
            $writer = new Csv($spreadsheet);
        }
        Frame::mkDir(dirname($file));
        $writer->save($file);
    }

}