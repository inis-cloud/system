<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: Date class - 日期类
// +----------------------------------------------------------------------

namespace inis\utils\tool;

/**
 * Class Date
 * @package inis\utils\tool
 */
class Date
{
    /**
     * 是否日期格式
     * @param string $string 日期
     * @return bool true/false
     */
    public function is($string)
    {
        $strtotime = strtotime($string);
        return (is_numeric($strtotime) and strtotime(date('Y-m-d H:i:s', $strtotime)) == $strtotime) ? true : false;
    }

    /**
     * 日期格式求相差天数
     * @param string $date1 日期1
     * @param string $date2 日期2
     * @return array 相差天数
     */
    public function diff($date1, $date2)
    {
        $datetime1 = new \DateTime($date1);
        $datetime2 = new \DateTime($date2);
        $interval  = $datetime1->diff($datetime2);
        
        $result['year']  = (int)$interval->format('%Y');
        $result['month'] = (int)$interval->format('%m');
        $result['day']   = (int)$interval->format('%d');
        $result['hour']  = (int)$interval->format('%H');
        $result['min']   = (int)$interval->format('%i');
        $result['sec']   = (int)$interval->format('%s');
        $result['days']  = (int)$interval->format('%a');
        
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