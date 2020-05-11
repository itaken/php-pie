<?php

/**
 * YII DI Container
 *
 * @see http://www.cnblogs.com/qq120848369/p/6129483.html
 */
class YiiDi
{
    private $_definitions = [];
    private $_params = [];
    private $_singletons = [];
    
    public function set($class, $definition = [], array $params = [])
    {
        $this->_definitions[$class] = $this->normalizeDefinition($class, $definition);
        $this->_params[$class] = $params;
        unset($this->_singletons[$class]);
        return $this;
    }
    
    public function get($class, $params = [], $config = [])
    {
        if (isset($this->_singletons[$class])) {
            // singleton
            // 此前已经get过并且设置为单例，那么返回单例对象既可
            return $this->_singletons[$class];
        } elseif (!isset($this->_definitions[$class])) {
            // 非单例需要生成新对象，但是此前没有set过类定义，
            // 因此只能直接反射分析构造函数的依赖
            return $this->build($class, $params, $config);
        }
    
        // 此前设置过的类定义，对类进行了更具体的定义，帮助我们更快的构造出对象
        $definition = $this->_definitions[$class];
    
        // 类定义可以是一个函数，用于直接为DI生成对象
        if (is_callable($definition, true)) {
            // 将set设置的构造参数和本次传入的构造参数merge到一起
            // 然后分析这些传入的构造参数是否为实参（比如:int,string），这是因为yii允许
            // params是Instance对象，它代表了另外一个类定义（它内部指向了DI容器中某个definition)
            // 为了这种构造参数能够传入到当前的构造函数，需要递归调用di->get将其创建为实参。
            $params = $this->resolveDependencies($this->mergeParams($class, $params));
            // 这个就是函数式的分配对象，前提是构造参数需要确保都是实参
            $object = call_user_func($definition, $this, $params, $config);
        } elseif (is_array($definition)) { // 普通的类定义
            $concrete = $definition['class'];
            unset($definition['class']);
    
            // 把set设置的config和这次传入的config合并一下
            $config = array_merge($definition, $config);
            // 把set设置的params构造参数和这次传入的构造参数合并一下
            $params = $this->mergeParams($class, $params);
    
            // 这里: $class代表的就是MailInterface，而$concrete代表的是Mailer
            if ($concrete === $class) {
                // 这里是递归出口，生成目标class对象既可，没有什么可研究的
                $object = $this->build($class, $params, $config);
            } else {
                // 显然，这里要构造MailInterface是等同于去构造Mailer对象
                $object = $this->get($concrete, $params, $config);
            }
        } elseif (is_object($definition)) {
            return $this->_singletons[$class] = $definition;
        } else {
            throw new \InvalidConfigException("Unexpected object definition type: " . gettype($definition));
        }
        if (array_key_exists($class, $this->_singletons)) {
            // singleton
            $this->_singletons[$class] = $object;
        }
        return $object;
    }
    
    protected function build($class, $params, $config)
    {
        /* @var $reflection ReflectionClass */
        // 利用反射，分析类构造函数的参数，
        // 其中，返回值reflection是class的反射对象，
        // dependencies就是构造函数的所有参数了，有几种情况：
        // 1，参数有默认值，直接用
        // 2, 没有默认值，并且不是int这种非class，那么返回Instance指向对应的class，等待下面的递归get
        list($reflection, $dependencies) = $this->getDependencies($class);
    
        // 传入的构造函数参数优先级最高，直接覆盖前面反射分析的构造参数
        foreach ($params as $index => $param) {
            $dependencies[$index] = $param;
        }
    
        // 完整的检查一次参数，如果依赖是指向class的Instance，那么递归DI->get获取实例
        // 如果是指定int,string这种的Instance，那么说明调用者并没有params传入值，构造函数默认参数也没有值，
        // 必须抛异常
        // 如果不是Instance，说明是params用户传入的实参可以直接用
        $dependencies = $this->resolveDependencies($dependencies, $reflection);
        if (empty($config)) {
            return $reflection->newInstanceArgs($dependencies);
        }
        // 最后通过反射对象，传入所有构造实参，完成对象创建
        if (!empty($dependencies) && $reflection->implementsInterface('yii\base\Configurable')) {
            // set $config as the last parameter (existing one will be overwritten)
            $dependencies[count($dependencies) - 1] = $config;
            return $reflection->newInstanceArgs($dependencies);
        } else {
            $object = $reflection->newInstanceArgs($dependencies);
            foreach ($config as $name => $value) {
                $object->$name = $value;
            }
            return $object;
        }
    }
    
    protected function getDependencies($class)
    {
        if (isset($this->_reflections[$class])) {
            return [$this->_reflections[$class], $this->_dependencies[$class]];
        }
    
        $dependencies = [];
        $reflection = new ReflectionClass($class);
    
        $constructor = $reflection->getConstructor();
        if ($constructor !== null) {
            foreach ($constructor->getParameters() as $param) {
                if ($param->isDefaultValueAvailable()) {
                    $dependencies[] = $param->getDefaultValue();
                } else {
                    $c = $param->getClass();
                    $dependencies[] = Instance::of($c === null ? null : $c->getName());
                }
            }
        }
    
        $this->_reflections[$class] = $reflection;
        $this->_dependencies[$class] = $dependencies;
    
        return [$reflection, $dependencies];
    }
    
    protected function resolveDependencies($dependencies, $reflection = null)
    {
        foreach ($dependencies as $index => $dependency) {
            if ($dependency instanceof Instance) {
                if ($dependency->id !== null) {
                    $dependencies[$index] = $this->get($dependency->id);
                } elseif ($reflection !== null) {
                    $name = $reflection->getConstructor()->getParameters()[$index]->getName();
                    $class = $reflection->getName();
                    throw new InvalidConfigException("Missing required parameter \"$name\" when instantiating \"$class\".");
                }
            }
        }
        return $dependencies;
    }
}
