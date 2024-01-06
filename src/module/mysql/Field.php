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
     * @param string $type 类型(长度)
     * @return SetField
     */
    public function field(string $name, string $type): SetField {
        $this->mysql->field = $name;
        $this->mysql->config[$this->mysql->make]['field'][$this->mysql->field] = [
            'type' => $type,
            'comment' => null,
            'key' => false,
            'def' => null,
            'null' => false
        ];
        return new SetField($this->mysql);
    }

    /**
     * @param string $data
     * @param string $type
     * @param string $comment
     * @param int $auto
     * @return Exec
     */
    public function create(string $data = 'id', string $type = 'int(11)', string $comment = '', int $auto = 1): Exec {
        return $this->mysql->SetFields->create($data, $type, $comment, $auto);
    }

    /**
     * @return Exec
     */
    public function row(): Exec {
        return $this->mysql->SetFields->row();
    }
}