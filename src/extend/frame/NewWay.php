<?php

namespace zhqing\extend\frame;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;

trait NewWay {
    /**
     * 检查目录/文件是否可写
     * @param $path
     * @return bool
     */
    public static function isPathWritable($path): bool {
        if (DIRECTORY_SEPARATOR == '/' && !@ini_get('safe_mode')) {
            return is_writable($path);
        }
        if (is_dir($path)) {
            $path = rtrim($path, '/') . '/' . md5(mt_rand(1, 100) . mt_rand(1, 100));
            if (($fp = @fopen($path, 'ab')) === false) {
                return false;
            }
            fclose($fp);
            @chmod($path, 0777);
            @unlink($path);
            return true;
        } elseif (!is_file($path) || ($fp = @fopen($path, 'ab')) === false) {
            return false;
        }
        fclose($fp);
        return true;
    }

    /**
     * 删除文件夹
     * @param string $dirname 目录
     * @param bool $densely 是否删除自身
     * @return boolean
     */
    public static function delDir(string $dirname, bool $densely = true): bool {
        if (!is_dir($dirname)) {
            return false;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $filing) {
            if ($filing->isDir()) {
                self::delDir($filing->getRealPath());
            } else {
                @unlink($filing->getRealPath());
            }
        }
        if ($densely) {
            @rmdir($dirname);
        }
        return true;
    }

    /**
     * 将一个文件单位转为字节
     * @param string $unit 将b、kb、m、mb、g、gb的单位转为 byte
     */
    public static function fileToByte(string $unit): int {
        preg_match('/([0-9\.]+)(\w+)/', $unit, $matches);
        if (!$matches) {
            return 0;
        }
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        return (int)($matches[1] * pow(1024, $typeDict[strtolower($matches[2])] ?? 0));
    }
}