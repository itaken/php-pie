<?php

namespace ItakenPHPie\config;

include('lib/Dotenv.class.php');

use ItakenPHPie\config\lib\Dotenv;

/**
 * 配置
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-11
 */
final class ConfigPie
{
    
    /**
     * 获取配置
     *
     * @param string $path 配置路径
     * @param string|int $name 配置项
     * @param mixed $params 附加参数
     * @return mixed
     */
    public static function get(string $path, $name=null, array $params=[])
    {
        if (empty($path)) {
            return null;
        }
        static $tConfigMap = [];
        // 静态缓存
        $cacheKey = $path . http_build_query($params);
        if (!isset($tConfigMap[$cacheKey])) {
            $path = str_replace(['.conf', '.php', ' '], '', $path);  // 去除空格,'.conf', '.php'
            $path = strpos($path, '/') === false ? "basic/{$path}" : $path;
            $file = __DIR__ . "/{$path}.conf.php";
            if (file_exists($file)) {
                if ($params && is_array($params)) {
                    extract($params);
                }
                $configList = require($file);  // 引用文件
            }
            $tConfigMap[$cacheKey] = $configList;
        } else {
            $configList = $tConfigMap[$cacheKey];
        }
        if (is_null($name) || false === $name || '' === $name) {
            return $configList ?: [];
        }
        $name = trim($name);  // 清除空格
        return $configList[$name] ?: null;
    }

    /**
     * 处理 .evn 配置
     * @doc https://symfony.com/doc/current/components/dotenv.html
     *
     * @param string|null $name
     * @param mixed $defaultVal 默认值
     * @param string $envFile env文件路径
     * @return array
     */
    public static function loadEnv($name=null, $defaultVal=null, string $envFile='')
    {
        static $tEnv = null;
        if (is_null($tEnv)) {
            $envFile = $envFile ?: dirname(dirname(__DIR__)) .'/.env';
            (new Dotenv())->loadEnv($envFile);
            $tEnv = $_ENV;
        }
        if (is_null($name) || $name === '' || $name === false) {
            return $tEnv;
        }
        return $tEnv[$name] ?: $defaultVal;
    }

    /**
     * 处理 .ini 配置
     * @doc https://www.php.net/manual/en/function.parse-ini-file.php
     *
     * @param string|null $name
     * @param mixed $defaultVal 默认值
     * @param bool $section 是否分块
     * @param string $iniFile ini文件路径
     * @return array
     */
    public static function loadIni($name=null, $defaultVal=null, bool $section=false, string $iniFile='')
    {
        static $tIni = null;
        if (is_null($tIni)) {
            $iniFile = $iniFile ?: dirname(dirname(__DIR__)) .'/itaken.ini';
            if(file_exists($iniFile)){
                $tIni = \parse_ini_file($iniFile, $section);
            }
        }
        if (is_null($name) || $name === '' || $name === false) {
            return $tIni;
        }
        return $tIni[$name] ?: $defaultVal;
    }

}
