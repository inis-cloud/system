<?php

namespace app\admin\controller;

use think\Request;
use app\BaseController;
use inis\utils\{helper};
use think\facade\{View, Session, Config};
use app\model\mysql\{Log, Users, Options};

class Comm extends BaseController
{
    // 助手函数
    protected $helper;
    
    // inis 配置
    protected $config;
    
    function initialize()
    {
        $this->helper = new helper;
        $this->config = Config::get('inis');
        
        // 系统配置
        $system_config = Options::where(['keys'=>'config:system'])->findOrEmpty();
        // 内置的CDN
        $in_cdn = $this->config['official']['cdn'];
        // 用户自己的CDN
        $u_cdn  = (!empty($system_config['opt']->optimize)) ? $system_config['opt']->optimize->cdn : '';
        // 处理后的CDN
        $cdn = array_values(array_filter(explode('/',str_replace(['https','http',':'],'',$u_cdn))));
        
        // 使用本地资源
        if (count($cdn) == 0) $cdn = '';
        // 处理CDN为合适的格式
        else if (count($cdn) == 1) $cdn = $this->helper->CustomProcessApi($u_cdn,'system/default');
        // 用户自己的CDN
        else $cdn = $u_cdn;
        // 去除末位 / 
        if (!empty($cdn) and (substr($cdn, -1) == '/')) $cdn = substr($cdn, 0, strlen($cdn)-1);
        
        // 站点信息
        $site = Options::where(['keys'=>'site'])->findOrEmpty();
        $sites= (!$site->isEmpty()) ? $site['opt'] : (object)[
            'title'       => 'inis博客系统',
            'keywords'    => 'INIS API,inis博客系统,inis程序,inis系统,inis blog',
            'description' => 'inis · 新一代博客系统！这是市面上为数不多的新一代博客系统，整站封装，全站分离，真正意义上的前后端分离。每一行代码都用心设计，用最少的代码量和最优雅架构设计，实现最完美的系统，让你拥有更极致的体验。复杂的研究留给我们，简单的体验留给用户！',
            'favicon'     => $this->helper->domain() . '/admin/images/logo_sm.png',
            'copy'        => '备案号'
        ];
        
        View::assign([
            'CONFIG'      => (object)[
                'ROOT'    => '/admin/',
                'CDN'     => empty($cdn) ? $in_cdn : $cdn . '/',
                'VERSION' => $this->config['version'],
                'SITE'    => $sites
            ]
        ]);
    }

    // 登录
    public function login(Request $request)
    {
        if ($request->isPost()) {
            
            $param= $request->param();
            
            $data = [];
            $code = 400;
            $msg  = '用户名或密码不正确！';
            
            $ip   = $this->helper->GetClientIP();
            $time = time() - $this->config['login']['error_time'];
            
            $factor1 = ['ip','=',$ip];
            $factor2 = ['create_time','>',$time];
            $factor3 = ['content','=',$param['account']];
            
            $login_error = Log::where([$factor1,$factor2])->field(['id'])->select();
            $account_err = Log::where([$factor2,$factor3])->field(['id'])->select();
            
            // 安全检查
            if (count($login_error) >= $this->config['login']['error_count']) {
                
                // 秒转人性化时间
                $second_to_time = $this->helper->NaturalSecond($this->config['login']['error_time']);
                
                $code = 403;
                $msg  = '您的错误次数达到'.$this->config['login']['error_count'].'次，该设备已被禁止'.$second_to_time.'内登陆此系统！';
                
            } else {
                
                $map1 = ['account', 'like', $param['account']];
                $map2 = ['email'  , 'like', $param['account']];
                // $map3 = ['phone', 'like', $param['account']];
                
                $users= Users::whereOr([$map1,$map2])->find();
                // $users= Users::where([function ($query) use ($map1, $map2) {
                //     $query->where(['level'=>'admin'])->where([$map1])->whereOr([$map2])->find();
                // }])->find();
                
                // 帐号自动锁定
                if ($this->config['login']['auto_lock_account'] and count($account_err) >= $this->config['login']['account_error_count']) {
                    $users->status = 0;
                    $users->save();
                }
                
                // 收集错误信息
                if (!$users or !$this->verify_password($param['password'], $users['password'])) {
                    
                    $log    = new Log;
                    $log->save([
                        'ip'     =>$ip,
                        'msg'    =>$msg,
                        'types'  =>'login',
                        'content'=>$param['account'],
                    ]);
                    
                } else {
                    
                    if ($users->status == 0) {
                        
                        $code   = 403;
                        $msg    = '该账号已被禁用！';
                        
                    } else if ($users->level != "admin"){
                        $code   = 403;
                        $msg    = '权限不足！';
                    } else {
                        
                        // 登录成功
                        $code   = 200;
                        $msg    = '登录成功！';
                        
                        // 单点登录字段
                        $login_auth = md5(md5('inis-'.time()));
                        if (empty($users->opt)) $users->opt = ['login_auth'=>$login_auth];
                        else $users->opt->login_auth = $login_auth;
                        $users->last_login_time = time();
                        $users->opt = json_encode($users->opt, JSON_UNESCAPED_UNICODE);
                        $users->save();
                        
                        unset($users['password']);
                        Session::set('login_account', $users);
                        Session::set('login_auth'   , $login_auth);
                    }
                    
                }
            }
            
            return $this->create($data, $msg, $code);
            
        }
        
        return View::fetch('/login');
    }

    // 退出
    public function logout()
    {
        $uid   = Session::get('login_account')['id'];
        $users = Users::find($uid);
        $users->opt->login_auth = '';
        $users->opt = json_encode($users->opt, JSON_UNESCAPED_UNICODE);
        $users->save();
        
        // 清除用户登录信息
        Session::clear();
        
        return redirect((string) url('admin/comm/login'));
    }

    // 忘记密码
    public function reset()
    {
        return View::fetch('/reset');
    }

    // 注册
    public function register()
    {
        return View::fetch('/register');
    }
    
    // 404 - 方法不存在的错误
    public function __call($name, $args)
    {
        return redirect((string) url('/admin'));
    }
}