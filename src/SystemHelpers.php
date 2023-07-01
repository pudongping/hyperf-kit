<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-06-25 17:51
 */
declare(strict_types=1);

use Pudongping\SmartAssist\CipherHelper;

if (! function_exists('aes_cbc_encrypt')) {
    /**
     * aes cbc 加密
     *
     * @param mixed $plaintext 明文
     * @param string $key 加密 key
     * @param string $iv 向量
     * @return string
     */
    function aes_cbc_encrypt($plaintext, string $key, string $iv = ''): string
    {
        return CipherHelper::AESCBCEncrypt($plaintext, $key, $iv);
    }
}

if (! function_exists('aes_cbc_decrypt')) {
    /**
     * aes cbc 解密
     *
     * @param string $encrypted 密文
     * @param string $key 解密 key
     * @param string $iv 向量
     * @return array
     */
    function aes_cbc_decrypt(string $encrypted, string $key, string $iv = ''): array
    {
        return CipherHelper::AESCBCDecrypt($encrypted, $key, $iv);
    }
}