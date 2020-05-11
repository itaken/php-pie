<?php

/**
 * 注册自动加载，对于没有使用composer的项目使用
 *
 * @author itaken<regelhh@gmail.com>
 * @since 2020-05-10
 */

spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);

    if (strpos($class, 'ItakenPHPie')===0) {
        $file = __DIR__ . '/' .str_replace('ItakenPHPie', 'src', $class).".php";
        if (file_exists($file)) {
            include($file);
        }
    }
});
