<?php

namespace zhqing\plugin;

use think\DbManager;
use zhqing\extend\Frame;

/**
 * @see DbManager
 * @mixin DbManager
 */
class Db extends \think\Facade {

    private static array $DataBase = [];

    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass(): string {
        if (empty(self::$DataBase)) {
            self::$DataBase = self::getBaseConfig();
            self::setConfig(self::$DataBase);
        }
        return 'think\DbManager';
    }

    public static function getBaseConfig(): array {
        return Frame::retFile(__DIR__ . '/../config/thinkorm.php');
    }
}