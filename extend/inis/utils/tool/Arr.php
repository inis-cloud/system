<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: Array class - 数组类
// +----------------------------------------------------------------------

namespace inis\utils\tool;

/**
 * Class Time
 * @package inis\utils\tool
 */
class Arr
{
    /**
     * 数组深度合并
     * @param array $array1 数组1
     * @param array $array2 数组2
     * @param array ...     数组N
     * @return array 合并后的数组
     */
    public function merge(array ...$arrays) {
    	$result = [];
    	while ($arrays) {
    		$array = array_shift($arrays);
    		if (!$array) continue;
    		foreach ($array as $key => $val) {
    			if (is_string($key)) {
    				if (is_array($val) && array_key_exists($key, $result) && is_array($result[$key])) {
    					$result[$key]    = $this->merge(...[$result[$key], $val]);
    				} else $result[$key] = $val;
    			} else $result[] = $val;
    		}
    	}
    	return $result;
    }

    /**
     * 数组键名转大写
     * @param array $array 数组
     * @param cost $case 转换类型
     * @param bool $flag 是否递归
     */
    public function keystoupper(&$array, $case = CASE_LOWER, $flag = false)
    {
        $array = array_change_key_case($array, $case);
        if ($flag) foreach ($array as $key => $val) if (is_array($val)) $this->keystoupper($array[$key], $case, true);
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