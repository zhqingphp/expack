<?php

namespace zhqing\extend\frame;

use Generator;

trait File {
    /**
     * 删除 PHP 注释以及空白字符
     * @param string $file
     * @return string
     */
    public static function phpFileWhite(string $file): string {
        return php_strip_whitespace($file);
    }

    /**
     * 代码高亮输出
     * @param string $data
     * @return bool|string
     */
    public static function highStr(string $data): bool|string {
        return highlight_string($data, true);
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
            $obj = self::DirList($path, $isDir);
            while ($obj->valid()) {
                $file = $obj->current();
                $array[self::strRep($file, $path, '')] = $file;
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
            $glob = self::FileData($file);
            while ($glob->valid()) {
                $str .= $glob->current();
                $glob->next();
            }
        }
        return $str;
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
            self::mkDir(dirname($newFilePath));
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
     * 写入文件时,用户无法读取
     * @param $file
     * @param  $data //闭包
     * @param string $mode
     * @param string $default
     * @return mixed
     */
    public static function lockAddFile($file, $data, string $mode = 'w', string $default = ''): mixed {
        self::mkDir(\dirname($file));
        $fp = \fopen($file, $mode);
        if (\flock($fp, LOCK_EX)) {
            if (!empty($default = $data())) {
                \fwrite($fp, $default);
            }
            \flock($fp, LOCK_UN);
        }
        \fclose($fp);
        return $default;
    }

    /**
     * serialize
     * 写入文件时,用户无法读取
     * 把内容添加到指定文件
     * @param $file //写入文件名
     * @param $data //文件内容
     * @param string $mode //模式     模式说明：https://www.runoob.com/php/func-filesystem-fopen.html
     */
    public static function addFileData($file, $data, string $mode = 'w') {
        self::mkDir(\dirname($file));
        $fp = \fopen($file, $mode);
        if (\flock($fp, LOCK_EX)) {
            \fwrite($fp, $data);
            \flock($fp, LOCK_UN);
        }
        \fclose($fp);
    }

    /**
     * unserialize
     * 文件写入完成才可以读取
     * @param $file //要读取的文件
     * @param null $data //默认返回内容
     * @return false|string|null
     */
    public static function getFileData($file, $data = null): bool|string|null {
        if (!empty(is_file($file))) {
            $fp = \fopen($file, "r");
            if (\flock($fp, LOCK_SH)) {
                $data = \fread($fp, \filesize($file));
            }
            \fclose($fp);
        }
        return $data;
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
                            self::delDirFile($files, $format);
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
                        self::delNullDir($dir);
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
                    $result = self::getDirFile($path . '/' . $file, $result);
                } else {
                    $result[$file] = $path . '/' . $file;
                }
            }
        }
        return $result;
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
                    $obj = self::DirList($dirFile, $isDir);
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
     * 添加内容到文件
     * @param string $file //文件
     * @param array $array //要添加的内容
     * @param string $sing //唯一标识
     * @param bool $type //是否添加
     */
    public static function addArrayFile(string $file, array $array, string $sing, bool $type = true) {
        if (!empty($type)) {
            self::mkDir(dirname($file));
            $oldData = [];
            if (is_file($file)) {
                $oldData = self::isJson(@file_get_contents($file));
            }
            $oldData[md5($sing)] = $array;
            @file_put_contents($file, self::json($oldData));
        }
    }
}