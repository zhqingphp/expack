<?php

namespace zhqing\module;

use zhqing\extend\Frame;

class SeekForm {
    private array $body = [];

    public function __construct(array|string $data) {
        $this->body = is_array($data) ? $data : Frame::isJson($this->body);
    }

    public function __call($method, $parameter) {
        return Frame::getStrArr($this->body, $method . (isset($parameter[0]) ? '.' . $parameter[0] : ''), ($parameter[1] ?? ''));
    }

    public function getJsonData(): bool|string {
        return Frame::json($this->body);
    }
}