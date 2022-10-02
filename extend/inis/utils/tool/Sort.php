<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: Sort class - 排序类
// +----------------------------------------------------------------------

namespace inis\utils\tool;

/**
 * Class Sort
 * @package inis\utils\tool
 */
class Sort
{
    /**
     * 二维数组冒泡排序
     * @param array  $array 数组
     * @param string $key   排序字段
     * @param string $sort  排序方式
     * @return array $array 数组
     */
    public function bubble($array, string $key, $sort = 'acs')
    {
        if ($sort == 'acs') {   // 升序
            for ($i = 0; $i < count($array); $i++) for ($j = $i; $j < count($array); $j++) if ($array[$i][$key] > $array[$j][$key]) {
                $temp       = $array[$i];
                $array[$i]  = $array[$j];
                $array[$j]  = $temp;
            }
        } elseif ($sort == 'desc') {    // 降序
            for ($i = 0; $i < count($array); $i++) for ($j = $i; $j < count($array); $j++) if ($array[$i][$key] < $array[$j][$key]) {
                $temp       = $array[$i];
                $array[$i]  = $array[$j];
                $array[$j]  = $temp;
            }
        }
        
        return $array;
    }
    
    // 调用不存在的方法时触发
    public function __call($name, $args)
    {
        // 获取当前 class 存在的方法
        $methods = get_class_methods($this);
        // 过滤掉魔术方法
        $methods = array_filter($methods, function ($item) {
            return !preg_match('/^__/', $item);
        });
        // 获取当前的 class 名称
        $class = get_class($this);
        // 返回异常
        throw new \Exception("当前 {$class} 类没有 {$name} 方法, 存在的方法有: " . implode('、', $methods));
    }
}