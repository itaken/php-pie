<?php

namespace ItakenPHPie\file;

use ItakenPHPie\file\lib\RemoveComments;

/**
 * 文件操作
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-10
 */
final class SectionPie
{
    /**
     * 文件 行数计算
     *
     * @param string $file 文件路径
     * @param string $delimiter 分隔符
     * @return int|false
     */
    public static function rowsCount($file, $delimiter = PHP_EOL)
    {
        if (!file_exists($file) || !is_readable($file)) {
            return false;
        }
        $line = 0;
        $handle = @fopen($file, 'r');
        //获取文件的一行内容，注意：需要php5才支持该函数
        while (\stream_get_line($handle, 10000, $delimiter)) {
            $line++;  // 行数叠加
        }
        if ($line <= 1) {  // 当查询的结果不是所期望的
            $line = 0;
            rewind($handle); // 重置指针
            while (\stream_get_line($handle, 10000, ' ')) {
                $line++;
            }
        }
        fclose($handle);
        return $line;
    }

    /**
     * 获取 部分日志
     *
     * @param string $file 日志文件
     * @param string $delimiter 分隔符
     * @param int $count 返回的行数 ( 从后往前取 )
     * @return array
     */
    public static function getSectionList($file, $count = 50, $delimiter = PHP_EOL)
    {
        if (!file_exists($file) || $count < 1) {
            return [];
        }
        $line = self::rowsCount($file, $delimiter);
        if ($line < 1) {
            return [];
        }
        $fp = new \SplFileObject($file, 'r');
        $count = $count * 1 < 1 ? 10 : intval($count);
        $start = $line - $count - 1;  // 起始行数
        $start = $start < 0 ? 0 : $start;
        $fp->seek($start); // 转到第N行, seek方法参数从0开始计数
        $log = [];
        for ($i = 0; $i < $count; ++$i) {
            $content = $fp->current(); // current()获取当前行内容
            if (empty($content)) {
                continue;
            }
            $log[] = trim($content);
            $fp->next(); // 下一行
            unset($content);
        }
        return ['total' => $line, 'list' => $log];
    }

    /**
     * 移除文件注释
     * 
     * @param string $path 文件或目录
     * @param string $saveFolder 保存的目录
     * @return bool
     */
    public static function removeComments($path, $saveFolder=null)
    {
        return (new RemoveComments($path, $saveFolder))->do_remove();
    }
}
