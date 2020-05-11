<?php 

namespace ItakenPHPie\browser;

use ItakenPHPie\browser\lib\Mobile_Detect;

/**
 * 终端 工具类
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-10
 */
final class TerminalPie
{

    /**
     * 是否 手机 客户端类型
     *
     * @return bool
     */
    public static function isMobile():bool
    {
        static $tIsMobile = null;
        if (is_null($tIsMobile)) {
            $tIsMobile = (new Mobile_Detect)->isMobile();
        }
        return $tIsMobile;
    }

    /**
     * 是否 平板 客户端类型
     *
     * @return bool -true 是
     */
    public static function isTablet(): bool
    {
        static $tIsTablet = null;
        if (is_null($tIsTablet)) {
            $tIsTablet = (new Mobile_Detect)->isTablet();
        }
        return $tIsTablet;
    }

    /**
     * 是否 wap 客户端类型 (包含平板,手机浏览器)
     *
     * @return bool -true 是wap
     */
    public static function isWap(): bool
    {
        static $tIsWap = null;
        if (is_null($tIsWap)) {
            $tIsWap = self::isMobile() || self::isTablet();
        }
        return $tIsWap;
    }

    /**
     * 判断是否阿里小程序(支付宝小程序)
     *
     * @return bool
     */
    public static function isAliXcx(): bool
    {
        static $tIsAliXcx = null;
        if (!is_null($tIsAliXcx)) {
            return $tIsAliXcx;
        }
        $ua = $_SERVER['HTTP_USER_AGENT'];
        if (stripos($ua, "AlipayClient") !== false) {
            $tIsAliXcx = true;
            return true;
        }
        if (preg_match('#(AliApp|AlipayClient)#isU', $ua) && 
            strpos($ua, "UCBrowser") === false  // 排除uc浏览器标识
        ){
            $tIsAliXcx = true;
            return true;
        }
        $tIsAliXcx = false;
        return false;
    }

    /**
     * 判断是否爬虫引擎
     *
     * @return bool -true 是
     */
    public static function isBot(): bool
    {
        //屏蔽搜索引擎蜘蛛
        $ua = $_SERVER['HTTP_USER_AGENT'];
        $spiderIds = ['HTTrack','pider','aidusp', 'ooglebot', 'Spider','org_bot', 'Sosospider', 'bingbot', 'YoudaoBot', 'Slurp'];
        foreach ($spiderIds as $id) {
            if (strpos($ua, $id) !== false) {
                return true;
            }
        }
        return false;
    }
}