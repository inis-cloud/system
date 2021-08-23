<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\Request;
use app\model\Log;
use think\facade\Cache;
use think\facade\Cookie;
use think\facade\Session;
use app\model\VerifyCode;
use think\facade\Validate;

use app\model\Users as UsersModel;
use app\validate\Users as UsersValidate;
use think\exception\ValidateException;

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\SignatureInvalidException;

/**
 * Class User
 * @package app\api\controller
 */
class Users extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        // 获取请求参数
        $param = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        $user   = !empty($param['login-token']) ? $this->parseJWT($param['login-token']) : [];
        
        // 存在的方法
        $method = ['one','all'];
        
        $mode   = (empty($param['id'])) ? 'all' : 'one';
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param, $user);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->create($data, $msg, $code);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['saves','remove','login','register'];
        
        $mode   = (empty($param['mode'])) ? 'saves' : $param['mode'];
        // 解析用户 token
        $user   = !empty($param['login-token']) ? $this->parseJWT($param['login-token']) : [];
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param,$user);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 清除缓存
        Cache::tag('users')->clear();
        
        return $this->create($data, $msg, $code);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read(Request $request, $id)
    {
        
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        
    }
    
    // 获取一条数据
    public function one($param, $user)
    {
        $data = [];
        $code = 400;
        $msg  = '无数据';
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        $opt = [
            'withoutField'=>['account','password','phone','email','level','remarks'],
        ];
        
        // 设置缓存名称
        $cache_name = 'users?id='.$param['id'];
        
        // 显示隐藏字段
        if (isset($user['data']) and in_array($user['data']->level, ['admin'])) $opt['withoutField'] = ['password'];
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
            // 获取数据库数据
            $data = UsersModel::ExpandAll($param['id'], $opt);
            Cache::tag(['users',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取全部数据
    public function all($param, $user)
    {
        $data = [];
        $code = 400;
        $msg  = '无数据';
        
        if (empty($param['page']))  $param['page']  = 1;
        if (empty($param['limit'])) $param['limit'] = 5;
        if (empty($param['order'])) $param['order'] = 'create_time asc';
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        $opt = [
            'page'   =>  (int)$param['page'], 
            'limit'  =>  (int)$param['limit'],
            'order'  =>  (string)$param['order'],
            'withoutField'=>['account','password','phone','email','level','remarks'],
        ];
        
        // 显示隐藏字段
        if (isset($user['data']) and in_array($user['data']->level, ['admin'])) $opt['withoutField'] = ['password'];
        
        // 设置缓存名称
        $cache_name = 'users?page='.$param['page'].'&limit='.$param['limit'].'&order='.$param['order'];
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
            
            // 获取数据库数据
            $data = UsersModel::ExpandAll(null, $opt);
            Cache::tag(['users'])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 登录
    public function login($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'error';
        $header = [];
        
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
            
            $users= UsersModel::whereOr([$map1,$map2])->withoutField(['account','phone','remarks'])->find();
            
            // 帐号自动锁定
            if ($this->config['login']['auto_lock_account'] and count($account_err) >= $this->config['login']['account_error_count']) {
                $users->status = 0;
                $users->save();
            }
            
            // 收集错误信息
            if (!$users or !$this->verify_password($param['password'], $users->password)) {
                
                $log = new Log;
                $log->save([
                    'ip'     =>$ip,
                    'msg'    =>$msg,
                    'types'  =>'login',
                    'content'=>$param['account'],
                ]);
                
                $msg = "帐号或密码错误！";
                
            }else{
                
                if ($users->status == 0) {
                    
                    $code   = 403;
                    $msg    = '该账号已被禁用！';
                    
                } else {
                    
                    $token = [
                        "iss" => "inis",            // 签发者 可以为空
                        "aud" => $users->account,   // 面象的用户，可以为空
                        "iat" => time(),            // 签发时间
                        "nbf" => time(),            // 在什么时候jwt开始生效  （这里表示生成100秒后才生效）
                        "exp" => time() + 7200,     // 过期时间 - 单位秒
                        "uid" => $users->id,        // 记录的userid的信息，这里是自已添加上去的，如果有其它信息，可以再添加数组的键值对
                    ];
                    
                    $jwt    = JWT::encode($token, $this->config['jwt_key'],'HS256');
                    $header = ['login-token'=>$jwt];
                    
                    // 单点登录字段
                    $login_auth = md5(md5('inis-'.time()));
                    if (empty($users->opt)) $users->opt = ['login_auth'=>$login_auth];
                    else $users->opt->login_auth = $login_auth;
                    $users->last_login_time = time();
                    $users->save();
                    
                    unset($users['password']);
                    
                    Session::set('login_account', $users);
                    Session::set('login_auth'   , $login_auth);
                    Cookie::set('login_account' , json_encode($users));
                    
                    // 登录成功
                    $data   = ['login-token'=>$jwt,'user'=>$users];
                    $code   = 200;
                    $msg    = '登录成功！';
                }
                
            }
        }
        
        $result = ['data'=>$data,'code'=>$code,'msg' =>$msg,'header'=>$header];
        
        return $result;
    }
    
    // 注册
    public function register($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        // 允许用户提交并存储的字段
        $obtain = ['email','nickname','password'];
        
        $time = time();
        $code_data = (empty($param['code'])) ? '' : strtoupper($param['code']);
        
        if (empty($code_data)) $msg = '验证码不得为空！';
        else {
            
            // 记录验证码 - 用于校验
            $verify_code = VerifyCode::where(['types'=>'email','content'=>$param['email'],'code'=>$code_data])->findOrEmpty();
            
            if (!$verify_code->isEmpty()) {
                
                if ($verify_code->end_time < $time) {
                    
                    $code = 412;
                    $msg  = '验证码已失效！';
                    
                } else {
                    
                    $users = new UsersModel;
                    
                    // 判断字段是否允许存储，防提权
                    foreach ($param as $key => $val) if (in_array($key, $obtain)) {
                        if ($key == 'password') $users->password = password_hash(md5($val), PASSWORD_BCRYPT);
                        else $users->$key = $val;
                    }
                    
                    // 随机默认头像
                    $users->head_img = $this->helper->RandomImg("local", "storage/users/anime/");
                    
                    $users->save();
                    $verify_code->delete();
                    
                    $data = ['email'=>$param['email'],'password'=>$param['password'], 'users'=>$users];
                    $code = 200;
                    $msg  = 'ok';
                }
                
            } else $msg = '验证码错误！';
        }
        
        $result = ['data'=>$data,'code'=>$code,'msg'=>$msg];
        
        return $result;
    }
    
    // 新增或者修改数据
    public function saves($param, $user)
    {
        $data   = [];
        $code   = 400;
        $msg    = 'ok';
        
        // 允许用户提交并存储的字段
        $obtain = ['account','password','nickname','sex','email','phone','head_img','description','status','level','address_url','remarks'];
        
        if (empty($param['id'])) $users = new UsersModel;
        else $users = UsersModel::find((int)$param['id']);
        
        // 判断字段是否允许存储，防提权
        foreach ($param as $key => $val) if (in_array($key, $obtain)) {
            if ($key == 'password') $users->password = password_hash(md5($val), PASSWORD_BCRYPT);
            else $users->$key = $val;
        }
        
        // 权限判断
        if (!in_array($user->level, ['admin'])) $msg = '无权限';
        else if ($user->status != 1) $msg = '账号被禁用';
        else {
            $code = 200;
            $users->save();
        }
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }
    
    // 删除数据
    public function remove($param, $user)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        $id = !empty($param['id']) ? $param['id']  : null;
        
        if (empty($id)) $msg = '请提交 id';
        else {
            
            $id = array_filter(explode(',', $id));
            
            // 存在该条数据
            if (in_array($user->level, ['admin'])) {
                
                $code = 200;
                UsersModel::destroy($id);
                
            } else {
                
                $code = 403;
                $msg  = '无权限';
            }
        }
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }
}
