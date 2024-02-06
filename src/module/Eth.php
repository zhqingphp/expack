<?php

namespace zhqing\module;

use Web3\Utils;
use Elliptic\EC;
use zhqing\extend\Curl;
use zhqing\extend\Frame;
use Web3p\EthereumUtil\Util;
use Comely\DataTypes\BcNumber;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Mnemonic\MnemonicFactory;
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39Mnemonic;
use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;
use BitWasp\Bitcoin\Key\Factory\HierarchicalKeyFactory;

class Eth {
    public static int $TRON_DECIMALS = 18;
    public static string $ETHERS_PRO_API_KEY = "";
    public static string $ETHERS_API_HOST = "https://api.etherscan.io";
    public static string $TRON_WEBSITE_ADDRESS = 'https://etherscan.io';
    public static string $ETHERS_CONTRACT_ADDRESS = "0xdAC17F958D2ee523a2206206994597C13D831ec7";
    //'https://etherscan.io/tx/' + data

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
        $hardened = $master->derivePath("44'/60'/0'/0/0");
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
        return (new Util())->privateKeyToPublicKey($hex);
    }

    /**
     * 公钥转钱包钱包地址
     * @param string $publicKey
     * @return string
     */
    public function publicKeyToAddress(string $publicKey): string {
        return $this->addressToHex((new Util())->publicKeyToAddress($publicKey));
    }

    /**
     * 钱包Hex地址转钱包地址
     * @param $address_hex
     * @return string
     */
    public function hexToAddress($address_hex): string {
        return hex2bin($address_hex);
    }

    /**
     * 钱包地址转Hex地址
     * @param $address
     * @return string
     */
    public function addressToHex($address): string {
        return bin2hex($address);
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
        $data['link'] = trim(self::$TRON_WEBSITE_ADDRESS, '/') . '/token/' . self::$ETHERS_CONTRACT_ADDRESS . '?a=' . $data['address'];
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
        $data['link'] = trim(self::$TRON_WEBSITE_ADDRESS, '/') . '/token/' . self::$ETHERS_CONTRACT_ADDRESS . '?a=' . $data['address'];
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
        $data['link'] = trim(self::$TRON_WEBSITE_ADDRESS, '/') . '/token/' . self::$ETHERS_CONTRACT_ADDRESS . '?a=' . $data['address'];
        return $data;
    }

    /**
     * 验证地址
     * @param $address
     * @return bool
     */
    public function isAddress($address): bool {
        return Utils::isAddress($address);
    }

    /**
     * 验证私钥
     * @param string $privateKey
     * @return bool true=正确
     */
    public function isPrivate(string $privateKey): bool {
        if (\IEXBase\TronAPI\Support\Utils::isHex($privateKey) === false) {
            return false;
        }
        $privateKey = Utils::stripZero($privateKey);
        if (strlen($privateKey) !== 64) {
            return false;
        }
        return (bool)(new EC('secp256k1'))->keyFromPrivate($privateKey, 'hex');
    }

    /**
     * @param string $key
     * @param string $contract
     */
    public function __construct(string $key = '', string $contract = '') {
        self::$ETHERS_PRO_API_KEY = $key;
        self::$ETHERS_CONTRACT_ADDRESS = !empty($contract) ? $contract : self::$ETHERS_CONTRACT_ADDRESS;
    }

    /**
     * 执行curl
     * @param $parameter
     * @return array
     */
    protected function curl($parameter): array {
        $url = trim(self::$ETHERS_API_HOST, "/") . "/api?{$parameter}&apikey=" . self::$ETHERS_PRO_API_KEY;
        $curl = Curl::get($url)->timeOut(15)->timeConnect(15)->exec();
        return Frame::isJson($curl->body());
    }

    /**
     * 转换金额
     * @param $data
     * @return float
     */
    public function toMoney($data): float {
        return $this->toAmount($data, 6);
    }

    /**
     * 10进制转换U币
     * @param $data
     * @param int $scale
     * @return string
     */
    public function toAmount($data, int $scale = 0): string {
        return (new BcNumber($data))->divide(pow(10, self::$TRON_DECIMALS), ($scale > 0 ? $scale : self::$TRON_DECIMALS))->value();
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
}