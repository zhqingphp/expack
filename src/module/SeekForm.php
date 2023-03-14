<?php

namespace package\tron;

use zhqing\extend\Frame;

class Seek {

    public array|string $SEEK_DATA = [];
    public string $SEEK_TYPE = '';

    public function __construct(array|string $data, string $type = '') {
        $this->SEEK_DATA = $data;
        $this->SEEK_TYPE = $type;
    }

    public function __call($method, $parameter) {
        return Frame::getStrArr((is_array($this->SEEK_DATA) ? $this->SEEK_DATA : Frame::isJson($this->SEEK_DATA)), (!empty($this->SEEK_TYPE) ? $this->SEEK_TYPE . '.' : '') . $method . (isset($parameter[0]) ? '.' . $parameter[0] : ''), ($parameter[1] ?? ''));
    }

    public function getBody(): array|string {
        return $this->SEEK_DATA;
    }

    public function toJson(): string {
        return (is_array($this->SEEK_DATA) ? Frame::json($this->SEEK_DATA) : $this->SEEK_DATA);
    }
}