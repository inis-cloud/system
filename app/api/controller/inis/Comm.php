<?php
declare (strict_types = 1);

namespace app\api\controller\inis;

use app\BaseController;
use think\{Request, Response};
use app\index\controller\Tool;
use inis\utils\{File, helper};
use think\facade\{Config, Validate};
use think\exception\ValidateException;
use app\validate\Users as UsersValidate;
use app\model\mysql\{Log, Users, Options, VerifyCode};

use Firebase\JWT\{JWT, ExpiredException, BeforeValidException, SignatureInvalidException};

class Comm extends BaseController
{
    // 工具类
    protected $tool;
    // 助手函数
    protected $helper;
    // inis 配置
    protected $config;
    
    public function __construct(Request $request)
    {
        $this->File   = new File;
        $this->tool   = new Tool;
        $this->helper = new helper;
        $this->config = Config::get('inis');
        
        // 获取请求参数
        $param  = $request->param();
        $header = $request->header();
        
        $this->header = $header;
        
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
        header("Access-Control-Allow-Headers:DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");
    }
    
    // 登录
    public function login(Request $request)
    {
        // 获取数据
        $param = $request->param();
        
        $data  = [];
        $code  = 400;
        $msg   = '帐号和密码不能为空！';
        $header= [];
        
        if (isset($param['account']) and isset($param['password'])) {
            
            $ip   = $this->helper->GetClientIP();
            $time = time() - $this->config['login_errot_time'];
            
            $factor1 = ['ip','=',$ip];
            $factor2 = ['create_time','>',$time];
            $factor3 = ['content','=',$param['account']];
            
            $login_error = Log::where([$factor1,$factor2])->field(['id'])->select();
            $account_err = Log::where([$factor2,$factor3])->field(['id'])->select();
            
            // 安全检查
            if (count($login_error) >= $this->config['login_error_count']) {
                
                // 秒转人性化时间
                $second_to_time = $this->helper->NaturalSecond($this->config['login_errot_time']);
                
                $code = 403;
                $msg  = '您的错误次数达到'.$this->config['login_error_count'].'次，该设备已被禁止'.$second_to_time.'内登陆此系统！';
                
            } else {
                
                $map1 = ['account', 'like', $param['account']];
                $map2 = ['email',   'like', $param['account']];
                // $map3 = ['phone',   'like', $param['account']];
                
                $users = Users::whereOr([$map1,$map2])->find();
                
                // 帐号自动锁定
                if ($this->config['auto_lock_account'] and count($account_err) >= $this->config['account_error_count']) {
                    $users->status = 0;
                    $users->save();
                }
                
                // 收集错误信息
                if (!$users or !$this->verify_password($param['password'], $users['password'])){
                    
                    $msg    = '用户名或密码不正确！';
                    
                    $log    = new Log;
                    $log->save([
                        'ip'     =>$ip,
                        'msg'    =>$msg,
                        'types'  =>'login',
                        'content'=>$param['account'],
                    ]);
                    
                }else{
                    
                    $token = [
                        "iss" => "inis",            // 签发者 可以为空
                        "aud" => $users['account'], // 面象的用户，可以为空
                        "iat" => time(),            // 签发时间
                        "nbf" => time(),            // 在什么时候jwt开始生效  （这里表示生成100秒后才生效）
                        "exp" => time() + 7200,     // 过期时间 - 单位秒
                        "uid" => $users['id'],      // 记录的userid的信息，这里是自已添加上去的，如果有其它信息，可以再添加数组的键值对
                    ];
                    
                    $jwt    = JWT::encode($token, $this->config['jwt']['key'], $this->config['jwt']['encrypt']);
                    $header = ['login-token'=>$jwt];
                    
                    // 登录成功
                    $data   = ['login-token'=>$jwt];
                    $code   = 200;
                    $msg    = '登录成功！';
                }
            }
        }
        
        return $this->create($data, $msg, $code)->header($header);
    }
    
    // 校验登录
    public function islogin(Request $request)
    {
        // 获取请求参数
        $param  = $request->param();
        $header = $request->header();
        
        $data = [];
        $code = 400;
        $msg  = '非法访问！';
        
        if(isset($param['login-token']) or isset($header['login-token'])){
            
            $login_token = (isset($header['login-token'])) ? $header['login-token'] : $param['login-token'];
            
            try {
                
                JWT::$leeway = 60;
                $decoded = JWT::decode($login_token, $this->config['jwt']['key'], [$this->config['jwt']['encrypt']]);
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
        }
        
        return $this->create($data, $msg, $code);
    }
    
    // 注册
    public function register(Request $request)
    {
        // 获取数据
        $param = $request->param();
        
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        // 允许用户提交并存储的字段
        $obtain = ['email','nickname','password'];
        
        $time = time();
        $code_data = (empty($param['code'])) ? '' : strtoupper($param['code']);
        
        if (empty($code_data)) $msg = '验证码不得为空！';
        else {
            
            $verify_code = VerifyCode::where(['types'=>'email','content'=>$param['email'],'code'=>$code_data])->findOrEmpty();
            
            if (!$verify_code->isEmpty()) {
                
                if ($verify_code->end_time < $time) {
                    
                    $code = 412;
                    $msg  = '验证码已失效！';
                    
                } else {
                    
                    $users = new Users;
                    
                    // 存储数据
                    foreach ($param as $key => $val) {
                        // 判断字段是否允许存储，防提权
                        if (in_array($key, $obtain)) {
                            if ($key == 'password') $users->password = password_hash(md5($val), PASSWORD_BCRYPT);
                            else $users->$key = $val;
                        }
                    }
                    
                    // 随机默认头像
                    $users->head_img = (new helper)->RandomImg("local", "storage/users/anime/");
                    
                    $users->save();
                    $verify_code->delete();
                    
                    $data = ['email'=>$param['email'],'password'=>$param['password']];
                    $code = 200;
                    $msg  = 'ok';
                }
                
            } else $msg = '验证码错误！';
        }
        
        return $this->create($data, $msg, $code);
    }

    // 获取 Token 接口
    public function token()
    {
        $options = Options::where(['keys'=>'config:security'])->find();
        
        if($options->opt->token->status == 0){
            $data = [];
            $msg  = '未开启 Token 验证';
            $code = 204;
        }elseif ($options->opt->token->open == 0){
            $data = ['想什么呢，Token这么重要的东西，能给你吗？'];
            $msg  = '未经授权！';
            $code = 403;
        }else{
            $data = $options->opt->token->value;
            $msg  = '请求成功！';
            $code = 200;
        }
        
        return $this->create($data, $msg, $code);
    }
}
