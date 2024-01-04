<?php

namespace zhqing\cli;

use zhqing\extend\Frame;

class Extend {

    public array $argv = []; //输入参数
    public array $array = []; //输出内容1
    public string $content = ''; //输出内容2
    public array $method = [
        'help' => '使用说明',//方法后面加Cli
    ];

    /**
     * 使用说明
     */
    public function helpCli() {
        foreach ($this->method as $k => $v) {
            $this->back($v, $k);
        }
    }

    /**
     * @param $argv
     */
    public function __construct($argv) {
        $this->argv = $argv;
    }

    /**
     * 设置方法
     * @param $data
     * @return $this
     */
    public function setMethod($data): static {
        $this->method = array_merge($this->method, $data);
        return $this;
    }

    /**
     * 执行
     */
    public function exec() {
        if (in_array($this->argv(1), array_keys($this->method))) {
            try {
                $sign = self::setCliText('==================', 'green');
                $this->content = $sign . self::setCliText(seekDate(), 'red') . self::setCliText('开始', 'yellow') . $sign . "\r\n";
                call_user_func([$this, $this->argv(1) . 'Cli']);
                if (!empty($this->array)) {
                    foreach ($this->array as $v) {
                        $this->content .= self::setCliText($v['title'], 'yellow') . ":" . self::setCliText($v['data'], 'blue') . "\r\n";
                    }
                }
                $this->content = trim($this->content, "\r\n") . "\r\n" . $sign . self::setCliText(seekDate(), 'red') . self::setCliText('结束', 'yellow') . $sign . "\r\n";
                echo $this->content;
            } catch (\Exception | \Error $e) {
                var_dump($e);
            }
            exit;
        }
    }

    /**
     * 获取输入参数
     * @param string $key
     * @param mixed|string $default
     * @return mixed
     */
    public function argv(string $key, mixed $default = ''): mixed {
        return Frame::getStrArr($this->argv, $key, $default);
    }

    /**
     * 设置返回
     * @param string $key
     * @param mixed|string $data
     * @param bool $type
     * @return $this
     */
    public function come(string $key, mixed $data = '', bool $type = false): static {
        $sign = $type === true ? ' ' : '';
        $this->array[] = ['title' => $key . $sign, 'data' => $sign . $data];
        return $this;
    }

    /**
     * 设置返回
     * @param $key
     * @param $data
     * @param bool $type
     * @return $this
     */
    public function res($key, $data, bool $type = true): static {
        $this->content .= self::setCliText($key, 'yellow') . ":" . self::setCliText($data, 'blue') . ($type === true ? "\r\n" : "");
        return $this;
    }

    /**
     * 设置返回
     * @param $key
     * @param $data
     * @return $this
     */
    public function back($key, $data): static {
        $this->content .= self::setCliText($key, 'blue') . ': ' . self::setCliText("php fileName ", 'green') . self::setCliText($data, 'blue') . "\r\n";
        return $this;
    }

    /**
     * 设置cli显示色彩
     * @param string $text 内容
     * @param string|int $color //色彩(数值也可以设置色彩(0-250))
     * @param string|int $set //设置(color=字体色,line=字体色加下划线,back=背景色),数字有不同设置
     * @return string
     */
    public static function setCliText(string $text, string|int $color = '', string|int $set = 'color'): string {
        $config = [
            //字体色
            'color' => [
                'red' => 196,//红色
                'yellow' => 190,//黄色
                'green' => 46,//绿色
                'blue' => 12,//蓝色
            ],
            'config' => [
                'color' => 5,//字体色
                'line' => 4,//下划线
                'back' => 7,//背景色
            ]
        ];
        return "\033[38;5;" . ($config['color'][$color] ?? (is_numeric($color) ? $color : 255)) . ";" . ($config['color'][$set] ?? (is_numeric($set) ? $set : 5)) . "m{$text}\033[0m";
    }

    /**
     * 启动
     * @param $argv
     */
    public static function run($argv) {
        (new self($argv))->exec();
    }

    /**
     * @param $method
     * @param $parameter
     */
    public function __call($method, $parameter) {
        $this->res('请写方法名', $method);
    }
}