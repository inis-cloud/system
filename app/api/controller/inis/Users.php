<?php
declare (strict_types = 1);

namespace app\api\controller\inis;

use think\Request;
use think\exception\ValidateException;
use app\validate\Users as UsersValidate;
use think\facade\{Cache, Cookie, Session, Validate};
use app\model\mysql\{Log, Options, VerifyCode, Users as UsersModel};

use Firebase\JWT\{JWT, ExpiredException, BeforeValidException, SignatureInvalidException};

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
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['one','all'];
        
        $mode   = (empty($param['id'])) ? 'all' : 'one';
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param);
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
        $method = ['saves','remove','login','register','check'];
        
        $mode   = (empty($param['mode'])) ? 'saves' : $param['mode'];
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 清除缓存
        Cache::tag('users')->clear();
        
        return $this->create($data, $msg, $code);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $IID
     * @return \think\Response
     */
    public function read(Request $request, $IID)
    {
        
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $IID
     * @return \think\Response
     */
    public function update(Request $request, $IID)
    {
        
    }

    /**
     * 删除指定资源
     *
     * @param  int  $IID
     * @return \think\Response
     */
    public function delete(Request $request, $IID)
    {
        
    }
    
    // 获取一条数据
    public function one($param)
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
        if (isset($this->user['data']) and in_array($this->user['data']->level, ['admin'])) $opt['withoutField'] = ['password'];
        
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
    public function all($param)
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
        if (isset($this->user['data']) and in_array($this->user['data']->level, ['admin'])) $opt['withoutField'] = ['password'];
        
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
            
            $users= UsersModel::whereOr([$map1,$map2])->withoutField(['remarks'])->find();
            
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
                    
                    $jwt    = JWT::encode($token, $this->config['jwt']['key'], $this->config['jwt']['encrypt']);
                    $header = ['login-token'=>$jwt];
                    
                    // 单点登录字段
                    $login_auth = md5(md5('inis-'.time()));
                    if (empty($users->opt)) $users->opt = json_encode(['login_auth'=>$login_auth], JSON_UNESCAPED_UNICODE);
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
                    $users->head_img = $this->helper->RandomImg("local", "admin/images/anime/");
                    
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
    public function saves($param)
    {
        $data   = [];
        $code   = 400;
        $msg    = 'ok';
        $time   = time();
        
        // 允许用户提交并存储的字段
        $obtain = ['account','password','nickname','sex','email','phone','head_img','description','address_url','longtext'];
        if (in_array($this->user['data']->level, ['admin'])) array_push($obtain, 'level', 'status', 'remarks');
        
        if (empty($param['id'])) $users = new UsersModel;
        else $users = UsersModel::find((int)$param['id']);
        
        // 判断字段是否允许存储，防提权
        foreach ($param as $key => $val) if (in_array($key, $obtain)) {
            if ($key == 'password') $users->password = password_hash(md5($val), PASSWORD_BCRYPT);
            else $users->$key = $val;
        }
        
        // 权限判断
        if ($this->user['data']->status != 1) $msg = '账号被禁用';
        else {
            
            $code = 200;
            
            $param['email'] = empty($param['email']) ? $this->user['data']->email : $param['email'];
            
            $email   = UsersModel::where(['email'=>$param['email']])->findOrEmpty();
            $account = UsersModel::where(['account'=>$param['account']])->findOrEmpty();
            
            // 修改了自己的信息
            if ($this->user['data']->id == (int)$param['id']) {
                
                // 修改了邮箱信息 - 任何人都有权限
                if ($this->user['data']->email   != $param['email']) {
                    
                    $code = 400;
                    
                    if (!$email->isEmpty()) $msg  = '邮箱已存在！';
                    else if (empty($param['code'])) {
                        
                        $code = 201;
                        $msg  = $this->verifyCode($param['email']);
                        
                    } else if (!empty($param['code'])) {
                        
                        $verify = VerifyCode::where(['content'=>$param['email'],'code'=>$param['code']])->findOrEmpty();
                        
                        if (!$verify->isEmpty()) {
                            
                            if ($verify->end_time >= $time) {
                                
                                $code = 200;
                                $msg  = '保存成功！';
                                $verify->delete();
                                
                            } else $msg = '验证码已失效，请重新获取！';
                            
                        } else $msg = '无效验证码！';
                    }
                    
                }
                // 修改了帐号信息
                if ($this->user['data']->account != $param['account'] and !$account->isEmpty()) {
                    $code = 400;
                    $msg  = '帐号已存在！';
                }
                
                if ($code == 200) $users->save();
                
            } else {
                
                // 邮箱信息
                if (!$email->isEmpty()) {
                    $code = 400;
                    $msg  = '邮箱已存在！';
                }
                // 帐号信息
                if (!$account->isEmpty()) {
                    $code = 400;
                    $msg  = '帐号已存在！';
                }
                
                if (in_array($this->user['data']->level, ['admin']) and $code == 200) $users->save();
                else $msg = '无权限';
            }
        }
        
        // 删除已失效的验证码
        VerifyCode::where('end_time','<',$time)->delete();
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }
    
    // 删除数据
    public function remove($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        $id = !empty($param['id']) ? $param['id']  : null;
        
        if (empty($id)) $msg = '请提交 id';
        else {
            
            $id = array_filter(explode(',', $id));
            
            // 存在该条数据
            if (in_array($this->user['data']->level, ['admin'])) {
                
                $code = 200;
                UsersModel::destroy($id);
                
            } else {
                
                $code = 403;
                $msg  = '无权限';
            }
        }
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }
    
    // 创建验证码
    public function verifyCode($email)
    {
        $time = time();
        
        // 验证码有效时间
        $valid_time  = $this->config['valid_time'];
        $verify_code = VerifyCode::where(['types'=>'email','content'=>$email])->findOrEmpty();
        $chars       = $this->helper->VerifyCode(6, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        // 秒转人性化时间
        $valid_time_str = $this->helper->NaturalSecond($valid_time);
        
        if (!$verify_code->isEmpty()) {
            
            // 验证码已经存在，避免重复记录
            $verify_code->code     = $chars;
            $verify_code->end_time = $time + $valid_time;
            $verify_code->save();
            
        } else {
            
            // 验证码不存在，则新建验证码
            $verify_code  = new VerifyCode;
            $end_time     = $time + $valid_time;
            $verify_code->save([
                'code'    => $chars,
                'types'   => 'email',
                'content' => $email,
                'end_time'=> $end_time
            ]);
        }
        
        $msg  = '验证码已发送至邮箱，'.$valid_time_str.'内有效！';
        
        $this->sendEmail($email, $chars, $valid_time_str);
        
        return $msg;
    }
    
    // 发送邮箱通知
    public function sendEmail($email, $code, $valid_time)
    {
        // 获取邮箱服务配置信息
        $options  = Options::where(['keys'=>'config:email-serve'])->findOrEmpty();
        // 获取邮箱模板
        $templates= (!empty($options->value)) ? json_decode($options->value) : [];
        $template = (!empty($templates)) ? $templates->template_3 : '';
        // 获取站点名称
        $site     = Options::field(['opt'])->where(['keys'=>'site'])->find()['opt']->title;
        // 获取站点地址
        $domain   = $this->tool->domain();
        // 当前时间
        $time     = date("Y-m-d H:i:s",time());
        
        // 模板变量替换
        $template = str_replace('{email}'  , $email , $template);
        $template = str_replace('{code}'   , $code  , $template);
        $template = str_replace('{site}'   , $site  , $template);
        $template = str_replace('{time}'   , $time  , $template);
        $template = str_replace('{domain}' , $domain, $template);
        $template = str_replace('{valid_time}' , $valid_time, $template);
        
        // 发送评论信息到邮箱
        $this->tool->sendMail(['email'=>$email,'title'=>$site.'邮箱换绑验证码','content'=>$template]);
    }
    
    // 校验合法登录 - 根据JWT
    public function check($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        if (isset($param['login-token']) or isset($header['login-token'])) {
            
            $token = (isset($header['login-token'])) ? $header['login-token'] : $param['login-token'];
            
            try {
                
                JWT::$leeway = 60;
                $decoded = JWT::decode($token, $this->config['jwt']['key'], [$this->config['jwt']['encrypt']]);
                $array   = (array) $decoded;
                
                $data = UsersModel::withoutField(['password'])->find($array['uid']);
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
            
        } else $msg = '请通过 params or headers 的方式提交参数为 login-token 的JWT密钥';
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }
}
