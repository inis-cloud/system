<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: Compare class - 比较类
// +----------------------------------------------------------------------

namespace inis\utils\tool;

/**
 * Class Compare
 * @package inis\utils\tool
 */
class Compare
{
    /**
     * 比较两个版本号大小
     * @param string|int $big  大版本号
     * @param string|int $small 小版本号
     * @param array $config 配置
     * @return string 返回结果
     */
    public function version($big = '1.0.0', $small = '1.0.0', array $config = [])
    {
        $config = array_merge(['symbol'=>true, 'parting'=>'.'], $config);

        if ((int)$big > 2147483646 || (int)$small > 2147483646) {
            throw new Exception('版本号,位数太大暂不支持!', '101');
        }
        
        $listBig   = explode($config['parting'], (string)$big);
        $listSmall = explode($config['parting'], (string)$small);
        
        $length = max(count($listBig), count($listSmall));
        $i   = -1;
        
        while ($i++ < $length) {
            
            $listBig[$i]   = intval(@$listBig[$i]);
            if ($listBig[$i] < 0 )   $listBig[$i] = 0;
            
            $listSmall[$i] = intval(@$listSmall[$i]);
            if ($listSmall[$i] < 0 ) $listSmall[$i] = 0;
            
            if ($listBig[$i] > $listSmall[$i])      return $config['symbol'] ? '>' : 'gt';
            else if ($listBig[$i] < $listSmall[$i]) return $config['symbol'] ? '<' : 'lt';
            else if ($i==($length - 1))             return $config['symbol'] ? '=' : 'eq';
        }
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