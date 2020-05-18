<?php

namespace ItakenPHPie\text\lib\calculate;

/**
 * 寻找一个字符串的重复子串
 * @doc https://segmentfault.com/a/1190000002646526
 * @doc https://segmentfault.com/a/1190000002641054
 * 
 * @modify itaken<regelhh@gmail.com>
 * @since 2020-05-14
 */
class LRS
{

    /**
     * 分割文本
     *
     * @param string $str
     * @return array
     */
    private static function stringSplit($str)
    {
        if(empty($str) || !is_string($str)){
            return [];
        }
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * 文本长度计算
     * 
     * @param string $word
     * @param int $k
     * @param int $j
     * @return int
     */
    private static function statLen(string $word, int $k, int $j)
    {
        $curLen = 0;
        $strLen = mb_strlen($word);  // 计算文本长度
        $wordArr = self::stringSplit($word);  // 分割字符组
        while ($k< $strLen && $j < $strLen && $wordArr[$k] == $wordArr[$j]) {
            $k++;
            $j++;
            $curLen++;
        }
        return $curLen;
    }

    /**
     * LRS计算
     * 
     * @param string $text
     * @return int
     */
    public static function naiveLRS(string $text)
    {
        $maxLen = 0;
        $length = mb_strlen($text);
        for ($i=0; $i< $length; $i++) {
            $len = 0;
            $k = $i;//第一个游标 k
            //第二个游标j
            for ($j = $i + 1; $j < $length; $j++) {
                $len = self::statLen($text, $k, $j);
                if ($maxLen < $len) {
                    $maxLen = $len;
                }
            }
        }
        return $maxLen;
    }
}
