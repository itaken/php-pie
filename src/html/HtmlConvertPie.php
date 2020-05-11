<?php

namespace ItakenPHPie\html;

/**
 * Html转换工具
 * 
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-10
 */
final class HtmlConvertPie
{
    /**
     * @var array 标签映射
     */
    const TAG_MAP = [
        'strong' => [
            'start' => '[b]', 'end' => '[/b]',
        ],
        'em' => [
            'start' => '[i]', 'end' => '[/i]',
        ],
        'text-align' => [
            'start' => '[align=%s]', 'end' => '[/align]',
        ],
        'font-size' => [
            'function' => __CLASS__ .'::px2em',
            'start' => '[size=%s]',
            'end' => '[/size]',
        ],
        'background-color' => [
            'function' => __CLASS__ . '::rgb2hex',
            'start' => '[backcolor=%s]',
            'end' => '[/backcolor]',
        ],
        'color' => [
            'function' => __CLASS__ . '::rgb2hex',
            'start' => '[color=%s]',
            'end' => '[/color]',
        ],
        'text-decoration' => [
            'condition' => 'underline',
            'style' => '',
            'start' => '[u]',
            'end' => '[/u]',
        ],
    ];

    /**
     * @var array 像素映射
     * @desc em => px
     */
    const SIZE_MAP = [
        7 => 34,
        6 => 24,
        5 => 16,
        4 => 14,
        3 => 12,
        2 => 10,
        1 => 8,
    ];

    /**
     * html 转 ubb 格式
     *
     * @param string $str
     * @return string
     */
    public static function ubbEncode($str)
    {
        if (empty($str)) {
            return $str;
        }
        $tagMap = self::TAG_MAP;
        $tagList = self::htmlTagsGrab($str);  // 抓取HTML中的标签
        if (empty($tagList)) {  // 没有抓取到标签
            return $str;
        }
        $endTagPop = [];  // 结束标签
        $ubbStr = '';   // 转编码后的字符串
        $isPTag = 0;  // 是否是p标签
        foreach ($tagList as $tagInfo) {
            $tagName = $tagInfo['name']; // 标签名
            $contents = trim($tagInfo['content']); // 内容
            if ($tagInfo['is_end'] == 1) { // 标签结束
                $ubbStr .= array_pop($endTagPop) . $contents;
                if ($tagName == 'p') {
                    $isPTag = 0;
                }
                continue;
            }
            $style = $tagInfo['style']; // 样式
            // 标签处理
            switch ($tagName) {
                case 'a': // a 标签
                    $link = $tagInfo['self'];
                    if (stripos($link, 'mailto') !== false) {
                        // 处理 邮件地址
                        $ubbStr .= '[email=' . str_replace('mailto:', '', $link) . ']' . $contents;
                        $endTagPop[] = '[/email]';
                        break;
                    }
                    $ubbStr .= '[url=' . $link . ']' . $contents;
                    $endTagPop[] = '[/url]';
                break;
                case 'br':  // 换行
                    if ($isPTag == 1) {  // p标签内的br不换行
                        $ubbStr .= $contents;
                    } else {
                        $ubbStr .= PHP_EOL . $contents;
                    }
                break;
                case 'strong':  // 粗体
                case 'u':  // 下划线
                case 'em':  // 斜体
                case 'b':  // 粗体
                case 'i':
                    if (isset($tagMap[$tagName])) {  // 是否是支持的样式
                        $match_tags = $tagMap[$tagName];
                        $ubbStr .= $match_tags['start'] . $contents;
                        $endTagPop[] = $match_tags['end'];
                    } else {
                        $ubbStr .= '[' . $tagName . ']' . $contents;
                        $endTagPop[] = '[/' . $tagName . ']';
                    }
                break;
                case 'img':
                    $link = $tagInfo['self'];
                    $ubbStr .= '[img]' . $link . '[/img]' . $contents;
                break;
                case 'p':
                    $ubbStr .= PHP_EOL;
                    $isPTag = 1;  // p标签
                    // no break
                case 'div':
                case 'section':
                case 'span':
                    if (empty($style)) { // 空样式,不用处理样式
                        $ubbStr .= $contents;
                        break;
                    }
                    $tagName_end = '';
                    foreach ($style as $single_style) {
                        if (empty($single_style)) {
                            break;
                        }
                        $styleName = $single_style['name'];  // 样式名
                        if (!isset($tagMap[$styleName])) {  // 不支持处理的样式,则略过
                            break;
                        }
                        $styleValue = $single_style['value']; // 样式值
                        if (isset($tagMap[$styleName]['condition'])) {  // 样式支持的值
                            if ($tagMap[$styleName]['condition'] != $styleValue) {
                                break;
                            }
                        }
                        if (isset($tagMap[$styleName]['function'])) {  // 需要特殊处理
                            $function = $tagMap[$styleName]['function'];
                            if (!empty($function)) {
                                $styleValue = call_user_func_array($function, [$styleValue]);
                            }
                        }
                        if (isset($tagMap[$styleName]['style'])) {
                            $styleValue = $tagMap[$styleName]['style'];
                        }
                        $ubbStr .= sprintf($tagMap[$styleName]['start'], $styleValue);  // 样式开始标签
                        $tagName_end = $tagMap[$styleName]['end'] . $tagName_end;  // 结束标签
                    }
                    $ubbStr .= $contents;
                    $endTagPop[] = $tagName_end;
                break;
                default:  // 不支持的标签
                    $ubbStr .= $contents;
                break;
            }
        }
        $filter_keymap = array(
            '/&amp;/i' => '&',
            '/&lt;/i' => '<',
            '/&gt;/i' => '>',
            '/&nbsp;/i' => ' ',
            '/&#160;/' => ' ', // 空格
            '/\<[^>]*?\>/i' => '',
            '/\&#\d+;/' => '', // 特殊符号
        );
        return preg_replace(array_keys($filter_keymap), array_values($filter_keymap), $ubbStr);
    }

    /**
     * 获取 html 中的标签
    *
    * @param string $str
    * @return array
    */
    public static function htmlTagsGrab($str)
    {
        if(empty($str)){
            return $str;
        }
        $matched =[];
        preg_match_all('/(\<[\/]?([a-zA-Z]+)([^>]*)\>)([^\<]*)/', $str, $matched);
        if (!isset($matched[1]) || empty($matched[1])) {
            return array();
        }
        $tagList = array();
        foreach ($matched[1] as $key => $tag) {
            $tagName = strtolower($matched[2][$key]);  // 标签
            $content = trim($matched[4][$key]);  // 内容
            if (strpos($tag, '</') === 0) {  // 结束标签
                $tagList[] = array(
                'tag' => $tag,
                'name' => $tagName,
                'content' => $content,
                'is_end' => 1, // 1 结束
                );
                continue;
            }
            // 处理非结束标签
            $style = trim($matched[3][$key]);  // 样式
            if (empty($style)) {  // 没有样式
                $tagList[] = array(
                'tag' => $tag,
                'name' => $tagName,
                'content' => $content,
                'is_end' => 0, // 0 非结束
                );
                continue;
            }
            // 特殊处理
            preg_match_all('/(style|href|src|align)=\"([^\"]+)\"/i', $style, $styleMatched);
            if (!isset($styleMatched[1]) || empty($styleMatched[1])) { // 没有支持的内容
                $tagList[] = array(
                'tag' => $tag,
                'name' => $tagName,
                'content' => $content,
                'is_end' => 0, // 0 非结束
                );
                continue;
            }
            $style_preg = array();
            foreach ($styleMatched[1] as $sk => $sv) {
                $style_preg[$sv] = $styleMatched[2][$sk];
            }
            $style_arr = array();
            if (isset($style_preg['style'])) {  // 有样式
                $style_split = explode(';', $style_preg['style']);  // 多样式分割
                foreach ($style_split as $single_style) {
                    if (empty($single_style)) {
                        continue;
                    }
                    // 示例: text-decoration: underline
                    list($styleName, $styleValue) = explode(':', $single_style);  // 样式分割, key=>value
                    $styleName = trim($styleName);  // 样式名
                    $style_arr[] = array(
                    'name' => $styleName,
                    'value' => trim($styleValue),
                    );
                }
            }
            $self = '';
            if ($tagName == 'img') {  // 图片标签
                $src = $style_preg['src'];
                if (!filter_var($src, FILTER_VALIDATE_URL)) {  // 非链接
                    if (strpos($src, '/') === false || strpos($src, ' ') !== false) {  // 非相对路径
                        continue;
                    }
                }
                $self = $src;
            } elseif ($tagName == 'a') {  // a标签
                if (isset($style_preg['href'])) {
                    $self = $style_preg['href'];
                }
            }
            $tagList[] = array(
                'tag' => $tag,
                'name' => $tagName,
                'self' => $self,
                'style' => $style_arr,
                'content' => $content,
                'is_end' => 0,
            );
        }
        return $tagList;
    }

    /**
     * RGB转 十六进制
    *
    * @param $rgb RGB颜色的字符串 如：rgb(255,255,255);
    * @return string 十六进制颜色值 如：#FFFFFF
    */
    public static function rgb2hex($rgb)
    {
        $match = [];
        $regexp = "/^rgb\(([0-9]{0,3})\,\s*([0-9]{0,3})\,\s*([0-9]{0,3})\)/";
        $re = preg_match($regexp, $rgb, $match);
        if ($re < 1) {
            return '#FFFFFF';
        }
        $re = array_shift($match);
        $hexColor = "#";
        $hex = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F'];
        for ($i = 0; $i < 3; $i++) {
            $r = null;
            $c = $match[$i];
            $hexAr = array();
            while ($c > 16) {
                $r = $c % 16;
                $c = ($c / 16) >> 0;
                array_push($hexAr, $hex[$r]);
            }
            array_push($hexAr, $hex[$c]);
            $ret = array_reverse($hexAr);
            $item = implode('', $ret);
            $item = str_pad($item, 2, '0', STR_PAD_LEFT);
            $hexColor .= $item;
        }
        return $hexColor;
    }

    /**
     * 字体 转换
    *
    * @param $pxSize 字体px大小 如：24px;
    * @return string em大小 如：1
    */
    public static function px2em($pxSize)
    {
        $pxSize = intval($pxSize);
        $sizeKeymap = self::SIZE_MAP;
        $em = 4;  // 默认是4
        foreach ($sizeKeymap as $em => $px) {
            if ($pxSize > $px) {
                break;
            }
        }
        return $em;
    }

}
