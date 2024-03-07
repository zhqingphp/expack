<?php

namespace zhqing\module;

class EnvHelper {

    public string $env_file = "";
    public array $env_array = [];
    public string $save_content = '';

    public function __construct($env_file) {
        $this->env_file = $env_file;
        $this->toArray();
    }

    /**
     * 获取指定env
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key = '', mixed $default = null): mixed {
        return !empty($key) ? ($this->env_array[strtoupper($key)] ?? $default) : $this->env_array;
    }

    /**
     * 删除env
     * @param string|array $key
     * @return int
     */
    public function del(string|array $key): int {
        $status = 0;
        if (is_array($key)) {
            foreach ($key as $v) {
                $v = strtoupper($v);
                if (isset($this->env_array[$v])) {
                    unset($this->env_array[$v]);
                    ++$status;
                }
            }
        } else {
            $key = strtoupper($key);
            if (isset($this->env_array[$key])) {
                unset($this->env_array[$key]);
                ++$status;
            }
        }
        $this->keep();
        return $status;
    }

    /**
     * 编辑Env,存在测修改，不存在测添加
     * @param string|array $key
     * @param mixed|string $data
     * @return bool|int
     */
    public function set(string|array $key, mixed $data = ""): bool|int {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->env_array[strtoupper(trim($k))] = $v;
            }
        } else {
            $this->env_array[strtoupper(trim($key))] = $data;
        }
        return $this->keep();
    }

    /**
     * 保存env
     * @return bool|int
     */
    private function keep(): bool|int {
        $save_array = [];
        foreach ($this->env_array as $k => $v) {
            if (str_starts_with($k, '#') || str_starts_with($k, ';') || str_starts_with($k, '//')) {
                $save_array[] = $k;
            } else {
                $save_array[] = $k . "=" . ($v === null ? 'null' : ($v === false ? 'false' : ($v === true ? 'true' : $v)));
            }
        }
        $this->save_content = join(PHP_EOL, $save_array);
        return @file_put_contents($this->env_file, $this->save_content);
    }

    /**
     * 获取env成array
     */
    private function toArray() {
        $body = @file_get_contents($this->env_file);
        $body = str_replace("\r", PHP_EOL, $body);
        $body = str_replace("\n", PHP_EOL, $body);
        $array = explode(PHP_EOL, trim($body, PHP_EOL));
        $symbol = [';', ',', ';', '"', "'", '"', ';', ',', ';', '"', "'"];
        foreach ($array as $val) {
            $arr = explode("=", $val);
            $k = trim(($arr[0] ?? ''));
            $v = trim(($arr[1] ?? ''));
            if (!str_starts_with($k, '#') && !str_starts_with($k, ';') && !str_starts_with($k, '//')) {
                foreach ($symbol as $s) {
                    $v = trim($v, $s);
                }
                $v = trim($v);
                $k = strtoupper(trim($k));
            }
            if (!empty($k)) {
                $this->env_array[$k] = ($v == 'null' ? null : ($v == 'false' ? false : ($v == 'true' ? true : $v)));
            }
        }
    }
}