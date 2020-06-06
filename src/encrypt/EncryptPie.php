<?php

namespace ItakenPHPie\encrypt;

include('lib/IntConvert.class.php');
include('lib/XXTea.class.php');

use ItakenPHPie\encrypt\lib\IntConvert;
use ItakenPHPie\encrypt\lib\XXTea;

/**
 * 一些 加密工具
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2019-3-6
 */
final class EncryptPie
{
    /**
     * @var string 加解密-密钥
     */
    private static $itakenKey = 'itaken@github';
 
    /**
     * openssl数据加密
     *
     * @param string $data
     * @return string
     */
    public static function opensslEncrypt($data)
    {
        return \openssl_encrypt($data, 'AES-128-ECB', self::$itakenKey);
    }

    /**
     * openssl数据解密
     *
     * @param string $data
     * @return string
     */
    public static function opensslDecrypt($data)
    {
        return \openssl_decrypt($data, 'AES-128-ECB', self::$itakenKey);
    }

    /**
     * discuz论坛的加密解密函数
     *
     * @param string $string
     * @param string $operation DECODE/ENCODE
     * @param string $key 加密密钥
     * @return string
     */
    public static function discuzAuthCode($string, $operation='ENCODE', $key='')
    {
        if (empty($string) || empty($operation)) {
            return false;
        }
        $operation = strtoupper($operation);  // 转为大写
        $key = $key ? md5($key) : md5(self::$itakenKey);
        $keyLen = strlen($key);
        $string = $operation == 'DECODE' ? base64_decode($string) : substr(md5($string . $key), 0, 8) . $string;
        $stringLen = strlen($string);
        $rndKey = $box = [];
        $result = '';
        for ($i = 0; $i <= 255; $i++) {
            $rndKey[$i] = ord($key[$i % $keyLen]);
            $box[$i] = $i;
        }
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndKey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $stringLen; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'DECODE') {
            if (substr($result, 0, 8) == substr(md5(substr($result, 8) . $key), 0, 8)) {
                return substr($result, 8);
            } else {
                return '';
            }
        } else {
            return str_replace('=', '', base64_encode($result));
        }
    }

    /**
     * 可逆的字符串加密函数
     * @param int $txtStream 待加密的字符串内容
     * @param int $password 加密密码
     * @return string 加密后的字符串
     */
    public static function strEncrypt($txtStream, $password='')
    {
        if (empty($txtStream)) {
            return false;
        }
        $password = $password ?: md5(self::$itakenKey);
        //密锁串，不能出现重复字符，内有A-Z,a-z,0-9,/,=,+,_,
        $lockStream = 'st=lDEFABCNOPyzghi_jQRST-UwxkVWXYZabcdef+IJK6/7nopqr89LMmGH012345uv';
        //随机找一个数字，并从密锁串中找到一个密锁值
        $lockLen = strlen($lockStream);
        $lockCount = rand(0, $lockLen - 1);
        $randomLock = $lockStream[$lockCount];
        //结合随机密锁值生成MD5后的密码
        $password = md5($password . $randomLock);
        //开始对字符串加密
        $txtStream = base64_encode($txtStream);
        $tmpStream = '';
        $i = $j = $k = 0;
        for ($i = 0; $i < strlen($txtStream); $i++) {
            $k = ($k == strlen($password)) ? 0 : $k;
            $j = (strpos($lockStream, $txtStream[$i]) + $lockCount + ord($password[$k])) % ($lockLen);
            $tmpStream .= $lockStream[$j];
            $k++;
        }
        return $tmpStream . $randomLock;
    }

    /**
     * 可逆的字符串解密函数
     * @param int $txtStream 待加密的字符串内容
     * @param int $password 解密密码
     * @return string 解密后的字符串
     */
    public static function strDecrypt($txtStream, $password='')
    {
        if (empty($txtStream)) {
            return false;
        }
        $password = $password ?: md5(self::$itakenKey);
        //密锁串，不能出现重复字符，内有A-Z,a-z,0-9,/,=,+,_,
        $lockStream = 'st=lDEFABCNOPyzghi_jQRST-UwxkVWXYZabcdef+IJK6/7nopqr89LMmGH012345uv';
        $lockLen = strlen($lockStream);
        //获得字符串长度
        $txtLen = strlen($txtStream);
        //截取随机密锁值
        $randomLock = $txtStream[$txtLen - 1];
        //获得随机密码值的位置
        $lockCount = strpos($lockStream, $randomLock);
        //结合随机密锁值生成MD5后的密码
        $password = md5($password . $randomLock);
        //开始对字符串解密
        $txtStream = substr($txtStream, 0, $txtLen - 1);
        $tmpStream = '';
        $i = $j = $k = 0;
        for ($i = 0; $i < strlen($txtStream); $i++) {
            $k = ($k == strlen($password)) ? 0 : $k;
            $j = strpos($lockStream, $txtStream[$i]) - $lockCount - ord($password[$k]);
            while ($j < 0) {
                $j = $j + ($lockLen);
            }
            $tmpStream .= $lockStream[$j];
            $k++;
        }
        return base64_decode($tmpStream);
    }


    /**
     * 加密方法 ( XOR 方法)
     *
     * @param string $string 要加密的字符串
     * @param string $key  加密密钥
     * @return string
     */
    public static function xorEncrypt($string, $key = '')
    {
        $string = base64_encode(trim($string));
        $key = md5($key ?: self::$itakenKey);
        $str_len = strlen($string);
        $key_len = strlen($key);
        for ($i = 0; $i < $str_len; $i++) {
            for ($j = 0; $j < $key_len; $j++) {
                $string[$i] = $string[$i] ^ $key[$j];
            }
        }
        return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
        //	return base64_encode($string);
    }

    /**
     * 解密方法 ( XOR 方法)
     *
     * @param  string $string 要解密的字符串 （必须是 xor_encrypt 方法加密的字符串）
     * @param  string $key  加密密钥
     * @return string
     */
    public static function xorDecrypt($string, $key = '')
    {
        $string = base64_decode(str_pad(strtr($string, '-_', '+/'), strlen($string) % 4, '=', STR_PAD_RIGHT));
        //	$string = base64_decode(trim($string));
        if (empty($string)) {
            return false;
        }
        $key = md5($key ?: self::$itakenKey);
        $str_len = strlen($string);
        $key_len = strlen($key);
        for ($i = 0; $i < $str_len; $i++) {
            for ($j = 0; $j < $key_len; $j++) {
                $string[$i] = $key[$j] ^ $string[$i];
            }
        }
        return base64_decode($string);
    }

    /**
     * 将数字编码为字符串
     *
     * @param int $num
     * @return string
     */
    public static function int2string($num = 0)
    {
        return IntConvert::toString($num);
    }

    /**
     * 将字符串编码为数字
     *
     * @param string $str
     * @return int
     */
    public static function string2int($str = '')
    {
        return IntConvert::toInt($str);
    }

    /**
     * XXTea 加密
     *
     * @param string $string
     * @param string $key
     * @return string
     */
    public static function xxTeaEncode($string, $key = XXTea::ITAKEN_KEY)
    {
        return (new XXTea)->encrypt($string, $key);
    }

    /**
     * XXTea 解密
     *
     * @param string $string
     * @param string $key
     * @return string
     */
    public static function xxTeaDecode($string, $key = XXTea::ITAKEN_KEY)
    {
        return (new XXTea)->decrypt($string, $key);
    }
}
