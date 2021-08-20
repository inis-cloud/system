<?php

namespace app\index\controller;

use app\model\Options;
use think\facade\Request;
use think\facade\Validate;
use think\facade\Filesystem;
use PHPMailer\PHPMailer\PHPMailer;

// extends Base
class Tool
{
    // 文件上传
    public function upload(string $file_name = 'image', $file_path = '', string $model = 'one', string $rule = '')
    {
        $files = Request::file($file_name);
        
        // 获取本站域名
        $domain = $this->domain();
        
        // 获取参数地址数据
        if(is_string($file_path)){
            
            if(empty($file_path)) $disk = 'storage';
            else $disk = $file_path;
            
            $path      = 'image';
            $path_rule = null;
            
        }elseif(is_array($file_path)){
            
            if(empty($file_path[0])) $disk = 'storage';
            else $disk = $file_path[0];
            
            if(empty($file_path[1])) $path = 'image';
            else $path = $file_path[1];
            
            if(empty($file_path[2])) $path_rule = null;
            else $path_rule = $file_path[2];
        }
        
        // 定义验证规则
        if(empty($rule)){
            
            // 默认验证规则
            $rule = 'file|fileExt:jpg,jpeg,png,gif,webp|fileSize:5242880';
            
            // 规则
            $validate = Validate::rule([
                'image' => $rule,
            ])->message([
                'image.fileSize'=>'上传的文件超过最大限制5MB！',
                'image.fileExt' =>'上传文件后缀不允许！请上传jpg,jpeg,png,gif,webp格式文件！',
            ]);
        }else $validate = Validate::rule(['image'=>$rule]);
        
        /* 单文件上传模式 */
        if($model == 'one'){
            
            // 验证规则
            $check = $validate->check([
                'image' => $files
            ]);
            
            if($check){
                
                if (is_string($path_rule))    $url = Filesystem::disk($disk)->putfile($path, $files, $path_rule);
                elseif (is_array($path_rule)) $url = Filesystem::disk($disk)->putfile($path, $files, function () use ($path_rule){ return $path_rule[0];});
                
                $data = $domain.'/storage/'.$url;
                $msg  = '图片上传成功！';
                $code = 200;
                
            }else{
                
                $data = [];
                $msg  = $validate->getError();
                $code = 400;
            }
            
        }elseif($model == 'more'){
            
            /* 多文件上传模式 */
            
            $data = [];
            
            foreach ($files as $file) {
                
                // 验证规则
                $check = $validate->check([
                    'image' => $file
                ]);
                
                if($check) $data['success'][] = $domain.'/storage/'.Filesystem::disk($disk)->putfile($path, $file, $path_rule);
                else $data['error'][] = $validate->getError();
            }
            
            $msg  = '图片上传成功！';
            $code = 200;
        }
        
        return ['code'=>$code,'msg'=>$msg,'data'=>$data];
    }
    
    // 获取本地域名
    public function domain()
    {
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        $domain = $_SERVER['HTTP_HOST'];
        
        return $http_type.$domain;
    }
    
    // 邮箱发送
    public function sendMail($to,$title,$content)
    {
        $options = Options::where(['keys'=>'email_serve'])->findOrEmpty();
        
    	$mail = new PHPMailer;
    	
    	// 是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
    	// $mail->SMTPDebug = 1;
    	
    	// 使用smtp鉴权方式发送邮件
    	$mail->isSMTP();
    	
    	// smtp需要鉴权 这个必须是true
    	$mail->SMTPAuth = true;
    	
    	// 链接qq域名邮箱的服务器地址
    	$mail->Host = $options->opt->smtp;
    	
    	// 设置使用ssl加密方式登录鉴权
    	$mail->SMTPSecure = $options->opt->encry;
    	
    	// 设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
    	$mail->Port = $options->opt->port;
    	
    	// 设置smtp的helo消息头 这个可有可无 内容任意
    	// $mail->Helo = 'Hello smtp.qq.com Server';
    	// 设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用你的域名
    	$mail->Hostname = '';
    	
    	// 设置发送的邮件的编码 可选GB2312 我喜欢utf-8 据说utf8在某些客户端收信下会乱码
    	$mail->CharSet = $options->opt->encoded;
    	
    	// 设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
    	$mail->FromName = $options->opt->nickname;
    	
    	// smtp登录的账号 这里填入字符串格式的qq号即可
    	$mail->Username = $options->opt->email;
    	
    	// smtp登录的密码 使用生成的授权码（就刚才叫你保存的最新的授权码）
    	$mail->Password = $options->opt->password;
    	
    	// 设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
    	$mail->From = $options->opt->email;
    	
    	// 邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
    	$mail->isHTML(true);
    	
    	// 设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
    	if(is_string($to)) $mail->addAddress($to,'');
    	elseif(is_array($to)) foreach ($to as $val) $mail->addAddress($val,'');
    	
    	// 添加该邮件的主题
    	$mail->Subject = $title;
    	
    	// 添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
    	$mail->Body = $content;
    	
    	// 为该邮件添加附件 该方法也有两个参数 第一个参数为附件存放的目录（相对目录、或绝对目录均可） 第二参数为在邮件附件中该附件的名称
    	// $mail->addAttachment('./d.jpg','mm.jpg');
    	// 同样该方法可以多次调用 上传多个附件
    	// $mail->addAttachment('./Jlib-1.1.0.js','Jlib.js');
    	
    	$status = $mail->send();
    	
    	$result = false;
    	
    	// 简单的判断与提示信息
    	if ($status) $result = true;
    	else $result = false;
    
    	return $result;
    }
    
    // 获取控制器所有方法
    public function MethodsALL($class = 'Index')
    {
        $class  = ucfirst(strtolower($class));
        $obtain = ['Index','Method','Handle','Filesystem'];
        
        if (in_array($class,$obtain)) {
            switch ($class) {
                case "Index":
                    
                    $data = new \ReflectionClass(\app\index\controller\Index::class);
                    $methods = $this->filterMethods(get_class_methods(\app\index\controller\Index::class));
                    
                    // 方法名和注释封装
                    foreach ($methods as $val) {
                        $item = ['id'=>$val,'text'=>$data->getMethod($val)->getDocComment()];
                        $results[]  = $item;
                    }
                    
                    $result = $this->notesParams($results);
                    
                    break;
                case "Method":
                    $data = new \ReflectionClass(\app\index\controller\Method::class);
                    $methods = $this->filterMethods(get_class_methods(\app\index\controller\Method::class));
                    
                    // 方法名和注释封装
                    foreach ($methods as $val) {
                        $item = ['id'=>$val,'text'=>$data->getMethod($val)->getDocComment()];
                        $results[]  = $item;
                    }
                    
                    $result = $this->notesParams($results);
                    break;
                case "Handle":
                    $data = new \ReflectionClass(\app\index\controller\Handle::class);
                    $methods = $this->filterMethods(get_class_methods(\app\index\controller\Handle::class));
                    
                    // 方法名和注释封装
                    foreach ($methods as $val) {
                        $item = ['id'=>$val,'text'=>$data->getMethod($val)->getDocComment()];
                        $results[]  = $item;
                    }
                    
                    $result = $this->notesParams($results);
                    break;
                case "Filesystem":
                    $data = new \ReflectionClass(\app\index\controller\FileSystem::class);
                    $methods = $this->filterMethods(get_class_methods(\app\index\controller\FileSystem::class));
                    
                    // 方法名和注释封装
                    foreach ($methods as $val) {
                        $item = ['id'=>$val,'text'=>$data->getMethod($val)->getDocComment()];
                        $results[]  = $item;
                    }
                    
                    $result = $this->notesParams($results);
                    break;
                default:
                    $result = [];
            }
        }
        
        return $result;
    }
    
    // 过滤方法
    public function filterMethods(array $array)
    {
        $unset = ['__construct','initialize','verify_password','create_password','create','GetMethods'];
        foreach ($array as $key => $val) if (in_array($val,$unset)) unset($array[$key]);
        
        return $array;
    }
    
    // 得到注释的参数
    public function notesParams($data)
    {
        foreach ($data as $key => $val) {
            
            $item['id'] = $val['id'];
            
            if ($val['text'] == false) $item['text'] = $val['id'];
            else {
                $text = explode("\r\n",$val['text']);
                array_shift($text);
                array_pop($text);
                $item['text'] = $this->getParam($text, 'name');
            }
            
            $result[]  = $item;
        }
        return $result;
    }
    
    // 获取注释的参数
    public function getParam($data, $param = 'name')
    {
        $array  = [];
        $result = '';
        
        foreach ($data as $key => $val) {
            $item = explode("@", $val);
            foreach ($item as $k => $v) if ($k != 0) $array[] = $v;
        }
        
        foreach ($array as $key => $val) {
            $item = explode(" ", $val);
            if ($item[0] == $param) $result = $item[1];
        }
        
        return $result;
    }
}