<?php

namespace ItakenPHPie\http;

use ItakenPHPie\http\CurlPie;

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
        return (filter_input(INPUT_SERVER, 'REQUEST_METHOD') == 'GET') ? true : false;
    }

    /**
     * 是否 POST 提交
     * 
     * @return boolean - true 是
     */
    public static function isPost()
    {
        return (filter_input(INPUT_SERVER, 'REQUEST_METHOD') == 'POST') ? true : false;
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
}
