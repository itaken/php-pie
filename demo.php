<?php

/**
 * 调试
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-10
 */

// 开启调试
set_time_limit(60);
ini_set('memory_limit', '-1');
ini_set("display_errors", "On");
error_reporting(E_ALL ^ E_NOTICE);

// 加载文件
include('autoload.php');

$vdFile = __DIR__ . '/vendor/autoload.php';
if (file_exists($vFile)) {
    include($vFile);
    // 注册调试类库
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}

// 定义常用变量
if (!function_exists('dump')) {
    function dump(...$argv)
    {
        var_dump(...$argv);
    }
}
if (!function_exists('p')) {
    function p(...$argv)
    {
        dump(...$argv);
    }
}

use ItakenPHPie\http\IpPie;
use ItakenPHPie\http\CurlPie;
use ItakenPHPie\other\TimePie;
use ItakenPHPie\http\OutputPie;
use ItakenPHPie\chinese\LangPie;
use ItakenPHPie\config\ConfigPie;
use ItakenPHPie\chinese\PinyinPie;
use ItakenPHPie\other\LocationPie;
use ItakenPHPie\cache\FileCachePie;
use ItakenPHPie\charset\CharsetPie;
use ItakenPHPie\encrypt\EncryptPie;
use ItakenPHPie\file\FileDetectPie;
use ItakenPHPie\browser\TerminalPie;
use ItakenPHPie\file\FileThroughPie;
use ItakenPHPie\html\HtmlConvertPie;
use ItakenPHPie\filter\WordFilterPie;
use ItakenPHPie\encrypt\IntConvertPie;

// $res = ConfigPie::get('basic/mimes');
// $res = WordFilterPie::sensitiveFilter('测试');
// $res = TerminalPie::isWap();
// $res = CharsetPie::toAlphabet('À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ/A/à|á|â|ã');
// $res = LangPie::toTrad('中华人民共和国');
// $res = PinyinPie::toPinyin('中华人民共和国');
// echo $str = EncryptPie::strEncrypt('同一个世界,同一个梦想', '奥运');
// $res = EncryptPie::strDecrypt($str, '奥运');
// echo $str = IntConvertPie::toString(234523);
// $res = IntConvertPie::toInt($str);
// $res = FileThroughPie::scanDirList(__DIR__ . '/src');
// $res = FileThroughPie::openDirList(__DIR__ . '/src');
// $res = FileThroughPie::recursiveDirList(__DIR__ . '/src');
// $res = FileDetectPie::detectFileRealExt(__DIR__ . '/composer.json');
$html=<<<HTML
<div class="trans-operation clearfix">
    <a href="javascript:void(0);" class="language-btn select-from-language">
        <span class="select-inner" style="background-color:#666;font-size:22px">
            <span class="language-selected" data-lang="auto">自动检测</span>
            <i class="arrow-down"></i>
        </span>
    </a>
    <a href="javascript:void(0);" class="language-btn-disable from-to-exchange">
        <span class="exchange-mask"></span>
    </a>
    <a href="javascript:void(0);" class="language-btn select-to-language">
        <span class="select-inner">
            <span class="language-selected" data-lang="zh">中文(简体)</span>
            <i class="arrow arrow-down"></i>
        </span>
    </a>
    <a href="javascript:void(0);" class="trans-btn trans-btn-zh" id="translate-button" target="_self"></a>
    <a href="javascript:" class="manual-trans-btn"></a>
</div>
HTML;
// $res = HtmlConvertPie::ubbEncode($html);
// $res = OutputPie::jsonEcho(['abc' => 111]);
// $res = OutputPie::jsonOutput(['abc' => 111]);
// $res = OutputPie::redirect('https://baidu.com');
// $res = OutputPie::alertRedirect('https://baidu.com');
// $res = OutputPie::showMessage('内容');
// $res = TimePie::timeDist(strtotime('2020-05-10 22:01:01'));
// $res = TimePie::timeCn(strtotime('2020-05-10 22:01:01'));
// $res = CurlPie::get('http://baidu.com/');
// $res = IpPie::queryIpAddress('103.84.139.98');
// $res = ConfigPie::loadEnv();
// $res = LocationPie::getLbsMap(29.918017, 121.606546);
// $res = LocationPie::getLbsMap(121.606546, 29.918017);
// $res = LocationPie::calDistance(121.606546, 29.818017, 121.544802, 29.96298);
$res = FileCachePie::cache(null);


p($res);
