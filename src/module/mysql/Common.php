<?php

namespace zhqing\module\mysql;

use zhqing\module\Mysql;

class Common {
    protected Mysql $mysql;

    /**
     * @param Mysql $mysql
     */
    public function __construct(Mysql $mysql) {
        $this->setThis();
        $this->mysql = $mysql;
    }

    /**
     * @param $data
     * @return string
     */
    protected static function setCome($data): string {
        return "-- ----------------------------\r\n-- {$data}\r\n-- ----------------------------";
    }
}