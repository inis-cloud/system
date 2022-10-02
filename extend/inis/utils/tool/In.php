<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: In class - in类
// +----------------------------------------------------------------------

namespace inis\utils\tool;

/**
 * Class In
 * @package inis\utils\tool
 */
class In
{
    /**
     * 是否在二维数组内
     * @param string $search 要查找的值
     * @param array  $array  数组
     * @return bool  $result 结果
     */
    public function array($search, array $array)
    {
        $result = false;
        
        if (is_array($search)) foreach ($array as $val) {
            foreach ($val as $k => $v) if ($k == $search[0] and $v == $search[1]) $result = true;
        }
        
        return $result;
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