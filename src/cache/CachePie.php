<?php

namespace ItakenPHPie\cache;

use ItakenPHPie\config\ConfigPie;

/**
 * 缓存
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-06-09
 */
final class CachePie
{
    /**
     * 实例化
     *
     * @param string $type
     * @return object
     */
    private static function getInstance($type='')
    {
        static $iCacheInstance = [];
        if (!isset($iCacheInstance[$type])) {
            $cacheType = $type;
            if (empty($type)) {
                $config = ConfigPie::loadEnv(null);
                $cacheType = ucfirst($config['CACHE_TYPE']) ?: '';
            }
            $class = __NAMESPACE__ . '\\lib\\' . $cacheType;
            if (!class_exists($class)) {
                throw new \RuntimeException("Not support cache type: {$cacheType}");
            }
            $iCacheInstance[$type] = new $class;
        }
        return $iCacheInstance[$type];
    }

    /**
     * 写入缓存
     *
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire 有效时间（秒）
     * @return boolean
     */
    public static function set($name, $value, int $expire=0)
    {
        return self::getInstance()->set($name, $value, $expire);
    }

    /**
     * 写入缓存
     *
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire 有效时间（秒）
     * @return boolean
     */
    public static function save($name, $value, int $expire=0)
    {
        return self::set($name, $value, $expire);
    }

    /**
     * 读取缓存
     *
     * @param string $name 缓存变量名
     * @return mixed
     */
    public static function get($name)
    {
        return self::getInstance()->get($name);
    }

    /**
     * 删除缓存
     *
     * @param string $name 缓存变量名
     * @return boolean
     */
    public static function delete($name)
    {
        return self::getInstance()->delete($name);
    }

    /**
     * 其他操作(直接操作类库)
     *
     * @param string $name 方法
     * @param mixed $arguments 参数
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $call = self::getInstance();
        if (method_exists($call, $name)) {
            return call_user_func_array([$call, $name,], $arguments);
        }
        return false;
    }
}
