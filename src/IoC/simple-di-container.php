<?php

/**
 * 控制反转
 *
 * @link https://github.com/laracasts/simple-di-container/blob/master/IoC.php
 * @see https://segmentfault.com/a/1190000002424023
 */
class IoC
{
    protected static $registry = [];
    
    public static function bind($name, callable $resolver)
    {
        static::$registry[$name] = $resolver;
    }
    
    public static function make($name)
    {
        if (isset(static::$registry[$name])) {
            $resolver = static::$registry[$name];
            return $resolver();
        }
        throw new Exception('Alias does not exist in the IoC registry.');
    }
}

IoC::bind('bim', function () {
    return new Bim();
});
IoC::bind('bar', function () {
    return new Bar(IoC::make('bim'));
});
IoC::bind('foo', function () {
    return new Foo(IoC::make('bar'));
});

// 从容器中取得Foo
$foo = IoC::make('foo');
$foo->doSomething(); // Bim::doSomething|Bar::doSomething|Foo::doSomething
