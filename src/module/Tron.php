<?php


namespace zhqing\module;


use zhqing\extend\Curl;
use zhqing\extend\Frame;

class Tron {
    public array $Config = [
        "apiKey" => "bb57c264-bd81-4778-bb30-afd5c071946a",
        "ApiUrl" => "https://api.trongrid.io/",
    ];

    public function demo(array $data = []): string|array {
        return $this->curl('/walletsolidity/getaccount', 'GET', array_merge([
            "to_address" => "THeqauxHqP5gciaoVrMpDn9GGQ14r4LKtX",
            "owner_address" => "41D1E7A6BC354106CB410E65FF8B181C600FF14292",
            "amount" => 1000
        ], $data));
    }

    private function curl(string $url, string $method, array $data = []): string|array {
        $body = Curl::url($this->Config['ApiUrl'], $method, $data)->path($url)->form(empty($method == 'GET'))->json()->setHead($this->setApi($data))->exec(false)->body();
        return (!empty($arr = Frame::isJson($body)) ? $arr : $body);
    }

    /**
     * @param array $data
     */
    public function __construct(array $data = []) {
        $this->Config = array_merge($this->Config, $data);
    }

    /**
     * @param $data
     * @return array
     */
    public function setApi($data): array {
        $header['TRON-PRO-API-KEY'] = $this->Config['apiKey'];
        return $header;
    }
}