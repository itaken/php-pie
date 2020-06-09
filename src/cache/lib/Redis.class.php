<?php

namespace ItakenPHPie\cache\lib;

use ItakenPHPie\config\ConfigPie;

/**
 * Redis 缓存驱动
 * @doc https://github.com/phpredis/phpredis
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-06-09
 */
final class Redis extends CacheExtend
{
    /**
     * 初始化
     * 
     * @param array $options 缓存参数
     */
    public function __construct(array $options=[])
    {
        if (!extension_loaded('redis')) {
            throw new \InvalidArgumentException('Redis extension NOT Found!');
        }
        $config = ConfigPie::loadEnv(null);
        if (empty($options)) {
            $options = [
                'host' => $config['REDIS_HOST'] ?: '127.0.0.1',
                'port' => $config['REDIS_PORT'] ?: 6379,
                'auth' => $config['REDIS_AUTH'] ?: false,
            ];
        }
        $options['host'] = explode(',', $options['host']);
        $options['port'] = explode(',', $options['port']);
        $options['auth'] = explode(',', $options['auth']);
        $options['timeout'] =  $options['timeout'] ?: ($config['CACHE_TIMEOUT'] ?: 0);
        $options['expire'] =  $options['expire'] ?: ($config['CACHE_EXPIRE'] ?: 0);
        $options['prefix'] =  $options['prefix'] ?: ($config['CACHE_PREFIX'] ?: '');

        $this->options =  $options;
        // 连接
        $this->connect(true);
    }
    
    /**
     * 连接Redis服务端
     * 
     * @param bool $is_master : 是否连接主服务器
     */
    private function connect(bool $is_master = true)
    {
        $options = $this->options;
        $i = 0;
        if (!$is_master) {
            $count = count($options['host']);
            $i = $count == 1 ? 0 : mt_rand(0, $count -1);
        }
        $host = $options['host'][$i] ?: $options['host'][0];
        $port = $options['port'][$i] ?: $options['port'][0];
        $func = $options['persistent'] ? 'pconnect' : 'connect';

        $redis  = new \Redis;
        $redis->$func($host, $port, $options['timeout']);
        $auth = $options['auth'][$i] ?: $options['auth'][0];
        if ($auth) {
            $redis->auth($auth);
        }
        $this->handler = $redis;
    }

    /**
     * 删除缓存
     * 
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function delete($name)
    {
        return $this->handler->del($this->genCacheKey($name));
    }

    /**
     * 清除缓存
     * 
     * @return boolean
     */
    public function clear()
    {
        return $this->handler->flushDB();
    }

    /**
     * 关闭长连接
     * 
     * @return void
     */
    public function __destruct()
    {
        if ($this->options['persistent'] == 'pconnect') {
            $this->handler->close();
        }
    }
}
