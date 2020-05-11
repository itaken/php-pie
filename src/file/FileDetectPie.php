<?php

namespace ItakenPHPie\file;

use ItakenPHPie\config\ConfigPie;

/**
 * 文件处理
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2019-8-9
 */
final class FileDetectPie
{
    /**
     * 检测文件的 MIME 类型
     *
     * @param string $filename 文件路径
     * @return array
     */
    public static function detectMIME($filename)
    {
        if (empty($filename)) {
            return '';
        }
        // 1.使用内部方法 see: https://www.php.net/manual/zh/function.mime-content-type.php
        $type = \mime_content_type($filename);
        if ($type) {
            return $type;
        }
        // 2.另一种方式
        $finfo = finfo_open(FILEINFO_MIME);
        if (!empty($finfo)) {
            // finfo_file return information of a file
            $type = \finfo_file($finfo, $filename);
            \finfo_close($finfo);  // 关闭资源
            return $type;
        }
        // 3.直接读取文件的前4个字节，根据硬编码判断
        $file = \fopen($filename, "rb");
        $bin = \fread($file, 4); //只读文件头4字节
        \fclose($file);
        $strInfo = @unpack("C4chars", $bin);
        //dechex() 函数把十进制转换为十六进制。
        $typeCode = dechex($strInfo ['chars1']) . dechex($strInfo ['chars2']) . dechex($strInfo ['chars3']) .dechex($strInfo ['chars4']);
        $type = '';
        switch ($typeCode) { // 硬编码值查表
            case "504b34":
                $type = 'application/zip; charset=binary';
                break;
            case "d0cf11e0":
                $type = 'application/vnd.ms-office; charset=binary';
                break;
            case "25504446":
                $type = 'application/pdf; charset=binary';
                break;
            default:
                $type = '';
                break;
        }
        return $type;
    }

    /**
     * 根据文件类型获取文件后缀
     * @see https://www.garykessler.net/library/file_sigs.html
     *
     * @param string $type  MIME名称
     * @param string
     */
    private static function getFileExtByType($type)
    {
        if (empty($type)) {
            return '';
        }
        $typeMap = ConfigPie::get('mimes.conf.php');
        foreach ($typeMap as $ext => $mime) {
            if (strpos($type, $mime) !== false) {
                return $ext;
            }
        }
        return '';
    }

    /**
     * 检测文件 真实后缀名
     *
     * @param string $file 文件路径
     * @return string
     */
    public static function detectFileRealExt($file)
    {
        if(is_string($file) && !file_exists($file)){
            return '';
        }
        $type = self::detectMIME($file);
        return self::getFileExtByType($type);
    }
}
