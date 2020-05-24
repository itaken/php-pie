<?php

namespace ItakenPHPie\text;

include('lib/calculate/LRS.class.php');
include('lib/markdown/Parsedown.php');

use ItakenPHPie\text\PinyinPie;
use ItakenPHPie\config\ConfigPie;
use ItakenPHPie\text\lib\calculate\LRS;
use ItakenPHPie\text\lib\markdown\Parsedown;

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
     * @param string $text
     * @return array
     */
    public static function stringSplit($text)
    {
        if (empty($text) || !is_string($text)) {
            return [];
        }
        // preg_match_all("/./us", $text, $match);
        // return $match[0] ?: [];
        return preg_split("//u", $text, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * 全角转半角
     *
     * @param string $text
     * @return string
     */
    public static function sbc2dbc(string $text)
    {
        $map = ConfigPie::get('basic/sbc');
        return strtr($text, $map);
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
    public static function symbolSplit($string)
    {
        $string = trim($string);
        if (empty($string)) {
            return [];
        }
        // (空格 逗号, 中分号| 点. 分号; 冒号: 斜杠/ 反斜杠\ 横杠- 中文横杠- 下划线 _ 星号 * 中文分隔符等)
        $stringArr = preg_split('/(\s+)|((,|\||\.|;|:|\/|\\|-|-|_|\*|\+|=|\#|\$|\%|\^|~|，|；|：|。|、|—)+)/', $string);
        
        return array_diff($stringArr, ['']);  // 去空格
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
            return [];
        }
        $stringArr = [];
        mb_internal_encoding('UTF-8');  // 设置内部编码方式
        $len = mb_strlen($string);
        for ($i = 0; $i < $len; $i++) {
            $str = mb_substr($string, $i, 1);
            $stringArr[] = $str;
            $str = null;
        }
        return $stringArr;
    }

    /**
     * 内容替换 （支持中文）
     * @doc http://www.cnblogs.com/strick/p/3936074.html
     *
     * @param string $text 文本内容
     * @param int $start 开始位置
     * @param int $length 截取长度 ( 0 为start之后所有 )
     */
    public static function replaceStar($text, $start, $length = 0)
    {
        $i = 0;
        $replacement = '';
        if ($start >= 0) {
            if ($length > 0) {
                $strLen = mb_strlen($text);
                $count = $length;
                if ($start >= $strLen) {//当开始的下标大于字符串长度的时候，就不做替换了
                    $count = 0;
                }
            } elseif ($length < 0) {
                $strLen = mb_strlen($text);
                $count = abs($length);
                if ($start >= $strLen) {//当开始的下标大于字符串长度的时候，由于是反向的，就从最后那个字符的下标开始
                    $start = $strLen - 1;
                }
                $offset = $start - $count + 1;//起点下标减去数量，计算偏移量
                $count = $offset >= 0 ? abs($length) : ($start + 1);//偏移量大于等于0说明没有超过最左边，小于0了说明超过了最左边，就用起点到最左边的长度
                $start = $offset >= 0 ? $offset : 0;//从最左边或左边的某个位置开始
            } else {
                $strLen = mb_strlen($text);
                $count = $strLen - $start;//计算要替换的数量
            }
        } else {
            if ($length > 0) {
                $offset = abs($start);
                $count = $offset >= $length ? $length : $offset;//大于等于长度的时候 没有超出最右边
            } elseif ($length < 0) {
                $strLen = mb_strlen($text);
                $end = $strLen + $start;//计算偏移的结尾值
                $offset = abs($start + $length) - 1;//计算偏移量，由于都是负数就加起来
                $start = $strLen - $offset;//计算起点值
                $start = $start >= 0 ? $start : 0;
                $count = $end - $start + 1;
            } else {
                $strLen = mb_strlen($text);
                $count = $strLen + $start + 1;//计算需要偏移的长度
                $start = 0;
            }
        }
        while ($i < $count) {
            $replacement .= '*';
            $i++;
        }
        // return substr_replace($text, $replacement, $start, $count);
        return self::mbSubstrReplace($text, $replacement, $start, $count);
    }

    /**
     * 文本替换 (支持中文)
     * @doc https://www.php.net/manual/en/function.substr-replace.php
     *
     * @param string $string
     * @param string $replacement 替换的文案
     * @param int $start 开始位置
     * @param int $length 替换长度
     * @param string $encoding 编码
     * @return string
     */
    public static function mbSubstrReplace($string, $replacement, $start, $length = null, $encoding = null)
    {
        if (extension_loaded('mbstring') === true) {
            $string_length = (is_null($encoding) === true) ? mb_strlen($string) : mb_strlen($string, $encoding);
            if ($start < 0) {
                $start = max(0, $string_length + $start);
            } elseif ($start > $string_length) {
                $start = $string_length;
            }
            if ($length < 0) {
                $length = max(0, $string_length - $start + $length);
            } elseif ((is_null($length) === true) || ($length > $string_length)) {
                $length = $string_length;
            }
            if (($start + $length) > $string_length) {
                $length = $string_length - $start;
            }
            if (is_null($encoding) === true) {
                return mb_substr($string, 0, $start) . $replacement . mb_substr($string, $start + $length, $string_length - $start - $length);
            }
            return mb_substr($string, 0, $start, $encoding) . $replacement . mb_substr($string, $start + $length, $string_length - $start - $length, $encoding);
        }
        return (is_null($length) === true) ? substr_replace($string, $replacement, $start) : substr_replace($string, $replacement, $start, $length);
    }


    /**
     * 计算 两个字符串 的相似度
     *
     * @param string $text1
     * @param string $text2
     * @return float 相似百分比，示例：82.352941176471
     */
    public static function similarText($text1, $text2)
    {
        $percent = 0;
        \similar_text($text1, $text2, $percent);
        
        return $percent;
    }

    /**
     * 计算一个字符串的重复子串
     *
     * @param string $text
     * @return int 重复字符串数
     */
    public static function calculateText($text)
    {
        return LRS::naiveLRS($text);
    }

    /**
     * 将markdown转为html
     * @doc https://github.com/erusev/parsedown
     *
     * @param string $mdText
     * @param bool $escapedHTML 过滤HTML标签，非xss安全
     * @return string
     */
    public static function md2html(string $mdText, bool $escapedHTML=false)
    {
        if (empty($mdText)) {
            return '';
        }
        $Parsedown = new Parsedown();
        $Parsedown->setSafeMode(true);
        if ($escapedHTML) {
            $Parsedown->setMarkupEscaped(true);
        }
        return $Parsedown->text($mdText);
    }
}
