<?php

namespace ItakenPHPie\text\lib\calculate;

/**
 * 动态规划 字符串匹配算法
 * @doc https://segmentfault.com/a/1190000022642180
 *
 * @modify itaken<regelhh@gmail.com>
 * @since 2020-05-15
 */
class KMP
{
    private $tmpArr=[];
    private $wordLen = 0;
    private $wordArr = [];

    /**
     * 初始化需要匹配的文本内容
     *
     * @param string $word
     * @return void
     */
    private function wordInit($word)
    {
        $wordLen = mb_strlen($word);
        $wordArr = $this->splitStr($word);

        $this->word = $word;
        $this->wordLen = $wordLen;
        $this->wordArr = $wordArr;
        
        $tmpArr = [
            0 => -1,
        ];
        $j = -1;
        for ($i=0; $i < $wordLen - 1; $i ++) {
            if ($j == -1 || $wordArr[$i] == $wordArr[$j]) {
                $i += 1;
                $j += 1;
                $tmpArr[$i] = $j;
            } else {
                $j = $tmpArr[$j] ?: 0;
            }
        }
        $this->tmpArr = $tmpArr;
    }

    /**
     * 分割文本
     *
     * @param string $text
     * @return array
     */
    private function splitStr($text)
    {
        if (empty($text) || !is_string($text)) {
            return [];
        }
        return preg_split("//u", $text, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * 计算文本第一次匹配位置 (功能同 mb_strpos)
     *
     * @param string $text 需要计算的文本
     * @param string $word 被匹配的字符串
     * @return int 匹配位置 不匹配返回-1
     */
    public function textPos($text, $word)
    {
        if (empty($text) || empty($word)) {
            return -1;
        }
        $this->wordInit($word);  // 初始化文本

        $textLen = mb_strlen($text);
        $textArr = $this->splitStr($text);
        $wordLen = $this->wordLen;
        $wordArr = $this->wordArr;
        $tmpArr = $this->tmpArr;
        $j = 0;
        for ($i=0; $i < $textLen; $i ++) {
            if ($textArr[$i] != $wordArr[$j]) {
                $j = $tmpArr[$j] != -1 ? ($tmpArr[$j] ?: 0) : 0;
            }
            if ($textArr[$i] == $wordArr[$j]) {
                $j += 1;
            }
            if ($j == $wordLen) {
                return $i + 1 - $wordLen;
            }
        }
        return -1;
    }
}
