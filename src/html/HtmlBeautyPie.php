<?php

namespace ItakenPHPie\html;

/**
 * Html转换工具
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-10
 */
final class HtmlBeautyPie
{
    /**
     * 美化HTML显示
     *
     * @param string $str html内容
     * @param boolean $stripAttr 是否过滤标签属性
     * @return string
     */
    public static function toBeauty($str, $stripAttr = false)
    {
        if (empty($str)) {
            return '';
        }
        $matched = [];
        preg_match_all('/(\<[\/]?\w+([^>]*)>)([^\<]*)/', $str, $matched);
        $beauty_str = '';  // 格式后html
        $space = "   ";  // 空格符
        $space_times = 0;  // 空格数量
        foreach ($matched[1] as $key => $value) {
            if (strpos($value, '</') === 0) {  // 结束标签
                $space_times --;  // 空格 -1
                $beauty_str .= str_repeat($space, $space_times) . $value . PHP_EOL;
                continue;
            }
            if ($stripAttr === true) {  // 过滤标签属性
                $style = $matched[2][$key];
                $replace = '';
                if (strpos($value, '<img') === 0) {  // 处理图片
                    preg_match('/(src=\S+)/', $style, $styleMatched);
                    $replace = ' ' . $styleMatched[0];
                }
                $value = str_replace($style, $replace, $value);
            }
            $beauty_str .= str_repeat($space, $space_times) . $value;  // 开始标签
            if (strpos($value, '<img') === 0 || strpos($value, '<br') === 0) {  // 图片/br换行
                $beauty_str .= PHP_EOL;
                continue;  // 没有内容,直接跳过循环
            }
            // 判断是否换行
            if (!isset($matched[3][$key]) || empty($matched[3][$key])) {  // 没有内容/空内容
                if (strpos($matched[1][$key + 1], '</') === false) {  // 下一个标签不是结束标签
                    $beauty_str .= PHP_EOL;
                }
                $space_times ++;
                continue;
            } elseif (!empty($matched[3][$key])) {  // 有内容
                $beauty_str .= PHP_EOL;
            } elseif (isset($matched[1][$key + 1])) {
                if (strpos($matched[1][$key + 1], '</') === false) {  // 下一个标签不是结束标签
                    $beauty_str .= PHP_EOL;
                }
            }
            $beauty_str .= str_repeat($space, $space_times + 1) . $matched[3][$key] . PHP_EOL;  // 内容显示
            $space_times ++;  // 空格 +1
        }
        return $beauty_str;
    }

    /**
     * 过滤商品介绍里面的js等有碍安全的代码
     *
     * @param string $html
     * @return html
     */
    public static function cleanScript(string $html)
    {
        if (empty($html) || !is_string($html)) {
            return $html;
        }
        $html = preg_replace('/<script.*<\/script>/isU', '', $html);
        return $html;
    }

    /**
     * 压缩html : 清除换行符,清除制表符,去掉注释标记
     * 
     * @param string $html
     * @return string 压缩后的内容
     */
    public static function compressHtml(string $html)
    {
        if (empty($html) || !is_string($html)) {
            return $html;
        }
        $html = str_replace(array("\r\n", "\n", "\t"), '', $html); //清除换行符 / 制表符
        $pattern = array("/> *([^ ]*) */", "/[\s]+/", "//", "/\" /", "/ \"/", "'/\*[^*]*\*/'");
        $replace = array(">\\1<", " ", "", "\"", "\"", "");
        return preg_replace($pattern, $replace, $html);
    }
}
