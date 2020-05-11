<?php

namespace ItakenPHPie\filter;

include('lib/SensitiveWordFilter.class.php');

use ItakenPHPie\filter\lib\SensitiveWordFilter;

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
     * @return array
     */
    public static function sensitiveFilter($text, $replace='*', $depth=2)
    {
        if (empty($text)) {
            return [];
        }
        $depth = $depth > 3 || $depth < 0 ? 2 : $depth;
        $filter = new SensitiveWordFilter();
        $filter->loadData();
        $text = $filter->filter($text, $replace, $depth);
        return [
            'text' => $text,
            'match' => $filter->getMatchDict(),
            'full_match' => $filter->getMatchFullDict(),
        ];
    }
}
