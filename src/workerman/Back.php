<?php

namespace zhqing\workerman;

use zhqing\extend\Frame;
use support\Response;

class Back {
    private array $Data = [
        'int' => true,
        'code' => '',
        'data' => '',
        'msg' => '',
        'success' => '',
        'status' => '',
        'count' => ''
    ];

    /**
     * @param int $Data
     * @return $this
     */
    public static function code(int $Data): static {
        $Self = new self();
        $Self->Data['code'] = $Data;
        return $Self;
    }

    /**
     * @param $Data
     * @return $this
     */
    public function data($Data): static {
        $this->Data['data'] = $Data;
        return $this;
    }

    /**
     * @param $Data
     * @return $this
     */
    public function msg($Data): static {
        $this->Data['msg'] = $Data;
        return $this;
    }

    /**
     * @param $Data
     * @return $this
     */
    public function success($Data): static {
        $this->Data['success'] = $Data;
        return $this;
    }

    /**
     * 数量
     * @param $Data
     * @return $this
     */
    public function count($Data): static {
        $this->Data['count'] = $Data;
        return $this;
    }

    /**
     * @return array
     */
    public function array(): array {
        if (is_array($this->Data['data']) && empty($this->Data['count'])) {
            $this->Data['count'] = count($this->Data['data']);
        }
        return $this->Data;
    }

    /**
     * @param bool $Type
     * @return Response
     */
    public function json(bool $Type = false): Response {
        return \response(Frame::json($this->array(), $Type), 200, ['Content-Type' => 'application/javascript']);
    }
}