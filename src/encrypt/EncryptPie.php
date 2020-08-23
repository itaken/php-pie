<?php

namespace ItakenPHPie\encrypt;

use ItakenPHPie\encrypt\lib\XXTea;
use ItakenPHPie\encrypt\lib\IntEncode;
use ItakenPHPie\encrypt\lib\IntConvert;
use ItakenPHPie\encrypt\lib\StringEncrypt;

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
    const ITAKEN_KEY = '#itaken-github:)-4@_encrypt';
 
    /**
     * openssl 数据加密
     *
     * @param string $data
     * @return string
     */
    public static function opensslEncrypt($data)
    {
        return \openssl_encrypt($data, 'AES-128-ECB', self::ITAKEN_KEY);
    }

    /**
     * openssl 数据解密
     *
     * @param string $data
     * @return string
     */
    public static function opensslDecrypt($data)
    {
        return \openssl_decrypt($data, 'AES-128-ECB', self::ITAKEN_KEY);
    }

    /**
     * discuz论坛 加密函数
     *
     * @param string $string
     * @param string $key 加密密钥
     * @return string
     */
    public static function discuzEncode($string, string $key=self::ITAKEN_KEY)
    {
        return StringEncrypt::discuzAuthCode($string, 'ENCODE', $key);
    }

    /**
     * discuz论坛 解密函数
     *
     * @param string $string
     * @param string $key 加密密钥
     * @return string
     */
    public static function discuzDecode($string, string $key=self::ITAKEN_KEY)
    {
        return StringEncrypt::discuzAuthCode($string, 'DECODE', $key);
    }

    /**
     * 可逆的字符串加密函数 (可变)
     *
     * @param int $txtStream 待加密的字符串内容
     * @param int $password 加密密码
     * @return string 加密后的字符串
     */
    public static function strEncrypt($txtStream, $password=self::ITAKEN_KEY)
    {
        return StringEncrypt::strEncrypt($txtStream, $password);
    }

    /**
     * 可逆的字符串解密函数
     *
     * @param int $txtStream 待加密的字符串内容
     * @param int $password 解密密码
     * @return string 解密后的字符串
     */
    public static function strDecrypt($txtStream, $password=self::ITAKEN_KEY)
    {
        return StringEncrypt::strDecrypt($txtStream, $password);
    }

    /**
     * 加密方法 ( XOR 方法)
     *
     * @param string $string 要加密的字符串
     * @param string $key  加密密钥
     * @return string
     */
    public static function xorEncrypt($string, $key=self::ITAKEN_KEY)
    {
        $string = base64_encode(trim($string));
        $key = md5($key ?: self::ITAKEN_KEY);
        $str_len = strlen($string);
        $key_len = strlen($key);
        for ($i = 0; $i < $str_len; $i++) {
            for ($j = 0; $j < $key_len; $j++) {
                $string[$i] = $string[$i] ^ $key[$j];
            }
        }
        return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
        // return base64_encode($string);
    }

    /**
     * 解密方法 ( XOR 方法)
     *
     * @param  string $string 要解密的字符串 （必须是 xor_encrypt 方法加密的字符串）
     * @param  string $key  加密密钥
     * @return string
     */
    public static function xorDecrypt($string, $key=self::ITAKEN_KEY)
    {
        $string = base64_decode(str_pad(strtr($string, '-_', '+/'), strlen($string) % 4, '=', STR_PAD_RIGHT));
        // $string = base64_decode(trim($string));
        if (empty($string)) {
            return false;
        }
        $key = md5($key ?: self::ITAKEN_KEY);
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
     * ThinkPHP 加密方法
     *
     * @param string $data 要加密的字符串
     * @param string $key  加密密钥
     * @param int $expire  过期时间 (单位:秒)
     * @return string
     */
    public static function thinkEncrypt($data, $key, $expire = 0)
    {
        $key = md5($key);
        $data = base64_encode($data);
        $x = 0;
        $len = strlen($data);
        $l = strlen($key);
        $char = '';
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= substr($key, $x, 1);
            $x++;
        }
        $str = sprintf('%010d', $expire ? $expire + time() : 0);
        for ($i = 0; $i < $len; $i++) {
            $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
        }
        return str_replace(array('=', '/'), array('', '_'), base64_encode($str));
    }

    /**
     * ThinkPHP 解密方法
     *
     * @param string $data 要解密的字符串 （必须是 thinkEncrypt 方法加密的字符串）
     * @param string $key  加密密钥
     * @return string
     */
    public static function thinkDecrypt($data, $key)
    {
        $key = md5($key);
        $x = 0;
        $data = base64_decode(str_replace('_', '/', $data));
        $expire = substr($data, 0, 10);
        $data = substr($data, 10);
        if ($expire > 0 && $expire < time()) {
            return '';
        }
        $len = strlen($data);
        $l = strlen($key);
        $char = $str = '';
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= substr($key, $x, 1);
            $x++;
        }
        for ($i = 0; $i < $len; $i++) {
            if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            } else {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        return base64_decode($str);
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
    public static function xxTeaEncode($string, $key = self::ITAKEN_KEY)
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
    public static function xxTeaDecode($string, $key = self::ITAKEN_KEY)
    {
        return (new XXTea)->decrypt($string, $key);
    }

    /**
     * int 加密
     *
     * @param int $num
     * @param string $key
     * @return string
     */
    public static function intEncode($num, $key = '3.1415926')
    {
        return IntEncode::encode($num, $key);
    }

    /**
     * int 解密
     *
     * @param string $string
     * @param string $key
     * @return string
     */
    public static function intDecode($string)
    {
        return IntEncode::decode($string);
    }

    /**
     * 加密hash，生成发送给用户的hash字符串
     *
     * @param array $hashArr
     * @param string $hashKey 加密干扰码
     * @return string
     */
    public static function encodeHash($hashArr, string $hashKey=self::ITAKEN_KEY)
    {
        if (empty($hashArr)) {
            return false;
        }
        $hashStr = "";
        foreach ($hashArr as $key=>$value) {
            $hashStr .= $key . "^]+" . $value . "!;-";
        }
        $hashStr = substr($hashStr, 0, -3);
        $tmpStr = '';
        for ($i=1; $i<=strlen($hashStr); $i++) {
            $char   = substr($hashStr, $i-1, 1);
            $keyChar = substr($hashKey, ($i % strlen($hashKey))-2, 1);
            $char   = chr(ord($char)+ord($keyChar));
            $tmpStr .= $char;
        }
        return str_replace(['+','/','='], ['-','_','.'], base64_encode($tmpStr));
    }

    /**
     * 解密hash，从用户回链的hash字符串解密出里面的内容
     *
     * @param string $hashStr
     * @param string $hashKey 加密干扰码
     * @return array
     */
    public static function decodeHash($hashStr, string $hashKey=self::ITAKEN_KEY)
    {
        if (empty($hashStr)) {
            return [];
        }
        $tmpStr  = '';
        if (strpos($hashStr, "-")||strpos($hashStr, "_")||strpos($hashStr, ".")) {
            $hashStr = str_replace(['-','_','.'], ['+','/','='], $hashStr);
        }
        $hashStr = base64_decode($hashStr);
        for ($i=1; $i<=strlen($hashStr); $i++) {
            $char   = substr($hashStr, $i-1, 1);
            $keyChar = substr($hashKey, ($i % strlen($hashKey))-2, 1);
            $char  = chr(ord($char)-ord($keyChar));
            $tmpStr .= $char;
        }
        $hashArr = [];
        $arr = explode("!;-", $tmpStr);
        foreach ($arr as $value) {
            list($k, $v) = explode("^]+", $value);
            if ($k) {
                $hashArr[$k] = $v;
            }
        }
        return $hashArr;
    }
}
