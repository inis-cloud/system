<?php
declare (strict_types = 1);

namespace app\api\middleware;

use Closure;
use app\model\mysql\{Users};
use think\{Request, Response};
use think\facade\{Config, Lang, Log};
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

        $data = [];
        $code = 403;
        $msg  = Lang::get('禁止非法访问！');
        
        // 获取请求参数
        $param  = $request->param();
        $header = $request->header();
        
        // 需要校验登录请求方式
        $method = ['POST','PUT','DELETE'];
        // 允许免登陆的请求接口
        $obtain = [
            'comments','test','proxy', 'users/login',
            'users/vcl','users/register','users/check', 'comments/like', 'comments/add'
        ];
        $allow  = ['users?mode=login','users?mode=register','users?mode=check','users?mode=vcl','links?mode=apply'];
        // 请求接口地址
        $path   = $request->pathinfo();
        
        $mode   = !empty($param['mode']) ? $param['mode'] : null;

        if (in_array($request->method(true), $method)) {

            $map1 = (in_array($path, $obtain) || in_array($path.'?mode='.$mode, $allow));
            $map2 = $request->method(true) === "PUT";
            $map3 = $request->method(true) === "POST";
            $map4 = $request->method(true) === "DELETE";
            $token= isset($header['authorization']) ? $header['authorization'] : (isset($param['login-token']) ? $param['login-token'] : null);
            
            // 允许未登录操作
            if (($map2 or $map3 or $map4) and $map1) {
                
            } else if (empty($token)) return json(['data'=>[],'code'=>403,'msg'=>Lang::get('禁止非法访问！')]);

            // 校验token，避免前端传递错误的token
            if (!empty($token) or !is_null($token)) foreach (explode('.', $token ?? '') as $key => $val) {
                if (in_array($key, [0,1]) and empty(json_decode(base64_decode($val), true))) {
                    return json(['data'=>[],'code'=>400,'msg'=>Lang::get('签名有误！')]);
                }
            }
            
            if (!empty($token) or !is_null($token)) try {
                
                JWT::$leeway = 60;
                $decoded = JWT::decode($token, $this->config['jwt']['key'], [$this->config['jwt']['encrypt']]);
                $array   = (array)$decoded;
                $user    = Users::withoutField(['password'])->find($array['uid']);
                $request->user = $user;

            } catch (SignatureInvalidException $e){

                $msg = Lang::get('签名不正确！');
                return json(['data'=>$data,'code'=>$code,'msg'=>$msg]);

            } catch (BeforeValidException $e){

                $msg = Lang::get('login-token失效！');
                return json(['data'=>$data,'code'=>$code,'msg'=>$msg]);

            } catch (ExpiredException $e){

                $msg = Lang::get('login-token失效！');
                return json(['data'=>$data,'code'=>$code,'msg'=>$msg]);

            } catch (Exception $e){

                $msg = Lang::get('未知错误！');
                return json(['data'=>$data,'code'=>$code,'msg'=>$msg]);
            };
        }

        // ↑↑↑ 前置中间件执行区域 ↑↑↑
        $reponse = $next($request);
        // ↓↓↓ 后置中间件执行区域 ↓↓↓

        // 后置中间件代码会继续执行
        
        // 回调本身并返回response对象
        return $reponse;
    }
}
