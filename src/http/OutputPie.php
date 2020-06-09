<?php

namespace ItakenPHPie\http;

/**
 * 输出
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2014-6-10
 */
final class OutputPie
{

    /**
     * 是否 GET 提交
     *
     * @return boolean - true 是
     */
    public static function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }

    /**
     * 是否 POST 提交
     *
     * @return boolean - true 是
     */
    public static function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    /**
     * 自定义 json 返回
     *
     * @param mixed $data 返回的数据
     * @param string $message 提示信息
     * @param int $status 状态
     * @return void
     */
    public static function jsonOutput($data, $message = '', $status = 200)
    {
        $return_arr = [
            'data' => $data,
            'message' => $message,
            'code' => $status
        ];
        header('Content-Type:application/json; charset=utf-8');  // 定义返回格式
        header('Cache-Control:Output no-cache, must-revalidate');
        header('Pragma: no-cache');  // 不缓存
        header('Expires: 0');
        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
        // preg_replace('#\":\s*(null|false)#iUs', '":""', json_encode($return_arr));
        exit();
    }

    /**
     * json文件缓存 输出
     *
     * @param string $file
     * @return void
     */
    public static function jsonFileOutput($file)
    {
        if (!file_exists($file)) {
            return false;
        }
        header('Content-Type:application/json; charset=utf-8');  // 定义返回格式
        header('Cache-Control: public, must-revalidate');
        $expires = gmdate('l d F Y H:i:s', time() + 5 * 60) . ' GMT';
        header('Expires:' . $expires);
        ob_start();
        ob_implicit_flush(false);  // 打开/关闭绝对刷送
        require($file);  // 引入模板
    //    $cache_data = ob_get_contents();
        ob_end_flush();
        //    var_dump($cache_data);
        exit;
    }

    /**
     * 自定义 json 输出
     *
     * @param mixed $json 返回的数据
     * @return void
     */
    public static function jsonEcho($json)
    {
        $json = !is_string($json) ? json_encode($json, JSON_UNESCAPED_UNICODE) : $json;
        header('Content-Type:application/json; charset=utf-8');  // 定义返回格式
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');  // 不缓存
        header('Expires: 0');
        echo $json;
        exit();
    }
    
    /**
     * 自定义 js 输出
     *
     * @param string $js 输出的 js 数据
     * @return void
     */
    public static function jsEcho($js)
    {
        header('Content-Type:application/javascript; charset=utf-8');  // 定义返回格式
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');  // 不缓存
        header('Expires: 0');
        exit($js);
    }

    /**
     * 推送缓存内容
     *
     * @param string $contents 输出内容
     * @return void
     */
    public static function cacheOutput($contents)
    {
        header('Content-Type:text/html;charset=utf-8');
        header('Cache-Control: public, must-revalidate');
        $expires = gmdate('l d F Y H:i:s', time() + 5 * 60) . ' GMT';
        header('Expires:' . $expires);
        $Etag = md5($contents);  // 设定 Etag key
        if (array_key_exists('HTTP_IF_NONE_MATCH', $_SERVER) && filter_input(INPUT_SERVER, 'HTTP_IF_NONE_MATCH') == $Etag) {
            header('HTTP/1.1 304 Not Modified');
        } else {
            header('Etag:' . $Etag);
            echo $contents;
        }
        exit;
    }
    
    /**
     * URL 重定向
     *
     * @param string $url 跳转URL
     * @param string $msg  跳转信息
     * @param int $time  跳转四件
     * @return void
     */
    public static function redirect($url, $msg = '', $time = 0)
    {
        $url = trim($url);
        if (empty($url)) {
            $url = '/';
        }
        if (empty($msg)) {
            $msg = "系统将在{$time}秒之后自动跳转到{$url}！";
        }
        if (!headers_sent()) {
            if (0 === $time) {
                header('Location: ' . $url);
            } else {
                header("refresh:{$time};url={$url}");
                echo($msg);
            }
            exit();
        } else {
            $str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
            if ($time != 0) {
                $str .= $msg;
            }
            exit($str);
        }
    }
 
    /**
     * URL alert提示后 重定向
     *
     * @param string $url 跳转URL
     * @param string $msg  alert提示信息
     * @return void
     * @author marco
     */
    public static function alertRedirect($url, $msg = '页面即将跳转...')
    {
        $url = trim($url);
        if (empty($url)) {
            $url = '/';
        }
        $str = '<!DOCTYPE html>
			<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
			</head>
			<body>
			<script language="JavaScript">
			<!--
				alert("' . $msg . '");
				window.location.href="' . $url . '";
			//-->
			</script>
			</body>
			</html>
			';
        exit($str);
    }

    /**
     * 页面打印内容
     *
     * @param	string	$message
     * @param 	int		$statusCode
     * @return	string
     */
    public static function showMessage($message, $statusCode = 200)
    {
        $message = '<p>'. (is_array($message) ? implode('</p><p>', $message) : $message).'</p>';

        // 设置状态码
        \http_response_code($statusCode);
        if (ob_get_level() > 1) {
            ob_end_flush();
        }

        ob_start();
        echo $message;
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }

}
