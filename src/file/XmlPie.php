<?php

namespace ItakenPHPie\file;

/**
 * XML
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-11
 */
final class XmlPie
{
    /**
     * 组装xml
     * 
     * @param array $dataArr 数据集
     * @param array $attrArr 属性数组
     * @return string
     */
    public static function xmlWriter($dataArr, $attrArr=[])
    {
        $xml = new \XMLWriter();
        $xml->openMemory();
        //$xml->openUri('php://output');
        //  输出方式，也可以设置为某个xml文件地址，直接输出成文件
        $xml->setIndentString('  ');
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'utf-8');
        //  开始创建文件
        //  根结点
        $xml->startElement('article');
        foreach ($dataArr as $data) {
            $xml->startElement('item');
            if (is_array($data)) {
                foreach ($data as $key => $row) {
                    $xml->startElement($key);
                    if (isset($attrArr[$key]) && is_array($attrArr[$key])) {
                        foreach ($attrArr[$key] as $akey => $aval) {
                            //  设置属性值
                            $xml->writeAttribute($akey, $aval);
                        }
                    }
                    $xml->text($row);   //  设置内容
                    $xml->endElement(); // $key
                }
            }
            $xml->endElement(); //  item
        }
        $xml->endElement(); //  article
        $xml->endDocument();
        $xmlRes = $xml->outputMemory(true);
        $xml->flush();
        return $xmlRes;
    }

    /**
     * SimpleXML 导入 DOM
     * 
     * @param string $dom
     * @return object
     */
    public static function parseDom($domStr)
    {
        $dom = new \domDocument();
        $dom->loadXML($domStr);
        if (empty($dom)) {
            return null;
        }
        return \simplexml_import_dom($dom);
    }
}
