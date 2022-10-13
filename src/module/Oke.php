<?php

namespace zhqing\module;

use zhqing\extend\Frame;
use zhqing\extend\Curl;


class Oke {
    public array $Config = [
        "apiKey" => "a83cf152-f130-45be-a026-3c5f1ae586aa",
        "apiSecret" => "60C4AF70FB24AC8651C6021A9F76CECC",
        "passWord" => "*&frvnf3499f923eIHBh43rikk",
        'ApiUrl' => 'https://www.okex.com',
    ];

    public function getQw(array $data = []): string|array {
        return $this->curl('/api/v5/broker/nd/subaccount-info', 'GET', array_merge([
            'subAcct' => '',
            'page' => '',
            'limit' => ''
        ], $data));
    }

    /**
     * 获取账户余额
     * @param array $data
     * @return string|array
     */
    public function getMoney(array $data = []): string|array {
        return $this->curl('/api/v5/account/balance', 'GET', $data);
    }

    /**
     * 获取账户余额
     * @param array $data
     * @return string|array
     */
    public function money(array $data = []): string|array {
        return $this->curl('/api/v5/asset/balances', 'GET', $data);
    }


    /**
     * 获取币种列表
     * @param string $data
     * @return string|array
     */
    public function getCurrencies(string $data = ''): string|array {
        return $this->curl('/api/v5/asset/currencies', 'GET', ['ccy' => $data]);
    }

    /**
     * 获取充值地址
     * @param string $data
     * @return string|array
     */
    public function getAddress(string $data = 'USDT'): string|array {
        return $this->curl('/api/v5/asset/deposit-address', 'GET', ['ccy' => $data]);
    }

    /**
     * 获取充值记录
     * @param array $data
     * @return string|array
     */
    public function moneyList(array $data = []): string|array {
        return $this->curl('/api/v5/asset/deposit-history', 'GET', array_merge([
            'ccy' => $data['ccy'] ?? 'USDT',
            'state' => '',
            'after' => '',
            'before' => '',
            'limit' => ''
        ], $data));
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $data
     * @return string|array
     */
    private function curl(string $url, string $method, array $data = []): string|array {
        $body = Curl::url($this->Config['ApiUrl'], $method, $data)->path($url)->form(empty($method == 'GET'))->json()->setHead($this->setApi($method, $url, $data))->exec(false)->body();
        return (!empty($arr = Frame::isJson($body)) ? $arr : $body);
    }

    /**
     * @param $method
     * @param $url
     * @param $data
     * @return array
     */
    private function setApi($method, $url, $data): array {
        //ini_set("date.timezone", "UTC");
        $time = date("Y-m-d\TH:i:s") . substr((string)microtime(), 1, 4) . 'Z';
        $method = strtoupper($method);
        $message = $time . $method . $url;
        if ($method === 'GET') {
            $message .= ($data ? '?' . http_build_query($data) : '');
        } else {
            $message .= $data ? json_encode($data, JSON_UNESCAPED_SLASHES) : '';
        }
        $sign = base64_encode(hash_hmac('sha256', $message, $this->Config['apiSecret'], true));
        $header['OK-ACCESS-KEY'] = $this->Config['apiKey'];
        $header['OK-ACCESS-SIGN'] = $sign;
        $header['OK-ACCESS-TIMESTAMP'] = $time;
        $header['OK-ACCESS-PASSPHRASE'] = $this->Config['passWord'];
        $header['OK-TEXT-TO-SIGN'] = $message;
        return $header;
    }

    /**
     * @param array $data
     */
    public function __construct(array $data = []) {
        $this->Config = array_merge($this->Config, $data);
    }
}