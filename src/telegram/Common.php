<?php

namespace zhqing\telegram;

use zhqing\extend\Frame;

/**
 * 公共方法
 */
class Common {
    public array $data = [];

    /**
     * 获取
     * @param string $key
     * @param mixed|string $default
     * @return mixed
     */
    public function get(string $key = '', mixed $default = ''): mixed {
        return (!empty($key) ? Frame::getStrArr($this->data, $key, $default) : $this->data);
    }

    /**
     * 设置
     * @param string $key
     * @param mixed $data
     * @return $this
     */
    public function set(string $key, mixed $data): static {
        $this->data[$key] = $data;
        return $this;
    }
}