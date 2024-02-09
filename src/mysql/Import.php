<?php

namespace zhqing\mysql;

use Exception;

/**
 * 导入sql文件
 */
trait Import {

    /**
     * 导入sql文件
     * @param string $FilePath sql文件路径
     * @param string|bool $charset 字符集,string=自定(空不作修改),true=默认数据库配置,false=删除
     * @param string|bool $collate 排序规则,string=自定(空不作修改),true=默认数据库配置,false=删除
     * @param string|bool $engine 引擎,string=自定(空不作修改),true=默认数据库配置
     * @return array
     */
    public function import(string $FilePath, string|bool $charset = true, string|bool $collate = true, string|bool $engine = ''): array {
        $start = microtime(true);
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
                    if (
                        empty($v) ||
                        str_starts_with($v, '--') ||
                        str_starts_with($v, '/*') ||
                        str_starts_with($v, '*/')
                    ) {
                        continue;
                    }
                    $sql .= " " . $v;
                    if (str_ends_with($v, ';')) {
                        $exec = trim($sql);
                        $sql = $this->strips($exec);
                        if (str_starts_with($exec, 'DROP')) {
                            if ($this->exec($sql) !== false) {
                                ++$ds;
                            } else {
                                ++$de;
                                $fail[] = $exec;
                            }
                        } else if (str_starts_with($exec, 'CREATE')) {
                            //修改字符集,排序规则,引擎
                            $sql = preg_replace_callback("/(ENGINE=|CHARSET=|COLLATE=)([^ ]+)/", function ($arr) use ($charset, $collate, $engine) {
                                $key = $arr[1] ?? '';
                                $k = strtolower($key);
                                if (str_starts_with($k, strtolower('CHARSET'))) {
                                    if (!empty($charset)) {
                                        if ($charset === true) {
                                            return $key . $this->config['charset'];
                                        }
                                        return $key . $charset;
                                    } elseif ($charset !== false) {
                                        return $key . ($arr[2] ?? '');
                                    }
                                } else if (str_starts_with($k, strtolower('COLLATE'))) {
                                    if (!empty($collate)) {
                                        if ($collate === true) {
                                            return $key . $this->config['collation'];
                                        }
                                        return $key . $collate;
                                    } elseif ($collate !== false) {
                                        return $key . ($arr[2] ?? '');
                                    }
                                } else if (str_starts_with($k, strtolower('ENGINE'))) {
                                    if (!empty($engine)) {
                                        if ($engine === true) {
                                            return $key . $this->config['engine'];
                                        }
                                        return $key . $engine;
                                    }
                                    return $key . ($arr[2] ?? '');
                                }
                                return '';
                            }, $sql);
                            if ($this->exec($sql) !== false) {
                                ++$cs;
                            } else {
                                ++$ce;
                                $fail[] = $exec;
                            }
                        } else if (str_starts_with($exec, 'INSERT')) {
                            if ($this->exec($sql) !== false) {
                                ++$is;
                            } else {
                                ++$ie;
                                $fail[] = $exec;
                            }
                        } else {
                            if ($this->exec($sql) !== false) {
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
        $this->set = [];
        $this->close();
        $data['time'] = static::decimal((microtime(true) - $start));//执行时间秒
        return $data;
    }
}