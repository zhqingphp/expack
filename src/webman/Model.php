<?php

namespace zhqing\webman;

use Illuminate\Database\Eloquent\Builder;
use zhqing\extend\Frame;
use support\Response;
use support\Db;

class Model extends \support\Model {
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
     * @param string|null $table
     * @return string
     */
    public static function getTabField(string $table = null): string {
        $self = new static();
        $base = self::getBase();
        $table = (!empty($table) ? $table : $self->getTable());
        $arr = self::getTab(null, $table);
        $html = "/**\r\n";
        $html .= " * " . ($base[$table]->TABLE_COMMENT ?? $table) . "\r\n";
        $data = "";
        foreach ($arr as $k => $v) {
            $html .= " * @property " . ($v->DATA_TYPE == 'int' ? 'int' : 'string') . " \$" . $k . " " . $v->COLUMN_COMMENT . "(" . $v->COLUMN_TYPE . ")\r\n";
            $data .= "\$data[\"{$k}\"]=\$v[\"{$k}\"];//" . $v->COLUMN_COMMENT . "\r\n";
        }
        $html .= " */\r\n/*\r\n" . $data . "*/\r\n";
        return $html;
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public static function whereLike(Builder $builder): Builder {
        $order = static::$orderArr ?? ['id', 'asc'];
        $builder->orderBy(($order[0] ?? 'id'), ($order[0] ?? 'asc'));
        if (!empty($key = (\request()->post('key', \request()->get('key'))))) {
            $builder->where(function (Builder $or) use ($key) {
                foreach (static::$keyList as $v) {
                    $or->orWhere($v, 'like', '%' . $key . '%');
                }
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
                $key = '[';
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
                $php .= "use Illuminate\Database\Eloquent\Builder;\r\n";
                $php .= self::getTabField($a);
                $php .= "class {$name} extends Common {\r\n";
                $php .= "    //与模型关联的表名\r\n";
                $php .= "public \$table = '{$a}';\r\n";
                $php .= "    //顺序\r\n";
                $php .= "public static array \$orderArr = ['id', 'desc'];\r\n";
                $php .= "    //模糊查找字段\r\n";
                $php .= "public static array \$keyList = {$key};\r\n";
                $php .= "}\r\n";
                $file = $dir . '/' . $name . '.php';
                file_put_contents($file, $php);
                $data[] = $file;
            }
        }
        return $data;
    }
}