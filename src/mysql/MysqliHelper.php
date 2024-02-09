<?php

namespace zhqing\mysql;

use mysqli_result;

class MysqliHelper {
    use Sql, Common, Export, Import, Table;

    /**
     * 获取数据库全部表单信息
     * @param string|bool $base 数据库名称,true=全部,false=当前,string=指定
     * @param array $data
     * @return array
     */
    public function getBase(string|bool $base = false, array $data = []): array {
        $result = $this->query($this->getBaseSql($base));
        while ($val = $result->fetch_assoc()) {
            $data[$val['TABLE_SCHEMA']][$val['TABLE_NAME']] = $val;
        }
        return $data;
    }

    /**
     * 获取表单数据
     * @param string $table 表单名
     * @param string $columns
     * @param bool $base 是否添加数据库名
     * @param array $data
     * @return bool|array
     */
    public function getTabData(string $table, string $columns = "*", bool $base = false, array $data = []): bool|array {
        $result = $this->query("SELECT {$columns} FROM " . $this->getFullTable($table, $base));
        while ($val = $result->fetch_assoc()) {
            $data[] = $val;
        }
        return $data;
    }

    /**
     * 获取表单字段信息
     * @param string $table 表单名
     * @param bool $base 是否添加数据库名
     * @param array $data
     * @return array
     */
    public function getTabInfo(string $table, bool $base = false, array $data = []): array {
        $database = !empty($base) ? ((!empty($database = ($this->set['database'] ?? '')) ? $database : $this->config['database'])) : "";
        $result = $this->query($this->getTabInfoSql($this->getFullTable($table), $database));
        while ($val = $result->fetch_assoc()) {
            $data[$val['COLUMN_NAME']] = $val;
        }
        return $data;
    }

    /**
     * 获取表单sql
     * @param string $table 表单名
     * @param bool $base 是否添加数据库名
     * @return string
     */
    public function getTabSql(string $table, bool $base = false): string {
        $data = $this->query("SHOW CREATE TABLE " . $this->getFullTable($table, $base))->fetch_assoc();
        return $data['Create Table'] ?? '';
    }

    /**
     * 获取所有表名
     * @param array $data
     * @return array
     */
    public function getAllTabName(array $data = []): array {
        $result = $this->query("SHOW TABLES");
        while ($val = $result->fetch_row()) {
            $data[] = $val[0];
        }
        return $data;
    }

    /**
     * 执行
     * @param string $sql
     * @return bool|mysqli_result
     */
    public function exec(string $sql): mysqli_result|bool {
        return $this->query($sql);
    }

    /**
     * 获取 MySQL 版本号
     * @return string
     */
    public function version(): string {
        return $this->mysqli()->get_server_info();
    }

    /**
     * 执行sql
     * @param string $sql
     * @return mysqli_result|bool
     */
    public function query(string $sql): mysqli_result|bool {
        return $this->mysqli()->query($sql);
    }

    /**
     * 转义字符串 addslashes
     * @param $string
     * @return string
     */
    public function quote($string): string {
        return "'" . $this->mysqli()->escape_string($string) . "'";
    }

    /**
     * 反转义字符串
     * @param $string
     * @return string
     */
    public function strips($string): string {
        return stripslashes($string);
    }

    /**
     * 关闭链接
     */
    public function close() {
        if (!empty($this->mysqli)) {
            $this->mysqli->close();
            $this->mysqli = null;
        }
    }
}