<?php

namespace ItakenPHPie\cache\lib;

/**
 * 文件缓存
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-06-10
 */
final class File implements CacheInterface
{
    /**
     * @var string 缓存KEY
     */
    const ITAKEN_KEY = '#itaken-cache:)-4@_encrypt';

    /**
     * 生成缓存key
     * 
     * @param string $key
     * @param string $path
     * @return string
     */
    protected function genCacheKey(string $key='', $path=null)
    {
        $name = md5(__METHOD__ . $key);
        $path = $path ?: dirname(__DIR__) . '/tmp/' . substr($name, 0, 2);  // 路径
        if (!is_dir($path)) {
            try {
                mkdir($path, 0755, true);
            } catch (\Exception $e) {
                throw new \RuntimeException("Mkdir folder failed: {$path}");
            }
        }
        return "{$path}/{$name}.cache";  // 文件
    }

    /**
     * 写入缓存
     * 
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire 有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, int $expire=0)
    {
        if(empty($name)){
            return false;
        }
        $file = $this->genCacheKey($name);
        // 设定缓存时间
        $time = $expire > 0 ? $expire + time() : 0;
        $jsonData = json_encode($value, JSON_UNESCAPED_UNICODE);
		$data = $time . '|' . md5($jsonData . self::ITAKEN_KEY) . PHP_EOL . $jsonData;
		return file_put_contents($file, $data, LOCK_EX);  // 独占锁
    }

    /**
     * 读取缓存
     * 
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name)
    {
        if (empty($name)) {
			return false;
		}
		$file = $this->genCacheKey($name);
		if (!file_exists($file)) {
			// 文件不存在
			return false;
		}
		$fileSize = filesize($file);  // 获取文件大小
		if ($fileSize <= 0) {
			unlink($file); // 删除文件
			return false;
		}
		$handle = fopen($file, 'r');  // 只读方式打开
		if (empty($handle)) {
			// 文件打开失败
			return false;
		}
		flock($handle, LOCK_SH);  // 共享锁
		$lineOne = fgets($handle, 100);  // 获取第一行 ( 缓存时间 )
        $lineArr = explode('|', $lineOne);
        $cacheTime = intval($lineArr[0]);  // 缓存时间
		if ($cacheTime > 0 && $cacheTime < time()) {
			// 设定缓存时间，并已过期
			flock($handle, LOCK_UN);  // 解锁
			fclose($handle);
			unlink($file);
			return false;
		}
		$data = fread($handle, $fileSize);  // 获取缓存内容
		flock($handle, LOCK_UN);  // 解锁
		fclose($handle);  // 关闭 文件
		if (trim($lineArr[1]) == md5($data . self::ITAKEN_KEY)) {
			return json_decode($data, true);
		}
		unlink($file);
		return false;
    }

    /**
     * 删除缓存
     * 
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function delete($name)
    {
        if(empty($name)){
            return false;
        }
        $file = $this->genCacheKey($name);
        return unlink($file);
    }

}
