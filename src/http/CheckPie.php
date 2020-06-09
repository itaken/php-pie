<?php

namespace ItakenPHPie\http;

use ItakenPHPie\http\CurlPie;
use ItakenPHPie\http\HttpPie;

/**
 * 校验
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-11
 */
final class CheckPie
{
    /**
     * 是否 GET 提交
     * 
     * @return boolean - true 是
     */
    public static function isGet()
    {
        return HttpPie::isGet();
    }

    /**
     * 是否 POST 提交
     * 
     * @return boolean - true 是
     */
    public static function isPost()
    {
        return HttpPie::isPost();
    }

    /**
     * 判断是否URL
     *
     * @param string $url
     * @return bool
     */
    public static function isUrl($url)
    {
        return CurlPie::isUrl($url);
    }

    /**
     * 判断是否HTTPS
     *
     * @return boolean -true 是https
     */
    public static function isHttps()
    {
        return HttpPie::isHttps();
    }

    /**
     * 判断 URL 是否有效
     *
     * @param string $url 传入的链接
     * @return string
     */
    public static function isUrlAvailable($url)
    {
        if (!self::isUrl($url)) {
            return false;
        }
        try {
            $header = @get_headers($url);  // 获取页面头部信息
        } catch (\Exception $exc) {
            return false;
        }
        // 判断是否 有效，20x 表示成功
        if (strpos($header[0], '20') === false) {
            return false;
        }
        return $url;
    }

    /**
     * 检验 主机 是否有效
     * 在linux中可以使用 checkdnsrr
     *
     * @param string $hostName
     * @param string $recType
     * @return bool
     */
    public static function checkDNSRR($hostName, $recType = '')
    {
        if (empty($hostName)) {
            return false;
        }
        $recType = $recType ?: 'MX';
        $result = array();
        exec("nslookup -type=$recType $hostName", $result);
        foreach ($result as $line) {
            if (preg_match("/^$hostName/", $line)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 请求判断
     * 
     * @param mixed $input
     * @return mixed
     */
    public static function attackCheck($input)
    {
        $inputCheck = is_array($input) ? implode($input) : $input;
        // post 过滤规则 来自 360safe
        $postFilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
		if (preg_match("/" . $postFilter . "/is", $inputCheck)) {
            throw new \InvalidArgumentException('Illegal argument!');
        }
        return $input;
    }

}
