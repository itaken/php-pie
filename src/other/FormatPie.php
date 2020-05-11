<?php

namespace ItakenPHPie\other;

/**
 * 格式化
 *
 * @modify itaken<regelhh@gmail.com>
 * @since 2020-05-10
 */
final class FormatPie
{
    /**
     * 格式化商品价格
     *
     * @param   float   $price  商品价格
     * @param   int     $formatType  价钱算法   0,直接保留两位小数 ； 1：保留不为0的尾数； 2：不四舍五入，保留1位 ；3：直接取整 ； 4：四舍五入，保留 1 位； 5：先四舍五入，不保留小数
     * @param   string  $currencyFormat  显示格式 默认￥%s
     * @return  string
     */
    public static function priceFormat($price, $formatType = 0, $currencyFormat = '￥%s') {
        if(!is_numeric($price)){
            return $price;
        }        
        switch ($formatType) {
            case 0:
                $price = number_format($price, 2, '.', '');
                break;
            case 1: // 保留不为 0 的尾数
                $price = preg_replace('/(.*)(\\.)([0-9]*?)0+$/', '\1\2\3', number_format($price, 2, '.', ''));
                if (substr($price, -1) == '.') {
                    $price = substr($price, 0, -1);
                }
                break;
            case 2: // 不四舍五入，保留2位
                $price = substr(number_format($price, 2, '.', ''), 0);
                break;
            case 3: // 直接取整
                $price = intval($price);
                break;
            case 4: // 四舍五入，保留 2 位
                $price = number_format($price, 2, '.', '');
                break;
            case 5: // 先四舍五入，不保留小数
                $price = round($price);
                break;
            default:
                $price = @number_format($price, 2, '.', '');
            break;
        }
        return $currencyFormat ? sprintf($currencyFormat, $price) : $price;
    }

    /**
     * 格式化 距离
     * 
     * @param string $dis 距离
     * @return string 
     */
    public static function distanceFormat($dis)
    {
        if (!is_numeric($dis)) {
            return $dis;
        }
        if ($dis < 501) {
            return ceil($dis) . ' m';
        }
        return sprintf('%.2f', $dis / 1000) . ' km';
    }

}