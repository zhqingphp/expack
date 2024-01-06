<?php

namespace zhqing\extend\frame;

use zhqing\extend\Curl;

trait Tool {
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