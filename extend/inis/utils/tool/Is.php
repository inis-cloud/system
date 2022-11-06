<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: Is class - in类
// +----------------------------------------------------------------------

namespace inis\utils\tool;

/**
 * Class Is
 * @package inis\utils\tool
 */
class Is
{
    /**
     * 是否为 true
     * @param any $value 值
     * @return bool  $result 结果
     */
    public function true($value = null)
    {

        return !empty($value) ? ($value == 'true' or $value === true ? true : false) : false;
    }

    /**
     * 是否为 false
     * @param any $value 值
     * @return bool  $result 结果
     */
    public function false($value = null)
    {
        return !empty($value) ? ($value == 'false' or $value === false ? false : true) : false;
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