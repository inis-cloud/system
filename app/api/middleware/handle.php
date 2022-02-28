<?php
declare (strict_types = 1);

namespace app\api\middleware;

use Closure;
use think\facade\{Config};
use think\{Request, Response};
use Firebase\JWT\{JWT, ExpiredException, BeforeValidException, SignatureInvalidException};

class handle
{
    protected $config;
    
    public function __construct()
    {
        $this->config = Config::get('inis');
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
        $obtain = ['search','comments','verify-code','test','proxy','cache'];
        $allow  = ['users?mode=login','users?mode=register'];
        // 请求接口地址
        $path   = $request->pathinfo();
        
        $mode   = !empty($param['mode']) ? $param['mode'] : null;
        
        // ↑↑↑ 前置中间件执行区域 ↑↑↑
        $reponse = $next($request);
        // ↓↓↓ 后置中间件执行区域 ↓↓↓
        
        if (in_array($request->method(true), $method))
        {
            $map1 = (in_array($path, $obtain) || in_array($path.'?mode='.$mode, $allow));
            $map2 = $request->method(true) === "PUT";
            $map3 = $request->method(true) === "POST";
            $map4 = $request->method(true) === "DELETE";
            
            // 允许未登录操作
            if (($map2 or $map3 or $map4) and $map1) {
                
                // code ...
                
            } else {
                
                $data = [];
                $code = 403;
                $msg  = '非法访问！';
                
                // 禁止未登录操作
                if (isset($param['login-token']) or isset($header['login-token'])) {
                    
                    $login_token = (isset($header['login-token'])) ? $header['login-token'] : $param['login-token'];
                    
                    try {
                        
                        JWT::$leeway = 60;
                        $decoded = JWT::decode($login_token, $this->config['jwt']['key'], [$this->config['jwt']['encrypt']]);
                        $arr = (array) $decoded;
                        
                    } catch (SignatureInvalidException $e){
                        $msg = '签名不正确！';
                        $reponse = json(['data'=>$data,'code'=>$code,'msg'=>$msg]);
                    } catch (BeforeValidException $e){
                        $msg = 'login-token失效！';
                        $reponse = json(['data'=>$data,'code'=>$code,'msg'=>$msg]);
                    } catch (ExpiredException $e){
                        $msg = 'login-token失效！';
                        $reponse = json(['data'=>$data,'code'=>$code,'msg'=>$msg]);
                    } catch (Exception $e){
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
