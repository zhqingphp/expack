<?php

namespace zhqing\extend\frame;

/**
 * 无限级分类处理方法
 * Class Classification
 * @package Common
 */
trait Sort {
    /**
     * 查找指定二维数组字段的值
     * @param $Data //要查找的数组
     * @param $Field //要查找的字段
     * @param $Val //要查找的值
     * @param bool $Type //是否模湖查找
     * @return array
     */
    public static function arraySearchFidel($Data, $Field, $Val, bool $Type = false): array {
        return array_filter($Data, function ($Row) use ($Field, $Val, $Type) {
            if (isset($Row[$Field])) {
                if (!empty($Type)) {
                    if (str_contains($Row[$Field], $Val)) {
                        return $Row[$Field];
                    }
                }
                return $Row[$Field] == $Val;
            }
        });
    }

    /**
     * 查找全部上级   false查找全部下级
     * @param $Data //查找的数组
     * @param $Val //查找的值
     * @param $Type //true 查找全部上级   false查找全部下级
     * @param string $Id //id字段名称
     * @param string $Pid //上下级字段名称
     * @param string $Key //返回字段名称的内容
     * @return mixed
     */
    public static function childParent($Data, $Val, $Type, string $Id = 'id', string $Pid = 'pid', string $Key = 'id'): mixed {
        if (!empty($Type)) {
            return array_slice(self::getParent($Data, $Val, $Id, $Pid, $Key), 1);
        } else {
            return self::getChild($Data, $Val, $Id, $Pid, $Key);
        }
    }

    /**
     * 获取下拉列表
     * @param  $Data //分类数据
     * @param $Name //分类字段名称
     * @param string $Id //数据唯一标识
     * @param string $Pid //数据库上级id
     * @param array $Arr
     * @return array
     */
    public static function optionArr($Data, $Name, string $Id = 'id', string $Pid = 'pid', array $Arr = []): array {
        if (!empty($Data)) {
            foreach ($Data as $v) {
                $Arr[$v[$Pid]][] = $v;
            }
            $Data = self::handleOption($Arr, $Name, $Id);
        }
        return $Data;
    }

    /**
     * @param  $Data //分类数据
     * @param $Name //分类字段名称
     * @param $Id //数据唯一标识
     * @param int $Pid
     * @param array $Arr
     * @param int $Spec
     * @return array
     */
    private static function handleOption($Data, $Name, $Id, int $Pid = 0, array $Arr = [], int $Spec = 0): array {
        $Spec = $Spec + 2;
        if (isset($Data[$Pid])) {
            if (!empty($Rs = $Data[$Pid])) {
                foreach ($Rs as $v) {
                    $v[$Name] = \str_repeat('&nbsp;&nbsp', $Spec) . '|--' . $v[$Name];
                    $Arr = \array_merge($Arr, self::handleOption($Data, $Name, $Id, $v[$Id], [$v], $Spec));
                }
            }
        }
        return $Arr;
    }

    /**
     * 获取全部下级id
     * @param $Data //查找的数组
     * @param $Val //查找的值
     * @param string $Id //id字段名称
     * @param string $Pid //上下级字段名称
     * @param string $Key //返回字段名称的内容
     * @param array $Arr
     * @return array
     */
    private static function getChild($Data, $Val, string $Id = 'id', string $Pid = 'pid', string $Key = 'id', array $Arr = []): array {
        foreach ($Data as $V) {
            if ($V[$Pid] == $Val) {
                $Arr[] = $V[$Key];
                $Arr = array_merge($Arr, self::getChild($Data, $V[$Id], $Id, $Pid, $Key));
            }
        }
        return $Arr;
    }

    /**
     * 获取全部上级id
     * @param $Data //查找的数组
     * @param $Val //查找的值
     * @param string $Id //id字段名称
     * @param string $Pid //上下级字段名称
     * @param string $Key //返回字段名称的内容
     * @param array $Arr
     * @return array
     */
    private static function getParent($Data, $Val, string $Id = 'id', string $Pid = 'pid', string $Key = 'id', array $Arr = []): array {
        foreach ($Data as $V) {
            if ($V[$Id] == $Val) {
                $Arr[] = $V[$Key];
                $Arr = array_merge($Arr, self::getParent($Data, $V[$Pid], $Id, $Pid, $Key));
            }
        }
        return $Arr;
    }
}