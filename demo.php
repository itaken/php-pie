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

// 加载类库
$vdFile = __DIR__ . '/vendor/autoload.php';
if (file_exists($vdFile)) {
    include($vdFile);
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
use ItakenPHPie\encrypt\PasswordPie;
use ItakenPHPie\file\FileThroughPie;
use ItakenPHPie\file\XmlPie;
use ItakenPHPie\html\HtmlConvertPie;
use ItakenPHPie\filter\WordFilterPie;
use ItakenPHPie\html\TagPie;
use ItakenPHPie\http\ClientPie;

// $res = ConfigPie::get('basic/mimes');
// $res = WordFilterPie::sensitiveFilter('测试');
// $res = TerminalPie::isWap();
// $res = CharsetPie::toAlphabet('À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ|A|à|á|â|ã');
// $res = LangPie::toTrad('中华人民共和国');
// $res = PinyinPie::toPinyin('中华人民共和国');
// echo $str = EncryptPie::strEncrypt('同一个世界,同一个梦想', '奥运');
// $res = EncryptPie::strDecrypt($str, '奥运');
// echo $str = EncryptPie::int2string(234523);
// $res = EncryptPie::string2int($str);
// $res = FileThroughPie::scanDirList(__DIR__ . '/src');
// $res = FileThroughPie::openDirList(__DIR__ . '/src');
// $res = FileThroughPie::recursiveDirList(__DIR__ . '/src');
// $res = FileDetectPie::detectFileRealExt(__DIR__ . '/composer.json');
$html=<<<HTML
<div class="clearfix">
    <a href="javascript:void(0);" class="select-from">
        <span style="background-color:#666;font-size:22px">
            <span data-lang="auto">自动检测</span>
            <i class="arrow"></i>
        </span>
    </a>
    <a href="javascript:void(0);" class="btn-disable">
        <span class="exchange">文本</span>
    </a>
    <a href="javascript:void(0);" class="btn-zh" id="button" target="_self">按钮</a>
    <a href="javascript:">链接</a>
</div>
HTML;
$res = HtmlConvertPie::ubbEncode($html);
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
// $res = FileCachePie::cache(null);
// $res = PasswordPie::isWeakPsw(12354);
// $res = TagPie::json2html('{"l1":{"l1_1":["l1_1_1","l1_1_2"],"l1_2":{"l1_2_1":121}},"l2":{"l2_1":null,"l2_2":true,"l2_3":[]}}');
// $res = ClientPie::getSoapResult('http://localhost/test.asmx?wsdl', []);
// $res = ClientPie::getClientInfo();
// $dataArray = [
// 	[
// 		'title' => 'title1',
// 		'content' => 'content1',
// 		'pubdate' => '2009-10-11',
// 	],
// 	[
// 		'title' => 'title2',
// 		'content' => 'content2',
// 		'pubdate' => '2009-11-11',
//     ]
// ];
// // 属性数组
// $attributeArray = [
// 	'title' => [
// 		'size' => 1
//     ],
// ];
// $res = XmlPie::xmlWriter($dataArray, $attributeArray);
// $dom = XmlPie::parseDom('<books><book><title>Great American Novel</title></book></books>');
// $res = $dom->book[0]->title; // Great American Novel
// $res = ClientPie::getMemoryUsageImplement();


p($res);
