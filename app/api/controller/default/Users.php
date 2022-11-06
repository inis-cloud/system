<?php
declare (strict_types = 1);

namespace app\api\controller\default;

use think\Request;
use think\exception\ValidateException;
use app\validate\Users as UsersValidate;
use think\facade\{Cache, Cookie, Session, Validate, Lang};
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
        $msg    = Lang::get('参数不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['one','all'];
        
        $mode   = (empty($param['id'])) ? 'all' : 'one';
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->json($data, $msg, $code);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function IPOST(Request $request, $IID)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = Lang::get('参数不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['save','remove','login','register','check','vcl'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 清除缓存
        Cache::tag('users')->clear();
        
        return $this->json($data, $msg, $code);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $IID
     * @return \think\Response
     */
    public function IGET(Request $request, $IID)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = Lang::get('参数不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['one','all','sql'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->json($data, $msg, $code);
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $IID
     * @return \think\Response
     */
    public function IPUT(Request $request, $IID)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = Lang::get('参数不存在！');
        $result = [];

        // 存在的方法
        $method = ['save'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 清除缓存
        Cache::tag('users')->clear();
        
        return $this->json($data, $msg, $code);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $IID
     * @return \think\Response
     */
    public function IDELETE(Request $request, $IID)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = Lang::get('参数不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['remove'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 清除缓存
        Cache::tag('users')->clear();
        
        return $this->json($data, $msg, $code);
    }
    
    // 获取一条数据
    public function one($param)
    {
        $data = [];
        $code = 400;
        $msg  = Lang::get('无数据！');

        if (empty($param['id'])) return ['data'=>[],'code'=>400,'msg'=>Lang::get('id 不能为空！')];
        
        $opt = [
            'withoutField'=>['account','password','phone','email','level','remarks'],
        ];
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'users'], $param));
        
        // 显示隐藏字段
        if (isset(request()->user) and in_array(request()->user->level, ['admin'])) $opt['withoutField'] = ['password'];
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            // 获取数据库数据
            $data = UsersModel::ExpandAll($param['id'], $opt);
            if ($this->ApiCache) Cache::tag(['users',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = Lang::get('无数据！');
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = Lang::get('数据请求成功！');
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取全部数据
    public function all($param)
    {
        $data = [];
        $code = 400;
        $msg  = Lang::get('无数据！');
        
        if (empty($param['page']))  $param['page']  = 1;
        if (empty($param['limit'])) $param['limit'] = 5;
        if (empty($param['order'])) $param['order'] = 'create_time asc';
        
        $opt = [
            'page'   =>  (int)$param['page'], 
            'limit'  =>  (int)$param['limit'],
            'order'  =>  (string)$param['order'],
            'withoutField'=>['account','password','phone','email','level','remarks'],
        ];
        
        // 显示隐藏字段
        if (isset(request()->user) and in_array(request()->user->level, ['admin'])) $opt['withoutField'] = ['password'];
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'users'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            
            // 获取数据库数据
            $data = UsersModel::ExpandAll(null, $opt);
            if ($this->ApiCache) Cache::tag(['users'])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = Lang::get('无数据！');
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = Lang::get('数据请求成功！');
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 登录
    public function login($param)
    {
        $data = [];
        $code = 400;
        $msg  = Lang::get('错误！');
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
            $msg  = Lang::get('登录错误上限', [$this->config['login']['error_count'], $second_to_time]);
            
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
                
                $msg = Lang::get("帐号或密码错误！");
                
            } else {
                
                if ($users->status == 0) {
                    
                    $code   = 403;
                    $msg    = Lang::get('该账号已被禁用！');
                    
                } else {
                    
                    $token = [
                        "iss" => "inis",            // 签发者 可以为空
                        "aud" => $users->account,   // 面象的用户，可以为空
                        "iat" => time(),            // 签发时间
                        "nbf" => time(),            // 在什么时候jwt开始生效  （这里表示生成100秒后才生效）
                        "exp" => time() + $this->config['login']['expired'],     // 过期时间 - 单位秒
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
                    $msg    = Lang::get('登录成功！');
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
        $msg  = Lang::get('数据请求成功！');

        if (empty($param['email']))    return ['data'=>[],'code'=>400,'msg'=>Lang::get('邮箱不能为空！')];
        if (empty($param['password'])) return ['data'=>[],'code'=>400,'msg'=>Lang::get('密码不能为空！')];
        if (empty($param['nickname'])) return ['data'=>[],'code'=>400,'msg'=>Lang::get('昵称不能为空！')];
        
        $cache_name  = 'email-register-' . $param['email'];
        $verify_code = !empty($param['code']) ? strtoupper($param['code']) : null;

        // 验证码为空 - 自动发送验证码
        if (empty($verify_code)) {
        
            $valid     = $this->config['valid_time'];
            $chars     = $this->helper->VerifyCode(6, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
            // 秒转人性化时间
            $time      = $this->helper->NaturalSecond($valid);
            
            $code = 200;
            $msg  = lang('验证码已发送至邮箱，%s 内有效！', [$time]);
            
            // 缓存验证码
            Cache::set($cache_name, $chars, $valid);
            $this->sendEmail($param['email'], $chars, $time, ['site'=>true,'value'=>'：邮箱换绑验证码！']);
        }
        // 开始注册
        else {

            if (!Cache::has($cache_name))                return ['data'=>[],'code'=>412,'msg'=>Lang::get('验证码已失效！')];
            if ($verify_code != Cache::get($cache_name)) return ['data'=>[],'code'=>400,'msg'=>Lang::get('验证码错误！')];
            $check = UsersModel::where('email', $param['email'])->findOrEmpty();
            if (!$check->isEmpty())                      return ['data'=>[],'code'=>400,'msg'=>Lang::get('该邮箱已被注册！')];

            $users = new UsersModel;
            // 允许用户提交并存储的字段
            $obtain = ['email','nickname','password'];
            
            // 判断字段是否允许存储，防提权
            foreach ($param as $key => $val) if (in_array($key, $obtain)) {
                if ($key == 'password') $users->password = $this->create_password($val);
                else $users->$key = $val;
            }
            
            // 随机默认头像
            $users->head_img = $this->helper->RandomImg("local", "admin/images/anime/");
            
            $users->save();
            Cache::delete($cache_name);
            
            $code = 200;
            // $data = ['email'=>$param['email'], 'password'=>$param['password']];
            $msg  = Lang::get('注册成功！');
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 新增或者修改数据
    public function save($param)
    {
        $data   = [];
        $code   = 400;
        $msg    = Lang::get('保存成功！');

        // 允许用户提交并存储的字段
        $obtain = ['account','password','nickname','sex','email','phone','head_img','description','address_url','longtext'];
        if (in_array(request()->user->level, ['admin'])) array_push($obtain, 'level', 'status', 'remarks');
        
        if (empty($param['id'])) $users = new UsersModel;
        else $users = UsersModel::find((int)$param['id']);
        
        // 判断字段是否允许存储，防提权
        foreach ($param as $key => $val) if (in_array($key, $obtain)) {
            if ($key == 'password') $users->password = $this->create_password($val);
            else $users->$key = $val;
        }
        
        // 权限判断
        if (request()->user->status != 1) return ['data'=>[],'msg'=>Lang::get('账号被禁用！'),'code'=>400];
         
        $code = 200;
        
        $param['email']   = empty($param['email'])   ? request()->user->email   : $param['email'];
        $param['account'] = empty($param['account']) ? request()->user->account : $param['account'];
        
        $email   = UsersModel::where(['email'  =>$param['email']])->findOrEmpty();
        $account = UsersModel::where(['account'=>$param['account']])->findOrEmpty();
        
        // 修改了自己的信息
        if (request()->user->id == (int)$param['id']) {
            
            // 修改了邮箱信息 - 任何人都有权限
            if (request()->user->email != $param['email']) {
                
                if (!$email->isEmpty()) return ['data'=>[],'msg'=>Lang::get('邮箱已存在！'),'code'=>400];

                // 验证码为空 - 自动发送验证码
                if (empty($param['code'])) {
                    
                    $code = 201;
                    $msg  = $this->verifyCode($param['email']);
                }
                // 修改信息
                else {
                    
                    $cache_name = 'verify-code-' . $param['email'];
                    
                    if (!Cache::has($cache_name))                  return ['data'=>[],'msg'=>Lang::get('验证码已失效，请重新获取！'),'code'=>400];
                    if ($param['code'] != Cache::get($cache_name)) return ['data'=>[],'msg'=>Lang::get('验证码错误！'),'code'=>400];

                    $code = 200;
                    $msg  = Lang::get('保存成功！');
                    Cache::delete($cache_name);
                }
                
            }
            // 修改了帐号信息
            if (request()->user->account != $param['account'] and !$account->isEmpty()) {
                $code = 400;
                $msg  = Lang::get('帐号已存在！');
            }
            
            if ($code == 200) $users->save();
            
        } else {
            
            if (!$email->isEmpty())   return ['data'=>[],'msg'=>Lang::get('邮箱已存在！'),'code'=>400];
            if (!$account->isEmpty()) return ['data'=>[],'msg'=>Lang::get('帐号已存在！'),'code'=>400];
            
            if (in_array(request()->user->level, ['admin'])) $users->save();
            else $msg = Lang::get('无权限！');
        }
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }
    
    // 删除数据
    public function remove($param)
    {
        $data = [];
        $code = 400;
        $msg  = Lang::get('删除成功！');
        
        $id = !empty($param['id']) ? $param['id']  : null;
        
        if (empty($id)) $msg = Lang::get('请提交 id！');
        else {
            
            $id = array_filter(explode(',', $id));
            
            // 存在该条数据
            if (in_array(request()->user->level, ['admin'])) {
                
                $code = 200;
                UsersModel::destroy($id);
                
            } else {
                
                $code = 403;
                $msg  = Lang::get('无权限！');
            }
        }
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }
    
    // 创建验证码
    public function verifyCode($email, $title = ['site'=>true,'value'=>'：邮箱换绑验证码！'])
    {
        // 验证码有效时间
        $valid  = $this->config['valid_time'];
        $chars  = $this->helper->VerifyCode(6, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        // 秒转人性化时间
        $time   = $this->helper->NaturalSecond($valid);
        
        // 缓存验证码
        Cache::set('verify-code-' . $email, $chars, $valid);
        $this->sendEmail($email, $chars, $time, $title);
        
        return Lang::get('验证码已发送至邮箱，%s 内有效！', [$time]);
    }
    
    // 发送邮箱通知
    public function sendEmail($email, $code, $valid_time, $title = ['site'=>true,'value'=>'：邮箱换绑验证码！'])
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
        $this->tool->sendMail([
            'email'  => $email,
            'title'  => $title['site'] ? $site . $title['value'] : Lang::get($title['value']),
            'content'=> $template
        ]);
    }
    
    // 校验合法登录 - 根据JWT
    public function check($param)
    {
        $data = [];
        $code = 400;
        $msg  = Lang::get('数据请求成功！');

        $token= isset($this->header['authorization']) ? $this->header['authorization'] : $param['login-token'];
        if (empty($token)) return ['data'=>[],'msg'=>Lang::get('Authorization 不得为空！'),'code'=>400];

        // 校验token，避免前端传递错误的token
        foreach (explode('.', $token) as $key => $val) {
            if (in_array($key, [0,1]) and empty(json_decode(base64_decode($val), true))) {
                return ['data'=>[],'msg'=>Lang::get('签名有误！'),'code'=>400];
            }
        }
        
        try {
            
            JWT::$leeway = 60;
            $decoded = JWT::decode($token, $this->config['jwt']['key'], [$this->config['jwt']['encrypt']]);
            $array   = (array) $decoded;
            
            $data = UsersModel::withoutField(['password'])->find($array['uid']);
            $code = 200;
            $msg  = Lang::get('合法登录！');
            
        } catch (SignatureInvalidException $e){

            $msg = Lang::get('签名不正确！');

        } catch (BeforeValidException $e){

            $msg = Lang::get('login-token失效！');

        } catch (ExpiredException $e){

            $msg = Lang::get('login-token失效！');

        } catch (Exception $e){

            $msg = Lang::get('未知错误！');
        };
            
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }

    // 通过验证码进行登录 - vcl(verification code login)
    public function vcl($param)
    {
        $data   = [];
        $code   = 400;
        $msg    = Lang::get('数据请求成功！');
        $header = [];

        if (empty($param['account'])) $msg = Lang::get('帐号或邮箱不得为空！请用参数 account 表示！');
        // 提交了帐号或邮箱，但是没有提交验证码 - 自动创建验证码
        else if (!empty($param['account']) and empty($param['code'])) {

            $map1 = ['account', 'like', $param['account']];
            $map2 = ['email'  , 'like', $param['account']];
            
            $users= UsersModel::whereOr([$map1,$map2])->findOrEmpty();

            if ($users->isEmpty())         $msg = Lang::get('帐号或邮箱不存在！');
            else if (empty($users->email)) $msg = Lang::get('该帐号没有绑定邮箱！');
            else {
                $code = 200;
                $msg = $this->verifyCode($users->email, ['site'=>true,'value'=>'：验证码登录！']);
            }
        
        }
        // 同时提交了帐号或邮箱和验证码 - 校验验证码是否正确
        else if (!empty($param['account']) and !empty($param['code'])) {

            $time = time();
            $map1 = ['account', 'like', $param['account']];
            $map2 = ['email'  , 'like', $param['account']];
            
            $users= UsersModel::whereOr([$map1,$map2])->withoutField(['remarks'])->findOrEmpty();

            if ($users->isEmpty())         $msg = Lang::get('帐号或邮箱不存在！');
            else if (empty($users->email)) $msg = Lang::get('该帐号没有绑定邮箱！');
            else {

                $cache_name = 'verify-code-' . $users->email;
                $verify = Cache::get('verify-code-' . $users->email);
                
                // 判断验证码是否存在数据库内
                if (Cache::has($cache_name)) {

                    // 检查验证码是否过期
                    if (Cache::get($cache_name) == $param['code']) {

                        $token = [
                            "iss" => "inis",            // 签发者 可以为空
                            "aud" => $users->account,   // 面象的用户，可以为空
                            "iat" => $time,             // 签发时间
                            "nbf" => $time,             // 在什么时候jwt开始生效  （这里表示生成100秒后才生效）
                            "exp" => $time + $this->config['login']['expired'],      // 过期时间 - 单位秒
                            "uid" => $users->id,        // 记录的userid的信息，这里是自已添加上去的，如果有其它信息，可以再添加数组的键值对
                        ];
                        
                        $jwt    = JWT::encode($token, $this->config['jwt']['key'], $this->config['jwt']['encrypt']);
                        $header = ['login-token'=>$jwt];
                        
                        // 单点登录字段
                        $loginAuth = md5(md5('inis-'.$time));
                        if (empty($users->opt)) $users->opt = json_encode(['login_auth'=>$loginAuth], JSON_UNESCAPED_UNICODE);
                        else $users->opt->login_auth = $loginAuth;
                        $users->last_login_time = $time;
                        $users->save();
                        
                        unset($users['password']);
                        
                        Session::set('login_account', $users);
                        Session::set('login_auth'   , $loginAuth);
                        Cookie::set('login_account' , json_encode($users));
                        
                        // 登录成功
                        $data   = ['login-token'=>$jwt,'user'=>$users];
                        $code   = 200;
                        $msg    = Lang::get('登录成功！');
                        Cache::delete($cache_name);
                        
                    } else $msg = Lang::get('验证码错误！');
                    
                } else $msg = Lang::get('验证码已失效，请重新获取！');
            }
        }

        $result = ['data'=>$data,'code'=>$code,'msg' =>$msg,'header'=>$header];
        
        return $result;
    }

    // SQL接口
    public function sql($param)
    {
        $where   = (empty($param['where']))   ? [] : $param['where'];
        $whereOr = (empty($param['whereOr'])) ? [] : $param['whereOr'];
        $page    = (!empty($param['page']))   ? $param['page']  : 1;
        $limit   = (!empty($param['limit']))  ? $param['limit'] : 5;
        $order   = (!empty($param['order']))  ? $param['order'] : 'create_time desc';
        
        $data = [];
        $code = 200;
        $msg  = Lang::get('数据请求成功！');
        
        $opt  = [
            'page' => $page,
            'limit'=> $limit,
            'order'=> $order,
            'where'=> $where,
            'whereOr'=> $whereOr,
            'withoutField'=>['account','password','phone','email','level','remarks'],
        ];
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'users/sql'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            $data = UsersModel::ExpandAll(null, $opt);
            if ($this->ApiCache) Cache::tag(['users',$cache_name])->set($cache_name, json_encode($data));
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}
