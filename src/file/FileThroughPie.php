<?php

namespace ItakenPHPie\file;

/**
 * 文件遍历
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-10
 */
final class FileThroughPie
{

    /**
     * 使用scandir 遍历目录
     *
     * @param string $dir
     * @return array
     */
    public static function scanDirList($dir)
    {
        //判断目录是否为空
        if (!is_dir($dir)) {
            return [];
        }
        $files = scandir($dir);
        $fileItem = [];
        foreach ($files as $v) {
            $newDir = $dir .DIRECTORY_SEPARATOR . $v;
            if (is_dir($newDir) && !in_array($v, ['.', '..'])) {
                $fileItem = array_merge($fileItem, self::scanDirList($newDir));
            } elseif (is_file($newDir)) {
                $fileItem[] = $newDir;
            }
        }
        return $fileItem;
    }

    /**
     * 使用 opendir 遍历目录
     *
     * @param string $dir
     * @return array
     */
    public static function openDirList($dir)
    {
        //判断目录是否为空
        if (!is_dir($dir)) {
            return [];
        }
        $handle = opendir($dir);
        $fileItem = [];
        if ($handle) {
            while (($file = readdir($handle)) !== false) {
                $newDir = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($newDir) && !in_array($file, ['.', '..'])) {
                    $fileItem = array_merge($fileItem, self::openDirList($newDir));
                } elseif (is_file($newDir)) {
                    $fileItem[] = $newDir;
                }
            }
        }
        @closedir($handle);
        return$fileItem;
    }

    /**
     * 使用 迭代器 遍历目录
     *
     * @param string $dir
     * @return array
     */
    public static function recursiveDirList($dir)
    {
        //判断目录是否为空
        if (!is_dir($dir)) {
            return [];
        }
        $dir = new \RecursiveDirectoryIterator($dir);
        $fileItem = [];
        foreach (new \RecursiveIteratorIterator($dir) as $k=>$v) {
            $fileName = $v->getBaseName();
            if ($fileName != '.' && $fileName != '..') {
                $fileItem[] = $k;
            }
        }
        return $fileItem;
    }

}
