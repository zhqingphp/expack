<?php

namespace zhqing\extend\frame;

use zhqing\extend\Curl;

trait Tool {
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

    public static function downAv($dir) {
        $k = 0;
        $e = 0;
        $dirAr = File::getDirList($dir . '/ts');
        $dArr = [];
        foreach ($dirAr as $k => $v) {
            $a = explode('-', trim($k, '/'));
            $dArr[($a[key($a)])] = '';
        }
        for ($i = 0; $i <= 9999; $i++) {
            if (empty(isset($dArr[$i]))) {
                $url = 'http://051005.222avs.net/video/' . $i . '/';
                $curl = Curl::get($url)->exec();
                if ($curl->code() == '200') {
                    $k++;
                    $body = $curl->body();
                    preg_match_all('/<source src="(.*?)"/i', $body, $arr);
                    if (isset($arr[1][0])) {
                        preg_match_all('/\<title\>(.*?)\<\/title\>/i', $body, $tArr);
                        $c = Curl::get($arr[1][0])->exec();
                        if ($curl->code() == '200') {
                            $name = self::strRep($tArr[1][0], '！');
                            $name = self::strRep($name, '千百撸');
                            $name = self::strRep($name, '/');
                            $name = self::strRep($name, '\\');
                            $name = self::strRep($name, ' ');
                            $file = $dir . '/ts/' . $i . '-' . (trim($name, '-')) . '.ts';
                            self::mkDir(dirname($file));
                            $c->saveFile($file);
                        }
                        echo $i . "ok\r\n";
                    }
                } else {
                    $e++;
                    File::addFileData($dir . '/err.txt', $url . "\r\n", "a+");
                    echo $i . "err\r\n";
                }
            } else {
                echo $i . "ok\r\n";
            }
        }
        echo "完成{$k},错误{$e},共{$i}\r\n";
    }
}