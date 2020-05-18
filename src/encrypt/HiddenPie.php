<?php

namespace ItakenPHPie\encrypt;

/**
 * 一些隐藏工具
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-18
 */
final class HiddenPie
{
    /**
     * 隐藏手机号中间四位数为****
     *
     * @param string $mobile 正常手机号
     * @return mixed
     */
    public static function phoneHide($mobile)
    {
        return substr_replace($mobile, '****', 3, 4);
    }
}
