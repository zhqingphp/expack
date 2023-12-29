<?php

namespace zhqing\extend\frame;

trait Arrays {

    /**
     * 根据数组的值从小到大排序
     * @param $array
     * @param $key
     * @return array
     */
    public static function arrAsc($array, $key): array {
        array_multisort(array_column($array, $key), SORT_ASC, $array);
        return $array;
    }

    /**
     * 根据数组的值从大到小排序
     * @param $array
     * @param $key
     * @return array
     */
    public static function arrDesc($array, $key): array {
        array_multisort(array_column($array, $key), SORT_DESC, $array);
        return $array;
    }

    /**
     * 通过a.b.c.d生成多维数组
     * @param $name //名字
     * @param $data //内容
     * @return mixed
     */
    public static function setStrArr($name, $data): mixed {
        $arr = \explode('.', $name);
        $arr[] = $data;
        while (\count($arr) > 1) {
            $v = \array_pop($arr);
            $k = \array_pop($arr);
            $arr[] = [$k => $v];
        }
        return $arr[\key($arr)];
    }

    /**
     * 通过a.b.c.d获取数组内容
     * @param $data //要取值的数组
     * @param string $name //支持aa.bb.cc.dd这样获取数组内容
     * @param $default //默认值
     * @return mixed
     */
    public static function getStrArr($data, string $name, $default = null): mixed {
        if (!isset($name)) {
            return $data;
        } else if (!empty($info = self::getArr($data, $name))) {
            return $info;
        } else {
            $nameArr = \explode('.', $name);
            foreach ($nameArr as $v) {
                if (isset($data[$v])) {
                    $data = $data[$v] ?: $default;
                    $data = isset($data[$v]) ? ($data[$v]) : $default;
                } else {
                    return $default;
                }
            }
            return $data;
        }
    }

    /**
     * 判断几维数组
     * @param $arr
     * @param int $j
     * @return int
     */
    public static function arrLevel($arr, int $j = 0): int {
        if (empty(\is_array($arr))) {
            return $j;
        }
        foreach ($arr as $K) {
            $v = self::arrLevel($K);
            if ($v > $j) {
                $j = $v;
            }
        }
        return $j + 1;
    }

    /**
     * 多维数组转1维,清空键名
     * @param $arr
     * @param array $data
     * @return array
     */
    public static function oneArr($arr, array $data = []): array {
        foreach ($arr as $K => $v) {
            if (\is_array($v)) {
                $data = self::oneArr($v, $data);
            } else {
                $data [] = $v;
            }
        }
        return $data;
    }

    /**
     * 数组根据值的长度排序
     * @param array $data //默认由高到低
     * @param bool $type //true=由低到高,false=由高到低
     * @return array
     */
    public static function arrLenSort(array $data, bool $type): array {
        \usort($data, function ($a, $b) use ($type) {
            return ($type ? \strlen($a) - \strlen($b) : \strlen($b) - \strlen($a));
        });
        return $data;
    }

    /**
     * 不是二维数组返回二维数组
     * @param array $data //判断的数组
     * @param null $key //要判断的Key
     * @return array
     */
    public static function isTwoArr(array $data, $key = null): array {
        if (!empty($data)) {
            if (empty(\is_array($data[($key ?? \key($data))]))) {
                $data = [$data];
            }
        }
        return $data;
    }

    /**
     * 合并两个多维数组
     * @param array $array
     * @param array $arr
     * @return array
     */
    public static function arrayMerge(array $array, array $arr): array {
        $new = array_merge($array, $arr);
        foreach ($new as $k => $v) {
            if (is_array($v) && isset($array[$k]) && isset($arr[$k])) {
                $new[$k] = self::arrayMerge($array[$k], $arr[$k]);
            }
        }
        return $new;
    }

    /**
     * 通过$reset获取$array
     * @param array $reset ['aa']=获取aa  ['aa as bb']=aa设置别名bb  ['aa as bb'=>'11']=aa设置别名bb(空值时设置11),['bb'=>'22']=bb设置值22
     * @param array $array 数据
     * @param array $arr 返回预设置值
     * @return array
     */
    public static function getArrDate(array $reset, array $array, array $arr = []): array {
        foreach ($reset as $k => $v) {
            if (is_numeric($k)) {
                $old_key = trim($v);
                $new_key = $old_key;
                $value = explode(" as ", $old_key);
                if (count($value) == 2) {
                    $old_key = isset($value[0]) ? (!empty($value[0]) ? trim($value[0]) : $v) : $v;
                    $new_key = isset($value[1]) ? (!empty($value[1]) ? trim($value[1]) : $old_key) : $old_key;
                }
                $arr[$new_key] = $array[$old_key] ?? '';
            } else {
                $val = $v;
                $old_key = trim($k);
                $new_key = $old_key;
                $value = explode(" as ", $old_key);
                if (count($value) == 2) {
                    $old_key = isset($value[0]) ? (!empty($value[0]) ? trim($value[0]) : $k) : $k;
                    $new_key = isset($value[1]) ? (!empty($value[1]) ? trim($value[1]) : $old_key) : $old_key;
                    $val = $array[$old_key] ?? $val;
                }
                $arr[$new_key] = $val;
            }
        }
        return $arr;
    }
}