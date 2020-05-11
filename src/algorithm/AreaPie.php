<?php

namespace ItakenPHPie\algorithm;

/**
 * 算法
 *
 * @editor itaken<regelhh@gmail.com>
 * @since 2013-11-1
 */
final class AreaPie
{
    /**
     * 由一个整数数组所代表，数组中每个数是墙的高度。墙之间的水坑能够装多少水呢？
     * @doc http://news.cnblogs.com/n/192014/
     *
     * @param array $arr
     * @return int
     */
    public static function arrayWall($arr)
    {
        $max_l = $p_l = 0;
        $max_r = $p_r  = count($arr) - 1;
        $volume = 0;
        while ($p_r > $p_l) {
            if ($arr[$max_l] < $arr[$max_r]) {
                $p_l = $p_l + 1;
                if ($arr[$p_l] >= $arr[$max_l]) {
                    $max_l = $p_l;
                } else {
                    $volume = $volume + ($arr[$max_l] - $arr[$p_l]);
                    continue;
                }
                continue;
            } else {
                $p_r = $p_r - 1;
                if ($arr[$p_r] >= $arr[$max_r]) {
                    $max_r = $p_r;
                } else {
                    $volume = $volume + ($arr[$max_r] - $arr[$p_r]);
                    continue;
                }
                continue;
            }
            continue;
        }
        return $volume;
    }
}
