<?php

namespace ItakenPHPie\text\lib\sensitive;

/**
 * Trie树 php 实现敏感词过滤 (DFA-Trie)
 * @doc 整理自 https://segmentfault.com/a/1190000019137933
 *
 * @modify itaken<regelhh@gmail.com>
 * @since 2020-05-08
 */
class SensitiveWordFilter
{
    protected $dict;

    /**
     * @var string 敏感词文件（一行一个敏感词）
     */
    protected $dictFile = __DIR__ . '/dirty_words.txt';

    protected $matchDict = [];
    protected $matchFullDict = [];

    /**
     * 初始化
     *
     * @param string $dictFile 字典文件路径, 每行一句
     * @return void
     */
    public function __construct($dictFile='')
    {
        if ($dictFile) {
            $this->dictFile = $dictFile;
        }
        $this->dict = [];
    }

    /**
     * 缓存
     *
     * @param string $cacheKey
     * @param mixed $value
     * @param int $cacheTime
     * @return mixed
     */
    private function cache($cacheKey, $value=null, $cacheTime=3600)
    {
        if (!function_exists('Memcached')) {
            return null;
        }
        $memcache = new \Memcached();
        $memcache->addServer("127.0.0.1", 11212);
        if (is_null($value)) {
            return $memcache->get($cacheKey);
        }
        return $memcache->set($cacheKey, $value, $cacheTime);
    }

    /**
     * 加载敏感词
     *
     * @param bool $cache
     * @return void
     */
    public function loadData($cache = true)
    {
        if ($cache && $this->dict) {
            return;
        }
        $cacheKey = __CLASS__ . "_" . md5($this->dictFile);
        $dict = $cache ? $this->cache($cacheKey) : null;
        if (empty($dict)) {
            // 从字典中加载
            $this->loadDataFromFile();

            $dict = $this->dict;
        }
        $this->cache($cacheKey, $dict, 3600);
    }

    /**
     * 从文件加载字典数据, 并构建 trie 树
     *
     * @return void
     */
    private function loadDataFromFile()
    {
        $file = $this->dictFile;
        if (!file_exists($file)) {
            throw new \InvalidArgumentException("字典文件不存在");
        }

        $handle = @fopen($file, "r");
        if (!is_resource($handle)) {
            throw new \RuntimeException("字典文件无法打开");
        }

        while (!feof($handle)) {
            $line = fgets($handle);
            if (empty($line)) {
                continue;
            }

            $this->addWords(trim($line));
        }

        fclose($handle);
    }

    /**
     * 分割文本(注意ascii占1个字节, unicode...)
     *
     * @param string $str
     *
     * @return string[]
     */
    protected function splitStr($str)
    {
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * 往dict树中添加语句
     *
     * @param $wordArr
     * @return void
     */
    protected function addWords($words)
    {
        $wordArr = $this->splitStr($words);
        $curNode = &$this->dict;
        foreach ($wordArr as $char) {
            if (!isset($curNode)) {
                $curNode[$char] = [];
            }

            $curNode = &$curNode[$char];
        }
        // 标记到达当前节点完整路径为"敏感词"
        if (!isset($curNode['end'])) {
            $curNode['end'] = 0;
        } else {
            $curNode['end']++;
        }
    }

    /**
     * 过滤文本
     *
     * @param string $str 原始文本
     * @param string $replace 敏感字替换字符
     * @param int    $skipDistance 严格程度: 检测时允许跳过的间隔
     *
     * @return string 返回过滤后的文本
     */
    public function filter($str, $replace = '*', $skipDistance = 0)
    {
        $maxDistance = max($skipDistance, 0) + 1;
        $strArr = $this->splitStr($str);
        $length = count($strArr);
        $matchFullText = '';
        for ($i = 0; $i < $length; $i++) {
            $char = $strArr[$i];

            $matchFullText = $char;
            if (!isset($this->dict[$char])) {
                continue;
            }
            
            $curNode = &$this->dict[$char];

            $dist = 0;
            $matchIndex = [$i];
            $tmpMatchFullText = '';
            for ($j = $i + 1; $j < $length && $dist < $maxDistance; $j++) {
                $tmpMatchFullText .= $strArr[$j];

                if (!isset($curNode[$strArr[$j]])) {
                    $dist ++;
                    continue;
                }

                $matchFullText .= $tmpMatchFullText;

                $tmpMatchFullText = '';

                $matchIndex[] = $j;
                $curNode = &$curNode[$strArr[$j]];
            }

            
            // 匹配
            if (isset($curNode['end'])) {
                $matchText = '';  // 匹配的文案
                foreach ($matchIndex as $index) {
                    $matchText .= $strArr[$index];

                    $strArr[$index] = $replace;
                }

                $this->matchDict[] = $matchText;
                $this->matchFullDict[] = $matchFullText;

                $i = max($matchIndex);
            }
            $matchFullText = '';
        }
        return implode('', $strArr);
    }

    /**
     * 确认所给语句是否为敏感词
     *
     * @param $strArr
     *
     * @return bool|mixed
     */
    public function isMatch($strArr)
    {
        $strArr = is_array($strArr) ? $strArr : $this->splitStr($strArr);
        $curNode = &$this->dict;

        foreach ($strArr as $char) {
            if (!isset($curNode[$char])) {
                return false;
            }
        }
        return $curNode['end'] ?? false;
    }

    /**
     * 获取匹配的文案
     *
     * @return array
     */
    public function getMatchDict()
    {
        return $this->matchDict;
    }

    /**
     * 获取全匹配的文案
     *
     * @return array
     */
    public function getMatchFullDict()
    {
        return $this->matchFullDict;
    }
}
