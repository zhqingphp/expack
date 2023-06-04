<?php

namespace zhqing\module;

use zhqing\extend\Frame;
use zhqing\extend\Curl;

class SwCompiler {
    protected array $data = [];

    /**
     * 设置帐号密码
     * @param $user
     * @param $pass
     * @return static
     */
    public static function user($user, $pass): static {
        $self = new self();
        $self->data = [
            'errHits' => 2,//出错次数
            'userHits' => 0,//已出错次数
            'ver' => 'v3.1',//默认Compiler版本
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
        return $self;
    }

    /**
     * 设置文件保存目录
     * @param $data
     * @return $this
     */
    public function dir($data): static {
        $this->data['dir'] = $data;
        return $this;
    }

    /**
     * 设置登录网站
     * @param $data
     * @return $this
     */
    public function url($data): static {
        $this->data['url'] = $data;
        return $this;
    }

    /**
     * 设置来路网址
     * @param $data
     * @return $this
     */
    public function refer($data): static {
        $this->data['refer'] = $data;
        return $this;
    }

    /**
     * 设置Compiler版本
     * @param $data
     * @return $this
     */
    public function ver($data): static {
        $this->data['ver'] = $data;
        return $this;
    }

    /**
     * 出错多少次后不再登录
     * @param $data
     * @return $this
     */
    public function errHits($data): static {
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
     * @param $data (7.2,7.3,7.4,8.0,8.1)
     * @return $this
     */
    public function php($data): static {
        $this->data['php'] = $data;
        return $this;
    }

    /**
     * 项目名称(格式为小于10位的英文字母和数字的组合)
     * @param $data
     * @return $this
     */
    public function name($data): static {
        $this->data['name'] = $data;
        return $this;
    }

    /**
     * 不加密的PHP文件或者文件夹的相对地址(如有多个地址请使用英文逗号进行分隔
     * 相对地址为相对于需要加密的代码的根目录),类似格式: vendor/composer,resource/view
     * @param $data
     * @return $this
     */
    public function exclude($data): static {
        $this->data['exclude'] = $data;
        return $this;
    }

    /**
     * 授权终止时间，限制加密后代码可用时间(Unix时间戳格式，默认为永久可用)
     * @param $data
     * @return $this
     */
    public function time($data): static {
        $this->data['time'] = $data;
        return $this;
    }

    /**
     * 运行加密后代码的服务器MAC地址 (如有多个MAC地址请使用英文逗号进行分隔,默认为不限制MAC地址)
     * @param $data
     * @return $this
     */
    public function add($data): static {
        $this->data['add'] = $data;
        return $this;
    }

    /**
     * 运行加密后代码的服务器内网IP地址 (如有多个IP地址请使用英文逗号进行分隔,默认为不限制IP地址)
     * @param $data
     * @return $this
     */
    public function ip($data): static {
        $this->data['ip'] = $data;
        return $this;
    }

    /**
     * 运行加密后代码的服务器HOST (如有多个HOST请使用英文逗号进行分隔,默认为不限制HOST)
     * 支持 * 前缀,例如 *.swoole.com 代表允许所有 swoole.com 的二级域名运行加密文件
     * @param $data
     * @return $this
     */
    public function host($data): static {
        $this->data['host'] = $data;
        return $this;
    }

    /**
     * 自定义信息(如有多个自定义信息请使用英文分号进行分隔，否则此选项请留空)
     * 自定义信息可通过在代码中使用 swoole_get_license() 函数获取，一般自定义信息为简单的键值对
     * @param $data
     * @return $this
     */
    public function config($data): static {
        $this->data['config'] = $data;
        return $this;
    }

    /**
     * 是否保留代码注释(0=不保留,1=保留)（某些框架如 Laravel/Hyperf 程序逻辑需要依赖注释）
     * @param $data
     * @return $this
     */
    public function comments($data): static {
        $this->data['comments'] = $data;
        return $this;
    }

    /**
     * 执行
     * @param string $file //加密文件保存路径
     * @return array
     */
    public function exec(string $file): array {
        $this->data['code'] = 300;
        $this->data['save'] = $file;
        $this->data['msg'] = '请设置要加密的文件';
        if (!empty($this->data('upload'))) {
            $this->data['cache'] = rtrim($this->data('dir'), '/') . '/' . substr(md5($this->data('user')), 8, 16);
            $this->handleZip()->encrypt();
        }
        if (empty($this->data('saveUploadFile')) && is_file($this->data('zip'))) {
            @unlink($this->data('zip'));
        }
        return $this->res();
    }

    /**
     * 加密
     * @param bool $type
     * @return $this
     */
    protected function encrypt(bool $type = false): static {
        if (empty(is_file($this->data('zip')))) {
            $this->data['code'] = 300;
            $this->data['msg'] = '上传文件不存在';
        } else if (empty($this->data('save')) || empty(is_string($this->data('save')))) {
            $this->data['code'] = 300;
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
                $header = strtolower($curl->header());
                if (empty($curl->body()) || !empty(Frame::strIn($header, 'location'))) {
                    return $this->encrypt(true);
                } else {
                    if (!empty(Frame::strIn($curl->body(), '<title>'))) {
                        $this->data['code'] = 301;
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
                    } else {
                        $file = trim(Frame::delPath(basename($this->data('zip'))) . '.tar.gz');
                        $gzFile = rtrim($this->data['save'], '/') . '/' . $file;
                        Frame::mkDir(dirname($gzFile));
                        @file_put_contents($gzFile, $curl->body());
                        $this->data['msg'] = '成功加密';
                        $this->data['code'] = 200;
                        $this->data['head'] = ['Content-Type' => 'application/octet-stream', 'Content-Disposition' => 'attachment;filename="' . $file . '"'];
                        $this->data['data'] = $gzFile;
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
        $this->data['zip'] = rtrim($this->data('cache'), '/') . '/zip/' . date('Ymd') . '_' . time() . '.zip';
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
            $this->data['cookie'] = Frame::getStrArr($data, 'cookie', []);
            if (Frame::getStrArr($data, 'userHits', 0) >= $this->data('errHits', 2)) {
                $this->data['code'] = 444;
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
            @file_put_contents($this->data('file'), serialize(['cookie' => $this->data('cookie', []), 'userHits' => $this->data['userHits']]));
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
        return array_merge(['code' => $this->data('code'), 'data' => $this->data('data'), 'msg' => $this->data('msg')], (!empty($this->data('head')) ? ['header' => $this->data('head')] : []));
    }
}