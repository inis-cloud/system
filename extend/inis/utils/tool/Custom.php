<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: Custom class - 自定义类
// +----------------------------------------------------------------------

namespace inis\utils\tool;

/**
 * Class Custom
 * @package inis\utils\tool
 */
class Custom
{
    /**
     * 自定义处理API
     * @param  string $url API地址
     * @param  string $api API应用名
     * @return string $result 结果
     */
    public function api($url = '', $api = 'api')
    {
        $result = $url;
        
        if (!empty($url)) {
            
            $prefix = '//';
            
            if (strstr($url, 'http://'))       $prefix = 'http://';
            else if (strstr($url, 'https://')) $prefix = 'https://';
            
            // 过滤http(s):// - 转数组 - 去空 - 重排序
            $result = array_values(array_filter(explode('/', str_replace(['https','http',':'], '', $url))));
            
            if (count($result) == 1) $result = $prefix . $result[0] . "/" . $api . "/";
            else if (count($result) == 2) {
                $result = $prefix + $result[0] + "/" + $result[1] + "/";
            }
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