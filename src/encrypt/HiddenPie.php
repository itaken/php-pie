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
     * 隐藏手机号中间 5位数 为*
     *
     * @param string $mobile 正常手机号
     * @return mixed 示例：138*****000
     */
    public static function phoneHide($mobile)
    {
        return substr($mobile, 0, 3) . '*****' . substr($mobile, -3);
        // return substr_replace($mobile, '*****', 3, 5);
    }

    /**
     * 隐藏昵称
     *
     * @param string $name 昵称
     * @return mixed
     */
    public static function nameHide($name)
    {
        if (preg_match('#[a-zA-Z]#', $name)) {
            // $name = mb_substr($name, 0, 3, 'UTF-8') . '****';
            $name = substr($name, 0, 1) . '**' . substr($name, -1);
        } else {
            $name = mb_substr($name, 0, 1, 'UTF-8') . '**';
        }
        return $name;
    }

    /**
     * 隐藏邮箱
     *
     * @param string $email 邮箱
     * @return mixed
     */
    public static function emailHide($email)
    {
        $emailArr = explode('@', $email);
        return substr($emailArr[0], 0, 2) . '***@' . $emailArr[1];
    }

}
