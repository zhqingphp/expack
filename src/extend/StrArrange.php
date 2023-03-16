<?php

namespace zhqing\extend;

class StrArrange {
    /**
     * 根据总列数生成EXCEL列名的算法
     * @param $i
     * @param string $str
     * @param int $iv
     * @return string
     */
    public static function aZ($i, string $str = '', int $iv = 26): string {
        while ($i > 0) {
            $int = $i % $iv;
            $int = ($int == 0) ? $iv : $int;
            $str = strtoupper(chr($int + 64)) . $str;
            $i = ($i - $int) / $iv;
        }
        return $str;
    }

    /**
     * 根据总列数生成EXCEL列名的算法
     * 最多输出二个组合
     * @param $i
     * @param string $data
     * @return string
     */
    public static function aAZZ($i, string $data = ''): string {
        $str = 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';
        $arr = explode(',', $str);
        $iv = count($arr);
        $key = ($i - 1);
        if ($i > $iv) {
            $number = ($i / $iv);
            $int = intval($number);
            $key = ($i - ($int * $iv));
            $key = (($key > 0 ? $key : $iv) - 1);
            $int = (($int > 0 ? is_int($number) ? $int - 2 : $int - 1 : ($iv - 1)));
            $data = join('', array_slice($arr, $int, 1));
        }
        return $data . $arr[$key] ?? $arr[0];
    }

    /**
     * 全部结合
     * row('ABC',2)
     * @param $letters
     * @param $num
     * @return array
     */
    public static function row($letters, $num): array {
        $last = str_repeat($letters[0], $num);
        $result = array();
        while ($last != str_repeat($letters[strlen($letters) - 1], $num)) {
            $result[] = $last;
            $last = self::charAdd($letters, $last, $num - 1);
        }
        $result[] = $last;
        return $result;
    }

    /**
     * @param $digits
     * @param $string
     * @param $char
     * @return mixed
     */
    private static function charAdd($digits, $string, $char): mixed {
        $chang = function ($string, $char, $start = 0, $end = 0) {
            if ($end == 0) $end = strlen($string) - 1;
            for ($i = $start; $i <= $end; $i++) {
                $string[$i] = $char;
            }
            return $string;
        };
        if ($string[$char] != $digits[strlen($digits) - 1]) {
            $string[$char] = $digits[strpos($digits, $string[$char]) + 1];
            return $string;
        } else {
            $string = $chang($string, $digits[0], $char);
            return self::charAdd($digits, $string, $char - 1);
        }
    }
}