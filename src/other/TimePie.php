<?php

namespace ItakenPHPie\other;

/**
 * 时间
 *
 * @modify itaken<regelhh@gmail.com>
 * @since 2020-05-10
 */
final class TimePie
{
    /**
     * 输出 GMT时间格式
     *
     * @return string
     */
    public static function getGMTTime(): string
    {
        return gmdate('l d F Y H:i:s', time() + 60) . ' GMT';
    }

    /**
     * 获取13位时间戳(毫秒)
     *
     * @return int
     */
    public static function getMsTimestamp()
    {
        return (int)sprintf('%.0f', microtime(true) * 1000);
    }

    /**
     * 判断是否周末
     *
     * @param string $time 日期/时间戳
     * @return bool
     */
    public static function isWeekend($time)
    {
        if (empty($time)) {
            return false;
        }
        $time = is_numeric($time) ? $time : strtotime($time);
        $w = date('w', $time);  // 周
        if ($w == 6 || $w == 0 || $w == 7) {
            return true;
        }
        return false;
    }

    /**
     * 判断是否是闰年
     *
     * @param int $year 2020
     * @return bool
     */
    public static function isLeapYear($year)
    {
        return ($year % 4 == 0) && ($year % 100 != 0 || $year % 400 == 0);
    }

    /**
     * 获取 星期名
     *
     * @param string $date 时间格式, 例如: 2018-6-30, 或者时间戳
     * @param bool $en
     * @return string
     */
    public static function getWeekName(string $date, bool $en= false):string
    {
        if (is_numeric($date)) {
            $time = $date;
        } else {
            $time = strtotime($date);
        }
        $week = date('w', $time);
        $weekMap = [
            '周日', '周一', '周二', '周三', '周四', '周五', '周六', '周日',
        ];
        $weekEnMap = [
            'Sunday', 'Monday', 'Tuesday', 'Wednesday','Thursday', 'Friday', 'Saturday',  'Sunday',
        ];
        return $en ? $weekEnMap[$week] : $weekMap[$week];
    }

    /**
     * 根据生日获取周岁
     *
     * @param string $birth 日期, 例如: 19971010(8位整数)
     * @param string $refer 参照时间, 例如: 20190101(默认当前)
     * @return int
     */
    public static function ageCalculation($birthday, $refer = ''): int
    {
        $birthday = str_replace(['-', '_', '.', ':', '/'], '', $birthday);
        $refer = $refer ? str_replace(['-', '_', '.', ':', '/'], '', $refer): date("Ymd");
        if (!is_numeric($birthday)|| !is_numeric($refer)) {
            return 0;
        }
        $birthday = strlen($birthday)== 4 ? $birthday . '0101' : $birthday;
        $begin_date = intval(trim($birthday));
        $end_date = intval(trim($refer));

        return floor(($end_date - $begin_date) / 10000);
    }

    /**
     * 时间计算
     *
     * 一分钟之内： 刚刚
     * 一个小时之内(大于一分钟)：xx 分钟前
     * 当天(大于一小时)： 上午 xx时xx 分，下午 xx 时 xx分
     * 其他的: xx 年 xx 月 xx日
     *
     * @param int $timestamp 时间戳
     * @return string 例如：下午10时01分
     */
    public static function timeDist($timestamp=0)
    {
        $timestamp = $timestamp ?: time();
        if (empty($timestamp) || !is_numeric($timestamp)) {
            return '未知';
        }
        $min = 60;
        $hour = $min * 60;
        $day = $hour * 24;
        $diff = time() - $timestamp;
        switch ($diff) {
            case ($diff < $min):
                $str = '刚刚';
                break;
            case ($diff < $hour):
                $str = floor($diff / $min) . ' 分钟前';
                break;
            case ($diff < $day):
                $today = date('Y-m-d');
                $todayStartStamp = strtotime($today . ' 00:00:00');
                if ($timestamp > $todayStartStamp) {  // 今天
                    $am_pm = date('a', $timestamp);
                    $apmMap = array(
                        'am' => '上午',
                        'pm' => '下午',
                    );
                    $str = $apmMap[$am_pm] . date('g时i分', $timestamp);
                } else {
                    $str = date('Y年n月d日', $timestamp);
                    // $str = floor($diff / $hour) . ' 小时前';
                }
                break;
            case ($diff >= $day):
                $str = date('Y年m月d日', $timestamp);
                break;
            default:
                $str = '公元前';
        }
        return $str;
    }

    /**
     * 格式化时间显示
     *
     * @param int $timestamp 时间戳
     * @return string 例如：今天 下午10:01
     */
    public static function timeCn($timestamp=0)
    {
        $timestamp = $timestamp ?: time();
        if (empty($timestamp) || !is_numeric($timestamp)) {
            return '未知';
        }
        $dayZ = date('z', $timestamp); // 一年中的第几天
        $todayZ = date('z');  // 今天是一年中的第几天
        $diffZ = $dayZ - $todayZ;
        switch ($diffZ) {
            case 0:
                $day_str = '今天';
                break;
            case 1:
                $day_str = '明天';
                break;
            case 2:
                $day_str = '后天';
                break;
            case -1:
                $day_str = '昨天';
                break;
            default:
                $day_str = date('Y年n月d日', $timestamp);
                break;
        }
        $am_pm = date('a', $timestamp);  // 上/下午
        $apmMap = array(
            'am' => '上午',
            'pm' => '下午',
        );
        $time_str = $day_str . ' ' . $apmMap[$am_pm] . date('g:i', $timestamp);
        return $time_str;
    }

    /**
     * 格式化时间显示
     *
     * @param int $timestamp 时间戳
     * @return string 例如：1 周前
     */
    public static function niceTime($timestamp)
    {
        $unixTime = is_numeric($timestamp) ? $timestamp : strtotime($timestamp);
        $periods = ['秒', '分', '小时', '天', '周', '月', '年', '十年'];
        $lengths = ['60','60','24','7','4.35','12','10'];
        $now = time();
        if ($now > $unixTime) {
            $difference = $now - $unixTime;
            $tense = '前';
        } else {
            $difference = $unixTime - $now;
            $tense = '后';
        }
        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
            $difference /= $lengths[$j];
        }
        $difference = round($difference);
        return "{$difference} {$periods[$j]}{$tense}";
    }
}
