<?php

namespace ItakenPHPie\text;

include('lib/pinyin/cn2pinyin.class.php');

use ItakenPHPie\text\lib\pinyin\cn2pinyin;

/**
 * 汉字转拼音
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-10
 */
final class PinyinPie
{

    /**
     * 基础翻译
     *
     * @param string $text
     * @param string $div 分隔符
     * @return string
     */
    public static function toPinyin(string $text, $div=' ')
    {
        $text = trim($text);
        if (empty($text)) {
            return '';
        }
        if (preg_match("#^[0-9a-zA-Z_-]+$#is", $text)) {
            return $text;
        }
        preg_match_all("/./us", $text, $match);
        
        $pinyin = '';
        $letterArr = ['a','e','i','o','u'];
        $pinyinObj = new cn2pinyin();
        foreach ($match[0] as $value) {
            $tmp = $pinyinObj->get($value);

            $pyLen = strlen($pinyin); // 长度
            $pyChart =substr($pinyin, $pyLen-1, 1);
            if (
                $pinyin &&
                !in_array(strtolower($pyChart), $letterArr) &&
                in_array(strtolower(substr($tmp, 0, 1)), $letterArr)
            ) {
                $tmp = "'".$tmp;
            }
            $pinyin .= $div . $tmp;
        }
        return strtolower($pinyin);
    }
}
