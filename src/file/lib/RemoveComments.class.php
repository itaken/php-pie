<?php

namespace ItakenPHPie\file\lib;

/**
 * 移除 文档注释
 *
 * @author itaken <regelhh@gmaill.com>
 * @since 2013-11-18
 */
class RemoveComments
{
    /**
     *  @var mixed 基本设定
     */
    private $orig_file = '';
    private $orig_folder = '';
    private $save_folder = '_done_';
    private $tmp_folder = [];  // 临时文件夹
    private $file_arr = [];  // 需操作的文件
    private $folder_arr = [];  // 需操作的文件夹
    private $fail_arr = [];  // 处理失败的文件列表
    private $succeed_arr = [];  // 成功处理的文件列表
    private $compressed_arr = [];  // 压缩后
    private $support_suffix = array('.php', '.js');  // 支持过滤的文件后缀名
    private $ignore_folder = array('config', 'cache', 'library', 'nbproject', '.idea', 'ThinkPHP', 'images', 'voice', 'data', 'tpl', 'uibox');  // 排除的 文件夹名称

    /**
     * 初始化
     *
     * @param string $file_or_folder
     */
    public function __construct($file_or_folder='', $save_folder=null)
    {
        $this->file_arr = [];
        $this->folder_arr = [];
        $this->tmp_folder = [];
        $this->fail_arr = [];
        $this->succeed_arr = [];
        $this->compressed_arr = [];
        if(!is_null($save_folder)){
            $this->save_folder = $save_folder;
        }
        if (!empty($file_or_folder)) {
            $this->select_original($file_or_folder);
        }
    }

    /**
     * 选择 需要处理的源
     *
     * @param string $file_or_folder
     * @return boolean
     */
    public function select_original($file_or_folder)
    {
        if (empty($file_or_folder)) {
            return $this;
        }
        $selections = str_replace('\\', '/', realpath($file_or_folder));
        if (is_dir($selections)) {
            $this->orig_folder = $selections;
            $save_folder = $selections . '/' . $this->save_folder . '/';
        } elseif (file_exists($selections)) {
            $this->orig_file = $selections;
            $save_folder = dirname($selections) . '/' . $this->save_folder . '/';
        } else {
            throw new \InvalidArgumentException("Not supported string: {$file_or_folder}");
        }
        $save_folder = str_replace('//', '/', $save_folder);

        $this->mk_dir($save_folder);
        $this->save_folder = $save_folder;

        return $this;
    }

    /**
     * (循环)创建文件夹
     *
     * @param string $dir
     * @param int $mode
     * @return boolean
     */
    private function mk_dir($dir, $mode = 0755)
    {
        if (is_dir($dir)) {
            return true;
        }
        try{
            mkdir($dir, $mode, true);
        }catch(\Exception $e){
            throw new \RuntimeException("MKDIR Failed: {$dir}");
        }
        return true;
    }

    /**
     * 移除 文件 注释
     *
     * @param string $file
     * @return string
     */
    private function remove_file_comments($file)
    {
        if (!file_exists($file)) {
            return false;
        }
        $contents = file_get_contents($file);
        if (empty($contents)) {
            return false;
        }
        $reg_1 = "/\/\*(.*\s+)*\*\//U";
        $reg_2 = "/\#.*/";
        $reg_3 = "/\/\/.*/";
        $reg_4 = "/\s+/";
        
        return preg_replace(array($reg_1, $reg_2, $reg_3), ' ', $contents);
    }

    /**
     * 移除文件注释 的操作
     *
     * @return array
     */
    public function do_remove()
    {
        if ($this->orig_file) {
            // 文件 处理
            $this->opt_file_rc($this->orig_file);
        } elseif ($this->orig_folder) {
            // 文件夹 处理
            $this->opt_folder_rc($this->orig_folder);
        }
        return array(
            'fail' => $this->fail_arr,
            'succeed' => $this->succeed_arr,
            'compressed' => $this->compressed_arr,
        );
    }

    /**
     * 移除 文件的注释
     *
     * @param string $file
     * @param string $new_path
     * @return boolean
     */
    private function opt_file_rc($file, $new_path='')
    {
        if (!file_exists($file)) {
            $this->fail_arr[] = $file;
            return false;
        }
        $new_path = $new_path ?: $this->save_folder;
        $contents = $this->remove_file_comments($file);  // 去除注释
        if (empty($contents)) {
            return false;
        }
        $new_file = str_replace('//', '/', $new_path . '/' . 'compressed-' . basename($file));
        try {
            // 写入文件
            file_put_contents($new_file, $contents, LOCK_EX);
        } catch (\Exception $e) {
            $this->fail_arr[] = $file;
            return false;
        }
        $this->succeed_arr[] = $file;
        $this->compressed_arr[] = $new_file;
        return true;
    }

    /**
     * 移除 文件夹 内所有文件的注释
     *
     * @param string $folder
     * @return boolean
     */
    private function opt_folder_rc($folder)
    {
        if (empty($folder)) {
            return false;
        }
        $this->folder_through(array($folder));
        $file_arr = $this->file_arr;  // 文件列表
        $save_folder = $this->save_folder;
        foreach ($file_arr as $path => $_files) {
            if (empty($_files)) {
                continue;
            }
            $new_path = str_replace($folder, $save_folder, $path);
            $this->mk_dir($new_path);
            foreach ($_files as $_file) {
                if (empty($_file)) {
                    continue;
                }
                $this->opt_file_rc($_file, $new_path);
            }
        }
        return true;
    }

    /**
     *  文件夹遍历
     *
     * @param array $tmp_folder
     * @return string
     */
    private function folder_through($tmp_folder)
    {
        if (empty($tmp_folder)) {
            return false;
        }
        foreach ($tmp_folder as $folder) {
            $this->file_collect($folder);
        }
        return $this->folder_through($this->tmp_folder);
    }

    /**
     * 文件收集
     *
     * @param string $folder
     * @return bool
     */
    private function file_collect($folder)
    {
        if(!is_dir($folder)){
            return false;
        }
        $handle = opendir($folder);
        if (empty($handle)) {
            return false;
        }
        $file_arr = $this->file_arr;
        $folder_arr = $this->folder_arr;
        $ignore_folder = $this->ignore_folder;
        $file_suffix = str_replace('.', '\.', implode('|', $this->support_suffix));  // 需忽略的文件后缀
        $tmp_arr = $_arr_1 = $_arr_2 = [];
        while (false !== ($file = readdir($handle))) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }
            $file_name = $folder . '/' . $file;
            if (is_dir($file_name)) {
                // 目录
                if (in_array($file, $ignore_folder)) {
                    continue;
                }
                $tmp_arr[] = $file_name;
                $_arr_1[] = $file_name;
                continue;
            }
            // 文件
            if (!preg_match("/({$file_suffix})$/i", $file)) {
                continue;
            }
            $_arr_2[] = $file_name;
        }
        closedir($handle);
        if (!empty($_arr_1)) {
            $folder_arr[$folder] = $_arr_1;
            $this->folder_arr = $folder_arr;
        }
        if (!empty($_arr_2)) {
            $file_arr[$folder] = $_arr_2;
            $this->file_arr = $file_arr;
        }
        $this->tmp_folder = $tmp_arr;
        return true;
    }
}
