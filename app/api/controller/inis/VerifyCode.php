<?php
declare (strict_types = 1);

namespace app\api\controller\inis;

use think\Request;
use app\model\mysql\{Users, Options, VerifyCode as VerifyCodeModel};

class VerifyCode extends Base
{
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
    public function save(Request $request)
    {
        // 获取请求参数
        $param = $request->param();
        
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        $time = time();
        $mode = (empty($param['mode']))  ? null : $param['mode'];
        $email= (empty($param['email'])) ? null : $param['email'];
        
        // 验证码有效时间
        $valid_time = $this->config['valid_time'];
        
        if ($mode == 'create') {
            
            if (empty($email)) $msg = '邮箱不得为空！';
            else {
                
                $users = Users::where(['email'=>$email])->findOrEmpty();
                $verify_code = VerifyCodeModel::where(['types'=>'email','content'=>$email])->findOrEmpty();
                $chars       = $this->helper->VerifyCode(6, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
                // 秒转人性化时间
                $valid_time_str = $this->helper->NaturalSecond($valid_time);
                
                if (!$users->isEmpty()) {
                    $code = 412;
                    $msg  = '邮箱已存在！';
                } else if (!$verify_code->isEmpty()) {
                    
                    // 验证码已经存在，避免重复记录
                    $verify_code->code = $chars;
                    $verify_code->end_time = $time + $valid_time;
                    $verify_code->save();
                    
                    $code = 200;
                    $msg  = '验证码已发送至邮箱，'.$valid_time_str.'内有效！';
                    
                    $this->sendEmail($email,$chars,$valid_time_str);
                    
                } else {
                    
                    // 验证码不存在，则新建验证码
                    $verify_code = new VerifyCodeModel;
                    $end_time    = $time + $valid_time;
                    $verify_code->save([
                        'code'    => $chars,
                        'types'   => 'email',
                        'content' => $email,
                        'end_time'=> $end_time
                    ]);
                    
                    $code = 200;
                    $msg  = '验证码已发送至邮箱'.$valid_time_str.'内有效！';
                    
                    $this->sendEmail($email,$chars,$valid_time_str);
                }
            }
            
        } else if ($mode == 'check') {
            
            $verify_data = (empty($param['code'])) ? '' : $param['code'];
            
            if (empty($email)) $msg = '邮箱不得为空！';
            elseif (empty($verify_data)) $msg = '验证码不得为空！';
            else {
                
                $verify_code = VerifyCodeModel::where(['content'=>$email,'code'=>$verify_data])->findOrEmpty();
                if (!$verify_code->isEmpty()) {
                    
                    if ($verify_code->end_time >= $time) {
                        
                        $code = 200;
                        $msg = '验证码有效！';
                        $verify_code->delete();
                        
                    } else $msg = '验证码已失效，请重新获取！';
                    
                } else $msg = '无效验证码！';
            }
        }
        
        // 删除已失效的验证码
        VerifyCodeModel::where('end_time','<',$time)->delete();
        
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
        // 
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
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $IID
     * @return \think\Response
     */
    public function delete(Request $request, $IID)
    {
        //
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
            'title'  =>$site.'注册验证码',
            'content'=>$template
        ]);
    }
}
