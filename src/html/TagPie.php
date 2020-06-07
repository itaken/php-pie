<?php

namespace ItakenPHPie\html;

/**
 * 标签
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-11
 */
final class TagPie
{
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
     * 反编译 JSON 为 HTML字符串
     *
     * @param string $json
     * @return string html字符串
     */
    public static function json2html($json)
    {
        // 非字符串
        if (!is_string($json)) {
            return false;
        }
        // 去除 空格
        $jsonSpace = preg_replace('/\s/', '', $json);
        if (empty($jsonSpace)) {
            return false;
        }
        // 将 HTML 标签 反编译
        $jsonDecode = htmlspecialchars_decode($jsonSpace);
        // 去除 反斜杠
        $patterns = ['/\\\"/' => '"', '/\\\\\'/' => '\'', '/\\\u/' => 'u'];
        $jsonSlash = preg_replace(array_keys($patterns), array_values($patterns), $jsonDecode);
        if (strpos($jsonSlash, '{') === false) {
            return false;
        }
        $jsonArr = explode('}{', $jsonSlash);  // 分割多个json
        $html = '';
        foreach ($jsonArr as $str) {
            if (empty($str)) {
                continue;
            }
            $l_str = ltrim($str, '{'); // 去左括号
            $r_str = rtrim($l_str, '}');  // 去右括号
            $json = '{' . $r_str . '}';  // 添加左括号和右括号
            $array = json_decode($json, true);
            if (!is_array($array)) {
                // 解析失败
                if (strlen($json) < 20) {
                    $html .= '<p style="color:#E3E;margin:3px 2px;background-color:#FDF;width:695px;">' . $json . ' 解析失败!</p>';
                } else {
                    $html .= '<p style="color:#E3E;margin:3px 2px;background-color:#FDF;width:695px;">' . substr($json, 0, 12) . '...' . substr($json, -11, 11) . ' 解析失败!</p>';
                }
                unset($array, $json, $str);
                continue;
            }
            $html .= '<div style="margin:3px 2px;background-color:#CEF;width:695px;"><p style="color:blue;font-weight:600;">{</span></p>';
            $html .= self::parseJson($array, '', '&nbsp;&nbsp;');
            $html .= '<p><span style="color:blue;font-weight:600;">}</span></p></div>';
            unset($array, $json, $str);
        }
        return $html;
    }

    /**
     * json 数组转为 html字符串
     *
     * @param array $jsonArr
     * @param string $html
     * @param string $space 空格
     * @return string
     */
    private static function parseJson(array $jsonArr, string $html = '', $space = '')
    {
        foreach ($jsonArr as $key => $value) {
            $type = gettype($value);
            switch (strtolower($type)) {
                case 'null':
                    $html .= '<p style="font-size:15px;">' . $space . '"<span style="color:#666">' . $key . '</span>": <span style="color:#AAA;font-weight:700;">NULL</span>,</p>';
                    break;
                case 'string':
                    $html .= '<p style="font-size:15px;">' . $space . '"<span style="color:#666">' . $key . '</span>": "' . $value . '",</p>';
                    break;
                case 'integer':
                    $html .= '<p style="font-size:15px;">' . $space . '"<span style="color:#666">' . $key . '</span>": <span style="color:#FF00FF;font-weight:700;">' . $value . '</span>,</p>';
                    break;
                case 'boolean':
                    $value = empty($value) ? 'false' : 'true';
                    $html .= '<p style="font-size:15px;">' . $space . '"<span style="color:#666">' . $key . '</span>": <span style="color:#FF00FF;font-weight:700;">' . $value . '</span>,</p>';
                    break;
                case 'array':
                    $key_type = strtolower(gettype($key));
                    if ($key_type == 'integer') {
                        $html .= '<p style="color:blue;font-weight:600;">' . $space . '{</p>';
                        $html = self::parseJson($value, $html, $space . '&nbsp;&nbsp;&nbsp;');
                        $html .= '<p style="color:blue;font-weight:600;">' . $space . '}</p>';
                    } else {
                        $html .= '<p style="font-size:15px;">' . $space . '"<span style="color:#666">' . $key . '</span>": <span style="color:blue;font-weight:600;">[</span>';
                        $html = self::parseJson($value, $html, $space . '&nbsp;&nbsp;&nbsp;');
                        $html .= '<p style="color:blue;font-weight:600;">' . $space . '],</span>';
                    }
                    break;
                default:
                    $html .= '<p style="font-size:15px;">' . $space . '"<span style="color:#666">' . $key . '</span>": "' . $value . '",</p>';
                    break;
            }
            unset($value, $key);
        }
        return $html;
    }

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
                    $str = str_replace($matched['0'][$i], self::safeMailto($email) . $period, $str);
                }
            }
        }
        return $str;
    }

    /**
     * Encoded Mailto Link (from CodeIgniter)
     *
     * @param string $email      the email address
     * @param string $title      the link title
     * @param mixed  $attributes any attributes
     *
     * @return string
     */
    public static function safeMailto(string $email, string $title = '', $attributes = ''): string
    {
        if (trim($title) === '') {
            $title = $email;
        }
        $x = str_split('<a href="mailto:', 1);
        for ($i = 0, $l = strlen($email); $i < $l; $i ++) {
            $x[] = '|' . ord($email[$i]);
        }
        $x[] = '"';
        if ($attributes !== '') {
            if (is_array($attributes)) {
                foreach ($attributes as $key => $val) {
                    $x[] = ' ' . $key . '="';
                    for ($i = 0, $l = strlen($val); $i < $l; $i ++) {
                        $x[] = '|' . ord($val[$i]);
                    }
                    $x[] = '"';
                }
            } else {
                for ($i = 0, $l = mb_strlen($attributes); $i < $l; $i ++) {
                    $x[] = mb_substr($attributes, $i, 1);
                }
            }
        }
        $x[] = '>';
        $temp = [];
        for ($i = 0, $l = strlen($title); $i < $l; $i ++) {
            $ordinal = ord($title[$i]);
            if ($ordinal < 128) {
                $x[] = '|' . $ordinal;
            } else {
                if (empty($temp)) {
                    $count = ($ordinal < 224) ? 2 : 3;
                }
                $temp[] = $ordinal;
                if (count($temp) === $count) {
                    $number = ($count === 3) ? (($temp[0] % 16) * 4096) + (($temp[1] % 64) * 64) + ($temp[2] % 64) : (($temp[0] % 32) * 64) + ($temp[1] % 64);
                    $x[]    = '|' . $number;
                    $count  = 1;
                    $temp   = [];
                }
            }
        }

        $x[] = '<';
        $x[] = '/';
        $x[] = 'a';
        $x[] = '>';
        $x = \array_reverse($x);
        $output = '<script type="text/javascript">' . 'var l=new Array();';
        for ($i = 0, $c = count($x); $i < $c; $i ++) {
            $output .= 'l[' . $i . "] = '" . $x[$i] . "';";
        }
        return $output . ('for (var i = l.length-1; i >= 0; i=i-1) {'
                . "if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");"
                . 'else document.write(unescape(l[i]));'
                . '}'
                . '</script>');
    }
}
