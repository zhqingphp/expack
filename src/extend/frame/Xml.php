<?php

namespace zhqing\extend\frame;

trait Xml {
    /**
     * Xml转Array
     * @param string|array $xml //str为xml内容,array为文件
     * @return mixed
     */
    public static function xmlToArr(string|array $xml): mixed {
        return json_decode(json_encode(simplexml_load_string((is_array($xml) ? (@file_get_contents($xml[key($xml)])) : $xml), "SimpleXMLElement", LIBXML_NOCDATA)), true);
    }

    /**
     * Array转Xml
     * @param array $arr //array
     * @param string $name //根名称
     * @param string $version //板本
     * @param string $encoding //编码
     * @param int $i
     * @param int $j
     * @return string
     */
    public static function arrToXml(array $arr, string $name = "XmlName", string $version = "1.0", string $encoding = "UTF-8", int $i = 0, int $j = 0): string {
        $attr = function ($v, $attrStr = '') {
            if (!empty($attr = ($v['@attributes'] ?? []))) {
                foreach ($attr as $a => $s) {
                    $attrStr .= $a . "=\"{$s}\" ";
                }
                $attrStr = " " . trim($attrStr);
            }
            return $attrStr;
        };
        $unset = function ($v) {
            if (is_array($v) && isset($v['@attributes'])) {
                unset($v['@attributes']);
            }
            return $v;
        };
        $xml = $i > 0 ? "" : "<?xml version=\"{$version}\" encoding=\"{$encoding}\"?><{$name}" . ($attr($arr)) . ">";
        $arr = $unset($arr);
        $j = $j > 0 ? $j : count($arr);
        if (is_array($arr) && !empty($arr)) {
            foreach ($arr as $k => $v) {
                ++$i;
                if (is_array($v) && !empty($v)) {
                    foreach ($v as $s) {
                        $xml .= "<{$k}" . $attr($s) . ">" . self::arrToXml($unset($s), $name, $version, $encoding, $i, $j) . "</{$k}>";
                    }
                } else {
                    $xml .= "<{$k}>" . (is_array($v) ? '' : $v) . "</{$k}>";
                }
            }
        }
        return ($xml . (($i != $j) ? "" : ("</{$name}>")));
    }
}