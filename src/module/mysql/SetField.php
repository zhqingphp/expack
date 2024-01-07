<?php

namespace zhqing\module\mysql;

class SetField extends Common {
    /**
     * @return $this
     */
    public function setThis(): static {
        $this->mysql->SetFields = $this;
        return $this;
    }

    /**
     * 字段是否主键
     * @param bool $data
     * @return $this
     */
    public function key(bool $data): static {
        $this->mysql->setConfig($this->mysql->field, ['key' => $data], 'field');
        return $this;
    }

    /**
     * 字段注释
     * @param string|null|int $data
     * @return $this
     */
    public function comment(string|null|int $data): static {
        $this->mysql->setConfig($this->mysql->field, ['comment' => $data], 'field');
        return $this;
    }

    /**
     * 字段默认值
     * @param string|null|int $data
     * @return $this
     */
    public function def(string|null|int $data): static {
        $this->mysql->setConfig($this->mysql->field, ['def' => $data], 'field');
        return $this;
    }

    /**
     * 字段不是NULL 不为空?  true=必须有值,false=允许空值
     * @param bool $data
     * @return $this
     */
    public function null(bool $data): static {
        $this->mysql->setConfig($this->mysql->field, ['null' => $data], 'field');
        return $this;
    }

    /**
     * 生成创造表单SQL
     * 设置主键(第一个为id)
     * @param string $data $primary 主键(第一个为id),多个使用,号分开
     * @param string $type id类型长度
     * @param string $comment 表单注释
     * @param int $auto 表单自增id
     * @return Exec
     */
    public function create(string $data = 'id', string $type = 'int(11)', string $comment = '', int $auto = 1): Exec {
        $this->mysql->setConfig('id', $data)
            ->setConfig('type', $type)
            ->setConfig('auto', $auto)
            ->setConfig('comment', $comment)
            ->setConfig('mode', 'create');
        return new Exec($this->mysql);
    }

    /**
     * 生成表单添加列SQL
     * @return Exec
     */
    public function row(): Exec {
        $this->mysql->setConfig('mode', 'row');
        return new Exec($this->mysql);
    }
}