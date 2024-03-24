<?php

namespace zhqing\workerman;

use Workerman\Connection\AsyncTcpConnection;
use Workerman\Connection\AsyncUdpConnection;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Worker;
use Workerman\Timer;

ini_set("memory_limit", "1024M");
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

class Proxy {

    protected $config = [];
    protected static $ipFile;

    /**
     * Proxy::set(7550, 29365, __DIR__ . '/ip.txt')->http(7999, '6bf07b0c3685894420cdc1a34efebd8e')->start();
     * @param $port //外部端口
     * @param $proxy //内部端口
     * @param $file //保存文件
     * @param $user //帐号
     * @param $pass //密码
     */
    public static function set($port, $proxy, $file, $user = '', $pass = '') {
        self::$ipFile = !empty($file) ? $file : __DIR__ . '/ip.cache';
        if (empty(is_file(self::$ipFile))) {
            @file_put_contents(self::$ipFile, "#白名单列表,IP段使用*号或者[0-255],一行一个记录\r\n");
        }
        $self = new self($port, $user, $pass);
        $self->exec($proxy);
        return $self;
    }

    /**
     * @param $proxy //启动端口
     * @param $path //访问路径+参数
     */
    public function http($proxy, $path) {
        $worker = new Worker('http://0.0.0.0:' . $proxy);
        $worker->count = 10;
        $worker->onMessage = function (TcpConnection $con, Request $req) use ($path) {
            $path = ('/' . ltrim(trim($path), '/'));
            if (substr(trim($req->uri()), 0, strlen($path)) == $path) {
                if (!empty($ip = $con->getRemoteIp())) {
                    $iv = 0;
                    self::createFilePath(self::$ipFile);
                    $body = @file_get_contents(self::$ipFile) ?: '';
                    $body = str_replace("\r\n", PHP_EOL, $body);
                    $array = explode(PHP_EOL, trim($body, PHP_EOL));
                    $anyIp = trim($ip);
                    $bool = false;
                    foreach ($array as $v) {
                        if ($anyIp == $v) {
                            $bool = true;
                            break;
                        }
                    }
                    if (empty($bool)) {
                        ++$iv;
                        $array[] = $anyIp;
                    }
                    if (!empty($ipaddress = $req->get('ip')) && empty(in_array($ipaddress, ['*', '*.*.*.*']))) {
                        $bool = false;
                        foreach ($array as $v) {
                            if ($ipaddress == $v) {
                                $bool = true;
                                break;
                            }
                        }
                        if (empty($bool)) {
                            ++$iv;
                            $array[] = $ipaddress;
                        }
                    }
                    $put = @file_put_contents(self::$ipFile, trim(join("\r\n", $array), "\r\n"));
                    if ($put > 0) {
                        $con->send('success(' . $iv . ')');
                        return;
                    }
                }
            }
            $con->send('error');
        };
        return $this;
    }

    public function start() {
        Worker::runAll();
    }

    public function __construct($port, $user, $pass) {
        $this->config = [
            "auth" => [
                METHOD_NO_AUTH => true,
                METHOD_USER_PASS => function ($request) use ($user, $pass) {
                    if (!empty($user) && !empty($pass)) {
                        return $request['user'] == $user && $request['pass'] == $pass;
                    }
                    return true;
                }
            ],
            "tcp_port" => $port,
            "udp_port" => 0,
            "wanIP" => '0.0.0.0',
        ];
    }

    protected function exec($proxy) {
        if (count($this->config['auth']) == 0) {
            $this->config['auth'] = [METHOD_NO_AUTH => true];
        }
        $worker = new Worker('tcp://0.0.0.0:' . $this->config['tcp_port']);
        $worker->count = 100;
        $worker->onConnect = function ($connection) {
            $connection->stage = STAGE_INIT;
            $connection->auth_type = NULL;
        };
        $worker->onMessage = function ($connection, $buffer) {
            if (empty(self::getAnyIp($connection->getRemoteIp()))) {
                $connection->close();
                return;
            }
            self::logger(LOG_DEBUG, "recv:" . bin2hex($buffer));
            switch ($connection->stage) {
                case STAGE_INIT:
                    $request = [];
                    $offset = 0;
                    if (strlen($buffer) < 2) {
                        self::logger(LOG_ERR, "init socks5 failed. buffer too short.");
                        $connection->send("\x05\xff");
                        $connection->stage = STAGE_DESTROYED;
                        $connection->close();
                        return;
                    }
                    $request['ver'] = ord($buffer[$offset]);
                    $offset += 1;
                    $request['method_count'] = ord($buffer[$offset]);
                    $offset += 1;
                    if (strlen($buffer) < 2 + $request['method_count']) {
                        self::logger(LOG_ERR, "init authentic failed. buffer too short.");
                        $connection->send("\x05\xff");
                        $connection->stage = STAGE_DESTROYED;
                        $connection->close();
                        return;
                    }
                    $request['methods'] = [];
                    for ($i = 1; $i <= $request['method_count']; $i++) {
                        $request['methods'][] = ord($buffer[$offset]);
                        $offset++;
                    }
                    foreach ($this->config['auth'] as $k => $v) {
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
                case STAGE_AUTH:
                    $request = [];
                    $offset = 0;
                    if (strlen($buffer) < 5) {
                        self::logger(LOG_ERR, "auth failed. buffer too short.");
                        $connection->send("\x01\x01");
                        $connection->stage = STAGE_DESTROYED;
                        $connection->close();
                        return;
                    }
                    switch ($connection->auth_type) {
                        case METHOD_USER_PASS:
                            $request['sub_ver'] = ord($buffer[$offset]);
                            $offset += 1;
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
                            if (($this->config["auth"][METHOD_USER_PASS])($request)) {
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
                    $request['ver'] = ord($buffer[$offset]);
                    $offset += 1;
                    $request['command'] = ord($buffer[$offset]);
                    $offset += 1;
                    $request['rsv'] = ord($buffer[$offset]);
                    $offset += 1;
                    $request['addr_type'] = ord($buffer[$offset]);
                    $offset += 1;
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
                    }
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
                            var_dump("CMD_UDP_ASSOCIATE " . $this->config['udp_port']);
                            if ($this->config['udp_port'] == 0) {
                                $connection->udpWorker = new Worker('udp://0.0.0.0:0');
                                $connection->udpWorker->incId = 0;
                                $connection->udpWorker->onMessage = function ($udp_connection, $data) use ($connection) {
                                    self::udpWorkerOnMessage($udp_connection, $data, $connection->udpWorker);
                                };
                                $connection->udpWorker->listen();
                                $listenInfo = stream_socket_get_name($connection->udpWorker->getMainSocket(), false);
                                list($bind_addr, $bind_port) = explode(":", $listenInfo);
                            } else {
                                $bind_port = $this->config['udp_port'];
                            }
                            $bind_addr = $this->config['wanIP'];
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
                    }
            }
        };
        $worker->onClose = function ($connection) {
            self::logger(LOG_INFO, "client closed.");
        };
        $udpWorker = new Worker('udp://0.0.0.0:' . $proxy);
        $udpWorker->count = 100;
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
        if ($request['addr_type'] == ADDRTYPE_HOST) {
            self::logger(LOG_DEBUG, '解析DNS');
            $addr = dns_get_record($request['dest_addr'], DNS_A);
            $addr = $addr ? array_pop($addr) : null;
            self::logger(LOG_DEBUG, 'DNS 解析完成' . $addr['ip']);
        } else {
            $addr['ip'] = $request['dest_addr'];
        }
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
        if (LOG_DEBUG >= $level) {
            echo "";
        }
    }

    protected static function getAnyIp($ip) {
        $body = @file_get_contents(self::$ipFile) ?: '';
        if (!empty($body)) {
            $body = str_replace("\r\n", PHP_EOL, $body);
            $array = explode(PHP_EOL, trim($body, PHP_EOL));
            return self::isIp($ip, $array);
        }
        return false;
    }

    protected static function isIp($ip, $array) {
        if (!empty($array) && filter_var($ip, FILTER_VALIDATE_IP) !== false) {
            foreach ($array as $v) {
                if (!empty($v) && ($ip == $v || in_array($v, ['*', '*.*.*.*']))) {
                    return true;
                }
            }
            $arrIp = explode('.', $ip);
            foreach ($array as $v) {
                if (str_contains($v, ".")) {
                    $ifIp = $arrIp;
                    $arr = explode('.', $v);
                    if (str_contains($v, "*") || (str_contains($v, "[") && str_contains($v, "]"))) {
                        foreach ($arr as $key => $val) {
                            if (isset($ifIp[$key])) {
                                if ($val == '*') {
                                    $ifIp[$key] = $val;
                                } else if (str_starts_with($val, '[') && str_ends_with($val, ']')) {
                                    $ipVal = $ifIp[$key];
                                    $arrA = explode('[', $val);
                                    $arrB = explode(']', ($arrA[1] ?? ''));
                                    $arrC = explode('-', ($arrB[0] ?? ''));
                                    $min = $arrC[0] ?? 0;
                                    $max = $arrC[1] ?? 255;
                                    if ($ipVal >= $min && $ipVal <= $max) {
                                        $ifIp[$key] = $val;
                                    }
                                }
                            }
                        }
                    }
                    if (join('.', $ifIp) == $v) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    protected static function createFilePath($filePath) {
        $path = dirname($filePath);
        if (empty(is_dir($path))) {
            mkdir($path, 0777, true);
        }
        return $filePath;
    }
}
