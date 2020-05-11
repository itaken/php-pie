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
     * 判断 URL 是否有效
     *
     * @param string $url 传入的链接
     * @return string
     */
    public static function isUrlAvailable($url)
    {
        if (!CurlPie::isUrl($url)) {
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
