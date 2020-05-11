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
}
