<?php

namespace ItakenPHPie\text;

use ItakenPHPie\config\ConfigPie;

/**
 * 字符串
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2019-3-14
 */
final class CharsetPie
{

    /**
     * 获取配置
     *
     * @return array
     */
    public static function getCharacterConfig()
    {
        return ConfigPie::get('basic/characters');
    }

    /**
     * 转为英文字符
     *
     * @param string $text
     * @return string
     */
    public static function toAlphabet($text)
    {
        if (empty($text) || !is_string($text)) {
            return $text;
        }
        $config = self::getCharacterConfig();
        foreach ($config as $key => $val) {
            $text = preg_replace($key, $val, $text);
        }
        return $text;
    }
}
