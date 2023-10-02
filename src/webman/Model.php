<?php

namespace zhqing\webman;

use Illuminate\Database\Eloquent\Builder;
use zhqing\extend\Frame;
use support\Response;
use support\Db;
use Closure;

class Model extends \support\Model {
    /**
     * @param Closure $callback
     * @param int $attempts
     * @return mixed
     * @throws \Throwable
     */
    public static function transaction(Closure $callback, $attempts = 1) {
        $self = new static();
        return DB::connection($self->connection)->transaction($callback, $attempts);
    }

    public static function beginTransaction() {
        $self = new static();
        DB::connection($self->connection)->beginTransaction();
    }

    public static function commit() {
        $self = new static();
        DB::connection($self->connection)->commit();

    }

    public static function rollback() {
        $self = new static();
        DB::connection($self->connection)->rollback();
    }

    /**
     * 获取表单信息
     * @param string|null $default //数据库配置
     * @param string|null $table //表单名称
     * @return array
     */
    public static function getTab(string|null $default = null, string|null $table = null): array {
        $data = [];
        $self = new static();
        $arr = config('database.connections');
        $connection = ((!empty($default) && isset($arr[$default])) ? $default : $self->connection);
        $table = ($arr[$connection]['prefix'] . (!empty($table) ? $table : $self->getTable()));
        $array = Db::connection($connection)->select('SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name="' . $table . '"');
        foreach ($array as $v) {
            $data[$v->COLUMN_NAME] = $v;
        }
        return $data;
    }

    /**
     * 获取数据库信息
     * @param string|null $default //数据库配置
     * @return array
     */
    public static function getBase(string|null $default = null): array {
        $data = [];
        $self = new static();
        $arr = config('database.connections');
        $connection = ((!empty($default) && isset($arr[$default])) ? $default : $self->connection);
        $table = $arr[$connection]['database'];
        $array = Db::connection($connection)->select('SELECT * FROM information_schema.TABLES WHERE table_schema="' . $table . '"');
        $prefix = $arr[$connection]['prefix'];
        foreach ($array as $v) {
            $data[substr($v->TABLE_NAME, strlen($prefix))] = $v;
        }
        return $data;
    }

    /**
     * 获取表单名称
     * @param string|null $default //数据库配置
     * @return mixed
     */
    public static function getTabName(string|null $default = null): mixed {
        $self = new static();
        $base = self::getBase($default);
        return (!empty($name = ($base[$self->getTable()]->TABLE_COMMENT ?? '')) ? $name : $self->getTable());
    }

    /**
     * 获取表单字段信息并生成
     * @param string|null $default //数据库配置
     * @param string|null $table
     * @return string
     */
    public static function getTabField(string|null $default = null, string $table = null): string {
        $self = new static();
        $base = self::getBase($default);
        $table = (!empty($table) ? $table : $self->getTable());
        $arr = self::getTab($default, $table);
        $html = "/**\r\n";
        $html .= " * " . ($base[$table]->TABLE_COMMENT ?? $table) . "\r\n";
        $data = "";
        foreach ($arr as $k => $v) {
            $html .= " * @property " . ($v->DATA_TYPE == 'int' ? 'int' : 'string') . " \$" . $k . " " . $v->COLUMN_COMMENT . "(" . $v->COLUMN_TYPE . ")\r\n";
            $data .= "\$data[\"{$k}\"]=\$req->post('{$k}');//" . $v->COLUMN_COMMENT . "\r\n";
        }
        $html .= " */\r\n/*\r\n" . $data . "*/\r\n";
        return $html;
    }

    /**
     * @param Builder|null $builder
     * @return Builder
     */
    public static function whereLike(Builder|null $builder = null): Builder {
        $order = static::$orderArr ?? ['id', 'asc'];
        $builder = (!empty($builder) ? $builder->orderBy(($order[0] ?? 'id'), ($order[1] ?? 'asc')) : static::orderBy(($order[0] ?? 'id'), ($order[1] ?? 'asc')));
        if (($keyList = static::$keyList ?? []) && !empty($key = (\request()->post('key', \request()->get('key'))))) {
            $builder = $builder->where(function (Builder $or) use ($key, $keyList) {
                foreach ($keyList as $v) {
                    $or = $or->orWhere($v, 'like', '%' . $key . '%');
                }
                return $or;
            });
        }
        return $builder;
    }

    /**
     * 生成model文件
     * @param string $path
     * @return array
     */
    public static function getModel(string $path = 'model'): array {
        $connections = config('database.connections');
        $data = [];
        foreach ($connections as $k => $v) {
            $kArr = explode('.', $k);
            $dir = base_path() . '/' . $path . '/' . end($kArr);
            Frame::mkDir($dir);
            $common = "<?php\r\n";
            $common .= "namespace plugin\back\app\model;\r\n";
            $common .= "use zhqing\webman\Model;\r\n";
            $common .= "class Common extends Model {\r\n";
            $common .= "    //模型的连接名称\r\n";
            $common .= "    public \$connection = '{$k}';\r\n";
            $common .= "    //重定义主键，默认是id\r\n";
            $common .= "    public \$primaryKey = 'id';\r\n";
            $common .= "    //指示是否自动维护时间戳\r\n";
            $common .= "    public \$timestamps = false;\r\n";
            $common .= "}\r\n";
            $file = $dir . '/Common.php';
            file_put_contents($file, $common);
            $data[] = $file;
            $arr = self::getBase($k);
            foreach ($arr as $a => $b) {
                $tab = self::getTab($k, $a);
                $key = '[\'id\',';
                foreach ($tab as $r => $s) {
                    $key .= ((
                        $s->DATA_TYPE == 'varchar'
                        || $s->DATA_TYPE == 'char'
                        || $s->DATA_TYPE == 'blob'
                        || $s->DATA_TYPE == 'text'
                    ) ? "'{$r}'," : "");
                }
                $sArr = explode('_', $a);
                $name = '';
                foreach ($sArr as $u) {
                    $name .= ucwords($u);
                }
                $key = trim($key, ',') . ']';
                $php = "<?php\r\n";
                $php .= "namespace plugin\\back\\app\\model;\r\n";
                $php .= self::getTabField($k, $a);
                $php .= "class {$name} extends Common {\r\n";
                $php .= "    //与模型关联的表名\r\n";
                $php .= "public \$table = '{$a}';\r\n";
                if (isset($tab['admin_id'])) {
                    $php .= "    //是否包含admin_id\r\n";
                    $php .= "public static string \$adminId = 'admin_id';\r\n";
                }
                $php .= "    //顺序\r\n";
                $php .= "public static array \$orderArr = ['id', 'desc'];\r\n";
                $php .= "    //模糊查找字段\r\n";
                $php .= "public static array \$keyList = {$key};\r\n";
                $php .= "    //编辑字段\r\n";
                $php .= "public static array \$editField = [\r\n";
                $php .= "'val'=>{$key},\r\n";
                $php .= "'html'=>[],\r\n";
                $php .= "'checked'=>[],\r\n";
                $php .= "'radio'=>[],\r\n";
                $php .= "'selected'=>[],\r\n";
                $php .= "'json'=>[],\r\n";
                $php .= "'htmls'=>[],\r\n";
                $php .= "'vals'=>[],\r\n";
                $php .= "'jsons'=>[],\r\n";
                $php .= "'edit'=>[],\r\n";
                $php .= "'time'=>[],\r\n";
                $php .= "'del'=>['id'],//排除保存的\r\n";
                $php .= "'save'=>[]//保存不显示的\r\n";
                $php .= "];\r\n";
                $php .= "}\r\n";
                $file = $dir . '/' . $name . '.php';
                file_put_contents($file, $php);
                $data[] = $file;
            }
        }
        return $data;
    }
}