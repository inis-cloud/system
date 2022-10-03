<?php

namespace app\api\controller\inis;

use app\model\mysql\{Users};
use think\{Request, Response};
use inis\utils\{File, helper};
use app\admin\controller\{Tool};
use think\facade\{Config, Lang};

use Firebase\JWT\{JWT, ExpiredException, BeforeValidException, SignatureInvalidException};

abstract class Base
{
    // inis 配置
    protected $tool;
    protected $File;
    protected $config;
    protected $helper;
    // 中间件
    protected $middleware = ['api', 'handle'];
    
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
        
        // 是否开启了缓存
        $apiCache = config('inis.api.cache', false);
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        $this->ApiCache = $apiCache and $cache;
        
        // 解析login-token
        $token        = !empty($header['authorization']) ? $header['authorization'] : (!empty($param['login-token']) ? $param['login-token'] : []);
        $this->user   = !empty($token) ? $this->parseJWT($token) : [];
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
    
    // 解析 JWT
    public function parseJWT($token)
    {
        $data = [];
        $code = 400;
        $msg  = Lang::get('错误！');

        // 校验token，避免前端传递错误的token
        if (!empty($token) or !is_null($token)) foreach (explode('.', $token) as $key => $val) {
            if (in_array($key, [0,1]) and empty(json_decode(base64_decode($val), true))) {
                return ['data'=>[], 'code'=>400, 'msg'=>Lang::get('签名有误！')];
            }
        }
        
        if (!empty($token) or !is_null($token)) try {
            
            JWT::$leeway = 60;
            $decoded = JWT::decode($token, $this->config['jwt']['key'], [$this->config['jwt']['encrypt']]);
            $array   = (array) $decoded;
            
            $data = Users::withoutField(['password'])->find($array['uid']);
            $code = 200;
            $msg  = Lang::get('合法登录！');
            
        } catch (SignatureInvalidException $e){
            // $e->getMessage()
            $msg = Lang::get('签名不正确！');
        } catch (BeforeValidException $e){
            $msg = Lang::get('login-token失效！');
        } catch (ExpiredException $e){
            $msg = Lang::get('login-token失效！');
        } catch (Exception $e){
            $msg = Lang::get('未知错误！');
        };
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];;
    }

    // 返回API的JSON标准结构
    protected function json($data = [], string $msg = '', int $code = 200, array $config = [], string $type = 'json') : Response
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
    
    public function __call($name, $arguments)
    {
        // 404 - 方法不存在的错误
        return $this->json([], Lang::get('资源不存在~'), 404);
    }
}