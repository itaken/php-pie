<?php

namespace ItakenPHPie\text;

use ItakenPHPie\text\StringPie;
use ItakenPHPie\text\lib\sensitive\SensitiveWordFilter;

/**
 * 文本过滤
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-08
 */
final class FilterPie
{
    /**
     * 敏感词过滤
     *
     * @param string $text
     * @param string $replace
     * @param int $depth
     * @param bool $wordCache 词库缓存
     * @return array
     */
    public static function sensitiveFilter($text, $replace='*', $depth=2, $wordCache=true)
    {
        if (empty($text)) {
            return [];
        }
        $depth = $depth > 3 || $depth < 0 ? 2 : $depth;
        $filter = new SensitiveWordFilter();
        $filter->loadData($wordCache);
        $filterStr = $filter->filter($text, $replace, $depth);
        return [
            'original_text' => $text,
            'text' => $filterStr,
            'match' => $filter->getMatchDict(),
            'full_match' => $filter->getMatchFullDict(),
        ];
    }

    /**
     * 全角转半角
     *
     * @param string $str
     * @return string
     */
    public static function sbc2dbc(string $str)
    {
        return StringPie::sbc2dbc($str);
    }
}
