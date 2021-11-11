<?php
declare (strict_types = 1);

namespace app\api\middleware;

use Closure;
use think\Request;
use think\Response;
use Firebase\JWT\JWT;
use think\facade\Config;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\SignatureInvalidException;

class handle
{
    protected $key = '';
    
    public function __construct()
    {
        $this->key = Config::get('app.jwt_key');
    }
    
    /**
     * 处理请求
     *
     * @access public
     * @param Request $request
     * @param Closure $next
     * @param array   $header
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        // halt($request);
        
        // 获取请求参数
        $param  = $request->param();
        $header = $request->header();
        
        // 需要校验登录请求方式
        $method = ['POST','PUT','DELETE'];
        // 允许免登陆的请求接口
        $obtain = ['search','comments','verify-code','test'];
        $allow  = ['users?mode=login','users?mode=register'];
        // 请求接口地址
        $path   = $request->pathinfo();
        
        $mode   = !empty($param['mode']) ? $param['mode'] : null;
        
        $request->param = 'ThinkPHP';
        
        // ↑↑↑ 前置中间件执行区域 ↑↑↑
        $reponse = $next($request);
        // ↓↓↓ 后置中间件执行区域 ↓↓↓
        
        if (in_array($request->method(true), $method))
        {
            $map1 = (in_array($path, $obtain) || in_array($path.'?mode='.$mode, $allow));
            // 允许未登录操作
            if ($request->method(true) === "POST" and $map1) {
                
                // code ...
                
            } else {
                
                $data = [];
                $code = 403;
                $msg  = '非法访问！';
                
                // 禁止未登录操作
                if(isset($param['login-token']) or isset($header['login-token'])){
                    
                    $login_token = (isset($header['login-token'])) ? $header['login-token'] : $param['login-token'];
                    
                    try{
                        
                        JWT::$leeway = 60;
                        $decoded = JWT::decode($login_token, $this->key, ['HS256']);
                        $arr = (array) $decoded;
                        
                    }catch (SignatureInvalidException $e){
                        $msg = '签名不正确！';
                        $reponse = json(['data'=>$data,'code'=>$code,'msg'=>$msg]);
                    }catch (BeforeValidException $e){
                        $msg = 'login-token失效！';
                        $reponse = json(['data'=>$data,'code'=>$code,'msg'=>$msg]);
                    }catch (ExpiredException $e){
                        $msg = 'login-token失效！';
                        $reponse = json(['data'=>$data,'code'=>$code,'msg'=>$msg]);
                    }catch (Exception $e){
                        $msg = '未知错误！';
                        $reponse = json(['data'=>$data,'code'=>$code,'msg'=>$msg]);
                    };
                    
                } else  $reponse = json(['data'=>$data,'code'=>$code,'msg'=>$msg]);
            }
        }
        
        // 回调本身并返回response对象
        return $reponse;
    }
}
