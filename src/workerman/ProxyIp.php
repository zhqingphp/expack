<?php

namespace zhqing\workerman;

use Workerman\Connection\AsyncTcpConnection;
use Workerman\Connection\AsyncUdpConnection;
use Workerman\Worker;
use Workerman\Timer;

ini_set("memory_limit", "512M");
define('STAGE_INIT', 0);
define('STAGE_AUTH', 1);
define('STAGE_ADDR', 2);
define('STAGE_UDP_ASSOC', 3);
define('STAGE_DNS', 4);
define('STAGE_CONNECTING', 5);
define('STAGE_STREAM', 6);
define('STAGE_DESTROYED', -1);

define('CMD_CONNECT', 1);
define('CMD_BIND', 2);
define('CMD_UDP_ASSOCIATE', 3);

define('ERR_GENERAL', 1);
define('ERR_NOT_ALLOW', 2);
define('ERR_NETWORK', 3);
define('ERR_HOST', 4);
define('ERR_REFUSE', 5);
define('ERR_TTL_EXPIRED', 6);
define('ERR_UNKNOW_COMMAND', 7);
define('ERR_UNKNOW_ADDR_TYPE', 8);
define('ERR_UNKNOW', 9);

define('ADDRTYPE_IPV4', 1);
define('ADDRTYPE_IPV6', 4);
define('ADDRTYPE_HOST', 3);

define('METHOD_NO_AUTH', 0);
define('METHOD_GSSAPI', 1);
define('METHOD_USER_PASS', 2);

/**
 * 开放代理ip
 */
class ProxyIp {

    protected static array $config = [];

    /**
     * 单个启动
     * @param string|int $port //外部端口
     * @param string|int $proxy //内部端口
     * @param string $user //帐号
     * @param string $pass //密码
     */
    public static function start(string|int $port, string|int $proxy, string $user, string $pass) {
        $self = new self($port, $user, $pass);
        $self->exec($proxy);
    }

    /**
     * 批量启动
     * @param int $tcp //外网起始端口
     * @param int $upd //内网起始端口
     * @param int $number //启动数量
     * @param $user //帐号
     * @param $pass //密码
     */
    public static function run(int $tcp, int $upd, int $number, string $user, string $pass) {
        for ($i = 1; $i <= $number; $i++) {
            self::start($tcp + $i, $upd + $i, $user, $pass);
        }
        Worker::runAll();
    }

    /**
     * 批量启动2
     * @param array $array //外网起始端口
     * @param $user //帐号
     * @param $pass //密码
     */
    public static function arr(array $array, string $user = '', string $pass = '') {
        foreach ($array as $v) {
            self::start($v['tcp'], $v['upd'], ($v['user'] ?? $user), ($v['pass'] ?? $pass));

        }
        Worker::runAll();
    }

    protected function exec($proxy) {
        if (count(self::$config['auth']) == 0) {
            self::$config['auth'] = [METHOD_NO_AUTH => true];
        }
        $worker = new Worker('tcp://0.0.0.0:' . self::$config['tcp_port']);
        $worker->onConnect = function ($connection) {
            $connection->stage = STAGE_INIT;
            $connection->auth_type = NULL;
        };
        $worker->onMessage = function ($connection, $buffer) {
            self::logger(LOG_DEBUG, "recv:" . bin2hex($buffer));
            switch ($connection->stage) {
                // 初始化环节
                case STAGE_INIT:
                    $request = [];
                    // 当前偏移量
                    $offset = 0;
                    // 检测buffer长度
                    if (strlen($buffer) < 2) {
                        self::logger(LOG_ERR, "init socks5 failed. buffer too short.");
                        $connection->send("\x05\xff");
                        $connection->stage = STAGE_DESTROYED;
                        $connection->close();
                        return;
                    }
                    // Socks5 版本
                    $request['ver'] = ord($buffer[$offset]);
                    $offset += 1;
                    // 认证方法数量
                    $request['method_count'] = ord($buffer[$offset]);
                    $offset += 1;
                    if (strlen($buffer) < 2 + $request['method_count']) {
                        self::logger(LOG_ERR, "init authentic failed. buffer too short.");
                        $connection->send("\x05\xff");
                        $connection->stage = STAGE_DESTROYED;
                        $connection->close();
                        return;
                    }
                    // 客户端支持的认证方法
                    $request['methods'] = [];
                    for ($i = 1; $i <= $request['method_count']; $i++) {
                        $request['methods'][] = ord($buffer[$offset]);
                        $offset++;
                    }
                    foreach (self::$config['auth'] as $k => $v) {
                        if (in_array($k, $request['methods'])) {
                            self::logger(LOG_INFO, "auth client via method $k");
                            self::logger(LOG_DEBUG, "send:" . bin2hex("\x05" . chr($k)));
                            $connection->send("\x05" . chr($k));
                            if ($k == 0) {
                                $connection->stage = STAGE_ADDR;
                            } else {
                                $connection->stage = STAGE_AUTH;
                            }
                            $connection->auth_type = $k;
                            return;
                        }
                    }
                    if ($connection->stage != STAGE_AUTH) {
                        self::logger(LOG_ERR, "client has no matched auth methods");
                        self::logger(LOG_DEBUG, "send:" . bin2hex("\x05\xff"));
                        $connection->send("\x05\xff");
                        $connection->stage = STAGE_DESTROYED;
                        $connection->close();
                    }
                    return;
                // 认证环节
                case STAGE_AUTH:
                    $request = [];
                    // 当前偏移量
                    $offset = 0;
                    if (strlen($buffer) < 5) {
                        self::logger(LOG_ERR, "auth failed. buffer too short.");
                        $connection->send("\x01\x01");
                        $connection->stage = STAGE_DESTROYED;
                        $connection->close();
                        return;
                    }
                    // var_dump($connection->auth_type);
                    switch ($connection->auth_type) {
                        case METHOD_USER_PASS:
                            //  子协议 协商 版本
                            $request['sub_ver'] = ord($buffer[$offset]);
                            $offset += 1;
                            // 用户名
                            $request['user_len'] = ord($buffer[$offset]);
                            $offset += 1;
                            if (strlen($buffer) < 2 + $request['user_len'] + 2) {
                                self::logger(LOG_ERR, "auth username failed. buffer too short.");
                                $connection->send("\x01\x01");
                                $connection->stage = STAGE_DESTROYED;
                                $connection->close();
                                return;
                            }
                            $request['user'] = substr($buffer, $offset, $request['user_len']);
                            $offset += $request['user_len'];
                            // 密码
                            $request['pass_len'] = ord($buffer[$offset]);
                            $offset += 1;
                            if (strlen($buffer) < 2 + $request['user_len'] + 1 + $request['pass_len']) {
                                self::logger(LOG_ERR, "auth password failed. buffer too short.");
                                $connection->send("\x01\x01");
                                $connection->stage = STAGE_DESTROYED;
                                $connection->close();
                                return;
                            }
                            $request['pass'] = substr($buffer, $offset, $request['pass_len']);
                            $offset += $request['pass_len'];
                            if ((self::$config["auth"][METHOD_USER_PASS])($request)) {
                                self::logger(LOG_INFO, "auth ok");
                                $connection->send("\x01\x00");
                                $connection->stage = STAGE_ADDR;
                            } else {
                                self::logger(LOG_INFO, "auth failed");
                                $connection->send("\x01\x01");
                                $connection->stage = STAGE_DESTROYED;
                                $connection->close();
                            }
                            break;
                        default:
                            self::logger(LOG_ERR, "unsupport auth type");
                            $connection->send("\x01\x01");
                            $connection->stage = STAGE_DESTROYED;
                            $connection->close();
                            break;
                    }
                    return;
                case STAGE_ADDR:
                    $request = [];
                    // 当前偏移量
                    $offset = 0;
                    if (strlen($buffer) < 4) {
                        self::logger(LOG_ERR, "connect init failed. buffer too short.");
                        $connection->stage = STAGE_DESTROYED;
                        $response = [];
                        $response['ver'] = 5;
                        $response['rep'] = ERR_GENERAL;
                        $response['rsv'] = 0;
                        $response['addr_type'] = ADDRTYPE_IPV4;
                        $response['bind_addr'] = '0.0.0.0';
                        $response['bind_port'] = 0;
                        $connection->close(self::packResponse($response));
                        return;
                    }
                    // Socks 版本
                    $request['ver'] = ord($buffer[$offset]);
                    $offset += 1;
                    // 命令
                    $request['command'] = ord($buffer[$offset]);
                    $offset += 1;
                    // RSV
                    $request['rsv'] = ord($buffer[$offset]);
                    $offset += 1;
                    // AddressType
                    $request['addr_type'] = ord($buffer[$offset]);
                    $offset += 1;
                    // DestAddr
                    switch ($request['addr_type']) {
                        case ADDRTYPE_IPV4:
                            if (strlen($buffer) < 4 + 4) {
                                self::logger(LOG_ERR, "connect init failed.[ADDRTYPE_IPV4] buffer too short.");
                                $connection->stage = STAGE_DESTROYED;
                                $response = [];
                                $response['ver'] = 5;
                                $response['rep'] = ERR_GENERAL;
                                $response['rsv'] = 0;
                                $response['addr_type'] = ADDRTYPE_IPV4;
                                $response['bind_addr'] = '0.0.0.0';
                                $response['bind_port'] = 0;

                                $connection->close(self::packResponse($response));
                                return;
                            }
                            $tmp = substr($buffer, $offset, 4);
                            $ip = 0;
                            for ($i = 0; $i < 4; $i++) {
                                // var_dump(ord($tmp[$i]));
                                $ip += ord($tmp[$i]) * pow(256, 3 - $i);
                            }
                            $request['dest_addr'] = long2ip($ip);
                            $offset += 4;
                            break;
                        case ADDRTYPE_HOST:
                            $request['host_len'] = ord($buffer[$offset]);
                            $offset += 1;
                            if (strlen($buffer) < 4 + 1 + $request['host_len']) {
                                self::logger(LOG_ERR, "connect init failed.[ADDRTYPE_HOST] buffer too short.");
                                $connection->stage = STAGE_DESTROYED;
                                $response = [];
                                $response['ver'] = 5;
                                $response['rep'] = ERR_GENERAL;
                                $response['rsv'] = 0;
                                $response['addr_type'] = ADDRTYPE_IPV4;
                                $response['bind_addr'] = '0.0.0.0';
                                $response['bind_port'] = 0;
                                $connection->close(self::packResponse($response));
                                return;
                            }
                            $request['dest_addr'] = substr($buffer, $offset, $request['host_len']);
                            $offset += $request['host_len'];
                            break;

                        case ADDRTYPE_IPV6:
                        default:
                            self::logger(LOG_ERR, "unsupport ipv6. [ADDRTYPE_IPV6].");
                            $connection->stage = STAGE_DESTROYED;
                            $response = [];
                            $response['ver'] = 5;
                            $response['rep'] = ERR_UNKNOW_ADDR_TYPE;
                            $response['rsv'] = 0;
                            $response['addr_type'] = ADDRTYPE_IPV4;
                            $response['bind_addr'] = '0.0.0.0';
                            $response['bind_port'] = 0;
                            $connection->close(self::packResponse($response));
                            return;
                            break;
                    }
                    // DestPort
                    if (strlen($buffer) < $offset + 2) {
                        self::logger(LOG_ERR, "connect init failed.[port] buffer too short.");
                        $connection->stage = STAGE_DESTROYED;
                        $response = [];
                        $response['ver'] = 5;
                        $response['rep'] = ERR_GENERAL;
                        $response['rsv'] = 0;
                        $response['addr_type'] = ADDRTYPE_IPV4;
                        $response['bind_addr'] = '0.0.0.0';
                        $response['bind_port'] = 0;
                        $connection->close(self::packResponse($response));
                        return;
                    }
                    $portData = unpack("n", substr($buffer, $offset, 2));
                    $request['dest_port'] = $portData[1];
                    $offset += 2;
                    // var_dump($request);
                    switch ($request['command']) {
                        case CMD_CONNECT:
                            self::logger(LOG_DEBUG, 'tcp://' . $request['dest_addr'] . ':' . $request['dest_port']);
                            if ($request['addr_type'] == ADDRTYPE_HOST) {
                                if (!filter_var($request['dest_addr'], FILTER_VALIDATE_IP)) {
                                    self::logger(LOG_DEBUG, 'resolve DNS ' . $request['dest_addr']);
                                    $connection->stage = STAGE_DNS;
                                    $addr = dns_get_record($request['dest_addr'], DNS_A);
                                    $addr = $addr ? array_pop($addr) : null;
                                    self::logger(LOG_DEBUG, 'DNS resolved ' . $request['dest_addr'] . ' => ' . $addr['ip']);
                                } else {
                                    $addr['ip'] = $request['dest_addr'];
                                }
                            } else {
                                $addr['ip'] = $request['dest_addr'];
                            }
                            if ($addr) {
                                $connection->stage = STAGE_CONNECTING;
                                $remote_connection = new AsyncTcpConnection('tcp://' . $addr['ip'] . ':' . $request['dest_port']);
                                $remote_connection->onConnect = function ($remote_connection) use ($connection, $request) {
                                    $connection->state = STAGE_STREAM;
                                    $response = [];
                                    $response['ver'] = 5;
                                    $response['rep'] = 0;
                                    $response['rsv'] = 0;
                                    $response['addr_type'] = $request['addr_type'];
                                    $response['bind_addr'] = '0.0.0.0';
                                    $response['bind_port'] = 18512;
                                    $connection->send(self::packResponse($response));
                                    $connection->pipe($remote_connection);
                                    $remote_connection->pipe($connection);
                                    self::logger(LOG_DEBUG, 'tcp://' . $request['dest_addr'] . ':' . $request['dest_port'] . ' [OK]');
                                };
                                $remote_connection->connect();
                            } else {
                                self::logger(LOG_DEBUG, 'DNS resolve failed.');
                                $connection->stage = STAGE_DESTROYED;
                                $response = [];
                                $response['ver'] = 5;
                                $response['rep'] = ERR_HOST;
                                $response['rsv'] = 0;
                                $response['addr_type'] = ADDRTYPE_IPV4;
                                $response['bind_addr'] = '0.0.0.0';
                                $response['bind_port'] = 0;
                                $connection->close(self::packResponse($response));
                            }
                            break;
                        case CMD_UDP_ASSOCIATE:
                            $connection->stage = STAGE_UDP_ASSOC;
                            var_dump("CMD_UDP_ASSOCIATE " . self::$config['udp_port']);
                            if (self::$config['udp_port'] == 0) {
                                $connection->udpWorker = new Worker('udp://0.0.0.0:0');
                                $connection->udpWorker->incId = 0;
                                $connection->udpWorker->onMessage = function ($udp_connection, $data) use ($connection) {
                                    self::udpWorkerOnMessage($udp_connection, $data, $connection->udpWorker);
                                };
                                $connection->udpWorker->listen();
                                $listenInfo = stream_socket_get_name($connection->udpWorker->getMainSocket(), false);
                                list($bind_addr, $bind_port) = explode(":", $listenInfo);
                            } else {
                                $bind_port = self::$config['udp_port'];
                            }
                            $bind_addr = self::$config['wanIP'];
                            $response['ver'] = 5;
                            $response['rep'] = 0;
                            $response['rsv'] = 0;
                            $response['addr_type'] = ADDRTYPE_IPV4;
                            $response['bind_addr'] = $bind_addr;
                            $response['bind_port'] = $bind_port;
                            self::logger(LOG_DEBUG, 'send:' . bin2hex(self::packResponse($response)));
                            $connection->send(self::packResponse($response));
                            break;
                        default:
                            self::logger(LOG_ERR, "connect init failed. unknow command.");
                            $connection->stage = STAGE_DESTROYED;
                            $response = [];
                            $response['ver'] = 5;
                            $response['rep'] = ERR_UNKNOW_COMMAND;
                            $response['rsv'] = 0;
                            $response['addr_type'] = ADDRTYPE_IPV4;
                            $response['bind_addr'] = '0.0.0.0';
                            $response['bind_port'] = 0;
                            $connection->close(self::packResponse($response));
                            return;
                            break;
                    }
            }
        };
        $worker->onClose = function ($connection) {
            self::logger(LOG_INFO, "client closed.");
        };

        $udpWorker = new Worker('udp://0.0.0.0:' . $proxy);
        $udpWorker->incId = 0;
        $udpWorker->onWorkerStart = function ($worker) {
            $worker->udpConnections = [];
            Timer::add(1, function () use ($worker) {
                foreach ($worker->udpConnections as $id => $remote_connection) {
                    if ($remote_connection->deadTime < time()) {
                        $remote_connection->close();
                        $remote_connection->udp_connection->close();
                        unset($worker->udpConnections[$id]);
                    }
                }
            });
        };

        $udpWorker->onMessage = 'self::udpWorkerOnMessage';
    }

    protected static function packResponse($response) {
        $data = "";
        $data .= chr($response['ver']);
        $data .= chr($response['rep']);
        $data .= chr($response['rsv']);
        $data .= chr($response['addr_type']);
        switch ($response['addr_type']) {
            case ADDRTYPE_IPV4:
                $tmp = explode('.', $response['bind_addr']);
                foreach ($tmp as $block) {
                    $data .= chr($block);
                }
                break;
            case ADDRTYPE_HOST:
                $host_len = strlen($response['bind_addr']);
                $data .= chr($host_len);
                $data .= $response['bind_addr'];
                break;
        }
        $data .= pack("n", $response['bind_port']);
        return $data;
    }

    protected static function udpWorkerOnMessage($udp_connection, $data, &$worker) {
        self::logger(LOG_DEBUG, 'send:' . bin2hex($data));
        $request = [];
        $offset = 0;
        $request['rsv'] = substr($data, $offset, 2);
        $offset += 2;
        $request['frag'] = ord($data[$offset]);
        $offset += 1;
        $request['addr_type'] = ord($data[$offset]);
        $offset += 1;
        switch ($request['addr_type']) {
            case ADDRTYPE_IPV4:
                $tmp = substr($data, $offset, 4);
                $ip = 0;
                for ($i = 0; $i < 4; $i++) {
                    $ip += ord($tmp[$i]) * pow(256, 3 - $i);
                }
                $request['dest_addr'] = long2ip($ip);
                $offset += 4;
                break;
            case ADDRTYPE_HOST:
                $request['host_len'] = ord($data[$offset]);
                $offset += 1;
                $request['dest_addr'] = substr($data, $offset, $request['host_len']);
                $offset += $request['host_len'];
                break;
            case ADDRTYPE_IPV6:
                if (strlen($data) < 22) {
                    echo "buffer too short\n";
                    $error = true;
                    break;
                }
                echo "todo ipv6\n";
                $error = true;
            default:
                echo "unsupported addrtype {$request['addr_type']}\n";
                $error = true;
        }
        $portData = unpack("n", substr($data, $offset, 2));
        $request['dest_port'] = $portData[1];
        $offset += 2;
        // var_dump($request['dest_addr']);
        if ($request['addr_type'] == ADDRTYPE_HOST) {
            self::logger(LOG_DEBUG, '解析DNS');
            $addr = dns_get_record($request['dest_addr'], DNS_A);
            $addr = $addr ? array_pop($addr) : null;
            self::logger(LOG_DEBUG, 'DNS 解析完成' . $addr['ip']);
        } else {
            $addr['ip'] = $request['dest_addr'];
        }
        // var_dump($request);
        // var_dump($udp_connection);
        $remote_connection = new AsyncUdpConnection('udp://' . $addr['ip'] . ':' . $request['dest_port']);
        $remote_connection->id = $worker->incId++;
        $remote_connection->udp_connection = $udp_connection;
        $remote_connection->onConnect = function ($remote_connection) use ($data, $offset) {
            $remote_connection->send(substr($data, $offset));
        };
        $remote_connection->onMessage = function ($remote_connection, $recv) use ($data, $offset, $udp_connection, $worker) {
            $udp_connection->close(substr($data, 0, $offset) . $recv);
            $remote_connection->close();
            unset($worker->udpConnections[$remote_connection->id]);
        };
        $remote_connection->deadTime = time() + 3;
        $remote_connection->connect();
        $worker->udpConnections[$remote_connection->id] = $remote_connection;
    }

    protected static function logger($level, $str) {
        if (self::$config['log_level'] >= $level) {
            echo $str . "\n";
        }
    }

    public function __construct($port, $user, $pass) {
        self::$config = [
            "auth" => [
                METHOD_NO_AUTH => true,
                METHOD_USER_PASS => function ($request) use ($user, $pass) {
                    return $request['user'] == $user && $request['pass'] == $pass;
                }
            ],
            "log_level" => LOG_DEBUG,
            "tcp_port" => $port,
            "udp_port" => 0,
            "wanIP" => '127.0.0.1',
        ];
    }
}
