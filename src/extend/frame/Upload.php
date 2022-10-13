<?php

namespace zhqing\extend\frame;

trait Upload {

    /**
     * @param array|string $arr //上传的数据
     * @param int|string $dir //上传路径
     * @param int|string $folder //上传文件夹
     * @param int|string $format //支持格式
     * @param int|string $size //限制大小
     * @return string|string[]
     */
    public static function getUploadFile(array|string $arr, int|string $dir, int|string $folder, int|string $format, int|string $size = 2): array|string {
        $name = $arr['name'] ?? '';
        if (!empty($name)) {
            $Path = \strtolower(\pathinfo($name, PATHINFO_EXTENSION));
            if (\in_array($Path, (\is_array($format) ? $format : \explode(',', \strtolower($format)))) === false) {
                $arr['error'] = 1009;
            }
            $size = $size * 1024;
            if ($arr['size'] > $size) {
                $arr['error'] = 1010;
            }
        }
        $error = [
            '1001' => '文件超过php.ini限',
            '1002' => '文件超过html限制',
            '1003' => '文件上传不完整',
            '1004' => '没有选择文件',
            '1006' => '服务器内部错误',
            '1007' => '服务器内部错误',
            '1008' => '上传文件不能为空',
            '1009' => '上传格式' . (!empty($Path) ? ('(' . $Path . ')') : '') . '不正确',
            '1010' => '文件大小必须不超过' . $size . 'KB',
            '1011' => '文件移动失败'
        ];
        if (isset($error[$arr['error']])) {
            return $error[$arr['error']];
        }
        $newDir = \rtrim($dir, '/') . '/' . \rtrim($folder, '/') . '/';
        if (empty(\is_dir($newDir))) {
            \mkdir($newDir, 0777, true);
        }
        $newFile = \time() . '_' . $name;
        $success = \move_uploaded_file($arr['tmp_name'], $newDir . $newFile);
        if (empty($success)) {
            return $error[1011];
        } else {
            return ['img' => \rtrim($folder, '/') . '/' . $newFile, 'base' => (\in_array(($Path ?? ''), ['jpg', 'gif', 'png', 'jpeg']) ? 'data:image/' . ($Path ?? '') . ';base64,' : '') . \base64_encode(\file_get_contents($newDir . $newFile))];
        }
    }
}