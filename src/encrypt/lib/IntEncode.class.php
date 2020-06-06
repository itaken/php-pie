<?php

namespace ItakenPHPie\encrypt\lib;

/**
 * 作用 int 值的加解密
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2015-5-14
 */
final class IntEncode
{

    /**
     * @var string 加密 码文
     */
    const ENCODE_BASE = "FlpOhvH6B1u3bXQxSyf7PmNJiz8cAEKwTd5UtZ4nR0CsakVjqMDgeWoG2YLrI";

    /**
     * @var int 密码长度
     */
    const ENCODE_LEN = 16;

    /**
     * 加密
     *
     * @param int $nums 整型数值
     * @param float $key 密钥
     * @return string
     */
    public static function encode($nums, $key = 2158.4354154122)
    {
        $length = self::ENCODE_LEN;
        $strbase = self::ENCODE_BASE;
        $codelen = substr($strbase, 0, $length);
        $codenums = substr($strbase, $length, 10);
        $codeext = substr($strbase, $length + 10);
        $rtn = "";
        $numslen = strlen($nums);
        //密文第一位标记数字的长度
        $begin = substr($codelen, $numslen - 1, 1);
        //密文的扩展位
        $extlen = $length - $numslen - 1;
        $temp = str_replace('.', '', $nums / $key);
        $temp = substr($temp, -$extlen);
        $arrextTemp = str_split($codeext);
        $arrext = str_split($temp);
        foreach ($arrext as $v) {
            $rtn .= $arrextTemp[$v];
        }
        $arrnumsTemp = str_split($codenums);
        $arrnums = str_split($nums);
        foreach ($arrnums as $v) {
            $rtn .= $arrnumsTemp[$v];
        }
        return $begin . $rtn;
    }

    /**
     * 解密
     *
     * @param string $code
     * @return int
     */
    public static function decode($code)
    {
        $length = self::ENCODE_LEN;
        $strbase = self::ENCODE_BASE;
        $codelen = substr($strbase, 0, $length);
        $codenums = substr($strbase, $length, 10);
        $begin = substr($code, 0, 1);
        $rtn = '';
        $len = strpos($codelen, $begin);
        if ($len !== false) {
            $len++;
            $arrnums = str_split(substr($code, -$len));
            foreach ($arrnums as $v) {
                $rtn .= strpos($codenums, $v);
            }
        }
        return intval($rtn);
    }
}
