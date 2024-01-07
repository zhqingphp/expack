<?php

namespace zhqing\module\mysql;

use zhqing\module\Mysql;

class Common {
    protected Mysql $mysql;

    /**
     * @param Mysql $mysql
     */
    public function __construct(Mysql $mysql) {
        $this->mysql = $mysql;
        $this->setThis();
    }

    /**
     * @param $data
     * @return string
     */
    protected static function setCome($data): string {
        return "-- ----------------------------\r\n-- {$data}\r\n-- ----------------------------";
    }
}