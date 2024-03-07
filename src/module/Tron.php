<?php

namespace zhqing\module;

use Elliptic\EC;
use zhqing\extend\Curl;
use zhqing\extend\Frame;
use Comely\DataTypes\BcNumber;
use IEXBase\TronAPI\Tron as Trons;
use IEXBase\TronAPI\Support\Utils;
use IEXBase\TronAPI\TRC20Contract;
use BitWasp\Bitcoin\Crypto\Random\Random;
use IEXBase\TronAPI\Provider\HttpProvider;
use BitWasp\Bitcoin\Mnemonic\MnemonicFactory;
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39Mnemonic;
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;
use BitWasp\Bitcoin\Key\Factory\HierarchicalKeyFactory;
use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;

/**
 * composer require zhqingphp/bitwasp
 * 安装后使用
 */
class Tron {
    public Trons $tron;
    public TRC20Contract $trc;
    public int $TRON_DECIMALS = 6;
    public string $TRON_PRO_API_KEY;
    public string $TRON_API_HOST = "https://api.trongrid.io";
    public string $TRON_WEBSITE_ADDRESS = 'https://tronscan.org';
    public string $TRON_TRON_ADDRESS = 'https://apiasia.tronscan.io:5566';
    public string $TRON_CONTRACT_ADDRESS = "TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t";

    /**
     * 生成助记词
     * @param bool $type true=返回string,false=返回array
     * @return array|string
     */
    public function getMnemonic(bool $type = false): array|string {
        $random = new Random();
        $entropy = $random->bytes(Bip39Mnemonic::MIN_ENTROPY_BYTE_LEN);
        $bip39 = MnemonicFactory::bip39();
        $array = $bip39->entropyToWords($entropy);
        return ($type ? implode(' ', $array) : $array);
    }

    /**
     * 通过助记词生成私钥
     * @param array|string $mnemonic 助记词
     * @return string
     */
    public function mnemonicToPrivateKey(array|string $mnemonic): string {
        $seedGenerator = new Bip39SeedGenerator();
        $seed = $seedGenerator->getSeed((is_array($mnemonic) ? implode(' ', $mnemonic) : $mnemonic));
        $hdFactory = new HierarchicalKeyFactory();
        $master = $hdFactory->fromEntropy($seed);
        $hardened = $master->derivePath("44'/195'/0'/0/0");
        return $hardened->getPrivateKey()->getHex();
    }

    /**
     * 生成私钥
     * @return string
     */
    public function getPrivateKey(): string {
        $random = (new Random());
        $PrivateKey = (new PrivateKeyFactory());
        $PrivateKey = $PrivateKey->generateCompressed($random);
        return $PrivateKey->getHex();
    }

    /**
     * 将私钥转换为公钥
     * @param string $hex
     * @return string
     */
    public function privateKeyToPublicKey(string $hex): string {
        $PrivateKey = new PrivateKeyFactory();
        $PrivateKey = $PrivateKey->fromHexUncompressed($hex);
        $PrivateKey = $PrivateKey->getPublicKey();
        return $PrivateKey->getHex();
    }

    /**
     * 公钥转钱包钱包地址
     * @param string $publicKey
     * @return string
     */
    public function publicKeyToAddress(string $publicKey): string {
        return $this->tron()->getAddressHex($this->tron()->hexString2Utf8($publicKey));
    }

    /**
     * 钱包Hex地址转钱包地址
     * @param $address_hex
     * @return string
     */
    public function hexToAddress($address_hex): string {
        return $this->tron()->fromHex($address_hex);
    }

    /**
     * 钱包地址转Hex地址
     * @param $address
     * @return string
     */
    public function addressToHex($address): string {
        return $this->tron()->toHex($address);
    }

    /**
     * 生成钱包信息
     * @param bool $isMnemonic 是否包含助记词
     */
    public function getWallet(bool $isMnemonic = false): array {
        if (!empty($isMnemonic)) {
            $data['mnemonic'] = $this->getMnemonic(true);
            $data['private'] = $this->mnemonicToPrivateKey($data['mnemonic']);
        } else {
            $data['mnemonic'] = '';
            $data['private'] = $this->getPrivateKey();
        }
        $data['public'] = $this->privateKeyToPublicKey($data['private']);
        $data['hex'] = $this->publicKeyToAddress($data['public']);
        $data['address'] = $this->hexToAddress($data['hex']);
        $data['link'] = trim($this->TRON_WEBSITE_ADDRESS, '/') . '/#/address/' . $data['address'] . '/transfers';//钱包详细
        return $data;
    }

    /**
     * 通过助记词生成钱包信息
     * @param string|array $mnemonic
     * @return array
     */
    public function mnemonicToWallet(string|array $mnemonic): array {
        $data['mnemonic'] = (is_array($mnemonic) ? implode(' ', $mnemonic) : $mnemonic);
        $data['private'] = $this->mnemonicToPrivateKey($data['mnemonic']);
        $data['public'] = $this->privateKeyToPublicKey($data['private']);
        $data['hex'] = $this->publicKeyToAddress($data['public']);
        $data['address'] = $this->hexToAddress($data['hex']);
        $data['link'] = trim($this->TRON_WEBSITE_ADDRESS, '/') . '/#/address/' . $data['address'] . '/transfers';//钱包详细
        return $data;
    }

    /**
     * 通过私钥生成钱包信息
     * @param string $private
     * @return array
     */
    public function privateToWallet(string $private): array {
        $data['private'] = $private;
        $data['public'] = $this->privateKeyToPublicKey($data['private']);
        $data['hex'] = $this->publicKeyToAddress($data['public']);
        $data['address'] = $this->hexToAddress($data['hex']);
        $data['link'] = trim($this->TRON_WEBSITE_ADDRESS, '/') . '/#/address/' . $data['address'] . '/transfers';//钱包详细
        return $data;
    }

    /**
     * 验证钱包地址
     * @param string $address
     * @return bool true=正确
     */
    public function isAddress(string $address): bool {
        return $this->tron()->isAddress($address);
    }

    /**
     * 验证私钥
     * @param string $privateKey
     * @return bool true=正确
     */
    public function isPrivate(string $privateKey): bool {
        if (Utils::isHex($privateKey) === false) {
            return false;
        }
        $privateKey = Utils::stripZero($privateKey);
        if (strlen($privateKey) !== 64) {
            return false;
        }
        return (bool)(new EC('secp256k1'))->keyFromPrivate($privateKey, 'hex');
    }

    /**
     * 获取TRC20余额
     * @param string $address
     * @return string
     */
    public function getTrc20Balance(string $address): string {
        $hexAddress = $this->addressToHex($address);
        $body = $this->tron()->getManager()->request('wallet/triggersmartcontract', [
            'contract_address' => $this->tron()->address2HexString($this->TRON_CONTRACT_ADDRESS),
            'function_selector' => 'balanceOf(address)',
            'parameter' => $this->toAddressFormat($hexAddress),
            'owner_address' => $hexAddress,
        ]);
        if (isset($body['result']['code'])) {
            return $this->tron()->hexString2Utf8(Frame::getStrArr($body, 'result.message'));
        }
        return $this->toAmount(base_convert(Frame::getStrArr($body, 'constant_result.0'), 16, 10));
    }

    /**
     * TRC20自由转账
     * @param $privateKey
     * @param $address
     * @param $amount
     * @return array
     */
    public function trc20Transfer($privateKey, $address, $amount): array {
        $this->tron()->setPrivateKey($privateKey);
        $wallet = $this->privateToWallet($privateKey);
        $this->tron()->setAddress($wallet['address']);
        $data = $this->trc()->transfer($address, $amount);;
        if (!empty($message = Frame::getStrArr($data, 'message'))) {
            $data['error'] = $this->tron()->hexString2Utf8($message);
        }
        return $data;
    }

    /**
     * 获取TRC20余额2
     * @param string $address
     */
    public function getTrc20Balances(string $address): string {
        return $this->trc()->balanceOf($address);
    }

    /**
     * 获取TRX余额
     * @param string $address
     * @return string
     */
    public function getTrxBalance(string $address): string {
        return $this->toAmount($this->tron()->getBalance($address));
    }

    /**
     * Trx转帐
     * @param string $privateKey 转帐私钥
     * @param string $address 收款地址
     * @param int|float $amount 转帐数量
     * @param string $content 备注
     * @return array
     */
    public function trxTransfer(string $privateKey, string $address, int|float $amount, string $content = ''): array {
        $this->tron()->setPrivateKey($privateKey);
        $wallet = $this->privateToWallet($privateKey);
        $data = $this->tron()->sendTrx($address, $amount, $wallet['address'], $content);
        if (!empty($message = Frame::getStrArr($data, 'message'))) {
            $data['error'] = $this->tron()->hexString2Utf8($message);
        }
        return $data;
    }

    /**
     * 获取汇率
     * @param string $from
     * @param string $to
     * @return array
     */
    public function getExchange(string $from = 'usdt', string $to = 'cny'): array {
        $to = strtoupper($to);
        $from = strtoupper($from);
        $url = 'https://coinyep.com';
        $path = "api/v1/?from={$from}&to={$to}&lang=zh&format=json";
        $curl = Curl::url($url)->path($path)->timeOut(5)->timeConnect(5)->referer()->exec();
        $body = Frame::isJson($curl->body());
        $data['money'] = $body['price'];
        $data['body'] = $body;
        return $data;
    }

    /**
     * 查询账户的资源信息（带宽，能量）
     * @param string $address
     * @return array
     */
    public function getAccountResources(string $address): array {
        return $this->tron()->getAccountResources($address);
    }

    /**
     * 查看区块高度
     * @return array
     */
    public function blockNumber(): array {
        return $this->tron()->getCurrentBlock();
    }

    /**
     * 根据区块链查询信息
     * @param string|int $blockID
     * @return array
     */
    public function blockByNumber(string|int $blockID): array {
        return $this->tron()->getBlockByNumber($blockID);
    }

    /**
     * 10进制转换U币
     * @param $data
     * @param int $scale
     * @return string
     */
    public function toAmount($data, int $scale = 0): string {
        $scale = ($scale > 0 ? $scale : $this->TRON_DECIMALS);
        return (new BcNumber($data))->divide(pow(10, $scale), $scale)->value();
    }

    /**
     * 地址签名
     * \Web3\Formatters\IntegerFormatter::format('TRU4rjXihrkBtGZ8uRXQTwoWVLWEhdGEdt')
     * @param string $address
     * @return string
     */
    public function toAddressFormat(string $address): string {
        $address = strtolower($address);
        if (Utils::isZeroPrefixed($address)) {
            $address = Utils::stripZero($address);
        }
        return implode('', array_fill(0, 64 - strlen($address), 0)) . $address;
    }

    /**
     * 查询交易手续费
     * @param string $hash 交易哈希号
     * @return array
     */
    public function getTransactionInfo(string $hash): array {
        $array = $this->tron()->getTransactionInfo($hash);
        $arr['status'] = $array['receipt']['result'] ?? '';//状态
        $arr['trx'] = $this->toAmount(($array['receipt']['net_fee'] ?? 0));//消耗TRX费用
        $arr['energy'] = $array['receipt']['energy_usage_total'] ?? ($array['receipt']['energy_usage'] ?? 0);//消耗带宽
        $arr['amount'] = $this->toAmount(base_convert(substr(($array['log'][0]['data'] ?? 0), -64), 16, 10));//交易数量
        $arr['hash'] = $array['id'] ?? $hash;//交易哈希号
        $arr['link'] = trim($this->TRON_WEBSITE_ADDRESS, '/') . '/#/transaction/' . $arr['hash'];//交易详细
        $arr['body'] = $array;
        return $arr;
    }


    /**
     * 查询交易详情
     * @param string $hash 交易哈希号
     * @return array
     */
    public function TransactionShow(string $hash): array {
        $body = $this->tron()->getTransaction($hash);
        $value = Frame::getStrArr($body, 'raw_data.contract.0.parameter.value');
        if (strlen(($value['data'] ?? '')) > 64) {
            $data = substr(Frame::getStrArr($value, 'data'), -64);
            $money = base_convert($data, 16, 10);
        } else {
            $money = Frame::getStrArr($value, 'amount');
        }
        $array['status'] = strtolower(Frame::getStrArr($body, 'ret.0.contractRet'));
        $array['amount'] = $this->toAmount($money);
        $array['hash'] = Frame::getStrArr($body, 'txID');
        $array['from'] = $this->hexToAddress(Frame::getStrArr($value, 'owner_address', ''));//付款地址
        $array['link'] = trim($this->TRON_WEBSITE_ADDRESS, '/') . '/#/transaction/' . $hash;
        $array['body'] = $body;
        return $array;
    }

    /**
     * 查询最新交易
     * https://api.trongrid.io/v1/accounts/TEEAuwA36kNc5RC71VnuDTpEqUWB14cEUg/transactions/trc20?limit=50&contract_address=TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t
     * @param string $address 要查询的钱包
     * @param int|string $limit int=最大200条,string=下一页
     * @param array $array
     * @return array
     */
    public function getNewTransaction(string $address, int|string $limit = 200, array $array = []): array {
        if (!empty($address) && !empty($limit)) {
            if (is_string($limit)) {
                $url = $limit;
            } else {
                $url = trim($this->TRON_API_HOST, '/') . '/v1/accounts/' . $address . '/transactions/trc20?limit=' . $limit . '&contract_address=' . $this->TRON_CONTRACT_ADDRESS;
            }
            $curl = Curl::get($url)->timeOut(10)->timeConnect(10)->exec();
            $data = Frame::isJson($curl->body());
            $array['success'] = $data['success'] ?? false;
            $array['data'] = $data['data'] ?? [];
            $array['mate'] = $data['meta'] ?? [];
            $array['time'] = seekDate((strToDate($data['meta']['at'] ?? (time() - 60 * 60 * 8)) + (60 * 60 * 8)));
            $array['next'] = $data['meta']['links']['next'] ?? '';//下一页
        }
        return $array;
    }

    /**
     * 获取查询最新交易并处理
     * @param string $address 要查询的钱包
     * @param int|string $limit int=最大200条,string=next下一个
     * @param array $array
     * @return array
     */
    public function getNewTrade(string $address, int|string $limit = 200, array $array = []): array {
        $data = $this->getNewTransaction($address, $limit);
        foreach (($data['data'] ?? []) as $v) {
            $arr = [];
            $status = strtolower(Frame::getStrArr($v, 'type', ''));
            $arr['sort'] = 'trc20';
            $arr['status'] = ($status == 'transfer' ? 'success' : $status);//交易状态[FAIL,SUCCESS]
            $arr['hash'] = Frame::getStrArr($v, 'transaction_id');//交易哈希号
            $arr['timestamp'] = Frame::getStrArr($v, 'block_timestamp');//未处理交易时间
            $arr['time'] = strToDate($arr['timestamp']) + 60 * 60 * 8;//交易时间+8小时等北京时间
            $arr['date'] = seekDate($arr['time']);//北京交易时间
            $arr['from'] = Frame::getStrArr($v, 'from', '');//付款地址
            $arr['to'] = Frame::getStrArr($v, 'to', '');//收款地址
            $arr['amount'] = $this->toMoney(Frame::getStrArr($v, 'value', 0), Frame::getStrArr($v, 'token_info.decimals', 0));//金额
            $arr['type'] = ((strtolower($arr['to']) == strtolower($address)) ? 1 : 2);//1=转入,2=转出
            $arr['link'] = trim($this->TRON_WEBSITE_ADDRESS, '/') . '/#/transaction/' . $arr['hash'];//交易详细
            $array['data'][] = $arr;
        }
        $array['count'] = count(($array['data'] ?? []));
        $array['next'] = ($data['next'] ?? []);
        $array['time'] = ($data['time'] ?? seekDate());
        return $array;
    }

    /**
     * 查询所有交易记录
     * https://apiasia.tronscan.io:5566/api/transaction?sort=-timestamp&count=true&limit=50&start=0&address=TEEAuwA36kNc5RC71VnuDTpEqUWB14cEUg
     * @param $address
     * @param int $start //起始记录
     * @param int $limit //最大50条记录
     * @param string $count
     * @param string $sort
     * @return array
     */
    public function getAllTransaction($address, int $start = 0, int $limit = 50, string $count = 'true', string $sort = '-timestamp'): array {
        $url = trim($this->TRON_TRON_ADDRESS, '/') . "/api/transaction?sort={$sort}&count={$count}&limit={$limit}&start={$start}&address=" . $address;
        $curl = Curl::get($url)->timeOut(15)->timeConnect(15)->referer()->exec();
        return Frame::isJson($curl->body());
    }

    /**
     * 获取处理过后的所有交易记录
     * @param string $address
     * @param int $start //起始记录
     * @param int $limit //最大50条记录
     * @param string $count
     * @param string $sort
     * @param array $array
     * @return array
     */
    public function getAllTrade(string $address, int $start = 0, int $limit = 50, string $count = 'true', string $sort = '-timestamp', array $array = []): array {
        $self = $this->getAllTransaction($address, $start, $limit, $count, $sort);
        if (!empty($data = ($self['data'] ?? []))) {
            $typeName = [
                1 => 'trx',//trx
                2 => 'trc10',//trc10
                12 => 'unstake',//TRX Unstake (1.0)
                31 => 'trc20',//Transfer
                57 => 'delegate',//Delegate Resources
                58 => 'reclaim',//Reclaim Resources
            ];
            foreach ($data as $v) {
                $arr = [];
                $contractType = Frame::getStrArr($v, 'contractType');
                $arr['sort'] = Frame::getStrArr($typeName, $contractType, 'unknown(' . $contractType . ')');
                $arr['status'] = strtolower(Frame::getStrArr($v, 'result', ''));//交易状态[FAIL,SUCCESS]
                $arr['hash'] = Frame::getStrArr($v, 'hash', '');//交易哈希
                $arr['timestamp'] = Frame::getStrArr($v, 'timestamp', time());//未处理交易时间
                $arr['time'] = strToDate($arr['timestamp']) + 60 * 60 * 8;//交易时间+8小时等北京时间
                $arr['date'] = seekDate($arr['time']);//北京交易时间
                $arr['from'] = Frame::getStrArr($v, 'contractData.owner_address', '');//付款地址
                $scale = Frame::getStrArr($v, 'tokenInfo.tokenDecimal', 0);
                if ($contractType == 31) {
                    $arr['to'] = Frame::getStrArr($v, 'trigger_info.parameter._to', Frame::getStrArr($v, 'contractData.contract_address', ''));//收款地址
                    $amount = Frame::getStrArr($v, 'contractData.data', '');
                    $arr['amount'] = $this->toMoney(base_convert(substr($amount, -64), 16, 10), $scale);
                } else {
                    $arr['to'] = Frame::getStrArr($v, 'toAddress', Frame::getStrArr($v, 'contractData.to_address', ''));//收款地址
                    $arr['amount'] = $this->toMoney(Frame::getStrArr($v, 'contractData.amount', ''), $scale);
                }
                $arr['type'] = ((strtolower($arr['from']) == strtolower($address)) ? 2 : 1);//1=转入,2=转出
                $arr['link'] = trim($this->TRON_WEBSITE_ADDRESS, '/') . '/#/transaction/' . $arr['hash'];//交易详细
                $arr['ret_status'] = strtolower(Frame::getStrArr($v, 'contractRet', ''));//状态[OUT_OF_ENERGY,SUCCESS]
                $array['data'][] = $arr;
            }
        }
        $array['page'] = $start;
        $array['limit'] = $limit;
        $array['count'] = count(($array['data'] ?? []));
        $array['total'] = $self['total'] ?? 0;
        $array['rangeTotal'] = $self['rangeTotal'] ?? 0;
        $array['time'] = ($data['time'] ?? seekDate());
        return $array;
    }

    /**
     * @param string $key
     */
    public function __construct(string $key = '') {
        $this->TRON_PRO_API_KEY = $key;
    }

    /**
     * @return TRC20Contract
     */
    public function trc(): TRC20Contract {
        if (empty($this->trc)) {
            $this->trc = $this->tron()->contract($this->TRON_CONTRACT_ADDRESS);
        }
        return $this->trc;
    }

    /**
     * @return Trons
     */
    public function tron(): Trons {
        if (empty($this->tron)) {
            $header = (!empty($key) ? ['TRON-PRO-API-KEY' => $this->TRON_PRO_API_KEY] : []);
            $HttpProvider = new HttpProvider($this->TRON_API_HOST, 30000, false, false, $header);
            $this->tron = new Trons($HttpProvider, $HttpProvider, $HttpProvider, $HttpProvider, $HttpProvider);
        }
        return $this->tron;
    }

    /**
     * @param $data
     * @param int $scale
     * @return string
     */
    public function toMoney($data, int $scale = 0) {
        $scale = ($scale > 0 ? $scale : $this->TRON_DECIMALS);
        return Frame::money(((!empty($data) ? $data : 0) / pow(10, $scale)), $scale, '');
    }
}