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
        $sql = "SELECT * FROM information_schema.TABLES";
        if ($base === false) {
            $sql .= " WHERE table_schema='" . $this->config['database'] . "';";
        } else if (!empty($base) && !empty(is_string($base))) {
            $sql .= " WHERE table_schema='" . $base . "';";
        }
        $tab = $this->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        foreach ($tab as $val) {
            $data[$val['TABLE_SCHEMA']][$val['TABLE_NAME']] = $val;
        }
        return $data;
    }

    /**
     * 获取表单数据
     * @param string $table 表单名
     * @return bool|array
     */
    public function getTabData(string $table): bool|array {
        return $this->query("SELECT * FROM " . $this->getFullTable($table))->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 获取表单字段信息
     * @param string $table 表单名
     * @param array $data
     * @return array
     */
    public function getTabInfo(string $table, array $data = []): array {
        $tab = $this->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name='" . $this->getFullTable($table) . "'")
            ->fetchAll(PDO::FETCH_ASSOC);
        foreach ($tab as $val) {
            $data[$val['COLUMN_NAME']] = $val;
        }
        return $data;
    }

    /**
     * 获取表单sql
     * @param string $table 表单名
     * @return string
     */
    public function getTabSql(string $table): string {
        $data = $this->query("SHOW CREATE TABLE " . $this->getFullTable($table))->fetch(PDO::FETCH_ASSOC);
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
     * 关闭链接
     */
    public function close() {
        $this->pdo = null;
    }
}