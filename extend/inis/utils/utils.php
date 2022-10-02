<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: Utils class - 工具类
// +----------------------------------------------------------------------

namespace inis\utils;

/**
 * Class Utils
 * @package inis\utils
 */
class utils
{
    private $class = [];

    // 构造器
    public function __construct()
    {
        // 获取当前目录下的所有文件夹
        $folder = function($path = __DIR__) {
            $result = [];
            foreach (scandir($path) as $val) {
                // 判断如果不是文件夹则进入下一次循环
                if (!is_dir("{$path}/$val")) continue;
                if ($val != '.' && $val != '..') $result[] = $val;
            }
            return $result;
        };

        // 获取指定目录下的所有php文件
        $file = function($path = __DIR__) {
            $result = [];
            foreach (scandir($path) as $val) {
                // 判断如果不是文件则进入下一次循环
                if (is_dir("{$path}/$val")) continue;
                if ($val != '.' && $val != '..') {
                    // 获取文件的拓展名为php的文件
                    if (pathinfo($val, PATHINFO_EXTENSION) == 'php') {
                        // 去除文件后缀
                        $result[] = pathinfo($val, PATHINFO_FILENAME);
                    }
                }
            }
            return $result;
        };

        $class = [];

        foreach ($folder() as $val) {
            $class = array_merge($class, $file(__DIR__ . '/' . $val));
            foreach ($class as $value) {
                $use = __NAMESPACE__ . '\\' . $val . '\\' . ucfirst($value);
                $this->{strtolower($value)} = new $use;
            }
        }

        $this->class = array_map(function($value) {
            return strtolower($value);
        }, array_merge($this->class, $class));
    }

    // 将 class 当作函数使用时触发
    public function __invoke()
    {
        // 获取指定当前类下的全部（属性->方法）
        foreach ($this->class as $val) $result[$val] = array_map(function($item) {
            return $item . '()';
        }, array_merge(array_filter(get_class_methods(new (__NAMESPACE__ . '\tool\\' . ucfirst($val))), function ($item) {
            return !preg_match('/^__/', $item);
        })));

        return $result;
    }

    // 调用不存在的方法时触发
    public function __call($name, $args)
    {
        if (in_array($name, $this->class)) return ($this->$name)(...$args);
        // 驼峰处理，找到对应的 类->小写下划线方法(...$args)
        else {

            $array     = [];
            $bigHump   = preg_match('/^[A-Z][A-Za-z0-9]+$/', $name);
            $smallHump = preg_match('/^[a-z][A-Za-z0-9]+$/', $name);

            if ($smallHump or $bigHump) {
                // 驼峰分隔数组
                $array = array_map(function ($item) {
                    return strtolower($item);
                }, preg_split('/(?=[A-Z])/', lcfirst($name)));
            }

            if (in_array($array[0], $this->class)) {
                // 拼接方法名
                $method = function($array) {
                    $result = '';
                    foreach ($array as $key => $val) if ($key != 0) $result .= strtolower($val) . '_';
                    return substr($result, 0, -1);
                };
                return ($this->{$array[0]})->{$method($array)}(...$args);

            }
            // 救不了了，报个异常
            else {

                // 获取当前的 class 名称
                $class = get_class($this);
                // 返回异常
                throw new \Exception("当前 {$class} 类没有 {$array[0]} 属性, 存在的属性有: " . implode('、', $this->class));
            }
        }
    }
}