<?php

namespace zhqing\workerman;

use Workerman\Connection\AsyncTcpConnection;
use Workerman\Connection\TcpConnection;
use zhqing\extend\Frame;
use Workerman\Worker;

/**
 * 长连接代理
 */
class AsyProxy {
    protected array $data = [];

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function data(string $key, mixed $default = ''): mixed {
        return Frame::getStrArr($this->data, $key, $default);
    }

    /**
     * 设置Compiler版本
     * @param $data
     * @return $this
     */
    public static function url($data): static {
        $self = new self();
        $self->data['url'] = $data;
        $self->data['ssl'] = 'tcp';
        return $self;
    }

    /**
     * 是否ssl
     * @param $data
     * @return $this
     */
    public function ssl($data): static {
        $this->data['ssl'] = $data;
        return $this;
    }

    /**
     * 设置请求ip
     * @param string|bool $data //true默认,string自定
     * @param array $arr //ip头名称
     * @return $this
     */
    public function ip(string|bool $data, array $arr = ['CDN_SRC_IP', 'CF_CONNECTING_IP']): static {
        $this->data['ip'] = $data;
        $this->data['ipArr'] = $arr;
        return $this;
    }

    /**
     * 设置请求来路
     * @param string $data
     * @return $this
     */
    public function origin(string $data): static {
        $this->data['origin'] = $data;
        return $this;
    }

    /**
     * 设置着部信息
     * @param array $data
     * @return $this
     */
    public function header(array $data): static {
        $this->data['header'] = $data;
        return $this;
    }

    /**
     * 设置浏览器
     * @param array $data
     * @return $this
     */
    public function browser(array $data): static {
        $this->data['browser'] = $data;
        return $this;
    }

    public function worker() {
        $array = [
            'ssl' => [
                'verify_peer' => false,
                'allow_self_signed' => true,
                'local_cert' => $cer, //fullchain.pem,fullchain.cer
                'local_pk' => $key  //privkey.pem,domain.key
            ]
        ];
        $worker = new Worker('websocket://' . $domain . ':' . $port, $array);
        $worker->transport = 'ssl';
        $worker->name = "Proxy";
        $worker->count = 10;
        $worker->onConnect = function (TcpConnection $connection) {
            $this->start($connection);
        };
        Worker::runAll();
    }

    /**
     * @param TcpConnection $connection
     */
    public function start(TcpConnection $connection) {
        $connection->onWebSocketConnect = function (TcpConnection $connection) {
            $client = new AsyncTcpConnection($this->data('url'));
            $header['User-Agent'] = $this->data('browser', Frame::getArr($_SERVER, 'HTTP_USER_AGENT'));
            if (!empty($ip = $this->data('ip'))) {
                $arr = $this->data('ipArr');
                $ip = (is_string($ip) ? $ip : $connection->getRemoteIp());
                foreach ($arr as $v) {
                    $header[$v] = $ip;
                }
            }
            if (!empty($origin = $this->data('origin'))) {
                $header['Origin'] = $origin;
            }
            $client->transport = $this->data('ssl', 'tcp');
            $client->headers = array_merge($header, $this->data('header'));
            //访客的数据发送到远程
            $connection->onMessage = function ($source, $data) use ($client) {
                $client->send($data);
            };
            $connection->onClose = function () use ($client) {
                $client->close();
            };
            $connection->onError = function ($connection, $err_code, $err_msg) {
                echo $this->data('url') . "\r\n";
            };
            $connection->onBufferFull = function () use ($client) {
                $client->pauseRecv();
            };
            $connection->onBufferDrain = function () use ($client) {
                $client->resumeRecv();
            };
            //远程返回的数据发送给访客
            $client->onMessage = function ($source, $data) use ($connection) {
                $connection->send($data);
            };
            $client->onClose = function () use ($connection) {
                $connection->close();
            };
            $client->onBufferFull = function () use ($connection) {
                $connection->pauseRecv();
            };
            $client->onBufferDrain = function () use ($connection) {
                $connection->resumeRecv();
            };
            $client->connect();
        };
    }
}