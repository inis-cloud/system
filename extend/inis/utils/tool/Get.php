<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: Get class - 获取类
// +----------------------------------------------------------------------

namespace inis\utils\tool;

/**
 * Class Get
 * @package inis\utils\tool
 */
class Get
{
    /**
     * 获取本地域名
     * @return string 返回本地域名
     */
    public function domain()
    {
        $type   = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        $domain = &$_SERVER['HTTP_HOST'];
        return $type.$domain;
    }

    /**
     * 获取客户端IP
     * @param string|null $ip IP
     * @return array 返回IP信息
     */
    public function ip($ip = null)
    {
        // 没有传入IP - 自动获取客户端IP
        if (is_null($ip)) {

            // 客户端IP 或 NONE
            if(!empty($_SERVER["HTTP_CLIENT_IP"])) $ip = $_SERVER["HTTP_CLIENT_IP"];
            
            // 多重代理服务器下的客户端真实IP地址（可能伪造）,如果没有使用代理，此字段为空
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                
                $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
                
                if ($ip) { array_unshift($ips, $ip); $ip = false; }
                
                for ($i = 0; $i < count($ips); $i++) {
                    try {
                        if (!preg_match("^(10│172.16│192.168).", $ips[$i])) {
                            $ip = $ips[$i];
                            break;
                        }
                    } catch (ValidateException $e) {
                        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
                    } catch (\Exception $e) {
                        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
                    }
                }
            }

            // 客户端IP 或 (最后一个)代理服务器 IP
            $ip = ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
        }

        $result['ip'] = $ip;
        $result['is'] = filter_var($ip, \FILTER_VALIDATE_IP) ? true : false;
        $result['version'] = filter_var($ip, \FILTER_VALIDATE_IP,\FILTER_FLAG_IPV4) ? 4 : (filter_var($ip, \FILTER_VALIDATE_IP,\FILTER_FLAG_IPV6) ? 6 : null);
        return $result;
    }

    /**
     * 获取重定向后的URL
     * @param string|null $url URL地址
     * @return string 返回重定向后的URL
     */
    public function redirect(string $url = null)
    {
        $result = $url;
        $header = get_headers($url, 1);
        
        if (strpos($header[0],'301') || strpos($header[0],'302')) {
            
            if(is_array($header['Location'])) $result = $header['Location'][count($header['Location'])-1];
            else $result = $header['Location'];
        }
        
        return $result;
    }

    /**
     * 生成唯一的GUID
     * @param string|null $prefix 前缀
     * @return string 返回唯一的GUID
     */
    public function guid($prefix = '')
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid  = substr($chars, 0, 8);
        $uuid .= substr($chars, 8, 4);
        return strtoupper($prefix . $uuid);
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