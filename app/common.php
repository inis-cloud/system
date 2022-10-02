<?php
/*
 * 应用公共文件
 */

// 跳转
function jump(...$args)
{
    throw new \think\exception\HttpResponseException(redirect(...$args));
}

// 过滤域名http(s):// 和末位 / 
function CommTrimURL($url = null)
{
    
    if (preg_match("/^http?:\\/\\/.+/", $url ?? '')) {
        
        $url = str_replace('http://', '', rtrim($url, '/'));
        
    } else if (preg_match("/^https?:\\/\\/.+/", $url ?? '')){
        
        $url = str_replace('https://', '', rtrim($url, '/'));
    }
    
    return $url;
}