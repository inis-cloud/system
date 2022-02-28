<?php
declare (strict_types = 1);

namespace app\admin\middleware;

use Closure;
use inis\utils\{File};
use app\model\mysql\{Users};
use think\{Request, Response};
use think\facade\{Cookie, Session};

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
        if (!preg_match('/login/', $request->pathinfo())) if (empty(Session::has('login_account'))) return redirect((string) url('admin/comm/login'));
        
        // 单点登录
        if (!empty(Session::get('login_account'))) {
            
            $login_account= Session::get('login_account');
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
                
                return redirect((string) url('admin/comm/login'));
            }
            
        } else return redirect((string) url('admin/comm/login'));
        
        // ↑↑↑ 前置中间件执行区域 ↑↑↑
        $reponse = $next($request);
        // ↓↓↓ 后置中间件执行区域 ↓↓↓
        
        // 回调本身并返回response对象
        return $reponse;
    }
}
