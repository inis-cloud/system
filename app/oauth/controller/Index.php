<?php
// +----------------------------------------------------------------------
// | 认证控制器
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------

namespace app\oauth\controller;

use think\Request;
use liliuwei\social\Oauth;
use liliuwei\social\GetInfo;

class Index
{
    
    public function __construct()
    {
        
    }
    // 登录地址
    public function login(Request $request)
    {
        $param = $request->param();
        $type  = (!empty($param['type'])) ? $param['type'] : null;
        
        if ($type == null) $this->error('参数错误');
        
        // 获取对象实例
        $sns = oauth::getInstance($type);
        
        // 跳转到授权页面
        return redirect((string) $sns->getRequestCodeURL(['type'=>$type]));
    }

    // 授权回调地址
    public function callback(Request $request, $code = null)
    {
        $param = $request->param();
        $type  = (!empty($param['type'])) ? $param['type'] : null;
        $code  = (!empty($param['code'])) ? $param['code'] : null;
        
        if ($type == null || $code == null) return json('error');
        
        $sns = Oauth::getInstance($type);
        
        // 获取TOKEN
        $token = $sns->getAccessToken($code);
        
        // 获取当前第三方登录用户信息
        if (is_array($token)) {
            $user_info = GetInfo::getInstance($type, $token);
            // 获取第三方用户资料
            dump($user_info);
            // 统一使用$sns->openid()获取openid
            $sns->openid();
            // QQ和微信、淘宝可以获取unionid
            // $sns->unionid();
            dump($sns->openid());
            echo '登录成功!!';
            echo '正在持续开发中，敬请期待!!';
        } else {
            echo "获取第三方用户的基本信息失败";
        }
    }
    
    
    
    
    
    
    
    
    
}