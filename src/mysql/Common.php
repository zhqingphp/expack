<?php

namespace zhqing\mysql;

use PDO;
use mysqli;

trait Common {
    /**
     * @var array 数据库配置
     */
    public array $config = [
        'username' => '', //用户名
        'password' => '', //密码
        'database' => '', //数据库名
        'host' => '', //服务器地址
        'port' => '', //端口号
        'charset' => '',//字符集
        'prefix' => '',//表前缀
    ];

    /**
     * @var PDO|null PDO链接
     */
    public PDO|null $pdo;

    /**
     * @var mysqli|null Mysql链接
     */
    public mysqli|null $mysqli;

    /**
     * @param array $config
     */
    public function __construct(array $config = []) {
        $this->config = [
            'username' => ($config['username'] ?? ''), //用户名
            'password' => ($config['password'] ?? ''), //密码
            'database' => ($config['database'] ?? ''), //数据库名
            'host' => ($config['host'] ?? ($config['hostname'] ?? '127.0.0.1')), //服务器地址
            'port' => ($config['port'] ?? ($config['hostport'] ?? 3306)), //端口号
            'charset' => ($config['charset'] ?? ''),//字符集
            'prefix' => ($config['prefix'] ?? ''),//表前缀
        ];
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $database
     * @param string $host
     * @param string|int $port
     * @param string $prefix
     * @param string $charset
     * @return static
     */
    public static function set(string $username = '', string $password = '', string $database = '', string $host = '127.0.0.1', string|int $port = 3306, string $prefix = '', string $charset = 'utf8mb4') {
        return new self([
            'username' => $username, //用户名
            'password' => $password, //密码
            'database' => $database, //数据库名
            'host' => $host, //服务器地址
            'port' => $port, //端口号
            'charset' => $prefix,//字符集
            'prefix' => $charset,//表前缀
        ]);
    }

    /**
     * pdo链接
     * @return PDO
     */
    public function pdo(): PDO {
        if (empty($this->pdo)) {
            $this->pdo = new PDO("mysql:host=" . $this->config['host'] . ";port=" . $this->config['port'] . ";dbname=" . $this->config['database'] . ";charset=" . $this->config['charset'], $this->config['username'], $this->config['password']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->pdo;
    }

    /**
     * mysqli链接
     * @return mysqli
     */
    public function mysqli(): mysqli {
        if (empty($this->mysqli)) {
            $this->mysqli = new mysqli($this->config['host'], $this->config['username'], $this->config['password'], $this->config['database'], $this->config['port']);
            $this->mysqli->set_charset($this->config['charset']);
        }
        return $this->mysqli;
    }

    /**
     * 获取表单全称
     * @param string $table
     * @return string
     */
    public function getFullTable(string $table): string {
        return (!empty($this->config['prefix']) && empty(str_starts_with($table, $this->config['prefix']))) ? ($this->config['prefix'] . $table) : $table;
    }

    /**
     * @param string $data
     * @return string
     */
    public function remark(string $data): string {
        return "-- ----------------------------\r\n-- {$data}\r\n-- ----------------------------\r\n";
    }

    /**
     * 文件夹不存在创建文件夹(无限级)
     * @param string $filePath 文件名
     * @return string
     */
    public function mkDir(string $filePath): string {
        $dir = dirname($filePath);
        if (empty(is_dir($dir))) {
            mkdir($dir, 0777, true);
        }
        return $filePath;
    }

    /**
     * 判断几维数组
     * @param $arr
     * @param int $j
     * @return int
     */
    public static function arrLevel($arr, int $j = 0): int {
        if (empty(\is_array($arr))) {
            return $j;
        }
        foreach ($arr as $K) {
            $v = self::arrLevel($K);
            if ($v > $j) {
                $j = $v;
            }
        }
        return $j + 1;
    }

    /**
     *
     */
    public function __destruct() {
        $this->close();
    }

    /**
     * 通过a.b.c.d获取数组内容
     * @param array $data //要取值的数组
     * @param string $name //支持aa.bb.cc.dd这样获取数组内容
     * @param mixed $default //默认值
     * @return mixed
     */
    public static function getStrArr(array $data, string $name, mixed $default = null): mixed {
        if (!isset($name)) {
            return $data;
        } else if (!empty($info = self::getArr($data, $name))) {
            return $info;
        } else {
            $nameArr = \explode('.', $name);
            foreach ($nameArr as $v) {
                if (isset($data[$v])) {
                    $data = $data[$v] ?: $default;
                } else {
                    return $default;
                }
            }
            return $data;
        }
    }

    /**
     * 读取数组
     * @param array $data
     * @param null|string|int $key
     * @param mixed $default
     * @return mixed
     */
    public static function getArr(array $data, null|string|int $key = null, mixed $default = ''): mixed {
        return isset($key) ? (isset($data[$key]) ? ($data[$key] ?: $default) : $default) : $data;
    }

    /**
     *
     * 转换回来第三个为空
     * @param $int
     * @param int $decimals
     * @param string $separator
     * @param string $thousands
     * @return string
     */
    public static function decimal($int, int $decimals = 2, string $thousands = ',', string $separator = '.'): string {
        return number_format($int, $decimals, $separator, $thousands);
    }

    /**
     * 数组转Json
     * @param $data
     * @param bool $type
     * @return false|string
     */
    public static function json($data, bool $type = true): bool|string {
        return $type ? \json_encode($data, JSON_NUMERIC_CHECK + JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES) : \json_encode($data, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
    }

    /**
     * 判断字符串是否json,返回array
     * @param mixed $data
     * @param bool $type
     * @return mixed
     */
    public static function isJson(mixed $data, bool $type = true): mixed {
        $data = \json_decode((is_string($data) ? ($data ?: '') : ''), $type);
        return (($data && \is_object($data)) || (\is_array($data) && $data)) ? $data : [];
    }
}