<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: Curl class - 网络请求类
// +----------------------------------------------------------------------

namespace inis\utils\tool;

/**
 * Class Curl
 * @package inis\utils\tool
 */
class Curl
{
    // CURL GET 请求
    public function get(string $url, array $params = [], array $headers = [], array $options = [])
    {
        $header  = [
            'Content-type' => 'application/json;',
            'Accept'       => 'application/json',
            'origin'       => str_replace(['https','http',':','//'], '', (new Get)->domain())
        ];
        $params  = !empty($params)  ? http_build_query($params) : json_encode($params);
        $headers = !empty($headers) ? array_merge($header, $headers) : $header;
        $ua      = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36';
        
        foreach ($headers as $key => $val) $_headers[] = $key . ':' . $val;
        
        $curl   = curl_init();
        
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $_headers);
        curl_setopt($curl, CURLOPT_URL, $url . '?' . $params);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_USERAGENT, $ua);
        curl_setopt_array($curl, $options);
        
        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result,true);
        
        return $result;
    }

    // CURL PUT 请求
    public function put(string $url, array $params = [], array $headers = [], array $options = [])
    {
        $header  = [
            'Content-type' => 'application/json;',
            'Accept'       => 'application/json',
            'origin'       => str_replace(['https','http',':','//'], '', (new Get)->domain())
        ];
        $params  = json_encode($params);
        $headers = !empty($headers) ? array_merge($header, $headers) : $header;
        $ua      = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36';
        
        foreach ($headers as $key => $val) $_headers[] = $key . ':' . $val;
        
        $curl   = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $_headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_USERAGENT, $ua);
        curl_setopt_array($curl, $options);

        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result, true);
        
        return $result;
    }

    // CURL POST 请求
    public function post(string $url, array $params = [], array $headers = [], array $options = [])
    {
        $header  = [
            'Content-type' => 'application/json;charset="utf-8"',
            'Accept'       => 'application/json',
            'origin'       => str_replace(['https','http',':','//'], '', (new Get)->domain())
        ];
        $params  = json_encode($params);
        $headers = !empty($headers) ? array_merge($header, $headers) : $header;
        $ua      = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36';
        
        foreach ($headers as $key => $val) $_headers[] = $key . ':' . $val;
        
        $curl   = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $_headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, $ua);
        curl_setopt_array($curl, $options);
        
        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result,true);
        
        return $result;
    }

    // CURL DELETE 请求
    public function delete(string $url, array $params = [], array $headers = [], array $options = [])
    {
        $header  = [
            'Content-type' => 'application/json;charset="utf-8"',
            'Accept'       => 'application/json',
            'origin'       => str_replace(['https','http',':','//'], '', (new Get)->domain())
        ];
        $params  = json_encode($params);
        $headers = !empty($headers) ? array_merge($header, $headers) : $header;
        $ua      = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36';
        
        foreach ($headers as $key => $val) $_headers[] = $key . ':' . $val;
        
        $curl   = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $_headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');   
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_USERAGENT, $ua);
        curl_setopt_array($curl, $options);
        
        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result,true);
        
        return $result;
    }

    // CURL PATCH 请求
    public function patch(string $url, array $params = [], array $headers = [], array $options = [])
    {
        $header  = [
            'Content-type' => 'application/json;charset="utf-8"',
            'Accept'       => 'application/json',
            'origin'       => str_replace(['https','http',':','//'], '', (new Get)->domain())
        ];
        $params  = json_encode($params);
        $headers = !empty($headers) ? array_merge($header, $headers) : $header;
        $ua      = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36';
        
        foreach ($headers as $key => $val) $_headers[] = $key . ':' . $val;
        
        $curl   = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $_headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');  
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_USERAGENT, $ua);
        curl_setopt_array($curl, $options);
        
        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result,true);
        
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