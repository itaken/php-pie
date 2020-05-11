<?php

namespace ItakenPHPie\http;

/**
 * CURL 操作类
 *
 * @editor itaken<regelhh@gmail.com>
 * @since 2018-6-22
 */
final class CurlPie
{

    /**
     * @var int 默认超时时间
     */
    private static $tTimeOut = 30;

    /**
     * @var string 默认UA
     */
    private static $tUseragent = 'CurlPie/1.0 Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.24) Gecko/20111103 Firefox/3.6.24';

    /**
     * @return array 头部信息
     */
    private static $tResponseHeader = [];

    /**
     * 判断是否URL
     *
     * @param string $url
     * @return bool
     */
    public static function isUrl($url)
    {
        // 判断链接
        if (\filter_var(str_replace('_', '-', $url), FILTER_VALIDATE_URL) === false) {
            return false;
        }
        return true;
    }

    /**
     * 执行请求
     * @doc https://www.php.net/manual/zh/ref.curl.php
     *
     * @param string $url
     * @param array $postData
     * @param int $timeout 超时时间
     * @param array $setOptArr 配置项
     * @param array $header 自定义头部
     * @param bool $postJson 使用使用json
     *
     * @return string
     */
    private static function doAction(string $url, array $postData, int $timeout = 30, array $setOptArr = [], array $header = [], bool $postJson = false)
    {
        if (empty($url) || strpos($url, 'http') !== 0) {
            return '';
        }
        // 判断链接
        if (!self::isUrl($url)) {
            return '';
        }
        $startTime = microtime(true);  // 开始时间

        if ($timeout < 1 || $timeout > 120) {
            $timeout = self::$tTimeOut;
        }
        if (!\function_exists('curl_init')) {
            throw new \Exception('ERROR: CURL extension no installed!');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, self::$tUseragent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        // POST数据
        if (sizeof($postData) > 0) {
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($postJson) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            }
        }

        // 自定义头部
        if (sizeof($header) > 0) {
            $new_header = [];
            foreach ($header as $_k => $_h) {
                if (\is_numeric($_h)) {
                    $new_header[] = $_h;
                    continue;
                }
                $new_header[] = "{$_k}:{$_h}";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $new_header);
        }

        // 额外参数 数据
        if (is_array($setOptArr) && sizeof($setOptArr) > 0) {
            foreach ($setOptArr as $key => $value) {
                curl_setopt($ch, $key, $value);
            }
        }

        // 获取错误信息
        self::$tResponseHeader = [
            'timeout' => $timeout,
            'header' => $header,
            'info' => curl_getinfo($ch),
            'error' => curl_error($ch),
            'errorNo' => curl_errno($ch),
        ];

        $content = curl_exec($ch);
        curl_close($ch);

        return $content;
    }

    /**
     * 获取相应头部信息
     *
     * @return array
     */
    public static function getResponseHeader() : array
    {
        return self::$tResponseHeader;
    }

    /**
     * GET 请求获取数据
     *
     * @param string $url
     * @param int $timeout
     * @param array $setOptArr
     * @param array $header
     *
     * @return string
     */
    public static function fetch(string $url, int $timeout = 30, array $setOptArr = [], array $header = [])
    {
        return self::doAction($url, [], $timeout, $setOptArr, $header);
    }

    /**
     * GET 请求数据 (允许使用参数)
     *
     * @param string $url
     * @param array $params
     * @param int $timeout
     * @param array $setOptArr
     * @param array $header
     *
     * @return mixed
     */
    public static function get(string $url, array $params=[], int $timeout = 30, array $setOptArr = [], array $header = [])
    {
        if ($params) {
            $url .= (strpos($url, '?') == false ? '?' : '&') . http_build_query($params);
        }
        return self::fetch($url, $timeout, $setOptArr, $header);
    }

    /**
     * POST 请求数据
     *
     * @param string $url
     * @param array $postData
     * @param int $timeout
     * @param array $setOptArr
     * @param array $header
     *
     * @return mixed
     */
    public static function post(string $url, array $postData, int $timeout = 30, array $setOptArr = [], array $header = [], bool $postJson = false)
    {
        return self::doAction($url, $postData, $timeout, $setOptArr, $header, $postJson);
    }

    /**
     * 批量获取URL内容
     *
     * @param array $urlArr
     * @param int $timeout 超时时间
     * @param int $setOptArr 设置变量
     * @return array
     */
    public static function multGet(array $urlArr, int $timeout = 10, array $setOptArr=[])
    {
        if (empty($urlArr) || !is_array($urlArr)) {
            return [];
        }

        if ($timeout < 1 || $timeout > 120) {
            $timeout = self::$tTimeOut;
        }

        $queue = curl_multi_init(); // 允许并行地处理批处理cURL句柄。
        foreach ($urlArr as $i => $url) {
            if (!self::isUrl($url)) {  // 判断链接
                continue;
            }

            $ch[$i] = curl_init();
            curl_setopt($ch[$i], CURLOPT_URL, $url);
            curl_setopt($ch[$i], CURLOPT_REFERER, $url);
            curl_setopt($ch[$i], CURLOPT_TIMEOUT, $timeout); //超时
            curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch[$i], CURLOPT_USERAGENT, self::$tUseragent);
            curl_setopt($ch[$i], CURLOPT_HEADER, 0);
            curl_setopt($ch[$i], CURLOPT_NOSIGNAL, true);
            curl_setopt($ch[$i], CURLOPT_ENCODING, 'gzip, deflate');
            // 额外参数 数据
            if (is_array($setOptArr) && sizeof($setOptArr) > 0) {
                foreach ($setOptArr as $key => $value) {
                    curl_setopt($ch[$i], $key, $value);
                }
            }
            curl_multi_add_handle($queue, $ch[$i]); // 向批处理句柄中添加句柄,后面批量的去模拟访问,抓取回资源
        }
        do {
            curl_multi_exec($queue, $active);
            curl_multi_select($queue, $timeout);
        } while ($active > 0); // 一直到资源里还有0条为止

        // 经过了刚才的循环指抓取 此时 $queue 就是一个批量的资源集了 这只是资源集,你需要把它读取出来就好
        $response = [];
        foreach ($urlArr as $key => $_) {
            if(!isset($ch[$key])){
                $response[$key] = null;
                continue;
            }
            $response[$key]['content']  = curl_multi_getcontent($ch[$key]);
            $response[$key]['header'] = curl_getinfo($ch[$key]);
            curl_close($ch[$key]);
        }
        curl_multi_close($queue); // 把总句柄关闭
        return $response;
    }

    /**
     * 批量获取URL内容
     *
     * @param array $urlArr 请求列表[['url'=>'xxx','postData'=>[...],'postJson'=>false,],...]
     * @param int $timeout 超时时间
     * @param bool $postJson 是否POST json数据
     * @return array
     */
    public static function multPost(array $urlArr, int $timeout=10, bool $postJson=false)
    {
        if (empty($urlArr) || !is_array($urlArr)) {
            return [];
        }

        if ($timeout < 1 || $timeout > 120) {
            $timeout = self::$tTimeOut;
        }
        $queue = curl_multi_init(); // 允许并行地处理批处理cURL句柄。
        foreach ($urlArr as $i => $urlInfo) {
            $url = $urlInfo['url'];
            $postData = $urlInfo['postData'] ?: [];
            $postJson = isset($urlInfo['postJson']) ? $urlInfo['postJson'] : $postJson;

            // 判断链接
            if (!self::isUrl($url)) {
                continue;
            }

            $ch[$i] = curl_init();
            curl_setopt($ch[$i], CURLOPT_URL, $url);
            curl_setopt($ch[$i], CURLOPT_HEADER, 0);
            curl_setopt($ch[$i], CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch[$i], CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch[$i], CURLOPT_CONNECTTIMEOUT, 8);
            curl_setopt($ch[$i], CURLOPT_REFERER, $url);
            curl_setopt($ch[$i], CURLOPT_USERAGENT, self::$tUseragent);
            curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch[$i], CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch[$i], CURLOPT_ENCODING, 'gzip, deflate');

            // POST数据
            if (sizeof($postData) > 0) {
                curl_setopt($ch[$i], CURLOPT_POST, 1);
                if ($postJson) {  // json 提交请求
                    curl_setopt($ch[$i], CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json']);
                    curl_setopt($ch[$i], CURLOPT_POSTFIELDS, json_encode($postData));
                } else {
                    curl_setopt($ch[$i], CURLOPT_POSTFIELDS, http_build_query($postData));
                }
            }
            curl_multi_add_handle($queue, $ch[$i]); // 向批处理句柄中添加句柄,后面批量的去模拟访问,抓取回资源
        }
        do {
            curl_multi_exec($queue, $active);
            curl_multi_select($queue, $timeout);
        } while ($active > 0); // 一直到资源里还有0条为止

        // 经过了刚才的循环指抓取 此时 $queue 就是一个批量的资源集了 这只是资源集,你需要把它读取出来就好
        $response = [];
        foreach ($urlArr as $key => $_) {
            if(!isset($ch[$key])){
                $response[$key] = null;
                continue;
            }
            $response[$key]['content']  = curl_multi_getcontent($ch[$key]);
            $response[$key]['header'] = curl_getinfo($ch[$key]);
            curl_close($ch[$key]);
        }
        curl_multi_close($queue); // 把总句柄关闭
        return $response;
    }

    /**
     * 判断是否HTTPS
     *
     * @return boolean -true 是https
     */
    public static function isHttps()
    {
        if (isset($_SERVER['HTTPS'])) {
            return ('on' == strtolower($_SERVER['HTTPS']) || 1 == $_SERVER['HTTPS']);
        }
        // 通过附加协议
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            return 'https' == strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_SERVER_PROTOCOL'])) {
            return 'https' == strtolower($_SERVER['HTTP_X_FORWARDED_SERVER_PROTOCOL']);
        }
        if(isset($_SERVER['HTTP_FRONT_END_HTTPS'])){
            return strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off';
        }
        // 通过请求头
        if (isset($_SERVER['REQUEST_SCHEME'])) {
            return 'https' == strtolower($_SERVER['REQUEST_SCHEME']);
        }
        // 通过端口号
        if (isset($_SERVER['SERVER_PORT'])) {
            return '443' == $_SERVER['SERVER_PORT'];
        }
        return false;
    }
}
