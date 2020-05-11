<?php

namespace ItakenPHPie\http;

/**
 * 导出
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-11
 */
final class ExportPie
{
    /**
     * 导出 JSON文件
     *
     * @param mixed $data  内容
     * @param string $filename 文件名称
     * @return void
     */
    public static function exportJson($data, $filename = '')
    {
        $filename = empty($filename) ? date('Ymd-His') . '.json' : (strcasecmp(strrchr($filename, '.'), '.json') === 0 ? $filename : $filename . '.json');
        header('Content-Type:application/json; charset=utf-8');
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        exit(json_encode($data));
    }

    /**
     * 导出 CSV文件
     *
     * @param string|array $data  内容 ( 字符串 或 二维数组 )
     * @param string $title 文件标题  ( 如: 你好,中国,你好,世界 )
     * @param string $filename 文件名称
     * @param boolean|string $conv 是否转换编码 ( 值为string时,表示转换编码名称 )
     * @return void
     */
    public static function exportCsv($data, $title = '', $filename = '', $conv = false)
    {
        $filename = empty($filename) ? date('Ymd-His') . '.csv' : (strcasecmp(strrchr($filename, '.'), '.csv') === 0 ? $filename : $filename . '.csv');
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        // Header('Content-Type: application/octet-stream;charset=utf-8');
        // Header("Accept-Ranges:   bytes");
        // Header('Content-disposition: attachment; filename='.$filename);
        if (empty($data)) {
            exit($title);  // 空内容, 只输出标题
        }
        $str = empty($title) ? '' : $title . "\n";
        if (is_array($data) || is_object($data)) {
            foreach ($data as $value) {
                $str .= is_string($value) ? $value : implode(',', (array) $value);
                $str .= "\n";
            }
        } else {
            $str .= $data;
        }
        if (!empty($str) && $conv !== false) {
            $charset = is_string($conv) ? $conv : 'GBK';
            $str = mb_convert_encoding($str, $charset, 'utf-8');
        }
        exit($str);
    }

    /**
     * PHP-HTTP断点续传实现
     * 
     * @param string $path 文件所在路径
     * @param string $file 文件名
     * @return void
     */
    public static function download($path, $file)
    {
        $realFilePath = $path . '/' . $file;
        if (!file_exists($realFilePath)) {
            return false;
        }
        $size = filesize($realFilePath);
        $size2 = $size - 1;
        $range = 0;
        if (isset($_SERVER['HTTP_RANGE'])) {
            header('HTTP /1.1 206 Partial Content');
            $range = str_replace('=', '-', $_SERVER['HTTP_RANGE']);
            $range = explode('-', $range);
            $range = trim($range[1]);
            header('Content-Length:' . $size);
            header('Content-Range: bytes ' . $range . '-' . $size2 . '/' . $size);
        } else {
            header('Content-Length:' . $size);
            header('Content-Range: bytes 0-' . $size2 . '/' . $size);
        }
        header('Accenpt-Ranges: bytes');
        header('application/octet-stream');
        header("Cache-control: public");
        header("Pragma: public");
        //解决在IE中下载时中文乱码问题
        $ua = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/MSIE/', $ua)) {
            $ie_filename = str_replace('+', '%20', urlencode($file));
            header('Content-Dispositon:attachment; filename=' . $ie_filename);
        } else {
            header('Content-Dispositon:attachment; filename=' . $file);
        }
        $fp = fopen($realFilePath, 'rb+');
        fseek($fp, $range);
        while (!feof($fp)) {
            set_time_limit(0);
            print(fread($fp, 1024));
            flush();
            ob_flush();
        }
        fclose($fp);
    }
}
