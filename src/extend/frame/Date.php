<?php

namespace zhqing\extend\frame;

trait Date {

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
}