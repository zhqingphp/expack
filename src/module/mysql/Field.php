<?php

namespace zhqing\module\mysql;

class Field extends Common {
    /**
     * @return $this
     */
    public function setThis(): static {
        $this->mysql->Fields = $this;
        return $this;
    }

    /**
     * 设置字段
     * @param string $name 字段名
     * @param string $type 类型
     * @param string|int $length 长度
     * @param string|int $decimal 小数
     * @return SetField
     */
    public function field(string $name, string $type = '', string|int $length = '', string|int $decimal = ''): SetField {
        $this->mysql->field = $name;
        $this->mysql->config[$this->mysql->make]['field'][$this->mysql->field] = [
            'type' => $type,
            'length' => $length,
            'decimal' => $decimal,
            'comment' => null,
            'key' => false,
            'def' => null,
            'null' => false
        ];
        return new SetField($this->mysql);
    }

    /**
     * @param string $comment
     * @param string $data
     * @param string $type
     * @param int $auto
     * @return Exec
     */
    public function create(string $comment = '', string $data = 'id', string $type = 'int(11)', int $auto = 1): Exec {
        return $this->mysql->SetFields->create($data, $type, $comment, $auto);
    }

    /**
     * @return Exec
     */
    public function row(): Exec {
        return $this->mysql->SetFields->row();
    }
}