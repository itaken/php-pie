<?php

namespace ItakenPHPie\algorithm;

/**
 * 排序
 *
 * @modify itaken<regelhh@gmail.com>
 * @since 2020-05-10
 */
final class SortPie
{
    /**
     * 快速排序法
     *
     * @param array $arr
     * @return array
     */
    public static function quickSort($arr)
    {
        if (count($arr) > 1) {
            $k = $arr[0];
            $x = array();
            $y = array();
            $_size = count($arr);
            for ($i = 1; $i < $_size; $i++) {
                if ($arr[$i] <= $k) {
                    $x[] = $arr[$i];
                } else {
                    $y[] = $arr[$i];
                }
            }
            $x = self::quickSort($x);
            $y = self::quickSort($y);
            return array_merge($x, array($k), $y);
        } else {
            return$arr;
        }
    }

    /**
     * 希尔排序
     *
     * @param array $arr
     * @return array
     */
    public static function shellSort($arr)
    {
        $n = count($arr);
        $d = $n;
        $tmp = 0;
        while ($d > 1) {
            $d = floor(($d + 1) / 2);
            for ($i = 0; $i < $n - $d; $i++) {
                if ($arr[$i] > $arr[$i + $d]) {
                    $tmp = $arr[$i];
                    $arr[$i] = $arr[$i + $d];
                    $arr[$i + $d] = $tmp;
                }
            }
        }
        return $arr;
    }
}
