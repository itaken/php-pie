<?php

namespace ItakenPHPie\cache;

/**
 * 文件缓存
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-11
 */
final class FileCachePie
{
    /**
     * 写文件
     *
     * @param mixed $data 内容
     * @param string $key 文件名
     * @return mixed
     */
    public static function cache($data, string $key='', $path=null)
    {
        $path = $path ?: __DIR__ . '/tmp/';  // 路径
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $file = $path . '/' .  md5(__CLASS__ . $key) . '.log';  // 文件
        if (is_null($data)) {
            if (!file_exists($file)) {
                return false;
            }
            $contents = file_get_contents($file);  // 获取缓存内容
            return json_decode($contents, true);
        }
        // 写入数据
        return file_put_contents($file, json_encode($data), LOCK_EX);
    }
}
