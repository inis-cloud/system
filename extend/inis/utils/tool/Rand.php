<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: Rand class - 随机类
// +----------------------------------------------------------------------

namespace inis\utils\tool;

/**
 * Class Rand
 * @package inis\utils\tool
 */
class Rand
{
    // 随机字符串
    public function string($length = 6, string $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
    {
        $result = '';
        for ($i = 0, $result = '', $len = strlen($chars) - 1; $i < (int)$length; $i++) $result .= $chars[mt_rand(0, $len)];
        return $result;
    }

    // 随机图
    public function image($type = 'link', $file_path = 'storage/random/image.txt')
    {
        $result = null;
        
        if ($type == 'link') {
            
            if (!file_exists($file_path)) $result = '文件不存在';
            
            // 从文本获取链接
            $pics = [];
            
            $fs = fopen($file_path, 'r');
            
            while (!feof($fs)) {
            
            	$line=trim(fgets($fs));
            	
            	if(!empty($line)) array_push($pics, $line);
            }
            
            // 从数组随机获取链接
            $result = $this->array($pics);
            
        } else if ($type == 'local') {
            
            // 得到所有的文件
            $files = scandir($file_path);
            
            // 符合要求的后缀
            $allow = ['jpg','jpeg','png','gif','webp'];
            
            foreach ($files as $key => $val) {
                $item = explode('.', $val);
                if (in_array(array_pop($item), $allow)) {
                    $result[] = '/' . $file_path . $val;
                }
            }
            
            $result = $this->array($result);
        }
        
        return $result;
    }

    // 随机数组
    public function array($array = ['light','danger','dark','primary','success','info','warning'])
    {
        return $array[array_rand($array)];
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