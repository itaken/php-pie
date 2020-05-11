<?php

namespace ItakenPHPie\file;

/**
 * 压缩操作
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-10
 */
final class ZipPie
{
    /**
     * zip 解压缩
     *
     * @param string $file 文件路径
     * @param string $extractPath 解压后文件存放的文件夹
     * @return bool
     */
    public static function unzip($zipFile, $extractPath)
    {
        if (!file_exists($zipFile)) {
            return false;
        }
        if (!function_exists('ZipArchive')) {
            throw new \InvalidArgumentException('ZipArchive extension NOT Found!');
        }
        $zip = new \ZipArchive();
        if ($zip->open($zipFile) !== true) {
            throw new \RuntimeException('Could not open archive');
        }
        $zip->extractTo($extractPath);
        $zip->close();
        return true;
    }

    /**
     * PHP文件 Zip 压缩
     *
     * @param array|string $files 需要压缩的文件列表
     * @param string $destination 压缩后文件名(含后缀)
     * @param boolean $overwrite 文件存在是否覆盖
     * @return bool
     */
    public static function zip($files, $destination = '', $overwrite = false)
    {
        if (file_exists($destination) && !$overwrite) {
            return false;
        }
        if (!function_exists('ZipArchive')) {
            throw new \InvalidArgumentException('ZipArchive extension NOT Found!');
        }
        $files = is_array($files) ? $files : [$files];
        $valid_files = [];
        foreach ($files as $file) {
            if (!is_string($file)) {
                continue;
            }
            if (file_exists($file)) {
                $valid_files[] = $file;
            }
        }
        if (count($valid_files) < 1) {
            return false;
        }
        $zip = new \ZipArchive();
        if ($zip->open($destination, $overwrite ? \ZIPARCHIVE::OVERWRITE : \ZIPARCHIVE::CREATE) !== true) {
            return false;
        }
        foreach ($valid_files as $file) {
            $zip->addFile($file, $file);
        }
        $zip->close();
        return file_exists($destination);
    }
}
