<?php

namespace zhqing\extend;

class StrArrange {
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