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

    /**
     * 人民币数字小写转大写
     * @doc https://segmentfault.com/a/1190000022618887
     *
     * @param string $number 人民币数值
     * @param string $int_unit 币种单位，默认"元"，有的需求可能为"圆"
     * @param bool $is_round 是否对小数进行四舍五入
     * @param bool $is_extra_zero 是否对整数部分以0结尾，小数存在的数字附加0,比如1960.30
     * @return string
     */
    public static function rmbFormat($money = 0, $int_unit = '元', $is_round = true, $is_extra_zero = false)
    {
        // 非数字，原样返回
        if (!is_numeric($money)) {
            return $money;
        }
        // 将数字切分成两段
        $parts = explode('.', $money, 2);
        $int = isset($parts[0]) ? strval($parts[0]) : '0';
        $dec = isset($parts[1]) ? strval($parts[1]) : '';
        // 如果小数点后多于2位，不四舍五入就直接截，否则就处理
        $dec_len = strlen($dec);
        if (isset($parts[1]) && $dec_len > 2) {
            $dec = $is_round ? substr(strrchr(strval(round(floatval("0." . $dec), 2)), '.'), 1) : substr($parts [1], 0, 2);
        }
        // 当number为0.001时，小数点后的金额为0元
        if (empty($int) && empty($dec)) {
            return '零';
        }
        // 定义
        $chs = ['0', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖'];
        $uni = ['', '拾', '佰', '仟'];
        $dec_uni = ['角', '分'];
        $exp = ['', '万'];
        $res = '';
        // 整数部分从右向左找
        for ($i = strlen($int) - 1, $k = 0; $i >= 0; $k++) {
            $str = '';
            // 按照中文读写习惯，每4个字为一段进行转化，i一直在减
            for ($j = 0; $j < 4 && $i >= 0; $j++, $i--) {
                // 非0的数字后面添加单位
                $u = $int[$i] > 0 ? $uni [$j] : '';
                $str = $chs [$int[$i]] . $u . $str;
            }
            // 去掉末尾的0
            $str = rtrim($str, '0');
            // 替换多个连续的0
            $str = preg_replace("/0+/", "零", $str);
            if (!isset($exp [$k])) {
                // 构建单位
                $exp [$k] = $exp [$k - 2] . '亿';
            }
            $u2 = $str != '' ? $exp [$k] : '';
            $res = $str . $u2 . $res;
        }
        // 如果小数部分处理完之后是00，需要处理下
        $dec = rtrim($dec, '0');
        // 小数部分从左向右找
        if (!empty($dec)) {
            $res .= $int_unit;
            // 是否要在整数部分以0结尾的数字后附加0，有的系统有这要求
            if ($is_extra_zero) {
                if (substr($int, -1) === '0') {
                    $res .= '零';
                }
            }
            for ($i = 0, $cnt = strlen($dec); $i < $cnt; $i++) {
                // 非0的数字后面添加单位
                $u = $dec[$i] > 0 ? $dec_uni [$i] : '';
                $res .= $chs [$dec[$i]] . $u;
                if ($cnt == 1)
                    $res .= '整';
            }
            // 去掉末尾的0
            $res = rtrim($res, '0');
            // 替换多个连续的0
            $res = preg_replace("/0+/", "零", $res);
        } else {
            $res .= $int_unit . '整';
        }
        return $res;
    }

}