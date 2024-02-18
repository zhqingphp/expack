<?php

namespace zhqing\module;

use Generator;

class FileHelper {
    /**
     * 添加内容到文件
     * @param string|null $file 文件
     * @param mixed $data 要添加的内容
     * @param string|int|bool|null $sing 标识,有标识可追加
     * @param bool $type 是否使用锁定
     * @return bool|int
     */
    public static function saveData(string|null $file, mixed $data, string|int|bool|null $sing = '', bool $type = false): bool|int {
        $file = !empty($file) ? $file : (__DIR__ . '/../../../file/fileData.cache');
        $content = is_callable($data) ? $data() : $data;
        if (!empty($sing)) {
            if (!empty($fileData = @file_get_contents($file))) {
                $arr = !empty($array = unserialize($fileData) ?? []) ? (is_array($array) ? $array : []) : [];
            }
            $arr[$sing] = $content;
        }
        return (
        !empty($type)
            ? (static::saveLockData($file, ($arr ?? $content), true))
            : (@file_put_contents(static::mkDir($file), serialize(($arr ?? $content))))
        );
    }

    /**
     * 读取文件内容
     * @param string|null $file 文件
     * @param string|int|bool|null $sing 标识
     * @param bool $type 是否判断锁定
     * @param mixed $default 默认
     * @return mixed
     */
    public static function getData(string|null $file = '', string|int|bool|null $sing = '', bool $type = false, mixed $default = ''): mixed {
        $file = !empty($file) ? $file : (__DIR__ . '/../../../file/fileData.cache');
        $content = empty($type) ? unserialize((@file_get_contents($file) ?? '')) : static::getLockData($file, true);
        return (
        !empty($sing)
            ? ((!empty($data = ($content[$sing] ?? $default)) ? $data : $default))
            : $content
        );
    }

    /**
     * 写入文件时,用户无法读取
     * 把内容添加到指定文件
     * @param string $file 写入文件名
     * @param mixed $data 文件内容
     * @param bool $type 是否使用serialize
     * @param string $mode 模式
     * 模式说明：https://www.runoob.com/php/func-filesystem-fopen.html
     * "r" （只读方式打开，将文件指针指向文件头）
     * "r+" （读写方式打开，将文件指针指向文件头）
     * "w" （写入方式打开，清除文件内容，如果文件不存在则尝试创建之）
     * "w+" （读写方式打开，清除文件内容，如果文件不存在则尝试创建之）
     * "a" （写入方式打开，将文件指针指向文件末尾进行写入，如果文件不存在则尝试创建之）
     * "a+" （读写方式打开，通过将文件指针指向文件末尾进行写入来保存文件内容）
     * "x" （创建一个新的文件并以写入方式打开，如果文件已存在则返回 FALSE 和一个错误）
     * "x+" （创建一个新的文件并以读写方式打开，如果文件已存在则返回 FALSE 和一个错误）
     * @return bool
     */
    public static function saveLockData(string $file, mixed $data, bool $type = false, string $mode = 'w'): bool {
        static::mkDir($file);
        $fp = \fopen($file, $mode);
        if (\flock($fp, LOCK_EX)) {
            $content = (is_callable($data) ? ($data()) : $data);
            \fwrite($fp, (!empty($type) ? serialize($content) : (is_array($content) ? static::json($content) : $content)));
            \flock($fp, LOCK_UN);
        }
        return \fclose($fp);
    }

    /**
     * 文件写入完成才可以读取
     * @param string $file 要读取的文件
     * @param bool $type 是否使用unserialize
     * @param string $mode 模式
     * @param mixed $data 默认返回内容
     * 模式说明：https://www.runoob.com/php/func-filesystem-fopen.html
     * "r" （只读方式打开，将文件指针指向文件头）
     * "r+" （读写方式打开，将文件指针指向文件头）
     * "w" （写入方式打开，清除文件内容，如果文件不存在则尝试创建之）
     * "w+" （读写方式打开，清除文件内容，如果文件不存在则尝试创建之）
     * "a" （写入方式打开，将文件指针指向文件末尾进行写入，如果文件不存在则尝试创建之）
     * "a+" （读写方式打开，通过将文件指针指向文件末尾进行写入来保存文件内容）
     * "x" （创建一个新的文件并以写入方式打开，如果文件已存在则返回 FALSE 和一个错误）
     * "x+" （创建一个新的文件并以读写方式打开，如果文件已存在则返回 FALSE 和一个错误）
     * @return mixed
     */
    public static function getLockData(string $file, bool $type = false, string $mode = 'r', mixed $data = ''): mixed {
        if (!empty(is_file($file))) {
            $fp = \fopen($file, $mode);
            if (\flock($fp, LOCK_SH)) {
                $data = \fread($fp, \filesize($file));
                $data = (!empty($type) ? unserialize($data) : (!empty($arr = static::isJson($data)) ? $arr : $data));
            }
            \fclose($fp);
        }
        return $data;
    }

    /**
     * 删除 PHP 注释以及空白字符
     * @param string $code 代码
     * @param string|null $save 保存文件
     * @return string|int|false
     */
    public static function phpCodeWhite(string $code, string|null $save = null): string|int|false {
        $tempFile = __DIR__ . '/../../../file/code/' . date("YmdHis") . '_' . rand(10000, 99999) . rand(10000, 99999) . '.php';
        @file_put_contents(static::mkDir($tempFile), $code);
        $strippedCode = static::phpWhite($tempFile);
        @unlink($tempFile);
        if (is_string($save) && !empty($save)) {
            return @file_put_contents(static::mkDir($save), $strippedCode);
        }
        return $strippedCode;
    }

    /**
     * 删除 PHP 注释以及空白字符
     * @param string $file 文件
     * @param string|null $save 保存文件
     * @return string|int|false
     */
    public static function phpWhite(string $file, string|null $save = null): string|int|false {
        if (is_file($file)) {
            $strippedCode = php_strip_whitespace($file);
            if (is_string($save) && !empty($save)) {
                return @file_put_contents(static::mkDir($save), $strippedCode);
            }
            return $strippedCode;
        }
        return 'File does not exist';
    }

    /**
     * PHP代码高亮输出
     * @param string $code
     * @return bool|string
     */
    public static function phpCodeHigh(string $code): bool|string {
        return highlight_string($code, true);
    }

    /**
     * PHP文件代码高亮输出
     * @param string $file
     * @return bool|string
     */
    public static function phpHigh(string $file): bool|string {
        if (is_file($file)) {
            return highlight_string(@file_get_contents($file), true);
        }
        return 'File does not exist';
    }

    /**
     * yield获取目录下的文件列表
     * @param $path //文件夹路径
     * @param bool $isDir //是否获取文件夹
     * @return array
     */
    public static function getDirList($path, bool $isDir = false): array {
        $array = [];
        if (!empty(is_dir($path))) {
            $obj = static::DirList($path, $isDir);
            while ($obj->valid()) {
                $file = $obj->current();
                $array[static::strRep($file, $path, '')] = $file;
                $obj->next();
            }
        }
        return $array;
    }


    /**
     * yield获取文件内容
     * @param $file
     * @return string
     */
    public static function getFile($file): string {
        $str = '';
        if (!empty(is_file($file))) {
            $glob = static::FileData($file);
            while ($glob->valid()) {
                $str .= $glob->current();
                $glob->next();
            }
        }
        return $str;
    }

    /**
     * yield读取文件
     * @param $file
     * @return Generator
     */
    private static function FileData($file): Generator {
        if ($handle = fopen($file, 'r')) {
            while (!feof($handle)) {
                yield trim(fgets($handle));
            }
            fclose($handle);
        }
    }

    /**
     * 复制文件
     * @param $filePath
     * @param $newFilePath
     * @return false|int
     */
    public static function copyFile($filePath, $newFilePath): bool|int {
        $type = false;
        if (is_readable($filePath)) {
            static::mkDir($newFilePath);
            if (($handle1 = fopen($filePath, 'r')) && ($handle2 = fopen($newFilePath, 'w'))) {
                $type = stream_copy_to_stream($handle1, $handle2);
                fclose($handle1);
                fclose($handle2);
            }
        }
        clearstatcache();
        return $type;
    }

    /**
     * 删除其文件夹下所有指定格式文件(文件夹，格式)
     * @param $dir
     * @param string $format (为空删除全部)
     */
    public static function delDirFile($dir, string $format = '') {
        if (\file_exists($dir)) {
            $fp = \opendir($dir);
            while ($file = \readdir($fp)) {
                if ($file != "." && $file != "..") {
                    $files = $dir . "/" . $file;
                    if (!\is_dir($files)) {
                        if (empty($format) || (\substr(\strrchr($files, '.'), 1) == $format)) {
                            @unlink($files);
                        }
                    } else {
                        if (\is_dir($files)) {
                            static::delDirFile($files, $format);
                        }
                    }
                }
            }
            \closedir($fp);
        }
    }

    /**
     * 删除其文件夹下所有的空文件夹
     * @param $path
     */
    public static function delNullDir($path) {
        if (\is_dir($path) && ($handle = \opendir($path)) !== false) {
            while (($file = \readdir($handle)) !== false) {
                if ($file != '.' && $file != '..') {
                    $dir = $path . '/' . $file;
                    if (\is_dir($dir)) {
                        static::delNullDir($dir);
                        if (\count(\scandir($dir)) == 2) {
                            \rmdir($dir);
                        }
                    }
                }
            }
            \closedir($handle);
        }
    }

    /**
     * 获取目录下全部文件列表
     * @param $path
     * @param array $result
     * @return mixed
     */
    public static function getDirFile($path, array $result = []): mixed {
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                if (is_dir($path . '/' . $file)) {
                    $result = static::getDirFile($path . '/' . $file, $result);
                } else {
                    $result[$file] = $path . '/' . $file;
                }
            }
        }
        return $result;
    }

    /**
     * yield读取文件夹
     * @param $path
     * @param bool $isDir
     * @return Generator
     */
    private static function DirList($path, bool $isDir = false): Generator {
        $path = rtrim($path, '/*');
        if (is_readable($path)) {
            $dh = opendir($path);
            while (($file = readdir($dh)) !== false) {
                if (str_starts_with($file, '.')) {
                    continue;
                }
                $dirFile = "{$path}/{$file}";
                if (is_dir($dirFile)) {
                    $obj = static::DirList($dirFile, $isDir);
                    while ($obj->valid()) {
                        yield $obj->current();
                        $obj->next();
                    }
                    if ($isDir) {
                        yield $dirFile;
                    }
                } else {
                    yield $dirFile;
                }
            }
            closedir($dh);
        }
    }

    /**
     * 创造文件夹
     * @param string $filePath 文件名
     * @return string
     */
    public static function mkDir(string $filePath): string {
        $path = dirname($filePath);
        if (empty(is_dir($path))) {
            mkdir($path, 0777, true);
        }
        return $filePath;
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

    /**
     * 替换内容
     * @param string $str
     * @param string $old
     * @param string $new
     * @return string
     */
    public static function strRep(string $str, string $old, string $new = ''): string {
        return \str_replace($old, $new, $str);
    }
}