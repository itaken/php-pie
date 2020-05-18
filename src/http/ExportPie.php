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


    /**
     * 导出excel表格数据
     * @doc https://segmentfault.com/a/1190000022618887
     *
     * @param array $data 表格数据，一个二维数组
     * @param array $title 第一行标题，一维数组
     * @param string $filename 下载的文件名
     * @return void
     */
    public static function exportExcel($data = [], $title = [], $filename = '')
    {
        // 默认文件名为时间戳
        if (empty($filename)) {
            $filename = time();
        }
        // 定义输出header信息
        header("Content-type:application/octet-stream;charset=GBK");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=" . $filename . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        ob_start();
        echo "<head><meta http-equiv='Content-type' content='text/html;charset=GBK' /></head> <table border=1 style='text-align:center'>\n";
        // 导出xls开始，先写表头
        if (!empty($title)) {
            foreach ($title as $k => $v) {
                $title[$k] = iconv("UTF-8", "GBK//IGNORE", $v);
            }
            $title = "<td>" . implode("</td>\t<td>", $title) . "</td>";
            echo "<tr>$title</tr>\n";
        }
        // 再写表数据
        if (!empty($data)) {
            foreach ($data as $key => $val) {
                foreach ($val as $ck => $cv) {
                    if (is_numeric($cv) && strlen($cv) < 12) {
                        $data[$key][$ck] = '<td>' . mb_convert_encoding($cv, "GBK", "UTF-8") . "</td>";
                    } else {
                        $data[$key][$ck] = '<td style="vnd.ms-excel.numberformat:@;">' . iconv("UTF-8", "GBK//IGNORE", $cv) . "</td>";
                    }
                }
                $data[$key] = "<tr>" . implode("\t", $data[$key]) . "</tr>";
            }
            echo implode("\n", $data);
        }
        echo "</table>";
        ob_flush();
        exit;
    }
}
