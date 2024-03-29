<?php

namespace zhqing\module;

use zhqing\extend\Frame;
use zhqing\extend\Curl;
use zhqing\extend\Safe;

class SwCompiler {
    protected array $data = [];
    protected array $var = [
        'v3.1' => ['7.2', '7.3', '7.4', '8.0', '8.1'],
        'v3.0' => ['5.4', '5.5', '5.6', '7.0', '7.1', '7.2', '7.3', '7.4', '8.0'],
        'v2.2' => ['5.4', '5.5', '5.6', '7.0', '7.1', '7.2', '7.3', '7.4'],
        'v2.1' => ['5.4', '5.5', '5.6', '7.0', '7.1', '7.2', '7.3'],
        'v2.0' => ['5.4', '5.5', '5.6', '7.0', '7.1', '7.2']
    ];

    /**
     * 设置帐号密码
     * @param string $user
     * @param string $pass
     * @param array $array
     * @return static
     */
    public static function user(string $user, string $pass, array $array = []): static {
        $self = new self();
        $config = [
            'errHits' => 2,//出错次数
            'userHits' => 0,//已出错次数
            'ver' => 'v3.1',//默认Compiler版本
            'php' => '8.1',
            'url' => 'https://business.swoole.com/',//登录网站
            'refer' => 'https://compiler.swoole.com/',//来路网址
            'dir' => __DIR__ . '/../../file/swoole/compiler',//文件保存目录
            'user' => $user,//帐号
            'pass' => $pass,//密码
            'code' => 200,//默认正常
            'msg' => '',//出错信息
            'data' => '',//返回数据
            'cookie' => [],//登录cookie
        ];
        $self->data = array_merge($config, $array);
        return $self;
    }

    /**
     * 设置文件保存目录
     * @param string $data
     * @return $this
     */
    public function dir(string $data): static {
        $this->data['dir'] = (!empty($data) ? $data : $this->data['dir']);
        return $this;
    }

    /**
     * 设置登录网站
     * @param string $data
     * @return $this
     */
    public function url(string $data): static {
        $this->data['url'] = (!empty($data) ? $data : $this->data['url']);
        return $this;
    }

    /**
     * 设置来路网址
     * @param string $data
     * @return $this
     */
    public function refer(string $data): static {
        $this->data['refer'] = (!empty($data) ? $data : $this->data['refer']);
        return $this;
    }

    /**
     * 设置Compiler版本
     * @param string $data
     * @return $this
     */
    public function ver(string $data): static {
        $this->data['ver'] = strtolower($data);
        return $this;
    }

    /**
     * 出错多少次后不再登录
     * @param int $data
     * @return $this
     */
    public function errHits(int $data): static {
        $this->data['errHits'] = $data;
        return $this;
    }

    /**
     * PHP程序源代码压缩包（仅支持.tar.gz和.zip格式）
     * 注意：代码压缩包的体积请限制在 100M 以内
     * 添加授权信息后加密的代码包里面会包含一个 license 授权文件
     * loader 端需要在 php.ini 中将授权文件添加上 格式为 swoole_loader.license_files=/yourpath/swoole-compiler.license 的配置
     * @param array|string $data //上传文件或者目录
     * @param bool $type //是否保存上传文件
     * @return $this
     */
    public function file(array|string $data, bool $type = false): static {
        $this->data['upload'] = $data;
        $this->data['saveUploadFile'] = $type;
        return $this;
    }

    /**
     * 设置php版本
     * @param string $data
     * @return $this
     */
    public function php(string $data): static {
        $this->data['php'] = $data;
        return $this;
    }

    /**
     * 项目名称(格式为小于10位的英文字母和数字的组合)
     * @param string $data
     * @return $this
     */
    public function name(string $data): static {
        $this->data['name'] = $data;
        return $this;
    }

    /**
     * 不加密的PHP文件或者文件夹的相对地址(如有多个地址请使用英文逗号进行分隔
     * 相对地址为相对于需要加密的代码的根目录),类似格式: vendor/composer,resource/view
     * @param string $data
     * @return $this
     */
    public function exclude(string $data): static {
        $this->data['exclude'] = $data;
        return $this;
    }

    /**
     * 授权终止时间，限制加密后代码可用时间(Unix时间戳格式，默认为永久可用)
     * @param string $data
     * @return $this
     */
    public function time(string $data): static {
        $this->data['time'] = $data;
        return $this;
    }

    /**
     * 运行加密后代码的服务器MAC地址 (如有多个MAC地址请使用英文逗号进行分隔,默认为不限制MAC地址)
     * @param string $data
     * @return $this
     */
    public function add(string $data): static {
        $this->data['add'] = $data;
        return $this;
    }

    /**
     * 运行加密后代码的服务器内网IP地址 (如有多个IP地址请使用英文逗号进行分隔,默认为不限制IP地址)
     * @param string $data
     * @return $this
     */
    public function ip(string $data): static {
        $this->data['ip'] = $data;
        return $this;
    }

    /**
     * 运行加密后代码的服务器HOST (如有多个HOST请使用英文逗号进行分隔,默认为不限制HOST)
     * 支持 * 前缀,例如 *.swoole.com 代表允许所有 swoole.com 的二级域名运行加密文件
     * @param string $data
     * @return $this
     */
    public function host(string $data): static {
        $this->data['host'] = $data;
        return $this;
    }

    /**
     * 自定义信息(如有多个自定义信息请使用英文分号进行分隔，否则此选项请留空)
     * 自定义信息可通过在代码中使用 swoole_get_license() 函数获取，一般自定义信息为简单的键值对
     * @param string $data
     * @return $this
     */
    public function config(string $data): static {
        $this->data['config'] = $data;
        return $this;
    }

    /**
     * 是否保留代码注释(0=不保留,1=保留)（某些框架如 Laravel/Hyperf 程序逻辑需要依赖注释）
     * @param int $data
     * @return $this
     */
    public function comments(int $data): static {
        $this->data['comments'] = $data;
        return $this;
    }

    /**
     * 执行
     * @param string $file //加密文件保存路径
     * @param bool $type //是否解压
     * @return array
     */
    public function exec(string $file, bool $type = false): array {
        if (empty($varArr = Frame::getStrArr($this->var, $this->data('ver', 'v3.1')))) {
            $this->data['code'] = 302;
            $this->data['msg'] = '加密版本仅支持:' . join(',', array_keys($this->var));
        } else if (empty(in_array($this->data('php'), $varArr))) {
            $this->data['code'] = 303;
            $this->data['msg'] = '加密版本' . $this->data('ver', 'v3.1') . '仅支持PHP:' . join(',', $varArr);
        } else {
            if (!empty($this->data('upload'))) {
                $this->data['save'] = $file;
                $this->data['cache'] = rtrim($this->data('dir'), '/') . '/' . substr(md5($this->data('user')), 8, 16);
                $this->handleZip()->encrypt();
            } else {
                $this->data['code'] = 304;
                $this->data['msg'] = '请设置要加密的文件';
            }
            if (empty($this->data('saveUploadFile')) && is_file($this->data('zip'))) {
                @unlink($this->data('zip'));
            }
        }
        return $this->unzip($type)->res();
    }

    /**
     * 解压
     * @param $type
     * @return $this
     */
    public function unzip($type): static {
        $this->data['unzip'] = ['type' => $type, 'status' => false];
        if (!empty($type) && $this->data('code') == 200 && !empty($file = $this->data('data'))) {
            $dir = rtrim(dirname($file)) . '/encrypt';
            Frame::mkDir($dir);
            exec("tar -xf $file -C $dir");
            if (!empty($array = $this->data('upload')) && is_array($array)) {
                $arr = [];
                foreach ($array as $v) {
                    $arr[] = trim(basename($v));
                }
                $data = Frame::getDirList($dir);
                $i = 0;
                Frame::delDirFile(rtrim(dirname($file)) . '/online');
                foreach ($data as $k => $v) {
                    $val = explode('/', trim($k, '/'));
                    if (in_array(join('', array_slice($val, 1, 1)), $arr)) {
                        $newFile = rtrim(dirname($file)) . '/online/' . trim(join('/', array_slice($val, 1)), '/');
                        Frame::mkDir(dirname($newFile));
                        if (!empty(Frame::copyFile($v, $newFile))) {
                            ++$i;
                            @unlink($v);
                        }
                    }
                }
                if ($i == count($data)) {
                    $this->data['unzip'] = ['type' => $type, 'status' => true];
                    Frame::delNullDir($dir);
                    @rmdir($dir);
                    @unlink($file);
                }
            }
        }
        return $this;
    }

    /**
     * 加密
     * @param bool $type
     * @return $this
     */
    protected function encrypt(bool $type = false): static {
        if (empty(is_file($this->data('zip')))) {
            $this->data['code'] = 305;
            $this->data['msg'] = '上传文件不存在';
        } else if (empty($this->data('save')) || empty(is_string($this->data('save')))) {
            $this->data['code'] = 306;
            $this->data['msg'] = '请设置保存位置';
        } else {
            $this->getCookie($type);
            if ($this->data('code') == 200) {
                $data = [
                    'file' => $this->data('zip'),
                    'php_version' => $this->data('php', '8.0'),
                    'project_name' => $this->data('name'),
                    'exclude_list' => $this->data('exclude'),
                    'final_unix_timestamp' => $this->data('time'),
                    'mac_address' => $this->data('add'),
                    'ip_address' => $this->data('ip'),
                    'hostname' => $this->data('host'),
                    'selfconfig' => $this->data('config'),
                    'save_comments' => $this->data('comments', 0),
                    'agreement' => 'on'
                ];
                $curl = Curl::post($this->data('refer'), $data)
                    ->multi(('----WebKitFormBoundary' . Frame::randStr(16)))
                    ->referer(trim($this->data('refer'), '/') . '/encryptor/index?version=' . $this->data('ver'))
                    ->path('/encryptor/index?version=' . $this->data('ver'))
                    ->cookie($this->data('cookie'))
                    ->timeConnect(8)
                    ->timeOut(8)
                    ->exec();
                if ($curl->code() == 302) {
                    $header = $curl->getHeadArr();
                    $location = Frame::getStrArr($header, 'location');
                    $task = explode('?task_id=', $location);
                    if (!empty($taskId = Frame::getStrArr($task, 1))) {
                        $get = Curl::get(trim($this->data('refer'), '/'), ['id' => $taskId])
                            ->referer(trim($this->data('refer'), '/') . '/encryptor/task/')
                            ->path('/encryptor/get_code/')
                            ->cookie($this->data('cookie'))
                            ->timeConnect(8)
                            ->timeOut(8)
                            ->exec();
                        if (!empty(Frame::strIn($get->header(), $taskId . '.'))) {
                            $ver = $this->data('ver') . '_' . $this->data('php');
                            $file = $ver . '_' . trim(Frame::delPath(basename($this->data('zip'))) . '.tar.gz');
                            $gzFile = rtrim($this->data['save'], '/') . '/' . $file;
                            Frame::mkDir(dirname($gzFile));
                            @file_put_contents($gzFile, $get->body());
                            $this->data['msg'] = '加密成功(' . $ver . ')';
                            $this->data['code'] = 200;
                            $this->data['head'] = ['Content-Type' => 'application/octet-stream', 'Content-Disposition' => 'attachment;filename="' . $ver . '_' . seekTime() . '.tar.gz"'];
                            $this->data['data'] = $gzFile;
                        } else {
                            $this->data['code'] = 308;
                            $this->data['msg'] = '下载数据错误';
                            $this->data['data'] = $header;
                        }
                    } else {
                        return $this->encrypt(true);
                    }
                } else {
                    $this->data['code'] = 307;
                    $this->data['msg'] = '提交数据错误';
                    $this->data['data'] = $data;
                    $arr = explode('<i class="fa fa-check-square-o"></i>', $curl->body());
                    if (isset($arr[1])) {
                        $array = explode('</h4>', $arr[1]);
                        if (!empty($msg = trim(Frame::getStrArr($array, 0)))) {
                            $this->data['code'] = 302;
                            $this->data['msg'] = $msg;
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * 处理上传文件
     * @return $this
     */
    protected function handleZip(): static {
        $this->data['zip'] = rtrim($this->data('cache'), '/') . '/' . date('YmdHis') . '_' . time() . '.zip';
        if (is_array($this->data('upload')) || empty(Frame::getPath($this->data('upload')))) {
            Frame::zips($this->data('upload'), $this->data['zip']);
        } else {
            Frame::copyFile($this->data['upload'], $this->data['zip']);
        }
        return $this;
    }

    /**
     * 登录获取cookie
     * @param bool $type
     * @return $this
     */
    protected function getCookie(bool $type = false): static {
        $this->data['file'] = rtrim($this->data('cache'), '/') . '/cookie.cache';
        Frame::mkDir(dirname($this->data['file']));
        if (is_file($this->data['file'])) {
            $data = unserialize(@file_get_contents($this->data['file']));
            $this->data['code'] = 200;
            $this->data['data'] = '';
            $this->data['cookie'] = Frame::isJson(Safe::movDe(Frame::getStrArr($data, 'cookie', [])));
            if (Frame::getStrArr($data, 'userHits', 0) >= $this->data('errHits', 2)) {
                $this->data['code'] = 301;
                $this->data['msg'] = '出错次数已达到' . $this->data('errHits', 2) . '次';
                $this->data['data'] = '';
                $this->data['cookie'] = [];
                return $this;
            }
        }
        if (!empty($type) || empty($this->data('cookie'))) {
            $this->data['code'] = 400;
            $this->data['msg'] = '';
            $this->data['data'] = '';
            $this->data['cookie'] = [];
            $curl = Curl::post($this->data('url'), [
                'name' => $this->data('user'),
                'password' => $this->data('pass'),
                'refer' => trim($this->data('refer'), '/') . '/encryptor/index?version=' . $this->data('ver')
            ])
                ->referer(trim($this->data('url'), '/') . '/page/login?refer=' . trim($this->data('refer'), '/') . '/encryptor/index?version=' . $this->data('ver'))
                ->path('page/logindo')
                ->timeConnect(8)
                ->timeOut(8)
                ->exec();
            if ($curl->code() == 200) {
                $arr = $curl->array();
                if (Frame::getStrArr($arr, 'code') == 200) {
                    $this->data['code'] = 200;
                    $cookie = $curl->getCookie();
                    foreach ($cookie as $k => $v) {
                        $val = explode(';', $v);
                        $cookie[$k] = trim($val[key($val)]);
                    }
                    $this->data['cookie'] = $cookie;
                    $this->data['userHits'] = 0;
                } else {
                    $this->data['userHits'] = ($this->data('userHits', 0) + 1);
                    $this->data['msg'] = Frame::getStrArr($arr, 'message');
                }
            } else {
                $this->data['userHits'] = ($this->data('userHits', 0) + 1);
                $this->data['msg'] = '访问出错';
            }
            @file_put_contents($this->data('file'), serialize([
                'time' => seekDate(),
                'userHits' => $this->data['userHits'],
                'cookie' => Safe::movEn($this->data('cookie', []))
            ]));
        }
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function data(string $key, mixed $default = ''): mixed {
        return Frame::getStrArr($this->data, $key, $default);
    }

    /**
     * 返回数据
     * @return array
     */
    protected function res(): array {
        return array_merge(['code' => $this->data('code'), 'data' => $this->data('data'), 'unzip' => $this->data('unzip'), 'msg' => $this->data('msg')], (!empty($this->data('head')) ? ['header' => $this->data('head')] : []));
    }
}