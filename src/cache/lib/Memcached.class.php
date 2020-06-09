<?php

namespace ItakenPHPie\cache\lib;

use ItakenPHPie\config\ConfigPie;

/**
 * Memcached 缓存驱动
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-06-09
 */
final class Memcached extends CacheExtend
{

    /**
     * 初始化
     * 
     * @param array $options 缓存参数
     */
    public function __construct(array $options=[])
    {
        if (!extension_loaded('Memcached')) {
            throw new \InvalidArgumentException('Memcached extension NOT Found!');
        }
        $config = ConfigPie::loadEnv(null);
        if (empty($options)) {
            $options = [
                'host' => $config['MEMCACHE_HOST'] ?: '127.0.0.1',
                'port' => $config['MEMCACHE_PORT'] ?: 11211,
            ];
        }
        $options['host'] = explode(',', $options['host']);
        $options['port'] = explode(',', $options['port']);
        $options['timeout'] =  $options['timeout'] ?: ($config['CACHE_TIMEOUT'] ?: 0);
        $options['expire'] =  $options['expire'] ?: ($config['CACHE_EXPIRE'] ?: 0);
        $options['prefix'] =  $options['prefix'] ?: ($config['CACHE_PREFIX'] ?: '');

        $this->options =  $options;
        // 连接
        $this->connect();
    }
    
    /**
     * 连接 memcached 服务端
     */
    private function connect()
    {
        $options = $this->options;

        $memcache = new \Memcached();
        $count = count($options['host']);
        if ($count > 1) {
            $hostArr = [];
            foreach($options['host'] as $key => $_host){
                $port = $options['port'][$key] ?: $options['port'][0];
                $hostArr[] = [$_host, $port];
            }
            $memcache->addServers($hostArr);
        }else{
            $memcache->addServer($options['host'][0], $options['port'][0]);
        }
        if ($options['timeout']) {
            $memcache->setOption(\Memcached::OPT_CONNECT_TIMEOUT, $options['timeout']);
        }
        $this->handler = $memcache;
    }

}
