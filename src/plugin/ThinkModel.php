<?php

namespace zhqing\plugin;

use think\db\exception\ModelNotFoundException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\BaseQuery;
use think\Collection;
use think\facade\Db;

trait ThinkModel {
    /**
     * 链接
     * @param string $table
     * @param string $connection
     * @return BaseQuery
     */
    public static function mysql(string $table = '', string $connection = ''): BaseQuery {
        $self = new self(false);
        return Db::connect(($connection ?: ($self->link ?? '')))->name(($table ?: ($self->table ?? '')));
    }

    /**
     * 不包含前缀
     * @param string $table
     * @param string $connection
     * @return BaseQuery
     */
    public static function table(string $table = '', string $connection = ''): BaseQuery {
        $self = new self(false);
        return Db::connect(($connection ?: ($self->link ?? '')))->table(($table ?: ($self->table ?? '')));
    }


    /**
     * 获取表信息
     * @param string $table
     * @param string $connection
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getTable(string $table = '', string $connection = ''): array {
        $self = new self(false);
        $data = Db::getBaseConfig();
        $default = $connection ?: ($self->link ?? $data['default']);
        $connections = $data['connections'][$default];
        $prefix = $connections['prefix'];
        return Db::connect(($connection ?: ($self->link ?? '')))
            ->table('INFORMATION_SCHEMA.COLUMNS')
            ->field('COLUMN_NAME, DATA_TYPE, COLUMN_COMMENT')
            ->where('table_name', ($prefix . ($table ?: ($self->table ?? ''))))
            ->select()
            ->toArray();
    }

    /**
     * 获取当前数据库信息
     * @param string $connection
     * @return mixed
     */
    public static function getBaseInfo(string $connection = ''): mixed {
        $self = new self(false);
        $data = Db::getBaseConfig();
        $default = $connection ?: ($self->link ?? $data['default']);
        return $data['connections'][$default];
    }

    /**
     * 获取数据库信息
     * @param string $connection
     * @param false $type //是否删除前缀
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getBases(string $connection = '', bool $type = false): array {
        $array = self::getBaseInfo($connection);
        $data = self::table('information_schema.TABLES')
            ->field('TABLE_NAME,TABLE_COMMENT')
            ->where('table_schema', $array['database'])
            ->select()
            ->toArray();
        $arr = [];
        $prefix = $array['prefix'] ?? '';
        foreach ($data as $v) {
            $name = $type ? (substr($v['TABLE_NAME'], strlen($prefix))) : $v['TABLE_NAME'];
            $arr[$name] = $v['TABLE_COMMENT'];
        }
        return $arr;
    }

    /**
     * 条件
     * @param $field
     * @param mixed|null $op
     * @param mixed|null $condition
     * @return BaseQuery
     */
    public static function where($field, mixed $op = null, mixed $condition = null): BaseQuery {
        return self::mysql()->where($field, $op, $condition);
    }

    /**
     * 排序
     * @param $field
     * @param string $order
     * @return BaseQuery
     */
    public static function order($field, string $order = ''): BaseQuery {
        return self::mysql()->order($field, $order);
    }

    /**
     * 条件
     * @param $field
     * @param mixed|null $op
     * @param mixed|null $condition
     * @return BaseQuery
     */
    public static function whereOr($field, mixed $op = null, mixed $condition = null): BaseQuery {
        return self::mysql()->whereOr($field, $op, $condition);
    }

    /**
     * 添加
     * @param array $data
     * @param bool $getLastInsID
     * @return string|int
     */
    public static function insert(array $data, bool $getLastInsID = true): string|int {
        return self::mysql()->insert($data, $getLastInsID);
    }

    /**
     * 查询
     * @param mixed|string $field
     * @param mixed|null $op
     * @param mixed|null $condition
     * @return array|Collection
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function select(mixed $field = '', mixed $op = null, mixed $condition = null): array|Collection {
        if (!empty($field)) {
            return self::where($field, $op, $condition)->select()->toArray();
        } else {
            return self::mysql()->select()->toArray();
        }
    }

    /**
     * 修改
     * @param $id
     * @param $data
     * @return int
     * @throws DbException
     */
    public static function update($id, $data): int {
        return self::where('id', $id)->update($data);
    }

    /**
     * 单条记录
     * @param $field
     * @param mixed|null $op
     * @param mixed|null $condition
     * @return mixed
     */
    public static function find($field, mixed $op = null, mixed $condition = null): mixed {
        return self::where($field, $op, $condition)->findOrEmpty();
    }

    /**
     * 删除
     * @param mixed|string $field
     * @param mixed|null $op
     * @param mixed|null $condition
     * @return int
     * @throws DbException
     */
    public static function delete(mixed $field = '', mixed $op = null, mixed $condition = null): int {
        if (!empty($field)) {
            return self::where($field, $op, $condition)->delete();
        } else {
            return self::mysql()->delete();
        }
    }

    /**
     * 统计
     * @param mixed|string $field
     * @param mixed|null $op
     * @param mixed|null $condition
     * @return int
     */
    public static function count(mixed $field = '', mixed $op = null, mixed $condition = null): int {
        if (!empty($field)) {
            return self::where($field, $op, $condition)->count();
        } else {
            return self::mysql()->count();
        }
    }

    /**
     * 把id转为key
     * @param mixed|string $field
     * @param mixed|null $op
     * @param mixed|null $condition
     * @param array $arr
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function selectId(mixed $field = '', mixed $op = null, mixed $condition = null, array $arr = []): array {
        $data = self::select($field, $op, $condition);
        foreach ($data as $v) {
            $arr[$v['id']] = $v;
        }
        return $arr;
    }

    /**
     * @param int $page
     * @param int $limit
     * @return BaseQuery
     */
    public static function page(int $page = 1, int $limit = 20): BaseQuery {
        $page = $page > 0 ? $page * $limit : $page;
        return self::mysql()->limit($page, $limit);
    }

    /**
     *
     * @param $key
     * @param mixed|string $field
     * @param mixed|null $op
     * @param mixed|null $condition
     * @param array $arr
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function selectKey($key, mixed $field = '', mixed $op = null, mixed $condition = null, array $arr = []): array {
        $data = self::select($field, $op, $condition);
        $k = is_array($key) ? $key[key($key)] : $key;
        $v = is_array($key) ? end($key) : '';
        foreach ($data as $r) {
            $arr[$r[$k]] = ($v ? $r[$v] : $r);
        }
        return $arr;
    }

    /**
     * 获取类名转表单
     * @return string
     */
    protected function classToTable(): string {
        $classArr = explode('\\', get_class($this));
        $tableArr = preg_split("/(?=[A-Z])/", end($classArr));
        return strtolower(trim(join('_', $tableArr), '_'));
    }
}