<?php

namespace ItakenPHPie\text;

include('lib/chinese/ChineseSTConvert.class.php');

use ItakenPHPie\text\lib\chinese\ChineseSTConvert;

/**
 * 汉字转拼音
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-10
 */
final class LangPie
{
    /**
     * 转换为简体
     * 
     * @param string $text
     * @return string
     */
    public static function toSimple($text)
    {
        return ChineseSTConvert::toSimple($text);
    }

    /**
     * 转换为繁体
     * 
     * @param string $text
     * @return string
     */
    public static function toTrad($text)
    {
        return ChineseSTConvert::toTrad($text);
    }
}