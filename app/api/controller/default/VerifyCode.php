<?php
declare (strict_types = 1);

namespace app\api\controller\default;

use think\Request;
use think\facade\{Cache, Lang};
use app\model\mysql\{Users, Options};

class VerifyCode extends Base
{
    
    protected $middleware = [];

    /**
     * 显示资源列表
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function index(Request $request)
    {
        // 获取请求参数
        // $param = $request->param();
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
        $msg    = lang('方法不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['create','check'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
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
        $msg    = lang('方法不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['check'];
        
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
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $IID
     * @return \think\Response
     */
    public function IDELETE(Request $request, $IID)
    {
        //
    }

    // 创建验证码
    public function create($param)
    {
        $data = [];
        $code = 200;
        $msg  = lang('验证码创建成功！');

        if (empty($param['email'])) return ['data'=>[],'code'=>400,'msg'=>lang('邮箱不得为空！')];

        $time       = time();
        $cache_name = 'email-verify-code-' . $param['email'];
        $users      = Users::where(['email'=>$param['email']])->findOrEmpty();

        if (!$users->isEmpty()) return ['data'=>[],'code'=>412,'msg'=>lang('邮箱已存在！')];
    
        // 验证码有效时间
        $valid = $this->config['valid_time'];
        $chars = $this->helper->VerifyCode(6, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        // 秒转人性化时间
        $time  = $this->helper->NaturalSecond($valid);
        Cache::set($cache_name, $chars, $valid);
        $this->sendEmail($param['email'], $chars, $time);

        $msg = lang('验证码已发送至邮箱，%s 内有效！', [$time]);
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 校验验证码
    public function check($param)
    {
        $data = [];
        $code = 400;
        $msg  = lang('邮箱不得为空！');

        if (empty($param['email'])) return ['data'=>[],'code'=>400,'msg'=>lang('邮箱不得为空！')];
        if (empty($param['code']))  return ['data'=>[],'code'=>400,'msg'=>lang('验证码不得为空！')];

        $cache_name = 'email-verify-code-' . $param['email'];

        if (Cache::has($cache_name)) {

            if (Cache::get($cache_name) != strtoupper($param['code'])) $msg = lang('验证码无效！');
            else {

                $code= 200;
                $msg = lang('验证码有效！');
                Cache::delete($cache_name);
            }

        } else $msg = lang('验证码已失效，请重新获取！');
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 发送邮箱通知
    public function sendEmail($email,$code,$valid_time)
    {
        // 获取邮箱服务配置信息
        $options  = Options::where(['keys'=>'config:email-serve'])->findOrEmpty();
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
            'email'  =>$email,
            'title'  =>$site . lang('注册验证码！'),
            'content'=>$template
        ]);
    }
}
