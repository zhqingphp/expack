<?php

namespace zhqing\workerman;

use Workerman\Connection\AsyncTcpConnection;
use Workerman\Connection\TcpConnection;
use zhqing\extend\Frame;
use Workerman\Worker;

/**
 * 长连接代理
 */
class Ws {
    /**
     * Sebsocket配置
     * @param $port //启动端口
     * @param $remote //远程域名
     * @param $domain //本地域名
     * @param array $header //本地域名
     * @param array $ssl //证书
     */
    public static function WebsocketConfig($port, $remote, $domain, array $header = [], array $ssl = []) {
        $array = $ssl ?? [
                'ssl' => array_merge([
                    'verify_peer' => false,
                    'allow_self_signed' => true,
                ], $ssl)
            ];
        $worker = new Worker('websocket://' . $domain . ':' . $port, $array);
        $worker->transport = 'ssl';
        $worker->name = "Proxy";
        $worker->count = 10;
        $worker->onConnect = function (TcpConnection $connection) use ($remote, $header) {
            $connection->onWebSocketConnect = function ($connection, $req) use ($remote, $header) {
                $client = new AsyncTcpConnection('ws://' . $remote . '/websocket');
                $reqIp = $connection->getRemoteIp();
                //$connection->origin = ($_SERVER['HTTP_ORIGIN'] ?? '');
                $client->headers = $header ?? [
                        //'http' => [
                        //'method' => 'GET',
                        //'header' => 'Proxy-Authorization: Basic ' . base64_encode(),
                        //'request_fulluri' => true,
                        //'protocol_version' => '1.1',
                        //'proxy' => '156.255.196.234:2000'
                        //],
                        //'request_fulluri' => true,
                        //'proxy' => '156.255.196.234:2000',
                        //'remoteAddress' => $reqIp,
                        //'RemoteAddr' => $reqIp,
                        //'X-Real-IP' => $reqIp,
                        //'X-Forwarded-For' => $reqIp,
                        //'CLIENT-IP' => $reqIp,
                        //'X-FORWARDED-FOR' => $reqIp,
                        //'protocol_version' => '1.1',
                        //'REMOTE_ADDR' => $reqIp,
                        //'PROXY_CLIENT_IP' => $reqIp,
                        //'WL_PROXY_CLIENT_IP' => $reqIp,
                        'CF_CONNECTING_IP' => $reqIp,
                        'CDN_SRC_IP' => $reqIp,
                        //'Origin' => Ab::$Origin,
                        'User-Agent' => Frame::getArr($_SERVER, 'HTTP_USER_AGENT')
                    ];
                $client->transport = 'ssl';
                self::sendService($connection, $client);
                self::sendUser($connection, $client);
                $client->connect();
            };
        };
    }

    /**
     * 访客的数据发送到远程
     * @param $connection
     * @param $client
     */
    protected static function sendService($connection, $client) {
        $connection->onMessage = function ($source, $data) use ($client) {
            $client->send($data);
        };
        $connection->onClose = function () use ($client) {
            $client->close();
        };
        $client->onBufferFull = function () use ($connection) {
            $connection->pauseRecv();
        };
        $client->onBufferDrain = function () use ($connection) {
            $connection->resumeRecv();
        };
    }

    /**
     * 远程返回的数据发送给访客
     * @param $connection
     * @param $client
     */
    protected static function sendUser($connection, $client) {
        $client->onMessage = function ($source, $data) use ($connection) {
            $connection->send($data);
        };
        $client->onClose = function () use ($connection) {
            $connection->close();
        };
        $client->onError = function ($connections, $err_code, $err_msg) use ($connection) {
            $connection->send(Frame::json([$err_code, $err_msg]));
        };
        $connection->onBufferFull = function () use ($client) {
            $client->pauseRecv();
        };
        $connection->onBufferDrain = function () use ($client) {
            $client->resumeRecv();
        };
    }
}