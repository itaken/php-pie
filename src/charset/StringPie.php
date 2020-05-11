<?php

namespace ItakenPHPie\charset;

use ItakenPHPie\chinese\PinyinPie;
use ItakenPHPie\config\ConfigPie;

/**
 * 字符串
 *
 * @modify itaken<regelhh@gmail.com>
 * @since 2020-05-10
 */
final class StringPie
{

    /**
     * 分割文本(注意ascii占1个字节, unicode...)
     *
     * @param string $str
     * @return array
     */
    public static function splitStr($str)
    {
        if(empty($str) || !is_string($str)){
            return [];
        }
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * 全角转半角
     *
     * @param string $str
     * @return string
     */
    public static function sbc2dbc(string $str)
    {
        $map = ConfigPie::get('basic/sbc');
        return strtr($str, $map);
    }

    /**
     * 汉字转拼音(汉字转拼音)
     *
     * @param string $text
     * @return string
     */
    public static function cn2pinyin(string $text)
    {
        return PinyinPie::toPinyin($text);
    }

    
    /**
     * 词汇分割
     * 
     * @param string $string 
     * @return array 
     */
    public static function stringSplit($string)
    {
        $string = trim($string);
        if (empty($string)) {
            return false;
        }
        // (空格 逗号, 中分号| 点. 分号; 冒号: 斜杠/ 反斜杠\ 横杠- 中文横杠- 下划线 _ 星号 * 中文分隔符等)
        $split_arr = preg_split('/(\s+)|((,|\||\.|;|:|\/|\\|-|-|_|\*|\+|=|，|；|：|。|、|—)+)/', $string);
        $split_space = array_diff($split_arr, array(''));  // 去空格

        return $split_space;
    }

    /**
     * 分割字符[可分割中文]
     * 
     * @param string $string 需要分割的字符串
     * @return array 分割后的字符
     */
    public static function zhStringSplit($string)
    {
        if (empty($string) || !is_string($string)) {
            return false;
        }
        mb_internal_encoding('UTF-8');  // 设置内部编码方式
        $len = mb_strlen($string);
        for ($i = 0; $i < $len; $i++) {
            $str = mb_substr($string, $i, 1);
            $arr[] = $str;
            $str = null;
        }
        return $arr;
    }

    /**
     * 内容替换
     * @doc http://www.cnblogs.com/strick/p/3936074.html
     * 
     * @param string $str 文本内容
     * @param int $start 开始位置
     * @param int $length 截取长度 ( 0 为start之后所有 )
     */
    public static function replaceStar($str, $start, $length = 0)
    {
        $i = 0;
        $star = '';
        if($start >= 0) {
            if($length > 0) {
                $str_len = strlen($str);
                $count = $length;
                if($start >= $str_len) {//当开始的下标大于字符串长度的时候，就不做替换了
                    $count = 0;
                }
            }elseif($length < 0){
                $str_len = strlen($str);
                $count = abs($length);
                if($start >= $str_len) {//当开始的下标大于字符串长度的时候，由于是反向的，就从最后那个字符的下标开始
                    $start = $str_len - 1;
                }
                $offset = $start - $count + 1;//起点下标减去数量，计算偏移量
                $count = $offset >= 0 ? abs($length) : ($start + 1);//偏移量大于等于0说明没有超过最左边，小于0了说明超过了最左边，就用起点到最左边的长度
                $start = $offset >= 0 ? $offset : 0;//从最左边或左边的某个位置开始
            }else {
                $str_len = strlen($str);
                $count = $str_len - $start;//计算要替换的数量
            }
        }else {
            if($length > 0) {
                $offset = abs($start);
                $count = $offset >= $length ? $length : $offset;//大于等于长度的时候 没有超出最右边
            }elseif($length < 0){
                $str_len = strlen($str);
                $end = $str_len + $start;//计算偏移的结尾值
                $offset = abs($start + $length) - 1;//计算偏移量，由于都是负数就加起来
                $start = $str_len - $offset;//计算起点值
                $start = $start >= 0 ? $start : 0;
                $count = $end - $start + 1;
            }else {
                $str_len = strlen($str);
                $count = $str_len + $start + 1;//计算需要偏移的长度
                $start = 0;
            }
        }
        while ($i < $count) {
            $star .= '*';
            $i++;
        }
        return substr_replace($str, $star, $start, $count);
    }

}
