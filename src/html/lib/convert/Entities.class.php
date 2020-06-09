<?php

namespace ItakenPHPie\html\lib\convert;

/**
 * 实体 转换
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-06-09
 */
final class Entities
{
    /**
     * ASCII 转 实体
     *
     * @param string $str
     * @return string
     */
    public static function asciiToEntities($str)
    {
        $count	= 1;
        $out	= '';
        $temp	= array();
        for ($i = 0, $s = strlen($str); $i < $s; $i++) {
            $ordinal = ord($str[$i]);
            if ($ordinal < 128) {
                if (count($temp) == 1) {
                    $out  .= '&#'.array_shift($temp).';';
                    $count = 1;
                }
                $out .= $str[$i];
            } else {
                if (count($temp) == 0) {
                    $count = ($ordinal < 224) ? 2 : 3;
                }
                $temp[] = $ordinal;
                if (count($temp) == $count) {
                    $number = ($count == 3) ? (($temp['0'] % 16) * 4096) + (($temp['1'] % 64) * 64) + ($temp['2'] % 64) : (($temp['0'] % 32) * 64) + ($temp['1'] % 64);
                    $out .= '&#'.$number.';';
                    $count = 1;
                    $temp = array();
                }
            }
        }
        return $out;
    }

    /**
     * 实体 转回 ASCII
     *
     * @param string $str
     * @param bool $all
     * @return string
     */
    public static function entitiesToAscii($str, $all = true)
    {
        if (preg_match_all('/\&#(\d+)\;/', $str, $matched)) {
            for ($i = 0, $s = count($matched['0']); $i < $s; $i++) {
                $digits = $matched['1'][$i];
                $out = '';
                if ($digits < 128) {
                    $out .= chr($digits);
                } elseif ($digits < 2048) {
                    $out .= chr(192 + (($digits - ($digits % 64)) / 64));
                    $out .= chr(128 + ($digits % 64));
                } else {
                    $out .= chr(224 + (($digits - ($digits % 4096)) / 4096));
                    $out .= chr(128 + ((($digits % 4096) - ($digits % 64)) / 64));
                    $out .= chr(128 + ($digits % 64));
                }
                $str = str_replace($matched['0'][$i], $out, $str);
            }
        }
        if ($all) {
            $str = str_replace(
                array("&amp;", "&lt;", "&gt;", "&quot;", "&apos;", "&#45;"),
                array("&","<",">","\"", "'", "-"),
                $str
            );
        }
        return $str;
    }
}