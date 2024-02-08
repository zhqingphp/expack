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
        $sql = "SELECT * FROM information_schema.TABLES";
        if ($base === false) {
            $sql .= " WHERE table_schema='" . $this->config['database'] . "';";
        } else if (!empty($base) && !empty(is_string($base))) {
            $sql .= " WHERE table_schema='" . $base . "';";
        }
        $result = $this->query($sql);
        while ($val = $result->fetch_assoc()) {
            $data[$val['TABLE_SCHEMA']][$val['TABLE_NAME']] = $val;
        }
        return $data;
    }

    /**
     * 获取表单数据
     * @param string $table 表单名
     * @param array $data
     * @return bool|array
     */
    public function getTabData(string $table, array $data = []): bool|array {
        $result = $this->query("SELECT * FROM " . $this->getFullTable($table));
        while ($val = $result->fetch_assoc()) {
            $data[] = $val;
        }
        return $data;
    }

    /**
     * 获取表单字段信息
     * @param string $table 表单名
     * @param array $data
     * @return array
     */
    public function getTabInfo(string $table, array $data = []): array {
        $result = $this->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name='" . $this->getFullTable($table) . "'");
        while ($val = $result->fetch_assoc()) {
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
        $data = $this->query("SHOW CREATE TABLE " . $this->getFullTable($table))->fetch_assoc();
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
     * 执行sql
     * @param string $sql
     * @return mysqli_result|bool
     */
    public function query(string $sql): mysqli_result|bool {
        return $this->mysqli()->query($sql);
    }

    /**
     * 转义字符串
     * @param $string
     * @return string
     */
    public function quote($string): string {
        //addslashes
        return "'" . $this->mysqli()->escape_string($string) . "'";
    }

    /**
     * 反转义字符串
     * @param $string
     * @return string
     */
    public function strips($string): string {
        //stripslashes
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