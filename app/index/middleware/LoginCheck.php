<?php
declare (strict_types = 1);

namespace app\index\middleware;

use Closure;
use think\Request;
use think\Response;
use app\model\Users;
use inis\utils\File;
use think\facade\Cookie;
use think\facade\Session;

class LoginCheck
{
    
    /**
     * 处理请求
     *
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        // halt($request);
        
        $install = false;
        
        $File = new File;
        $list = $File->listDirInfo('./',true,'env');
        
        foreach ($list as $val) {
            $item    = explode('/', $val);
            $install = array_pop($item);
            if ($install == 'install.env') $install = true;
        }
        
        // 登录验证
        if (empty(Session::has('login_account')) && !preg_match('/login/', $request->pathinfo())) {
            
            return redirect((string) url('index/comm/login'));
        }
        
        // 单点登录
        if (!empty(Session::get('login_account'))) {
            
            $login_account   = Session::get('login_account');
            $users = Users::find($login_account['id']);
            
            $login_auth_1 = $users->opt->login_auth;
            $login_auth_2 = Session::get('login_auth');
            
            // 登录密钥不一致或帐号被禁用，挤退
            if ($login_auth_1 !== $login_auth_2 or $users->status == 0) {
                
                $users->opt->login_auth = '';
                // 不转换中文编码
                $users->opt = json_encode($users->opt, JSON_UNESCAPED_UNICODE);
                $users->save();
                
                // 清除用户登录信息
                Session::clear();
                Cookie::delete('login_account');
                
                return redirect((string) url('index/comm/login'));
            }
        }
        
        // ↑↑↑ 前置中间件执行区域 ↑↑↑
        $reponse = $next($request);
        // ↓↓↓ 后置中间件执行区域 ↓↓↓
        
        // 回调本身并返回response对象
        return $reponse;
    }
}