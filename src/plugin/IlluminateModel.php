<?php

namespace zhqing\plugin;

use think\db\exception\ModelNotFoundException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\BaseQuery;
use think\Collection;
use think\facade\Db;

trait IlluminateModel {

    public function find($columns = ['*']) {
        return !empty($obj = $this->first($columns)) ? $obj->toArray() : [];
    }
}