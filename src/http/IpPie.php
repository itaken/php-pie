<?php

namespace ItakenPHPie\http;

use ItakenPHPie\config\ConfigPie;
use ItakenPHPie\http\CurlPie;

/**
 * IP
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-11
 */
final class IpPie
{
    /**
     * 获取 客户端IP
     *
     * @return string
     */
    public static function getOnlineIp()
    {
        static $clientIp = null;
        if (!is_null($clientIp)) {
            return $clientIp;
        }
        if (!empty($_SERVER['HTTP_WL_PROXY_CLIENT_IP'])) {
            $clientIp	=	$_SERVER['HTTP_WL_PROXY_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $clientIp	=	$_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $clientIp	=	$_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $clientIp	=	$_SERVER['REMOTE_ADDR'];
        }
        if (strpos($clientIp, ',')) {
            $ip_arr	= explode(',', $clientIp);
            $clientIp = trim($ip_arr[0]);
        }
        // 验证ip的格式正确性
        if (!preg_match('#^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$#', $clientIp)) {
            $clientIp = '8.8.8.8';
        }
        return $clientIp ?: '';
    }

    /**
     * IP 地址 查询
     *
     * @param string $ip IP
     * @return array
     */
    public static function queryIpAddress($ip)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        $useless = ['127.0.0.1', '0.0.0.0', '255.255.255.255', '192.168.*.*'];  // 本地IP 或 保留IP
        $quote = function (&$str) {
            $str = preg_quote($str);
        };
        array_walk($useless, $quote);
        if (preg_match('/' . str_replace('\*', '\d+', implode('|', $useless)) . '/', $ip)) { // 无效 IP
            return ['address' => '广州', 'point' => ['x' => '113.31542', 'y' => '23.125036'], ];   //为本地IP的时候直接设置为一个经纬度
        }
        // 百度地图接口 地址
        $url = 'http://api.map.baidu.com/location/ip?';
        $apiRes = CurlPie::get($url, [
            'ak' => ConfigPie::loadEnv('BAIDU_AK'),
            'ip' => $ip,
            'coor' => 'bd09ll',
        ]);
        if (empty($apiRes)) {
            return false;
        }
        $array_res = json_decode($apiRes, true);
        return isset($array_res['content']) ? $array_res['content'] : false;
    }
}
