<?php

namespace zhqing\extend;

class IosWebClip {
    public array $Arr = [];

    /**
     * APP名称
     * @param string $data
     * @return $this
     */
    public static function title(string $data): static {
        $self = new self();
        $self->Arr['title'] = $data;
        return $self;
    }

    /**
     * APP网站
     * @param string $data
     * @return $this
     */
    public function url(string $data): static {
        $this->Arr['url'] = $data;
        return $this;
    }

    /**
     * 是否全屏显示
     * @param string $data
     * @return $this
     */
    public function screen(string $data): static {
        $this->Arr['screen'] = $data;
        return $this;
    }

    /**
     * 是否可移除
     * @param string $data
     * @return $this
     */
    public function move(string $data): static {
        $this->Arr['move'] = $data;
        return $this;
    }

    /**版本号
     * @param int $data
     * @return $this
     */
    public function version(int $data): static {
        $this->Arr['version'] = $data;
        return $this;
    }

    /**
     * APP图标(绝对路径)
     * @param string $data
     * @return $this
     */
    public function icon(string $data): static {
        $this->Arr['icon'] = $data;
        return $this;
    }

    /**
     * 唯一ID
     * @param string $data
     * @return $this
     */
    public function build(string $data): static {
        $this->Arr['build'] = $data;
        return $this;
    }

    /**
     * 生成文件(绝对路径)
     * @param string $data
     * @return $this
     */
    public function file(string $data): static {
        $this->Arr['file'] = $data;
        return $this;
    }

    /**
     * 签名信息['file'=>'绝对路径签名后的文件.mobileconfig','key'=>'','pem'=>'']
     * @param array $data
     * @return $this
     */
    public function ssl(array $data): static {
        $this->Arr['ssl'] = $data;
        return $this;
    }

    /**
     * 返回xml
     * @return array
     */
    public function xml(): array {
        $data = $this->handle();
        if (!empty(isset($data['code']))) {
            return $data;
        }
        return ['code' => 200, 'msg' => '', 'file' => self::xmlString($data)];
    }

    /**
     * 执行生成
     * @param false $type //是否签名
     * @return array
     */
    public function exec(bool $type = false): array {
        $data = $this->handle();
        if (!empty(isset($data['code']))) {
            return $data;
        }
        if (empty($data['file'])) {
            return ['code' => 405, 'msg' => '请填写输出的文件名(绝对路径)', 'file' => ''];
        }
        \file_put_contents($this->Arr['file'], self::xmlString($data));
        $info = ['code' => 200, 'msg' => 'success', 'file' => $this->Arr['file']];
        if (!empty($ssl = ($this->Arr['ssl'] ?? '')) && !empty($type)) {
            if (empty(isset($ssl['file']))) {
                $ssl_data = ['code' => 406, 'msg' => '请填写签名输出的文件名(绝对路径)', 'file' => ''];
            } else if (empty($ssl['file'])) {
                $ssl_data = ['code' => 407, 'msg' => '请填写签名输出的文件名(绝对路径)', 'file' => ''];
            } else if (empty(isset($ssl['pem']))) {
                $ssl_data = ['code' => 408, 'msg' => '请填写pem文件(绝对路径)', 'file' => ''];
            } else if (empty(is_file($ssl['pem']))) {
                $ssl_data = ['code' => 409, 'msg' => 'pem文件不存在(绝对路径)', 'file' => ''];
            } else if (empty(isset($ssl['key']))) {
                $ssl_data = ['code' => 410, 'msg' => '请填写key文件(绝对路径)', 'file' => ''];
            } else if (empty(is_file($ssl['key']))) {
                $ssl_data = ['code' => 411, 'msg' => 'key文件不存在(绝对路径)', 'file' => ''];
            } else {
                $ssl_data = $this->execSsl($this->Arr['ssl']);
            }
            return \array_merge($info, ['ssl' => $ssl_data]);
        }
        return $info;
    }

    /**
     * 数据处理
     * @return array
     */
    private function handle(): array {
        if (empty(isset($this->Arr['icon']))) {
            return ['code' => 401, 'msg' => '请填写图标文件(绝对路径)', 'file' => ''];
        } else if (empty(is_file($this->Arr['icon']))) {
            return ['code' => 402, 'msg' => $this->Arr['icon'] . '(图标文件不存在)', 'file' => ''];
        }
        $data = \array_merge(
            [
                'build' => self::getUid(),
                'screen' => 'true',
                'move' => 'true',
                'version' => '1',
                'title' => '',
                'url' => '',
                'file' => '',
                'ssl' => [],
            ],
            $this->Arr,
            ['icon' => \base64_encode(\file_get_contents($this->Arr['icon']))]
        );
        if (empty($data['title'])) {
            return ['code' => 403, 'msg' => '请填写APP标题', 'file' => ''];
        } else if (empty($data['url'])) {
            return ['code' => 404, 'msg' => '请填写APP网站地址', 'file' => ''];
        }
        return $data;
    }

    /**
     * 执行签名
     * @param $Arr
     * @return array
     */
    private function execSsl($Arr): array {
        \date_default_timezone_set('America/Los_Angeles');
        \exec("openssl x509 -enddate -noout -in " . $Arr['pem'], $time); //查询证书到期时间的格林时间
        $last_data = isset($time[0]) ? \date('Y-m-d', \strtotime(\ltrim(\strstr($time[0], '='), '='))) : (\time() - 60 * 60);
        if (\date('Y-m-d') < $last_data) {
            \exec("openssl smime -sign -in {$this->Arr['file']} -out {$Arr['file']} -signer {$Arr['pem']}  -inkey {$Arr['key']} -certfile {$Arr['pem']}  -outform der -nodetach", $K, $V);//执行签名
            if ($V == 0) {
                return ['code' => 200, 'msg' => 'success', 'file' => $Arr['file']];
            }
        } else {
            return ['code' => 412, 'msg' => '证书已过期(到期时间:' . $last_data . '),签名未成功', 'file' => ''];
        }
        return ['code' => 413, 'msg' => '证书错误,签名未成功', 'file' => ''];
    }

    /**
     * xml内容
     * @param $data
     * @return string
     */
    private static function xmlString($data): string {
        return '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
    <dict>
        <key>PayloadContent</key>
        <array>
            <dict>
                <key>FullScreen</key>
                <' . $data['screen'] . '/>
                <key>Icon</key>
                <data>
                   ' . $data['icon'] . '
                </data>
                <key>IsRemovable</key>
                <' . $data['move'] . '/>
                <key>Label</key>
                <string>' . $data['title'] . '</string>
                <key>PayloadDescription</key>
                <string>Adds a Web Clip.</string>
                <key>PayloadDisplayName</key>
                <string>Web Clip (' . $data['title'] . ')</string>
                <key>PayloadIdentifier</key>
                <string>com.apple.webClip.Packer.' . $data['build'] . '</string>
                <key>PayloadOrganization</key>
                <string>' . $data['title'] . '</string>
                <key>PayloadType</key>
                <string>com.apple.webClip.managed</string>
                <key>PayloadUUID</key>
                <string>' . $data['build'] . '</string>
                <key>PayloadVersion</key>
                <integer>' . $data['version'] . '</integer>
                <key>Precomposed</key>
                <true/>
                <key>URL</key>
                <string>' . $data['url'] . '</string>
            </dict>
        </array>
        <key>PayloadDescription</key>
        <string>请点击右上角的"安装",这将会把"' . $data['title'] . '"添加到您的主屏上</string>
        <key>PayloadDisplayName</key>
        <string>' . $data['title'] . '</string>
        <key>PayloadIdentifier</key>
        <string>com.apple.webClip.Packer.' . $data['build'] . '</string>
        <key>PayloadOrganization</key>
        <string>' . $data['title'] . '</string>
        <key>PayloadRemovalDisallowed</key>
        <false/>
        <key>PayloadType</key>
        <string>Configuration</string>
        <key>PayloadUUID</key>
        <string>' . $data['build'] . '</string>
        <key>PayloadVersion</key>
        <integer>' . $data['version'] . '</integer>
    </dict>
</plist>';
    }

    /**
     * 生成唯一标识
     * @return string
     */
    private static function getUid(): string {
        return \sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', \mt_rand(0, 65535), \mt_rand(0, 65535), \mt_rand(0, 65535), \mt_rand(16384, 20479), \mt_rand(32768, 49151), \mt_rand(0, 65535), \mt_rand(0, 65535), \mt_rand(0, 65535));
    }
}