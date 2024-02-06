<?php

namespace zhqing\module;

use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Mnemonic\MnemonicFactory;
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39Mnemonic;
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;
use BitWasp\Bitcoin\Key\Factory\HierarchicalKeyFactory;
use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;

class Tron {
    /**
     * 生成助记词
     * @param bool $type true=返回string,false=返回array
     * @return array|string
     */
    function getMnemonic(bool $type = false): array|string {
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
    function mnemonicToAddress(array|string $mnemonic): string {
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
    function getPrivateKey(): string {
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
    function privateKeyToPublicKey(string $hex): string {
        $PrivateKey = new PrivateKeyFactory();
        $PrivateKey = $PrivateKey->fromHexUncompressed($hex);
        $PrivateKey = $PrivateKey->getPublicKey();
        return $PrivateKey->getHex();
    }
}