<?php

namespace zhqing\module;

class DatabaseSql {
    public array $conf = [];
    public string $savePath = __DIR__ . "/../../file/date/backup/";
    public array $config = [
        'default' => 'default',
        'connections' => [
            'default' => [
                'driver' => "mysql",//数据库类型
                'host' => "localhost", //服务器地址
                'port' => 3306, //端口号
                'username' => "root", //用户名
                'password' => "root", //密码
                'database' => "root", //数据库名
                'prefix' => "",//表前缀
                'charset' => "utf8mb4",//字符集
                'collation' => "utf8mb4_general_ci",//排序规则
                'row_format' => "DYNAMIC",//行格式
                'unix_socket' => null,//Unix域套
                'engine' => "InnoDB",//引擎
            ]
        ]
    ];

    /**
     * 配置
     * @param array $config
     */
    public function __construct(array $config) {
        ini_set('memory_limit', '8192M');
        $this->config = $config;
        $this->conf = $config['connections'][$config['default']] ?? [];
    }

    /**
     * 选择配置
     * @param string|null $name
     * @return $this
     */
    public function opt(string|null $name): static {
        $name = !empty($name) ? $name : $this->config['default'];
        $this->conf = $this->config['connections'][$name] ?? [];
        return $this;
    }

    /**
     * 设置数据库信息
     * @param string $name
     * @param string $key
     * @return $this
     */
    public function set(string $name, string $key = 'database'): static {
        $this->conf = array_merge($this->conf, [$key => $name]);
        return $this;
    }

    /**
     * 设置保存目录
     * @param $path
     * @return $this
     */
    public function path($path): static {
        $this->savePath = rtrim($path, '/');
        return $this;
    }

    /**
     * 执行
     * @param string $file
     * @param bool $type 是否显示执行过程
     * @return array
     */
    public function exec(string $file = "", bool $type = true): array {
        $path = rtrim($this->savePath, '/');
        $file = !empty($file) ? trim($file, '/') : trim(($this->conf['database'] . "/" . date("YmdHis") . ".sql"), '/');
        $FilePath = $path . "/" . $file;
        $dir = dirname($FilePath);
        if (empty(is_dir($dir))) mkdir($dir, 0777, true);
        $mysql = new MysqlHelper($this->conf);
        return $mysql->exportCallable($type)->export($FilePath);
    }

    /**
     * 执行备份
     * @param string $dir 保存目录
     * @param array $arr ['配置'=>['数据库']]
     * @param bool $type 是否显示执行过程
     * @param array $data
     * @return array
     */
    public function backup(string $dir, array $arr, bool $type = false, array $data = []): array {
        foreach ($arr as $k => $val) {
            foreach ($val as $v) {
                $path = $k . "/" . trim(($v . "/" . date("YmdHis") . ".sql"), '/');
                $data[$k][$v] = $this->opt($k)->set($v)->path($dir)->exec($path, $type);
            }
        }
        return $data;
    }

    /**
     * 还原
     * @param string $FilePath
     * @param bool $type 是否显示执行过程
     * @return array
     */
    public function regain(string $FilePath, bool $type = true): array {
        $mysql = new MysqlHelper($this->conf);
        return $mysql->importCallable($type)->import($FilePath);
    }
}