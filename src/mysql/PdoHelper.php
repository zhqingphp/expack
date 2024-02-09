<?php

namespace zhqing\mysql;

use PDO;
use PDOStatement;

class PdoHelper {
    use Sql, Common, Export, Import, Table;

    /**
     * 获取数据库全部表单信息
     * @param string|bool $base 数据库名称,true=全部,false=当前,string=指定
     * @param array $data
     * @return array
     */
    public function getBase(string|bool $base = false, array $data = []): array {
        $tab = $this->query($this->getBaseSql($base))->fetchAll(PDO::FETCH_ASSOC);
        foreach ($tab as $val) {
            $data[$val['TABLE_SCHEMA']][$val['TABLE_NAME']] = $val;
        }
        return $data;
    }

    /**
     * 获取表单数据
     * @param string $table 表单名
     * @param string $columns
     * @param bool $base 是否添加数据库名
     * @return bool|array
     */
    public function getTabData(string $table, string $columns = "*", bool $base = false): bool|array {
        return $this->query("SELECT {$columns} FROM " . $this->getFullTable($table, $base))->fetchAll(PDO::FETCH_ASSOC);
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
        $tab = $this->query($this->getTabInfoSql($this->getFullTable($table), $database))
            ->fetchAll(PDO::FETCH_ASSOC);
        foreach ($tab as $val) {
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
        $data = $this->query("SHOW CREATE TABLE " . $this->getFullTable($table, $base))->fetch(PDO::FETCH_ASSOC);
        return $data['Create Table'] ?? '';
    }

    /**
     * 获取所有表名
     * @return array|bool
     */
    public function getAllTabName(): bool|array {
        return $this->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * 获取 MySQL 版本号
     * @return string
     */
    public function version(): string {
        $data = $this->query("SELECT VERSION() as version")->fetch(PDO::FETCH_ASSOC);
        return $data['version'] ?? '';
    }

    /**
     * 执行
     * @param string $sql
     * @return int|bool
     */
    public function exec(string $sql): bool|int {
        return $this->pdo()->exec($sql);
    }

    /**
     * 执行sql
     * @param string $sql
     * @return bool|PDOStatement
     */
    public function query(string $sql): bool|PDOStatement {
        return $this->pdo()->query($sql);
    }

    /**
     * 转义字符串 addslashes
     * @param $string
     * @return string
     */
    public function quote($string): string {
        return $this->pdo()->quote($string);
    }

    /**
     * 反转义字符串 stripslashes
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
        $this->pdo = null;
    }
}