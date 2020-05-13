<?php

namespace ItakenPHPie\filter;

include('lib/sensitive/SensitiveWordFilter.class.php');

use ItakenPHPie\filter\lib\sensitive\SensitiveWordFilter;

/**
 * 文本过滤
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-08
 */
final class WordFilterPie
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
        $text = $filter->filter($text, $replace, $depth);
        return [
            'text' => $text,
            'match' => $filter->getMatchDict(),
            'full_match' => $filter->getMatchFullDict(),
        ];
    }
}
