<?php

namespace ItakenPHPie\cache\lib;

/**
 * 抽象接口
 * 
 * @author itaken<regelhh@gmail.com>
 * @since 2020-06-09
 */
interface CacheInterface
{
    /**
     * 写入缓存
     * 
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire 有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, int $expire=0);

    /**
     * 读取缓存
     * 
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name);

    /**
     * 删除缓存
     * 
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function delete($name);
}
