<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: Time class - 时间类
// +----------------------------------------------------------------------

namespace inis\utils\tool;

/**
 * Class Time
 * @package inis\utils\tool
 */
class Time
{
    /**
     * 秒转人性化时间
     * @param int $second 秒时间
     * @return string 人性化时间
     */
    public function natural(int $second = 0)
    {
        $result = '';
        
        if ($second < 60) {
            $result = floor($second) . '秒';
        } else if ($second >= 60 and $second < 60 * 60) {
            $result = floor($second / 60) . '分钟';
        } else if ($second >= 60 * 60 and $second < 60 * 60 * 24) {
            $result = floor($second / (60 * 60)) . '小时';
        } else if ($second >= 24 * 60 * 60) {
            $result = floor($second / (24 * 60 * 60)) . '天';
        } 
        
        return $result;
    }

    /**
     * 时间戳格式求相差天数
     * @param int $strtotime1 时间戳1
     * @param int $strtotime2 时间戳2
     * @return string 相差天数
     */
    public function diff($strtotime1, $strtotime2)
    {
    	if ($strtotime1 < $strtotime2) {
    		$starttime = $strtotime1;
    		$endtime   = $strtotime2;
    	} else {
    		$starttime = $strtotime2;
    		$endtime   = $strtotime1;
    	}
    	
    	// 计算天数
    	$timediff = $endtime - $starttime;
    	$days     = intval($timediff / 86400);
    	// 计算小时数
    	$remain   = $timediff % 86400;
    	$hours    = intval($remain / 3600);
    	// 计算分钟数
    	$remain   = $remain % 3600;
    	$mins     = intval($remain / 60);
    	// 计算秒数
    	$secs     = $remain % 60;
    	$result   = ['day' => $days,'hour' => $hours,'min' => $mins,'sec' => $secs];
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