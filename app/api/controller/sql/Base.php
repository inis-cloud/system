<?php

namespace app\api\controller\sql;

use inis\utils\{utils};
use think\{Request, Response};

abstract class Base
{
    // 中间件
    protected $middleware = ['auth','method'];
    
    // 构造器
    public function __construct(Request $request)
    {
        $this->utils = new utils;
        
        // 是否获取缓存
        $cache = empty($param['cache']) or $param['cache'] == 'true' ? true : false;
        $this->Cache = config('inis.api.cache', true) and $cache;
    }

    // 密码验证
    public function verify_password($password, $enpassword)
    {
        return password_verify(md5($password), $enpassword);
    }

    // 创建密码
    public static function create_password($password)
    {
        return password_hash(md5($password), PASSWORD_BCRYPT);
    }

    // 获取请求参数
    public function param($value = null, $default = null)
    {
        return request()->param($value, $default);
    }

    // 获取请求头
    public function header($value = null, $default = null)
    {
        return request()->header($value, $default);
    }
    
    // 返回API的JSON标准结构
    protected function json($data = [], ?string $msg = 'success', ?int $code = 200, ?array $config = [], ?string $type = 'json') : Response
    {
        // 标准API结构生成
        $result = ['code'=>$code, 'msg'=>$msg, 'data'=>$data];
        
        return Response::create(array_merge($result, $config), $type);
    }
    
    public function __call($name, $arguments)
    {
        // 404 - 方法不存在的错误
        return $this->json([], lang('难道你和我一样，也迷路了吗？'), 404);
    }
}