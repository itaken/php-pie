<?php

namespace ItakenPHPie\file;

/**
 * 输出
 * @doc https://segmentfault.com/a/1190000022515502
 *
 * @modify itaken<regelhh@gmail.com>
 * @since 2020-05-18
 */
final class StreamPie
{

    /**
     * php 发送流文件
     *
     * @param  string  $url  接收的路径
     * @param  string  $file 要发送的文件
     * @return boolean
     */
    public static function sendFile($url, $file)
    {
        if (file_exists($file)) {
            return false;
        }
        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'content-type:application/x-www-form-urlencoded',
                'content' => file_get_contents($file),
                'timeout' => 30, // 超时(单位秒)
            )
        );
        $context = stream_context_create($opts);
        $response = file_get_contents($url, false, $context);
        $ret = json_decode($response, true);
        return $ret['success'];
    }

    /**
     * php 接收流文件
     *
     * @param string  $file 接收后保存的文件名
     * @return boolean
     */
    public static function receiveFile($receiveFile)
    {
        $streamData = $GLOBALS['HTTP_RAW_POST_DATA'] ?: '';
        if (empty($streamData)) {
            $streamData = file_get_contents('php://input');
        }
        if ($streamData!='') {
            $ret = file_put_contents($receiveFile, $streamData, true);
        } else {
            $ret = false;
        }
        return $ret;
    }

    /**
     * 上传图片
     *
     * @param string $path 保存路径：/path/to/save
     * @param string $name 文件名：abc.jpg
     * @return false|array
     */
    public static function upload($path, $name = '')
    {
        $upload_files = current($_FILES);
        if (empty($upload_files)) {
            return false;
        }
        if (!is_uploaded_file($upload_files['tmp_name'])) {
            return false;
        }
        $md5 = md5_file($upload_files['tmp_name']);
        $name = empty($name) ? $md5 . '.jpg' : $name;  // 文件名
        $file = $path . (strpos($name, '.') < 1 ? $name . '.jpg' : $name); // 保存的文件路径
        if (file_exists($file)) {
            return array(
                'file_name' => $name,
                'size' => filesize($file),
                'md5' => $md5,
                'file' => $file,
            );
        }
        //	$size = $upload_files['size'];  // 文件大小
        //	$type = $upload_files['type'];  // 文件类型
        if (!move_uploaded_file($upload_files['tmp_name'], $file)) {
            return false;
        }
        return array(
            'source_name' => $upload_files['name'],
            'file_name' => $name,
            'size' => $upload_files['size'],
            'md5' => $md5,
            'file' => $file,
        );
    }
}
