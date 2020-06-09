<?php

namespace ItakenPHPie\html;

/**
 * 格式化
 *
 * @modify itaken<regelhh@gmail.com>
 * @since 2020-05-10
 */
final class SecurityPie
{

    /**
     * 过滤xss
     * @doc https://bbs.ichunqiu.com/thread-24972-1-1.html?from=sec
     *
     * @param string $html 需要过滤的内容
     * @return string
     */
    public static function removeXSS($html)
    {
        $html = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $html);
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';
        for ($i = 0; $i < strlen($search); $i ++) {
            $html = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $html);
            $html = preg_replace('/(�{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $html);
        }
        $ra1 = [
            'javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script',
            'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base',
        ];
        $ra2 = [
            'onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy',
            'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint',
            'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick',
            'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged',
            'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave',
            'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish',
            'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete',
            'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout',
            'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste',
            'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart',
            'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange',
            'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload',
        ];
        $ra = array_merge($ra1, $ra2);
    
        $found = true;
        while ($found == true) {
            $html_before = $html;
            for ($i = 0; $i < sizeof($ra); $i ++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j ++) {
                    if ($j > 0) {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|';
                        $pattern .= '|(&#{0,8}([9|10|13]);)';
                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i] [$j];
                }
                $pattern .= '/i';
                $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2);
                $html = preg_replace($pattern, $replacement, $html);
                if ($html_before == $html) {
                    $found = false;
                }
            }
        }
        return $html;
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
     * PHP标签 转换 实体
     *
     * @param string $str
     * @return string
     */
    public static function encodePHPTags($str)
    {
        return str_replace(array('<?php', '<?PHP', '<?', '?>'), array('&lt;?php', '&lt;?PHP', '&lt;?', '?&gt;'), $str);
    }

    /**
     * 引号 转换 实体
     *
     * @param string $str
     * @return string
     */
    public static function quotesToEntities($str)
	{
		return str_replace(array("\'","\"","'",'"'), array("&#39;","&quot;","&#39;","&quot;"), $str);
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
