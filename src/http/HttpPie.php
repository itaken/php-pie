<?php

namespace ItakenPHPie\http;

use ItakenPHPie\http\CurlPie;

/**
 * http
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-06-07
 */
final class HttpPie
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
     * 判断是否HTTPS
     *
     * @return boolean -true 是https
     */
    public static function isHttps()
    {
        if (isset($_SERVER['HTTPS'])) {
            return ('on' == strtolower($_SERVER['HTTPS']) || 1 == $_SERVER['HTTPS']);
        }
        // 通过附加协议
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            return 'https' == strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_SERVER_PROTOCOL'])) {
            return 'https' == strtolower($_SERVER['HTTP_X_FORWARDED_SERVER_PROTOCOL']);
        }
        if(isset($_SERVER['HTTP_FRONT_END_HTTPS'])){
            return strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off';
        }
        // 通过请求头
        if (isset($_SERVER['REQUEST_SCHEME'])) {
            return 'https' == strtolower($_SERVER['REQUEST_SCHEME']);
        }
        // 通过端口号
        if (isset($_SERVER['SERVER_PORT'])) {
            return '443' == $_SERVER['SERVER_PORT'];
        }
        return false;
    }

    /**
     * 判断是否ajax请求
     * @deprecated
     *
     * @return bool -true 是
     */
    public static function isAjax()
    {
        // 判断是否是 ajax 请求 
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                return true;
            }
        }
        return false;
    }

}