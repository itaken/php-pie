<?php

namespace ItakenPHPie\text;

use ItakenPHPie\http\OutputPie;

/**
 * JSON工具类
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2019-8-2
 */
final class JsonPie
{

    /**
     * JSON encode
     *
     * @param mixed $data
     * @param int $type 类型 see:https://www.php.net/manual/zh/json.constants.php
     * @return string|false
     */
    public static function encode($data, $type = JSON_UNESCAPED_UNICODE)
    {
        $json = \json_encode($data, $type);
        if (false === $json) {
            // self::getJsonLastError()['message'];
        }
        return $json;
    }

    /**
     * JSON 解析为数组
     * @see https://www.php.net/manual/zh/function.json-decode.php
     *
     * @param string $json
     * @return array
     */
    public static function decodeToArr($json)
    {
        $data = json_decode(trim($json), true);
        if (false === $data || is_null($data)) {
            // self::getJsonLastError()['message'];
        }
        return $data ?: [];
    }

    /**
     * JSON输出
     *
     * @param int $code 200
     * @param string $message
     * @param mixed $data
     * @return void
     */
    public static function output($code = 200, $message = '', $data = ''): void
    {
        OutputPie::jsonOutput($data, $message, $code);
    }

    /**
     * 获取 json 最后发生的错误
     * @see https://www.php.net/manual/zh/function.json-last-error.php
     *
     * @return array
     */
    public static function getJsonLastError(): array
    {
        $code = \json_last_error();
        $errMap = [
            JSON_ERROR_NONE => '没有错误发生',
            JSON_ERROR_DEPTH => '到达了最大堆栈深度',
            JSON_ERROR_STATE_MISMATCH => '无效或异常的 JSON',
            JSON_ERROR_CTRL_CHAR => '控制字符错误，可能是编码不对',
            JSON_ERROR_SYNTAX => '语法错误',
            JSON_ERROR_UTF8 => '异常的 UTF-8 字符，也许是因为不正确的编码',
            JSON_ERROR_RECURSION => 'One or more recursive references in the value to be encoded',
            JSON_ERROR_INF_OR_NAN => 'One or more NAN or INF values in the value to be encoded',
            JSON_ERROR_UNSUPPORTED_TYPE => '指定的类型，值无法编码',
            JSON_ERROR_INVALID_PROPERTY_NAME => '指定的属性名无法编码',
            JSON_ERROR_UTF16 => '畸形的 UTF-16 字符，可能因为字符编码不正确',
        ];
        return [
            'code' => $code,
            'message' => $errMap[$code] ?: '',
        ];
    }
}
