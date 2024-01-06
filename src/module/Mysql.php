<?php

namespace zhqing\module;

use zhqing\extend\Frame;
use zhqing\module\mysql\Field;
use zhqing\module\mysql\SetField;
use zhqing\module\mysql\Exec;

class Mysql {
    //前缀
    public string $prefix = '';
    //标识
    public string $make = '';
    //字段名称
    public string $field = '';
    //输出sql
    public string $sql = '';
    //设置数据
    public array $config = [];
    public Field $Fields;
    public SetField $SetFields;
    public Exec $Execs;

    /**
     * 获取数据库信息
     * @param string $name
     * @return string
     */
    public static function getBase(string $name): string {
        return "\r\n" . self::setCome('数据库信息') . "\r\nSELECT * FROM information_schema.TABLES WHERE table_schema='{$name}';";
    }

    /**
     * 获取表单信息
     * @param string $name
     * @return string
     */
    public static function getTable(string $name): string {
        return "\r\n" . self::setCome('表单信息') . "\r\nSELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name='{$name}';";
    }

    /**
     * 设置表单名称
     * @param string $name 表单名称
     * @param string $charset 表单编码
     * @param string $engine 表单引擎
     * @return Field
     */
    public function table(string $name, string $charset = 'utf8mb4', string $engine = 'InnoDB'): Field {
        $this->make = $this->make($this->prefix . $name);
        $this->setConfig('table', $name)->setConfig('charset', $charset)->setConfig('engine', $engine);
        return (new Field($this));
    }

    /**
     * @return string
     */
    public function exec(): string {
        return $this->Execs->exec();
    }


    /**
     * 设置表单信息
     * @param string $key
     * @param mixed $data
     * @param string $type
     * @return $this
     */
    public function setConfig(string $key, mixed $data, string $type = ''): static {
        if (empty($type)) {
            $this->config[$this->make][$key] = $data;
        } else {
            if (isset($this->config[$this->make][$type][$key])) {
                $this->config[$this->make][$type][$key] = array_merge($this->config[$this->make][$type][$key], $data);
            } else {
                $this->config[$this->make][$type][$key] = $data;
            }
        }
        return $this;
    }

    /**
     * @param string $prefix
     */
    public function __construct(string $prefix = '') {
        $this->prefix = $prefix;
    }

    /**
     * 标识
     * @param $table
     * @return string
     */
    protected static function make($table): string {
        return md5(seekTime() . $table . Frame::getToken() . uniqid());
    }

    /**
     * @param $data
     * @return string
     */
    protected static function setCome($data): string {
        return "-- ----------------------------\r\n-- {$data}\r\n-- ----------------------------";
    }
}