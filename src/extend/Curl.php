<?php

namespace zhqing\extend;

class Curl {
    public array $setData = [];
    public array $curlSet = [];
    public array $curlInfo = [];
    public string $header = '';
    public string $body = '';
    public string $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.74 Safari/537.36 Edg/99.0.1150.46';

    /**
     * 通用请求
     * @param string|array $url //请求链接,string=单个请求，array为多个请求，请求错误自动请求下一个
     * @param string $mode //请求方式
     * @param string|array $data //请求数据
     * @return static
     */
    public static function url(string|array $url, string $mode = 'get', string|array $data = []): static {
        $self = new self();
        $self->setData['url'] = $url;
        $self->setData['mode'] = strtoupper($mode);
        $self->setData['data'] = $data;
        $self->setData['form'] = true;
        return $self;
    }

    /**
     * get请求
     * @param string|array $url //请求链接,string=单个请求，array为多个请求，请求错误自动请求下一个
     * @param string|array $data //请求数据,会自动转为get
     * @return static
     */
    public static function get(string|array $url, string|array $data = []): static {
        return self::url($url, 'get', $data);
    }

    /**
     * post请求
     * @param string|array $url //请求链接,string=单个请求，array为多个请求，请求错误自动请求下一个
     * @param string|array $data //请求数据
     * @return static
     */
    public static function post(string|array $url, string|array $data = []): static {
        return self::url($url, 'post', $data);
    }

    /**
     * put请求
     * @param string|array $url //请求链接,string=单个请求，array为多个请求，请求错误自动请求下一个
     * @param string|array $data //请求数据
     * @return static
     */
    public static function put(string|array $url, string|array $data = []): static {
        return self::url($url, 'put', $data);
    }

    /**
     * delete请求
     * @param string|array $url //请求链接,string=单个请求，array为多个请求，请求错误自动请求下一个
     * @param string|array $data //请求数据
     * @return static
     */
    public static function delete(string|array $url, string|array $data = []): static {
        return self::url($url, 'delete', $data);
    }

    /**
     * patch请求
     * @param string|array $url //请求链接,string=单个请求，array为多个请求，请求错误自动请求下一个
     * @param string|array $data //请求数据
     * @return static
     */
    public static function patch(string|array $url, string|array $data = []): static {
        return self::url($url, 'patch', $data);
    }

    /**
     * head请求
     * @param string|array $url //请求链接,string=单个请求，array为多个请求，请求错误自动请求下一个
     * @param string|array $data //请求数据
     * @return static
     */
    public static function head(string|array $url, string|array $data = []): static {
        return self::url($url, 'head', $data);
    }

    /**
     * connect请求
     * @param string|array $url //请求链接,string=单个请求，array为多个请求，请求错误自动请求下一个
     * @param string|array $data //请求数据
     * @return static
     */
    public static function connect(string|array $url, string|array $data = []): static {
        return self::url($url, 'connect', $data);
    }

    /**
     * options请求
     * @param string|array $url //请求链接,string=单个请求，array为多个请求，请求错误自动请求下一个
     * @param string|array $data //请求数据
     * @return static
     */
    public static function options(string|array $url, string|array $data = []): static {
        return self::url($url, 'options', $data);
    }

    /**
     * 设置请求路径(在请求连接后面加上路径)，简单是说后缀
     * @param string $data
     * @return $this
     */
    public function path(string $data): static {
        $this->setData['path'] = $data;
        return $this;
    }

    /**
     * 自定义Curl设置(不能设置请求头部信息)
     * @param mixed $name
     * @param mixed $data
     * @return $this
     */
    public function curl(mixed $name, mixed $data): static {
        $this->setData['curl'][$name] = $data;
        return $this;
    }

    /**
     * 设置cookie|可连贯
     * @param string|array $data
     * @param string $info
     * @return $this
     */
    public function cookie(string|array $data, string $info = ''): static {
        $cookie = $this->setData['cookie'] ?? [];
        if (is_array($data)) {
            $cookie = array_merge($cookie, $data);
        } else if (!empty($data)) {
            if (!empty($info)) {
                $cookie[$data] = $info;
            } else {
                $arr = explode(';', trim($data));
                foreach ($arr as $v) {
                    $k = explode('=', trim($v));
                    if (isset($k[0]) && isset($k[1])) {
                        $cookie[$k[0]] = $k[1];
                    }
                }
            }
        }
        $this->setData['cookie'] = $cookie;
        return $this;
    }

    /**
     * 设置头部信息|可连贯
     * @param array|string $key
     * @param string $data
     * @return $this
     */
    public function setHead(array|string $key, string $data = ''): static {
        $head = $this->setData['header'] ?? [];
        if (is_array($key)) {
            $header = array_merge($head, $key);
        } else {
            $header = array_merge($head, [$key => $data]);
        }
        $this->setData['header'] = $header;
        return $this;
    }

    /**
     * 设置伪装ip
     * @param string $data
     * @return $this
     */
    public function reqIp(string $data): static {
        $this->setData['reqIp'] = $data;
        return $this;
    }

    /**
     * 设置来路
     * @param string $data //为空自动设置
     * @return $this
     */
    public function referer(string $data = ''): static {
        $this->setData['referer'] = $data;
        return $this;
    }

    /**
     * 设置浏览器信息，默认$_SERVER['HTTP_USER_AGENT']
     * @param string $data
     * @return $this
     */
    public function userAgent(string $data): static {
        $this->setData['userAgent'] = $data;
        return $this;
    }

    /**
     * 设置解码名称
     * @param string $data
     * @return $this
     */
    public function encoding(string $data): static {
        $this->setData['encoding'] = $data;
        return $this;
    }

    /**
     * 是否提交数据
     * @param bool $data
     * @return $this
     */
    public function form(bool $data): static {
        $this->setData['form'] = $data;
        return $this;
    }

    /**
     * 设置转换编码
     * @param string $to
     * @param string $from
     * @return $this
     */
    public function coding(string $to, string $from): static {
        $this->setData['coding'] = ['to' => $to, 'from' => $from];
        return $this;
    }

    /**
     * 设置代理IP
     * ['ip'=>'代理ip','port'=>'代理ip端口','userPass'=>'帐号:密码','type'=>'代理模式:http|socks5或者自定','auth'=>'认证模式:basic|ntlm或者自定']
     * @param mixed $data
     * @param bool $type //是否启用代理ip
     * @return $this
     */
    public function proxy(mixed $data, bool $type = true): static {
        if (!empty($type)) {
            $this->setData['proxy'] = $data;
        }
        return $this;
    }

    /**
     * 连接时间,设置为0，则无限等待,默认5
     * @param int $data
     * @return $this
     */
    public function timeConnect(int $data): static {
        $this->setData['timeConnect'] = $data;
        return $this;
    }

    /**
     * 超时时间,设置为0，则无限等待,默认5
     * @param int $data
     * @return $this
     */
    public function timeOut(int $data): static {
        $this->setData['timeOut'] = $data;
        return $this;
    }

    /**
     * 是否检查证书,默认不检查
     * @param bool|int $data
     * @return $this
     */
    public function sslPeer(bool|int $data): static {
        $this->setData['sslPeer'] = $data;
        return $this;
    }

    /**
     * 是否检查证书公用名,默认不检查
     * @param bool|int $data
     * @return $this
     */
    public function sslHost(bool|int $data): static {
        $this->setData['sslHost'] = $data;
        return $this;
    }

    /**
     * 是否自动跳转,默认不跳转
     * @param bool $data
     * @return $this
     */
    public function follow(bool $data): static {
        $this->setData['follow'] = $data;
        return $this;
    }

    /**
     * 是否json提交
     * @param bool $data
     * @return $this
     */
    public function json(bool $data = true): static {
        $this->setData['json'] = $data;
        return $this;
    }

    /**
     * 是否模拟ajax
     * @param bool $data
     * @return $this
     */
    public function ajax(bool $data = true): static {
        $this->setData['ajax'] = $data;
        return $this;
    }

    /**
     * 设置完成后处理
     * @param bool $type //是否执行多个域名
     * @return $this
     */
    public function exec(bool $type = true): static {
        $curl = curl_init();
        $this->handleCurl()->handleReq(function ($url) use ($curl, $type) {
            $this->setData['reqUrl'] = $url;
            $this->curlSet[CURLOPT_URL] = $url;
            curl_setopt_array($curl, $this->curlSet);
            $content = curl_exec($curl);
            $this->curlInfo = curl_getinfo($curl);
            $this->header = trim(substr($content, 0, $this->curlInfo['header_size']), "\r\n\r\n");
            $this->body = substr($content, $this->curlInfo['header_size']);
            if (empty($type) || ($this->curlInfo['http_code'] ?? 0) == 200) {
                if (isset($this->setData['coding'])) {
                    $coding = $this->setData['coding'];
                    $this->body = mb_convert_encoding($this->body, $coding['to'], $coding['from']);
                }
                return true;
            } else {
                $this->body = $this->body ?: curl_error($curl);
                return false;
            }
        });
        curl_close($curl);
        return $this;
    }

    /**
     * 设置保存文件名(exec后执行)
     * @param string $fileName //保存文件名称
     * @param object|string $fun //执行方法(返回false保存)
     * @return false|string
     */
    public function saveFile(string $fileName, object|string $fun = ''): bool|string {
        if (is_callable($fun)) {
            $is = $fun($this);
        }
        if ($this->code() == 200 && empty($is)) {
            @file_put_contents($fileName, $this->body);
        } else {
            $this->body = self::arrJson([
                'setData' => $this->setData,
                'info' => $this->curlInfo,
                'header' => $this->header,
                'curlSet' => $this->curlSet,
                'body' => $this->body
            ]);
            @file_put_contents($fileName . '_error.json', $this->body);
            return false;
        }
        return $this->body;
    }

    /**
     * 调试信息
     * @param false $type
     * @return string|void
     */
    public function debug(bool $type = true) {
        $html = '<pre>' . print_r($this->info('total_time'), true) . '</pre>';
        $html .= "<textarea rows='30' cols='300'>" . $this->header . "\r\n\r\n" . $this->body . "</textarea>";
        $html .= '<pre>' . print_r($this->curlSet, true) . '</pre>';
        $html .= '<pre>' . print_r($this->setData, true) . '</pre>';
        $html .= '<pre>' . print_r(($this->curlInfo), true) . '</pre>';
        if ($type) {
            return $html;
        }
        echo $html;
    }

    /**
     * 接收内容(exec后执行)
     * @return string
     */
    public function body(): string {
        return $this->body;
    }

    /**
     * 接收JSON内容转为数组(exec后执行)
     * @return mixed  //返回空不是json
     */
    public function array(): mixed {
        return self::isJson($this->body());
    }

    /**
     * 获取头部信息(exec后执行)
     * @param mixed|null $key //名称 (只能获取第一个)
     * @param mixed|null $default //不存在时返回内容
     * @return mixed
     */
    public function header(mixed $key = null, mixed $default = null): mixed {
        $handle = function ($key, $default) {
            \preg_match_all("/" . $key . ":(.*?)\r\n/i", $this->header, $arr);
            return $arr[1] ?? $default;
        };
        return isset($key) ? $handle($key, $default) : $this->header;
    }

    /**
     * 获取header数组
     * @param array $arr //包含的名称(不分大小写)
     * @return array
     */
    public function getHeadArr(array $head = []): array {
        preg_match_all("/(.*?):(.*?)\r\n/i", $this->header . "\r\n", $arr);
        $array = [];
        $keyArr = $arr[1] ?? [];
        $head = $head ? array_map('strtolower', $head) : [];
        foreach ($keyArr as $k => $v) {
            if ((!empty($key = trim($v)) && !empty($val = trim($arr[2][$k] ?? ''))) && (empty($head) || !empty(in_array(strtolower($key), $head)))) {
                $array[$key] = $val;
            }
        }
        return $array;
    }

    /**
     * 获取cookie
     * @param array $cookie //可设置自带cookie
     * @return mixed
     */
    public function getCookie(array $cookie = []): mixed {
        preg_match_all("/set-cookie:(.*?)(; path|\r\n)/i", $this->header, $arr);
        $cookieArr = $arr[1] ?? [];
        foreach ($cookieArr as $v) {
            $k = explode('=', $v);
            if (!empty($a = trim(($k[0] ?? ''))) && !empty($s = trim($k[1] ?? ''))) {
                $cookie[$a] = $s;
            }
        }
        return $cookie;
    }

    /**
     * 获取状态(exec后执行)
     * @return mixed
     */
    public function code(): mixed {
        return $this->info('http_code', 0);
    }

    /**
     * 获取执行时间
     * @return mixed
     */
    public function time(): mixed {
        return $this->info('total_time');
    }

    /**
     * 获取mime类型
     * @param string $default
     * @return string
     */
    public function type(string $default = ''): string {
        $type = $this->info('content_type');
        if (!empty($type)) {
            $data = explode(';', $type);
            $default = ($data[0] ?? '');
        }
        return $default;
    }

    /**
     * @return string
     */
    public function dis(): string {
        $data = $this->header('Content-Disposition');
        return ($data[key($data)] ?? '');
    }

    /**
     * 相关信息(exec后执行)
     * @param null $key //名称
     * @param null $default //不存在时返回内容
     * @return mixed
     */
    public function info($key = null, $default = null): mixed {
        return isset($key) ? ($this->curlInfo[$key] ?? $default) : $this->curlInfo;
    }

    /**
     * 获取请求URL
     * @return string
     */
    public function reqUrl(): string {
        return $this->setData['reqUrl'];
    }

    /**
     * 当前请求代理ip
     * @return string
     */
    public function proxyIp(): string {
        return $this->setData['proxy'];
    }

    /**
     * 获取请求域名参数
     * @return array
     */
    public function getDomain(): array {
        return [$this->setData['domainId'] => $this->setData['domain']];
    }

    /**
     * @param $fun
     * @return void
     */
    private function handleReq($fun): void {
        if (is_string($this->setData['url'])) {
            $this->setData['url'] = [$this->setData['url']];
        }
        foreach ($this->setData['url'] as $k => $v) {
            $v = trim($v, '/') . '/';
            $this->setData['domain'] = $v;
            $this->setData['domainId'] = $k;
            $this->setData['path'] = $this->setData['path'] ?? '';
            $this->setData['path'] = ($this->setData['path'] ? ltrim($this->setData['path'], '/') : '');
            if ($this->setData['mode'] == 'GET') {
                $newUrl = $this->handleGetUrl($v . $this->setData['path'], $this->setData['data']);
            } else {
                $newUrl = $v . $this->setData['path'];
            }

            $this->setOriginHeader($newUrl);
            if (!empty($fun($newUrl))) {
                return;
            }
        }
    }

    /**
     * 设置来源和请求头
     * @param $url
     */
    private function setOriginHeader($url) {
        //设置来路(为空自动设置)
        if (isset($this->setData['referer'])) {
            $referer = $this->setData['referer'];
            if (empty($referer)) {
                $parse = parse_url($url);
                $referer = ($parse['scheme'] ?? 'http') . '://';
                $referer .= $parse['host'] ?? '';
                $referer .= isset($parse['port']) ? ':' . $parse['port'] : '';
            }
            $this->curlSet[CURLOPT_REFERER] = $referer;
            $this->setHead(['REFERER' => $referer, 'ORIGIN' => $referer]);
        }
        //设置头部信息
        if (!empty($header = $this->setData['header'] ?? [])) {
            $head = [];
            foreach ($header as $k => $v) {
                if (!empty($v) && is_string($v)) {
                    $head[] = $k . ': ' . $v;
                }
            }
            $this->curlSet[CURLOPT_HTTPHEADER] = $head;
        }
    }

    /**
     * 处理Curl参数
     * @return $this
     */
    private function handleCurl(): static {
        //设置请求方式
        $mode = $this->setData['mode'] ?? 'GET';
        $this->curlSet[CURLOPT_CUSTOMREQUEST] = $mode;
        if ($mode == 'POST') {
            $this->curlSet[CURLOPT_POST] = true;
        }
        //数据提交方式
        if (!empty($this->setData['form']) && !empty($this->setData['data'] ?? '')) {
            //json提交
            if (($this->setData['json'] ?? false)) {
                $json = self::arrJson($this->setData['data']);
                $data = !empty($json) ? $json : $this->setData['data'];
                $this->setData['data'] = $data;
                $this->setHead(['Content-Type' => 'application/json', 'Content-Length' => strlen($data)]);
            } else {
                //为数组时提交
                if (is_array($this->setData['data'])) {
                    $data = http_build_query($this->setData['data']);
                    $this->setData['data'] = $data;
                    $this->setHead(['Content-Length' => strlen($data)]);
                }
            }
            $this->curlSet[CURLOPT_POSTFIELDS] = $this->setData['data'];
        }
        //是否ajax提交
        if (!empty($this->setData['ajax'] ?? false)) {
            $this->setHead(['X-Requested-With' => 'XMLHttpRequest']);
        }
        //设置cookie
        if (!empty($arr = ($this->setData['cookie'] ?? []))) {
            $cookie = '';
            foreach ($arr as $k => $v) {
                if (!empty($v) && is_string($v)) {
                    $cookie .= $k . '=' . $v . ';';
                }
            }
            $this->curlSet[CURLOPT_COOKIE] = $cookie;
        }
        //设置解码名称
        if (!empty($this->setData['encoding'] ?? '')) {
            $this->curlSet[CURLOPT_ENCODING] = $this->setData['encoding'];
        }
        //连接时间,设置为0，则无限等待
        $this->curlSet[CURLOPT_CONNECTTIMEOUT] = $this->setData['timeConnect'] ?? 8;
        //超时时间,设置为0，则无限等待
        $this->curlSet[CURLOPT_TIMEOUT] = $this->setData['timeOut'] ?? 8;
        //否检查证书,默认不检查
        $this->curlSet[CURLOPT_SSL_VERIFYPEER] = $this->setData['sslPeer'] ?? false;
        //设置成 2，会检查公用名是否存在，并且是否与提供的主机名匹配。 0 为不检查名称。 在生产环境中，这个值应该是 2（默认值）
        $this->curlSet[CURLOPT_SSL_VERIFYHOST] = $this->setData['sslHost'] ?? false;
        //自动设置浏览器信息
        $this->curlSet[CURLOPT_USERAGENT] = ($this->setData['userAgent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? $this->userAgent));
        //自动跳转时设置开启头部
        $follow = $this->setData['follow'] ?? false;
        $this->curlSet[CURLOPT_FOLLOWLOCATION] = $follow;
        if (!empty($follow)) {
            $this->curlSet[CURLOPT_AUTOREFERER] = true;
        }
        //true 将curl_exec()获取的信息以字符串返回，而不是直接输出。
        $this->curlSet[CURLOPT_RETURNTRANSFER] = true;
        //true 时将不输出 BODY 部分。同时 Mehtod 变成了 HEAD。修改为 false 时不会变成 GET
        $this->curlSet[CURLOPT_NOBODY] = false;
        //是否返回头部信息
        $this->curlSet[CURLOPT_HEADER] = true;
        //设置代理ip
        if (isset($this->setData['proxy'])) {
            $proxy = $this->setData['proxy'];
            if (is_callable($proxy)) {
                $proxy = $proxy();
            }
            if (!empty($ip = ($proxy['ip'] ?? ''))) {
                if (empty(isset($proxy['port']))) {
                    $ipArr = explode(":", $ip);
                    $this->curlSet[CURLOPT_PROXY] = $ipArr[0] ?? $ip;
                    if (!empty($port = $ipArr[1])) {
                        $this->curlSet[CURLOPT_PROXYPORT] = $port;
                        $proxy['port'] = null;
                    }
                } else {
                    $this->curlSet[CURLOPT_PROXY] = $ip;
                }
            }
            if (!empty($port = ($proxy['port'] ?? ''))) {
                $this->curlSet[CURLOPT_PROXYPORT] = $port;
            }
            if (!empty($userPass = ($proxy['userPass'] ?? ''))) {
                $this->curlSet[CURLOPT_PROXYUSERPWD] = $userPass;
            }
            if (!empty($type = ($proxy['type'] ?? ''))) {
                $this->curlSet[CURLOPT_PROXYTYPE] = ($type == 'http' ? CURLPROXY_HTTP : ($type == 'socks5' ? CURLPROXY_SOCKS5 : $type));
            }
            if (!empty($auth = ($proxy['auth'] ?? ''))) {
                $this->curlSet[CURLOPT_PROXYAUTH] = ($auth == 'basic' ? CURLAUTH_BASIC : ($auth == 'ntlm' ? CURLAUTH_NTLM : $auth));
            }
        }
        if (!empty($ReqIp = ($this->setData['reqIp'] ?? ''))) {
            if ($ReqIp != '127.0.0.1') {
                $this->setHead([
                    'CLIENT-IP' => $ReqIp,
                    'X-FORWARDED-FOR' => $ReqIp,
                    'CDN_SRC_IP' => $ReqIp,
                    'CF_CONNECTING_IP' => $ReqIp
                ]);
            }
        }
        //自定义Curl设置(不能设置请求头部信息)
        if (!empty($curl = $this->setData['curl'] ?? '')) {
            foreach ($curl as $k => $v) {
                $this->curlSet[$k] = $v;
            }
        }
        return $this;
    }

    /**
     * 处理GET请求链接
     * @param string $url //请求链接
     * @param mixed $arr //请求参数
     * @return string
     */
    private static function handleGetUrl(string $url, mixed $arr): string {
        if (!empty($arr)) {
            if (!empty(is_string($arr))) {
                \parse_str($arr, $array);
            } else {
                $array = $arr;
            }
            $parse = \parse_url($url);
            \parse_str(($parse['query'] ?? ''), $data);
            foreach ($array as $k => $v) {
                $data[$k] = $v;
            }
            $query = \http_build_query($data);
            $newUrl = ($parse['scheme'] ?? 'http') . '://';
            $newUrl .= $parse['host'] ?? '';
            $newUrl .= isset($parse['port']) ? ':' . $parse['port'] : '';
            $newUrl .= isset($parse['path']) ? '/' . ltrim($parse['path'], '/') : '';
            $newUrl .= !empty($query) ? '?' . $query : '';
            $newUrl .= (isset($parse['fragment']) ? '#' . $parse['fragment'] : '');
            return $newUrl;
        }
        return $url;
    }

    /**
     * 数组转json
     * @param $data
     * @return string|bool
     */
    private static function arrJson($data): string|bool {
        return json_encode($data, JSON_NUMERIC_CHECK + JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
    }

    /**
     * 判断字符串是否json,返回array
     * @param string $data
     * @param bool $type
     * @return mixed
     */
    private static function isJson(string $data, bool $type = true): mixed {
        $data = \json_decode($data, $type);
        return (($data && \is_object($data)) || (\is_array($data) && $data)) ? $data : [];
    }
}