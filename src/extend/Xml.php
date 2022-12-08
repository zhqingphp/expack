<?php

namespace zhqing\extend;

use DOMDocument;

class Xml {

    /**
     * 获取Xml内容
     * @param $arr
     * @param $key
     * @param $default
     * @return mixed
     */
    public static function get($arr, $key, $default = null): mixed {
        return (!empty($data = ($arr['data'][$key]['data'] ?? $default)) ? $data : $default);
    }

    /**
     * 简单转array
     * @param string|array $xml
     * @return mixed
     */
    public static function xmlToArr(string|array $xml): mixed {
        return json_decode(json_encode(simplexml_load_string((is_array($xml) ? (@file_get_contents($xml[key($xml)])) : $xml), "SimpleXMLElement", LIBXML_NOCDATA)), true);
    }

    /**
     * 简单转xml
     * @param array $arr
     * @param string $name
     * @param string $version
     * @param string $encoding
     * @return string
     */
    public static function arrToXml(array $arr, string $name = "XmlName", string $version = "1.0", string $encoding = "UTF-8"): string {
        $xml = "<?xml version=\"{$version}\" encoding=\"{$encoding}\"?>";
        $xml .= "<{$name}>";
        foreach ($arr as $k => $v) {
            $xml .= "<{$k}>{$v}</{$k}>";
        }
        $xml .= "</{$name}>";
        return $xml;
    }

    /**
     * 支持一级属性标签
     * Array转Xml
     * @param array $arr //array
     * @param string $name //根名称
     * @param string $version //板本
     * @param string $encoding //编码
     * @return string
     */
    public static function toXml(array $arr, string $name = "XmlName", string $version = "1.0", string $encoding = "UTF-8"): string {
        $tagFun = function ($arr, $str = '') {
            if (count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $str .= $k . "=\"{$v}\" ";
                }
                $str = " " . trim($str);
            }
            return $str;
        };
        $arr['tag']['version'] = ($arr['tag']['version'] ?? $version);
        $arr['tag']['encoding'] = ($arr['tag']['encoding'] ?? $encoding);
        $xml = "<?xml" . ($tagFun($arr['tag'])) . "?>";
        $xml .= "<" . ($arr['name'] ?? $name) . $tagFun(($arr['attr'] ?? [])) . ">";
        $xml .= self::toXmlHandle(($arr['data'] ?? ''));
        $xml .= "</" . ($arr['name'] ?? $name) . ">";
        return $xml;
    }

    /**
     * 支持一级属性标签
     * XML转Array
     * @param array|string $data //str为xml内容,array为文件
     * @return array
     */
    public static function toArr(array|string $data): array {
        $content = (is_array($data) ? (@file_get_contents($data[key($data)])) : $data);
        $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
        $attr = [];
        $attributes = $xml->attributes();
        if (count($attributes) > 0) {
            foreach ($attributes as $k => $v) {
                $attr[$k] = (string)$v;
            }
        }
        $arr['tag'] = self::getXmlTag($content);
        $arr['name'] = $xml->getName();
        $arr['attr'] = $attr;
        $arr['data'] = self::toArrHandle($xml->children());
        return $arr;
    }

    /**
     * 支持一级属性标签
     * XML转Array
     * @param array|string $data //str为xml内容,array为文件
     * @param string $type //可选text
     * @return array
     */
    public static function toArray(array|string $data, string $type = 'val'): array {
        $content = (is_array($data) ? (@file_get_contents($data[key($data)])) : $data);
        $xml = new DOMDocument();
        $xml->loadXML($content);
        $root = $xml->documentElement;//根目录
        $index = $xml->getElementsByTagName($root->tagName);//根对像
        $obj = $index->item(0)->attributes;
        if ($obj->length > 0) {
            for ($i = 0; $i < $obj->length; $i++) {
                $tag = $obj->item($i);
                if (!empty($tag->nodeName)) {
                    $attr[$tag->nodeName] = ($type == 'val' ? $tag->nodeValue : $tag->textContent);
                }
            }
        }
        $arr['tag'] = self::getXmlTag($content);
        $arr['name'] = $root->tagName;
        $arr['attr'] = (!empty($attr) ? $attr : []);
        $arr['data'] = self::toArrayHandle($index->item(0), $type);
        return $arr;
    }


    /**
     * @param $content
     * @return array
     */
    private static function getXmlTag($content): array {
        preg_match_all("/ (.*?)\=(\'|\")(.*?)(\'|\")/i", explode("?>", $content)[0], $array);
        if (isset($array[1])) {
            foreach ($array[1] as $k => $v) {
                $arr[$v] = $array[3][$k] ?? '';
            }
        }
        return (!empty($arr) ? $arr : []);
    }

    /**
     * Array转Xml处理
     * @param $arr
     * @return string
     */
    private static function toXmlHandle($arr): string {
        $xml = '';
        $attr = function ($v, $attrStr = '') {
            $attr = ($v['attr'] ?? []);
            if (count($attr) > 0) {
                foreach ($attr as $a => $s) {
                    $attrStr .= $a . "=\"{$s}\" ";
                }
                $attrStr = " " . trim($attrStr);
            }
            return $attrStr;
        };
        if (is_array($arr) && !empty($arr)) {
            foreach ($arr as $k => $v) {
                $attrStr = $attr($v);
                if (empty(isset($v['data']))) {
                    foreach ($v as $s) {
                        $attrStr = $attr($s);
                        $xml .= "<" . ($k . $attrStr) . ">";
                        $xml .= (is_array($s['data']) ? self::toXmlHandle($s['data']) : $s['data']);
                        $xml .= "</" . $k . ">";
                    }
                } else {
                    $xml .= "<" . ($k . $attrStr) . ">";
                    $xml .= ($v['data'] ?? '');
                    $xml .= "</" . $k . ">";
                }
            }
        }
        return $xml;
    }

    /**
     * XMl转Array处理
     * @param $xml
     * @return array
     */
    private static function toArrHandle($xml): array {
        foreach ($xml as $child) {
            $attr = [];
            $attributes = $child->attributes();
            if (count($attributes) > 0) {
                foreach ($attributes as $k => $v) {
                    $attr[$k] = (string)$v;
                }
            }
            $tagName = $child->getName();
            $array = ['attr' => $attr];
            if ($child->count() > 0) {
                $array['data'] = self::toArrHandle($child);
                $arr[$tagName][] = $array;
            } else {
                $array['data'] = (string)$child;
                if (isset($arr[$tagName])) {
                    if (isset($arr[$tagName]['data'])) {
                        $arr[$tagName] = array_merge([$arr[$tagName]], [$array]);
                    } else {
                        $arr[$tagName][] = $array;
                    }
                } else {
                    $arr[$tagName] = $array;
                }
            }
        }
        return !empty($arr) ? $arr : [];
    }

    /**
     * XMl转Array处理
     * @param $obj
     * @param $type
     * @return array
     */
    private static function toArrayHandle($obj, $type): array {
        $child = $obj->childNodes;
        $arr = [];
        if ($child->length > 0) {
            foreach ($child as $v) {
                $attr = [];
                if (!empty($tagName = ($v->tagName ?? ''))) {
                    if ($v->attributes->length > 0) {
                        foreach ($v->attributes as $tag) {
                            if (!empty($nodeName = ($tag->nodeName ?? ''))) {
                                $attr[$nodeName] = ($type == 'val' ? $tag->nodeValue : $tag->textContent);
                            }
                        }
                    }
                    $array = ['attr' => $attr];
                    if ($v->childElementCount > 0) {
                        $array['data'] = self::toArrayHandle($v, $type);
                        $arr[$tagName][] = $array;
                    } else {
                        $array['data'] = ($type == 'val' ? $v->nodeValue : $v->textContent);
                        if (isset($arr[$tagName])) {
                            if (isset($arr[$tagName]['data'])) {
                                $arr[$tagName] = array_merge([$arr[$tagName]], [$array]);
                            } else {
                                $arr[$tagName][] = $array;
                            }
                        } else {
                            $arr[$tagName] = $array;
                        }
                    }
                }
            }
        }
        return $arr;
    }
}