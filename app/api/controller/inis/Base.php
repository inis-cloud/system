<?php

namespace app\api\controller\inis;

use think\facade\{Config};
use app\model\mysql\{Users};
use think\{Request, Response};
use inis\utils\{File, helper};
use app\admin\controller\{Tool};
use app\api\middleware\{api, handle};

use Firebase\JWT\{JWT, ExpiredException, BeforeValidException, SignatureInvalidException};

abstract class Base
{
    // inis 配置
    protected $tool;
    protected $File;
    protected $config;
    protected $helper;
    // 中间件
    protected $middleware = [api::class, handle::class];
    
    // 构造器
    public function __construct(Request $request)
    {
        $this->tool   = new Tool;
        $this->File   = new File;
        $this->helper = new helper;
        $this->config = Config::get('inis');
        
        // 获取请求参数
        $param  = $request->param();
        $header = $request->header();
        
        $this->header = $header;
        
        // 解析login-token
        $token  = !empty($header['login-token']) ? $header['login-token'] : (!empty($param['login-token']) ? $param['login-token'] : []);
        $this->user   = !empty($token) ? $this->parseJWT($token) : [];
    }

    protected function create($data = [], string $msg = '', int $code = 200, array $config = [], string $type = 'json') : Response
    {
        // 标准API结构生成
        $result = [
            // 状态码
            'code'  =>  $code,
            // 消息
            'msg'   =>  $msg,
            // 数据
            'data'  =>  $data
        ];
        
        // 合并其他数据数据
        $result = !empty($config) ? array_merge($result, $config) : $result;
        
        return Response::create($result, $type);
    }

    // 密码验证
    public function verify_password($npwd,$rpwd)
    {
        return password_verify(md5($npwd), $rpwd);
    }

    // 创建密码
    public static function create_password($pwd)
    {
        return password_hash(md5($pwd),PASSWORD_BCRYPT);
    }
    
    // 解析 JWT
    public function parseJWT($token)
    {
        $data = [];
        $code = 400;
        $msg  = 'error';
        
        try {
            
            JWT::$leeway = 60;
            $decoded = JWT::decode($token, $this->config['jwt']['key'], [$this->config['jwt']['encrypt']]);
            $arr = (array) $decoded;
            
            $data = Users::withoutField(['password'])->find($arr['uid']);
            $code = 200;
            $msg  = '合法登录！';
            
        } catch (SignatureInvalidException $e){
            // $e->getMessage()
            $msg = '签名不正确！';
        } catch (BeforeValidException $e){
            $msg = 'login-token失效！';
        } catch (ExpiredException $e){
            $msg = 'login-token失效！';
        } catch (Exception $e){
            $msg = '未知错误！';
        };
        
        $result = ['data'=>$data,'code'=>$code,'msg'=>$msg];
        
        return $result;
    }
    
    public function __call($name, $arguments)
    {
        // 404 - 方法不存在的错误
        return $this->create([], '资源不存在~', 404);
    }
}