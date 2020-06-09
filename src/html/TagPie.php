<?php

namespace ItakenPHPie\html;

use ItakenPHPie\html\SecurityPie;
use ItakenPHPie\html\lib\convert\Json;
use ItakenPHPie\html\lib\convert\Entities;
use ItakenPHPie\html\lib\convert\Ubb;

/**
 * 标签
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-11
 */
final class TagPie
{

    /**
     * 反编译 JSON 为 HTML字符串
     *
     * @param string $json
     * @return string html字符串
     */
    public static function json2html($json)
    {
        return Json::json2html($json);
    }

    /**
     * ASCII 转 实体
     *
     * @param string $str
     * @return string
     */
    public static function asciiToEntities($str)
    {
        return Entities::asciiToEntities($str);
    }

    /**
     * 实体 转回 ASCII
     *
     * @param string $str
     * @param bool $all 是否包含（"&","<",">","\"", "'", "-"）
     * @return string
     */
    public static function entitiesToAscii($str, $all = true)
    {
        return Entities::entitiesToAscii($str, $all);
    }

    /**
     * HTML 转 UBB
     *
     * @param string $html
     * @return string
     */
    public static function html2UBB($html)
    {
        return Ubb::ubbEncode($html);
    }

    /**
     * HTML 简单替换为 UBB
     *
     * @param string $html
     * @return string
     */
    public static function html2UBBSimple($html)
    {
        return Ubb::toUbb($html);
    }

    /**
     * email 转为 链接
     *
     * @param string $email
     * @return string
     */
    public static function emailLink($email)
    {
        $email = str_replace(array('@', '.'), array('&#64;', '&#46;'), $email);
        return '<a href="mailto: ' . $email . '">' . $email . '</a>';
    }

    /**
     * Auto-linker 自动添加链接 (from CodeIgniter)
     *
     * @param string $str
     * @param string $type 类型：both,email,url
     * @param bool $popup
     * @return string
     */
    public static function autoLink($str, $type = 'both', $popup = false)
    {
        if ($type != 'email') {
            if (preg_match_all("#(^|\s|\()((http(s?)://)|(www\.))(\w+[^\s\)\<]+)#i", $str, $matched)) {
                $pop = ($popup == true) ? " target=\"_blank\" " : "";
                for ($i = 0; $i < count($matched['0']); $i++) {
                    $period = '';
                    if (preg_match("|\.$|", $matched['6'][$i])) {
                        $period = '.';
                        $matched['6'][$i] = substr($matched['6'][$i], 0, -1);
                    }
                    $replace = $matched['1'][$i].'<a href="http'.$matched['4'][$i].'://'.$matched['5'][$i].$matched['6'][$i].'"'.$pop.'>http'.$matched['4'][$i].'://'.$matched['5'][$i].$matched['6'][$i].'</a>'.$period;
                    $str = str_replace($matched['0'][$i], $replace, $str);
                }
            }
        }
        if ($type != 'url') {
            if (preg_match_all("/([a-zA-Z0-9_\.\-\+]+)@([a-zA-Z0-9\-]+)\.([a-zA-Z0-9\-\.]*)/i", $str, $matched)) {
                for ($i = 0; $i < count($matched['0']); $i++) {
                    $period = '';
                    if (preg_match("|\.$|", $matched['3'][$i])) {
                        $period = '.';
                        $matched['3'][$i] = substr($matched['3'][$i], 0, -1);
                    }
                    $email = $matched['1'][$i].'@'.$matched['2'][$i].'.'.$matched['3'][$i];
                    $str = str_replace($matched['0'][$i], SecurityPie::safeMailto($email) . $period, $str);
                }
            }
        }
        return $str;
    }
}
