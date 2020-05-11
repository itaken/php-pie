<?php

namespace ItakenPHPie\encrypt;

use ItakenPHPie\config\ConfigPie;

/**
 * 密码
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-11
 */
final class PasswordPie
{
    /**
     * 是否简单弱密码
     * 
     * @param string $text
     * @return bool
     */
    public static function isWeakPsw($text)
    {
        if(empty($text) || strlen($text) < 5){
            return true;
        }
        $pswConfig = ConfigPie::get('basic/weakpsw');
        foreach($pswConfig as $psw){
            if($text == $psw){
                return true;
            }
        }
        return false;
    }

}