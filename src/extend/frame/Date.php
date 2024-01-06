<?php

namespace zhqing\extend\frame;

trait Date {

    /**
     * 生成时间列表
     * @param string|int $top //开始时间
     * @param string|int $end //结束时间
     * @param int $time //相隔时间
     * @param string $type //可选top,end
     * @param array $data
     * @return array
     */
    public static function getTimeList(string|int $top, string|int $end, int $time = 10, string $type = '', array $data = []): array {
        $topInt = is_numeric($top) ? $top : strtotime($top);
        $endInt = is_numeric($end) ? $end : strtotime($end);
        while (true) {
            $top_time = date('Y-m-d H:i:s', $topInt);
            $topInt = $topInt + $time;
            $end_time = date('Y-m-d H:i:s', (($topInt >= $endInt) ? $endInt : $topInt));
            if ($type == 'top') {
                $data[] = $top_time;
            } else if ($type == 'end') {
                $data[] = $end_time;
            } else {
                $data[] = ['top' => $top_time, 'end' => $end_time];
            }
            if ($topInt >= $endInt) {
                break;
            }
        }
        return $data;
    }

    /**
     * 获取本周星期(1-7)的日期
     * @param int $s 要获取的星期(1-7)
     * @param int $data //当前时间
     * @return int
     */
    public static function getWDate(int $s = 1, int $data = 0): int {
        return ($data > 0 ? $data : time()) - (60 * 60 * 24 * ((date('w', ($data > 0 ? $data : time())) ?: 7) - $s));
    }

    /**
     * @param $type //格式
     * @param string|array|int $format string|array=返回格式,int=设置时间
     * @param int|string $time 设置时间
     * @return array
     */
    public static function getDateTime($type, string|array|int $format = '', int|string $time = 0): array {
        $time = (!empty($format) && is_numeric($format)) ? $format : (is_string($time) ? strtotime($time) : ($time > 0 ? $time : time()));
        switch ($type) {
            case 1:
                //今天
                $data['top'] = date('Y-m-d 00:00:00', $time);
                $data['end'] = date('Y-m-d 23:59:59', $time);
                break;
            case 2:
                //昨天
                $time = strtotime('-1 day', $time);
                $data['top'] = date('Y-m-d 00:00:00', $time);
                $data['end'] = date('Y-m-d 23:59:59', $time);
                break;
            case 3:
                //本周
                $data['top'] = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $time), date('d', $time) - date('w', $time) + 1, date('Y', $time)));
                $data['end'] = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', $time), date('d', $time) - date('w', $time) + 7, date('Y', $time)));
                break;
            case 4:
                //上周
                $time = strtotime('-1 week', $time);
                $data['top'] = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $time), date('d', $time) - date('w', $time) + 1, date('Y', $time)));
                $data['end'] = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', $time), date('d', $time) - date('w', $time) + 7, date('Y', $time)));
                break;
            case 5:
                //近一周
                $data['top'] = date('Y-m-d H:i:s', strtotime('-7 day', $time));
                $data['end'] = date('Y-m-d H:i:s', $time);
                break;
            case 6:
                //本月
                $data['top'] = date('Y-m-01 00:00:00', $time);
                $data['end'] = date('Y-m-t 23:59:59', $time);
                break;
            case 7:
                //上月
                $time = strtotime('-1 month', $time);
                $data['top'] = date('Y-m-01 00:00:00', $time);
                $data['end'] = date('Y-m-t 23:59:59', $time);
                break;
            case 8:
                //近一月
                $data['top'] = date('Y-m-d H:i:s', strtotime('-1 month', $time));
                $data['end'] = date('Y-m-d H:i:s', $time);
                break;
            case 9:
                //近三月
                $data['top'] = date('Y-m-d H:i:s', strtotime('-3 month', $time));
                $data['end'] = date('Y-m-d H:i:s', $time);
                break;
            case 10:
                //本季度
                $quarter = ceil((date('n', $time)) / 3);//当月是第几季度
                $data['top'] = date('Y-m-d H:i:s', mktime(0, 0, 0, $quarter * 3 - 3 + 1, 1, date('Y', $time)));
                $data['end'] = date('Y-m-d H:i:s', $time);
                break;
            case 11:
                //上季度
                $y = date('Y', $time);
                $quarter = (ceil((date('n', $time)) / 3) - 1) * 3;//上季度是第几季度
                $data['top'] = date('Y-m-d H:i:s', mktime(0, 0, 0, $quarter - 3 + 1, 1, $y));
                $data['end'] = date('Y-m-d H:i:s', mktime(23, 59, 59, $quarter, date('t', mktime(0, 0, 0, $quarter, 1, $y)), $y));
                break;
            case 12:
                //第1季度
                $y = date('Y', $time);
                $quarter = 1 * 3;//上季度是第几季度
                $data['top'] = date('Y-m-d H:i:s', mktime(0, 0, 0, $quarter - 3 + 1, 1, $y));
                $data['end'] = date('Y-m-d H:i:s', mktime(23, 59, 59, $quarter, date('t', mktime(0, 0, 0, $quarter, 1, $y)), $y));
                break;
            case 13:
                //第2季度
                $y = date('Y', $time);
                $quarter = 2 * 3;//上季度是第几季度
                $data['top'] = date('Y-m-d H:i:s', mktime(0, 0, 0, $quarter - 3 + 1, 1, $y));
                $data['end'] = date('Y-m-d H:i:s', mktime(23, 59, 59, $quarter, date('t', mktime(0, 0, 0, $quarter, 1, $y)), $y));
                break;
            case 14:
                //第3季度
                $y = date('Y', $time);
                $quarter = 3 * 3;//上季度是第几季度
                $data['top'] = date('Y-m-d H:i:s', mktime(0, 0, 0, $quarter - 3 + 1, 1, $y));
                $data['end'] = date('Y-m-d H:i:s', mktime(23, 59, 59, $quarter, date('t', mktime(0, 0, 0, $quarter, 1, $y)), $y));
                break;
            case 15:
                //第4季度
                $y = date('Y', $time);
                $quarter = 4 * 3;//上季度是第几季度
                $data['top'] = date('Y-m-d H:i:s', mktime(0, 0, 0, $quarter - 3 + 1, 1, $y));
                $data['end'] = date('Y-m-d H:i:s', mktime(23, 59, 59, $quarter, date('t', mktime(0, 0, 0, $quarter, 1, $y)), $y));
                break;
            case 16:
                //当年
                $data['top'] = date('Y-01-01 00:00:00', $time);
                $data['end'] = date('Y-m-d H:i:s', $time);
                break;
            case 17:
                //去年
                $data['top'] = date('Y-01-01 00:00:00', strtotime('-1 year', $time));
                $data['end'] = date('Y-12-31 23:59:59', strtotime('-1 year', $time));
                break;
            case 18:
                //近一年
                $data['top'] = date('Y-m-d H:i:s', strtotime('-1 year', $time));
                $data['end'] = date('Y-m-d H:i:s', $time);
                break;
            default:
                //时间
                list($t1, $t2) = explode(" ", (!empty($time) ? $time : microtime()));
                $data['top'] = (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
                $data['end'] = date(((!empty($type) && is_string($type)) ? $type : 'Y-m-d H:i:s'), $time);
                break;
        }
        if (!empty($format)) {
            if (is_array($format)) {
                $data['top'] = date($format[0], strtotime($data['top']));
                $data['end'] = date($format[1], strtotime($data['end']));
            } else if (is_string($format)) {
                $data['top'] = date($format, strtotime($data['top']));
                $data['end'] = date($format, strtotime($data['end']));
            }
        }
        return $data;
    }

    /**
     * 月帐期数表列表
     * @param int $time //系统时间
     * @param string $date //月帐单已知年度第一期数
     * @param array $topData
     * @param int $j
     * @param string $current
     * @return array
     */
    public static function periodBillTime(int $time = 0, string $date = '', array $topData = [], int $j = 0, string $current = ''): array {
        $time = (!empty($time) ? $time : (strtotime(date('Y-m-d')) - 60 * 60 * 8));
        $start = strtotime((!empty($date) ? $date : '2020-12-28'));
        $end = strtotime("+27 day", $start);
        $data[date("Y-m-d", $start)] = date("Y-m-d", $end);
        $is = 0;
        for ($i = 1; $i <= 25; $i++) {
            $d = $i * 28;
            $s = strtotime("+{$d} day", $start);
            $e = strtotime("+{$d} day", $end);
            if ($s <= $time && $e >= $time) {
                $current = date("Y-m-d", $s);
                $is = $i;
            }
            $data[date("Y-m-d", $s)] = date("Y-m-d", $e);
        }
        $j = $j + 1;
        if (empty($is)) {
            return self::periodBillTime($time, date("Y-m-d", strtotime("+1 day", strtotime(end($data)))), $data, $j);
        } else {
            $data = array_slice($data, 0, $is + 4, true);
            if ($j > 1 && count($data) <= 13) {
                $data = array_slice($topData, count($topData) - 13, 13, true) + $data;
            }
        }
        return ['list' => $data, 'start' => $current, 'end' => $data[$current]];
    }

    /**
     * 生成月日星期几
     * @param $time //时间
     * @param $layer //语言
     * @param bool $type
     * @return string
     */
    public static function getWeek($time, $layer, bool $type = false): string {
        $time = !empty($time) ? (!empty($type) ? strtotime($time) : $time) : time();
        if ($layer == 'en-us') {
            $weekArr = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
            $week = date("d M", $time) . " (" . $weekArr[date("w", $time)] . ")";
        } else {
            $weekArr = ["日", "一", "二", "三", "四", "五", "六"];
            $week = date("m月d日", $time) . " 星期" . $weekArr[date("w", $time)];
        }
        return $week;
    }

    /**
     * 获取上周一和周日的时间
     * @param string $time
     * @return array
     */
    public static function getLastWeek(string $time = ''): array {
        $time = !empty($time) ? strtotime($time) : time();
        $w = !empty($w = date("w", $time)) ? $w : 7;
        $start = date('Y-m-d', strtotime("-" . ($w + 6) . " days", $time));
        $end = date('Y-m-d', strtotime("-{$w} days", $time));
        return [$start, $end];
    }
}