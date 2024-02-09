<?php

namespace zhqing\mysql;

use Exception;

trait Import {

    /**
     * sql文件导入数据库
     * @param string $FilePath sql文件路径
     * @return array
     */
    public function import(string $FilePath): array {
        if (!empty(is_file($FilePath))) {
            try {
                $sql = '';
                $body = @file_get_contents($FilePath);
                $ds = 0;
                $de = 0;
                $cs = 0;
                $ce = 0;
                $is = 0;
                $ie = 0;
                $ss = 0;
                $se = 0;
                $fail = [];
                $body = preg_replace('/\/\*(?![^\/]*\*\/)(.*?)(?=\*\/)/s', '', $body);
                $body = str_replace("\r\n", PHP_EOL, $body);
                $array = explode(PHP_EOL, $body);
                foreach ($array as $v) {
                    if (empty($v) || str_starts_with($v, '--') || str_starts_with($v, '/*') || str_starts_with($v, '*/')) {
                        continue;
                    }
                    $sql .= " " . $v;
                    if (str_ends_with($v, ';')) {
                        $exec = trim($sql);
                        $res = $this->exec($this->strips($exec));
                        if (str_starts_with($exec, 'DROP')) {
                            if ($res !== false) {
                                ++$ds;
                            } else {
                                ++$de;
                                $fail[] = $exec;
                            }
                        } else if (str_starts_with($exec, 'CREATE')) {
                            if ($res !== false) {
                                ++$cs;
                            } else {
                                ++$ce;
                                $fail[] = $exec;
                            }
                        } else if (str_starts_with($exec, 'INSERT')) {
                            if ($res !== false) {
                                ++$is;
                            } else {
                                ++$ie;
                                $fail[] = $exec;
                            }
                        } else {
                            if ($res !== false) {
                                ++$ss;
                            } else {
                                ++$se;
                                $fail[] = $exec;
                            }
                        }
                        $sql = '';
                    }

                }
                $data['code'] = 200;
                $data['data'] = [
                    'count' => ($ds + $de + $cs + $ce + $is + $ie + $ss + $se),//执行总数
                    'success' => ($ds + $cs + $is + $ss),//成功总数
                    'error' => ($de + $ce + $ie + $se),//失败总数
                    'drop_success' => $ds,//删除表单成功数量
                    'drop_error' => $de,//删除表单失败数量
                    'create_success' => $cs,//创建表单成功数量
                    'create_error' => $ce,//创建表单失败数量
                    'insert_success' => $is,//添加记录成功数量
                    'insert_error' => $ie,//添加记录失败数量
                    'set_success' => $ss,//其它成功数量
                    'set_error' => $se,//其它失败数量
                    'fail' => $fail,//失败的记录
                ];
            } catch (Exception $e) {
                $data['code'] = 400;
                $data['data'] = $e->getMessage();
            }
        } else {
            $data['code'] = 400;
            $data['data'] = "file does not exist";
        }
        return $data;
    }
}