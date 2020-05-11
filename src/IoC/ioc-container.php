<?php
/**
 * 控制反转类
 *
 * @see http://www.cnblogs.com/52php/p/6379020.html
 */
class Ioc
{
    
    /**
     * @var array 注册的依赖数组
     */
    protected static $registry = array();
 
    /**
     * 添加一个 resolve （匿名函数）到 registry 数组中
     *
     * @param string  $name    依赖标识
     * @param Closure $resolve 一个匿名函数，用来创建实例
     * @return void
     */
    public static function register($name, Closure $resolve)
    {
        static::$registry[$name] = $resolve;
    }
 
    /**
     * 返回一个实例
     *
     * @param string $name 依赖的标识
     * @return mixed
     * @throws \Exception
     */
    public static function resolve($name)
    {
        if (static::registered($name)) {
            $name = static::$registry[$name];
            return $name();
        }
        throw new \Exception("Nothing registered with that name");
    }
 
    /**
     * 查询某个依赖实例是否存在
     *
     * @param string $name
     * @return bool
     */
    public static function registered($name)
    {
        return array_key_exists($name, static::$registry);
    }
}

Ioc::register("book", function () {
    $book = new Book();
    $book->setdb('db');
    $book->setfile('file');

    return $book;
});

// 注入依赖
$book = Ioc::resolve('book');
