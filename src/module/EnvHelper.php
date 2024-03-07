<?php

namespace zhqing\module;

class EnvHelper {

    private string $env_file;
    private array $env_array = [];

    public function __construct($env_file) {
        $this->env_file = $env_file;
        $this->toArray();
    }

    /**
     * 编辑Env,存在测修改，不存在测添加
     * @param string|array $key
     * @param mixed|string $data
     * @return int
     */
    public function save(string|array $key, mixed $data = ""): int {
        $status = 0;
        $way = function ($k, $v) {
            $k = strtoupper(trim($k));
            if (isset($this->env_array[$k])) {
                if ($this->env_array[$k] != $v) {
                    $this->env_array[$k] = $v;
                    $status = 1;
                }
            } else {
                $this->env_array[$k] = $v;
                $status = 1;
            }
            return ($status ?? 0);
        };
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                if ($way($k, $v) > 0) {
                    ++$status;
                }
            }
        } else {
            if ($way($key, $data) > 0) {
                ++$status;
            }
        }
        if ($status > 0) {
            $this->keep();
        }
        return $status;
    }

    /**
     * 获取
     * @param string $key
     * @param mixed $default
     * @param bool $type
     * @return mixed
     */
    public function seek(string $key = '', mixed $default = null, bool $type = true): mixed {
        if ($type === true) {
            $this->toArray();
        }
        return !empty($key) ? ($this->env_array[strtoupper($key)] ?? $default) : $this->env_array;
    }

    /**
     * 删除
     * @param string|array $key
     * @return int
     */
    public function delete(string|array $key): int {
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
        if ($status > 0) {
            $this->keep();
        }
        return $status;
    }

    /**
     * 保存env
     */
    private function keep() {
        $save_array = [];
        foreach ($this->env_array as $k => $v) {
            if (str_starts_with($k, '#') || str_starts_with($k, ';') || str_starts_with($k, '//')) {
                $save_array[] = $k;
            } else {
                $save_array[] = $k . "=" . ($v === null ? 'null' : ($v === false ? 'false' : ($v === true ? 'true' : $v)));
            }
        }
        @file_put_contents($this->env_file, join(PHP_EOL, $save_array));
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
                $iv = strtolower($v);
                $this->env_array[$k] = ($iv == 'null' ? null : ($iv == 'false' ? false : ($iv == 'true' ? true : $v)));
            }
        }
    }

    /**
     * @return static
     */
    public static function webMan(): static {
        return (new static((base_path() . '/.env')));
    }

    /**
     * @param string|array $key
     * @param mixed|string $data
     * @return int
     */
    public static function set(string|array $key, mixed $data = ""): int {
        return static::webMan()->save($key, $data);
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public static function get(string $key = '', mixed $default = null): mixed {
        return static::webMan()->seek($key, $default, false);
    }

    /**
     * @param string|array $key
     * @return int
     */
    public static function del(string|array $key): int {
        return static::webMan()->delete($key);
    }
}