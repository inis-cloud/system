<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: Domain class - 域名类
// +----------------------------------------------------------------------

namespace inis\utils\tool;

/**
 * Class Domain
 * @package inis\utils\tool
 */
class Domain
{
    // 将 class 当作函数使用时触发
    public function __invoke($url = null)
    {
        if (is_null($url)) {
            return [
                'is'    => true,
                'value' => $this->local()
            ];
        }
        else {
            $rule = "/^(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/";
            $url  = str_replace(['https://','http://','//'], '', $url);
            return [
                'is'    => preg_match($rule, $url) ? true : false,
                'value' => $this->extract($url)
            ];
        }
    }

    /**
     * 获取本地域名
     * @return string 返回本地域名
     */
    public function local()
    {
        $type   = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        $domain = &$_SERVER['HTTP_HOST'];
        return $type.$domain;
    }

    /**
     * 获取顶级域名
     * @param string|null $url
     * @return string 返回顶级域名
     */
    public function top(string $url = null)
    {
        if (is_null($url)) $url = $this->local();

        $url = $this->extract($url);
        
        // 查看是几级域名
        $data = explode('.', $url);
        
        $n = count($data);
        
        // 判断是否是双后缀
        $preg = '/[\w].+\.(com|net|org|gov|edu|hk)\.cn$/';
        
        // 双后缀取后3位
        if (($n > 2) && preg_match($preg, $url)) $url = $data[$n-3] . '.' . $data[$n-2] . '.' . $data[$n-1];
        // 非双后缀取后两位
        else $url = $data[$n-2] . '.' . $data[$n-1];
        
        return $url;
    }

    /**
    * 提取域名
    * @param string|null $url 需要提取的域名
    * @return string 返回提取的域名
    */
    public function extract($url = null)
    {
        $url    = 'https://' . str_replace(['https://','http://','//'], '', $url);
        $result = $url;
        
        $url = parse_url($url);
        if (!isset($url['host'])) $result = null;
        $host = $url['host'];
        
        if(!strcmp(long2ip(sprintf('%u', ip2long($host))), $host)) $result = $host;
        else {
            $array  = explode('.', $host);
            $count  = count($array);
            // com.cn net.cn 等情况
            $endArr = ['com', 'net', 'org'];
            if (in_array($array[$count - 2], $endArr)) {
            	$result = $array[$count - 3] . '.' . $array[$count - 2] . '.' . $array[$count - 1];
            } else {
            	$result = $array[$count - 2] . '.' . $array[$count - 1];
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