<?php

namespace zhqing\extend\frame;

use zhqing\extend\Xml as x;

trait Xml {
    /**
     * XML转array|object
     * @param string|array $xml
     * @param bool $type //true=array false=object
     * @return mixed
     */
    public static function xmlToArr(string|array $xml, bool $type = true): mixed {
        return x::xmlToArr($xml, $type);
    }

    /**
     * array|object转xml
     * @param array|object $arr
     * @param string|null $name
     * @param string|null $version
     * @param string|null $encoding
     * @param string $tag
     * @return string
     */
    public static function arrToXml(array|object $arr, string|null $name = "XmlName", string|null $version = "1.0", string|null $encoding = "UTF-8", string $tag = "@attributes"): string {
        return x::arrToXml($arr, $name, $version, $encoding, $tag);
    }
}