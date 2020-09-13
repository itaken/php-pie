<?php

namespace ItakenPHPie\text;

/**
 * 随机字符串
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-09-13
 */
final class RandPie
{

    /**
     * 生成随机数
     *
     * @param int $min
     * @param int $max
     * @param string $salt
     * @return mixed
     */
    public static function randomStatic(int $min=0, int $max=100, string $salt='')
    {
        if (empty($min) || empty($max)) {
            return 0;
        }
        $randArr = array();
        for ($i = $min; $i <= $max; $i++) {
            $randArr[] = $i;
        }
        //个数
        $total = count($randArr);
        $ip = date("h");
        $seed = (substr($ip, -1) + abs(crc32($salt))) % $total;
        return $randArr[$seed];
    }

    /**
     * 获取随机数字
     *
     * @param int $len 字符长度
     * @return string
     */
    public static function randomInteger(int $len=6)
    {
        $keyMap = '012345678901234567890123456789';
        $pieces = [];
        $max = mb_strlen($keyMap, '8bit') - 1;
        for ($i = 0; $i < $len; ++$i) {
            $pieces []= $keyMap[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    /**
     * 获取随机字符串
     *
     * @param int $len 字符长度
     * @param bool $upper 是否转为大写
     * @return string
     */
    public static function randomString(int $len=6, bool $upper=false)
    {
        $keyMap = '0123456789abcdefghjklmnpqrstuvwxyz0123456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $pieces = [];
        $max = mb_strlen($keyMap, '8bit') - 1;
        for ($i = 0; $i < $len; ++$i) {
            $pieces []= $keyMap[random_int(0, $max)];
        }
        $str = implode('', $pieces);
        return $upper ? strtoupper($str) : $str;
    }

    /**
     * 生成随机 字符串
     *
     * @param int $len 字符串长度
     * @param int $type 字符类型 0纯数字,1纯字母(默认),2数字字母混合
     * @return string
     */
    public static function randomText(int $len=6, int $type=1)
    {
        $len = abs(intval($len));
        if ($len < 1) {
            return '';
        }
        switch (intval($type)) {
            case 0:
                $key = '01234567890123456789';
                break;
            case 1:
                $key = 'UvVwdDeEfFgGhHiIjJkKlWxXyYzZaAbBcPqQrRsStTuCLmMnNoOp';
                break;
            case 2:
                $key = '78eEfFgGhH90aAbBcCd12sStTuUvqQrR7654VwWxXyYzZ0983nNoOpP3456DiIjJkKlLmM21';
                break;
            default:
                $key = 'hHifFgGUvVwStTyYzulLmMnNoOpPIjJkKdDeEqQrRsaAbBcCWxXZ';
                break;
        }
        $max = intval(strlen($key)) - 1;
        $str = '';
        for ($i = 0; $i < $len; $i++) {
            $str .= $key[random_int(0, $max)];
        }
        return $str;
    }

}
