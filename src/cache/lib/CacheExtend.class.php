<?php

namespace ItakenPHPie\cache\lib;

/**
 * 抽象接口
 * 
 * @author itaken<regelhh@gmail.com>
 * @since 2020-06-09
 */
abstract class CacheExtend implements CacheInterface
{
    /**
     * @var array 参数
     */
    protected $options = [];

    /**
     * @var object 链接对象
     */
    protected $handler;

    /**
     * 生成缓存key
     * 
     * @param string $name
     * @return string
     */
    protected function genCacheKey($name)
    {
        return $this->options['prefix'] . $name;
    }

    /**
     * 写入缓存
     * 
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = 0)
    {
        if (is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        //对数组/对象数据进行缓存处理，保证数据完整性
        $value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;

        return $this->handler->set($this->genCacheKey($name), $value, $expire);
    }

    /**
     * 读取缓存
     * 
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name)
    {
        $value = $this->handler->get($this->genCacheKey($name));
        $jsonData  = json_decode($value, true);

        return ($jsonData === null) ? $value : $jsonData;	//检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
    }

    /**
     * 删除缓存
     * 
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function delete($name)
    {
        return $this->handler->delete($this->genCacheKey($name));
    }
}
