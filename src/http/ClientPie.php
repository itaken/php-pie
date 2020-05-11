<?php

namespace ItakenPHPie\http;

use ItakenPHPie\http\CurlPie;

/**
 * 链接
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-11
 */
final class ClientPie
{
    /**
     * 使用 SOAP 获取数据
     *
     * @param string $url
     * @param array|null $param
     * @return object
     */
    public static function getSoapResult($url, $param=[])
    {
        if (!CurlPie::isUrl($url)) {
            return null;
        }
        $soap = new \SoapClient($url);   // 使用 SOAP 获取数据
        return $soap->__call('GetBindFlag', $param);
    }

    /**
     * 获取 MAC 地址
     *
     * @param string $osType  服务器类型
     * @return string
     */
    public static function getMacAddr($osType = PHP_OS)
    {
        $configArr = [];
        switch (strtolower($osType)) {
            case "linux":
            case "solaris":
            case "unix":
                @exec("ifconfig -a", $configArr);
                break;
            case "aix":
                break;
            default:
                // windows
                @exec("ipconfig /all", $configArr);
                if (empty($configArr)) {
                    $ipconfig = $_SERVER['WINDIR'] . '\system32\ipconfig.exe';
                    if (file_exists($ipconfig)) {
                        @exec($ipconfig . " /all", $configArr);
                    } else {
                        @exec($_SERVER['WINDIR'] . '\system\ipconfig.exe /all', $configArr);
                    }
                }
                break;
        }
        if (empty($configArr) || !is_array($configArr)) {
            return false;
        }
        $matched = [];
        foreach ($configArr as $config) {
            if (preg_match('/([0-9a-f]{2}-?){6}/i', $config, $matched)) {
                return $matched[0];  // 第一个物理地址即为网卡MAC
            }
        }
        return false;
    }

    /**
     * 获取 机器信息
     * ru_oublock: 块输出操作
     * ru_inblock: 块输入操作
     * ru_msgsnd: 发送的message
     * ru_msgrcv: 收到的message
     * ru_maxrss: 最大驻留集大小
     * ru_ixrss: 全部共享内存大小
     * ru_idrss:全部非共享内存大小
     * ru_minflt: 页回收
     * ru_majflt: 页失效
     * ru_nsignals: 收到的信号
     * ru_nvcsw: 主动上下文切换
     * ru_nivcsw: 被动上下文切换
     * ru_nswap: 交换区
     * ru_utime.tv_usec: 用户态时间 (microseconds)
     * ru_utime.tv_sec: 用户态时间(seconds)
     * ru_stime.tv_usec: 系统内核时间 (microseconds)
     * ru_stime.tv_sec: 系统内核时间?(seconds)
     *
     * @return array
     */
    public static function getClientInfo()
    {
        return [
            // 输出 内存使用信息
            'memory_usage' => memory_get_usage() . ' bytes',   // 564160 bytes
            // 输出 内存 峰值
            'memory_peak_usage' => memory_get_peak_usage() . ' bytes',  // 564160 bytes
            // 输出CPU使用情况,win下不可用
            'cup' => getrusage(),
        ];
    }
}
