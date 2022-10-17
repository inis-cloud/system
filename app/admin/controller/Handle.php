<?php

namespace app\admin\controller;

use app\Request;
use app\model\sqlite\{Search};
use PHPMailer\PHPMailer\PHPMailer;
use inis\utils\{File, helper, Image};
use think\facade\{Db, Cache, Config, Session};
use app\model\mysql\{Tag, Page, Links, Users, Music, Options, Article, LinksSort, ArticleSort};

class Handle extends Base
{
    // 工具类
    protected $tool;
    protected $File;
    protected $helper;
    protected $DBUPDATE;

    protected $token_prefix = 'inis.';
    
    public function __construct()
    {
        $this->tool   = new Tool;
        $this->File   = new File;
        $this->helper = new helper;
        $this->DBUPDATE = Config::get('dbupdate');
    }

    /** 
     * @name 设置Token验证
     */
    public function SetToken(Request $request)
    {
        if ($request->isPost())
        {
            $param = $request->param();
            
            $data = [];
            $code = 200;
            
            $item = Options::where(['keys'=>'config:security'])->find();
            
            if($param['status'] == 1){
                
                $token = md5($this->token_prefix.time());
                $data['status'] = 1;
                $data['token']  = $token;
                $msg  = 'Token验证 开启 成功！';
                
                $item->opt->token->status = 1;
                $item->opt->token->value  = $token;
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $data['status'] = 0;
                $data['token']  = '';
                $msg  = 'Token验证 关闭 成功！';
                
                $item->opt->token->status = 0;
                $item->opt->token->value = '';
            }
            $item->opt = json_encode($item->opt, JSON_UNESCAPED_UNICODE);
            $item->save();
            
            return $this->json($data, $msg, $code);
        }
    }

    /** 
     * @name 允许通过API方式获取Token
     */
    public function TokenIsOpen(Request $request)
    {
        if ($request->isPost())
        {
            $param = $request->param();
            
            $data = [];
            $code = 200;
            
            $item = Options::where(['keys'=>'config:security'])->find();
            
            if($param['status'] == 1){
                
                $data['status'] = 1;
                $msg  = '允许通过API方式获取Token 开启 成功！';
                
                $item->opt->token->open = 1;
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $data['status'] = 0;
                $msg  = '允许通过API方式获取Token 关闭 成功！';
                
                $item->opt->token->open = 0;
            }
            $item->opt = json_encode($item->opt, JSON_UNESCAPED_UNICODE);
            $item->save();
            
            return $this->json($data, $msg, $code);
        }
    }

    /** 
     * @name 重置站点Token
     */
    public function ResetToken(Request $request)
    {
        if ($request->isPost()) {
            
            $param = $request->param();
            
            $token = md5($this->token_prefix.time());
            
            $item = Options::where(['keys'=>'config:security'])->find();
            
            if($param['code'] == 1){
                $item->opt->token->value = $token;
                $item->opt = json_encode($item->opt, JSON_UNESCAPED_UNICODE);
                $item->save();
                
                $data = ['token'=>$token];
                $code = 200;
                $msg  = '刷新Token 成功！';
            }else{
                $data = [];
                $code = 400;
                $msg  = '刷新Token 失败！';
            }
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /**
     * @name 保存Token
     */
    public function SaveToken(Request $request)
    {
        if ($request->isPost()) {
            
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            $param = $request->param();
            
            $token = (!empty($param['token'])) ? $param['token'] : '';
            
            $item = Options::where(['keys'=>'config:security'])->find();
            
            $item->opt->token->value = $token;
            $item->opt = json_encode($item->opt, JSON_UNESCAPED_UNICODE);
            $item->save();
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 设置站点白名单开关
     */
    public function SetDomain(Request $request)
    {
        if ($request->isPost())
        {
            $param = $request->param();
            
            $data = [];
            $code = 200;
            
            $item = Options::where(['keys'=>'config:security'])->find();
            
            if($param['status'] == 1){
                
                $data['status'] = 1;
                $msg  = '白名单 开启 成功！';
                $item->opt->domain = ['status'=>1];
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $data['status'] = 0;
                $msg  = '白名单 关闭 成功！';
                $item->opt->domain = ['status'=>0];
            }
            $item->opt = json_encode($item->opt, JSON_UNESCAPED_UNICODE);
            $item->save();
            
            return $this->json($item->opt->domain, $msg, $code);
        }
    }
    
    /** 
     * @name 设置文章置顶
     */
    public function SetArticleTop(Request $request)
    {
        if ($request->isPost())
        {
            $param = $request->param();
            
            $data = [];
            $code = 200;
            
            $item = Article::find($param['id']);
            
            if ($param['status'] == 1) {
                
                $item->is_top = 1;
                $data['status']  = 1;
                $msg = '文章置顶开启成功！';
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $item->is_top = 0;
                $data['status']  = 0;
                $msg = '文章置顶关闭成功！';
            }
            $item->save();
            
            // 清除缓存
            Cache::tag(['article','group'])->clear();
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 设置文章显示
     */
    public function SetArticleShow(Request $request)
    {
        if ($request->isPost())
        {
            $param = $request->param();
            
            $data = [];
            $code = 200;
            
            $item = Article::find($param['id']);
            
            if($param['status'] == 1){
                
                $item->is_show = 1;
                $data['status']   = 1;
                $msg = '文章显示开启成功！';
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $item->is_show = 0;
                $data['status']   = 0;
                $msg = '文章显示关闭成功！';
            }
            $item->save();
            
            // 清除缓存
            Cache::tag(['article','group'])->clear();
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 设置文章显示
     */
    public function SetPageShow(Request $request)
    {
        if ($request->isPost())
        {
            $param = $request->param();
            
            $data = [];
            $code = 200;
            
            $item = Page::find($param['id']);
            
            if($param['status'] == 1){
                
                $item->is_show = 1;
                $data['status']   = 1;
                $msg = '页面显示开启成功！';
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $item->is_show = 0;
                $data['status']   = 0;
                $msg = '页面显示关闭成功！';
            }
            $item->save();
            
            // 清除缓存
            Cache::tag(['page','group'])->clear();
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 设置文章分类显示
     */
    public function SetArticleSortShow(Request $request)
    {
        if ($request->isPost())
        {
            $param = $request->param();
            
            $data = [];
            $code = 200;
            
            $item = ArticleSort::find($param['id']);
            
            if($param['status'] == 1){
                
                $item->is_show = 1;
                $data['status']   = 1;
                $msg = '文章分类显示开启成功！';
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $item->is_show = 0;
                $data['status']   = 0;
                $msg = '文章分类显示关闭成功！';
            }
            $item->save();
            
            // 清除缓存
            Cache::tag('article-sort')->clear();
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 设置用户状态
     */
    public function SetUserEnable(Request $request)
    {
        if ($request->isPost())
        {
            $param = $request->param();
            
            $data = [];
            $code = 200;
            
            $item = Users::find($param['id']);
            
            if($param['status'] == 1){
                
                $item->status   = 1;
                $data['status']  = 1;
                $msg = '该用户被启用！';
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $item->status   = 0;
                $data['status']  = 0;
                $msg = '该用户被禁用！';
            }
            $item->save();
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 设置友链显示
     */
    public function SetLinksShow(Request $request)
    {
        if ($request->isPost())
        {
            $param = $request->param();
            
            $data = [];
            $code = 200;
            
            $item = Links::find($param['id']);
            
            if($param['status'] == 1){
                
                $item->is_show = 1;
                $data['status']   = 1;
                $msg = '友链显示开启成功！';
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $item->is_show = 0;
                $data['status']   = 0;
                $msg = '友链显示关闭成功！';
            }
            $item->save();
            
            // 清除缓存
            Cache::tag('links')->clear();
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 设置友链分类显示
     */
    public function SetLinksSortShow(Request $request)
    {
        if ($request->isPost())
        {
            $param = $request->param();
            
            $data = [];
            $code = 200;
            
            $item = LinksSort::find($param['id']);
            
            if($param['status'] == 1){
                
                $item->is_show = 1;
                $data['status']   = 1;
                $msg = '友链分类显示开启成功！';
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $item->is_show = 0;
                $data['status']   = 0;
                $msg = '友链分类显示关闭成功！';
            }
            $item->save();
            
            // 清除缓存
            Cache::tag('links-sort')->clear();
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 设置标签显示
     */
    public function SetTagShow(Request $request)
    {
        if ($request->isPost())
        {
            $param = $request->param();
            
            $data = [];
            $code = 200;
            
            $item = Tag::find($param['id']);
            
            if($param['status'] == 1){
                
                $item->is_show = 1;
                $data['status']   = 1;
                $msg = '友链显示开启成功！';
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $item->is_show = 0;
                $data['status']   = 0;
                $msg = '友链显示关闭成功！';
            }
            $item->save();
            
            // 清除缓存
            Cache::tag('tag')->clear();
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 保存邮箱服务配置
     */
    public function SaveEmailCC(Request $request)
    {
        if ($request->isPost())
        {
            $param = $request->param();
            
            $data = [];
            $code = 200;
            $msg  = '保存成功！';
            
            $item = Options::where(['keys'=>'config:email-serve'])->findOrEmpty();
            
            if ($item->isEmpty()) $item->keys = 'config:email-serve';
            $item->opt->email_cc = $param['email_cc'];
            
            $item->opt = json_encode($item->opt, JSON_UNESCAPED_UNICODE);
            $item->save();
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 上传图片到本地
     */
    public function uploadImage()
    {
        $item = (new Tool)->upload('image', '', 'more');
        
        return $item;
    }
    
    /** 
     * @name 上传资源
     */
    public function upload(Request $request)
    {
        if ($request->isPost()) {
            
            $param = $request->param();
            $mode  = (empty($param['mode'])) ? '' : $param['mode'];
            
            // 取原文件名
            $name  = explode('.', $_FILES['file']['name'] ?? '');
            // 去除后缀
            $pop   = array_pop($name);
            // 过滤非法字符 - 数组转字符串
            $name  = str_replace([
                '.', '\\', '/', ':', '*', '`', '?', '<', '>', '%', '&', '$', '#', ' '
            ], '', implode('.', $name));
            
            $time  = time();
            // 登录用户的ID
            $uid   = Session::get('login_account')['id'];
            
            if (empty($mode))           $upload = $this->tool->upload('file', ['storage', 'users/head/'.$uid, [$time]]);
            elseif ($mode == 'banner')  $upload = $this->tool->upload('file', ['storage', 'banner', [$time]], 'one', 'file|fileExt:jpg,jpeg,png,gif,webp|fileSize:20971520');
            elseif ($mode == 'pay')     $upload = $this->tool->upload('file', ['storage', 'users/pay/'.$uid, [$time]]);
            elseif ($mode == 'article') $upload = $this->tool->upload('file', ['storage', 'article/'.$uid.'/'.date("Y-m"), [$time]], 'one', 'file|fileExt:jpg,jpeg,png,gif,webp|fileSize:20971520');
            elseif ($mode == 'file')    $upload = $this->tool->upload('file', ['storage', 'users/files/'.$uid.'/'.date("Y-m"), [$time]], 'one', 'file|fileExt:jpg,jpeg,png,gif,webp,svg,ico,zip,gz,mp3,mp4,avi|fileSize:20971520');
            
            // 读取配置
            $config = Options::where(['keys'=>'config:system'])->findOrEmpty();
            if (!$config->isEmpty()) $config = json_decode(json_encode($config['opt']), true);
            else $config = ['optimize'=>['image'=>['open'=>true,'ratio'=>50]]];
            // 空配置处理
            
            // 读取配置
            $config = Options::where(['keys'=>'config:system'])->findOrEmpty();
            $config = !$config->isEmpty() ? json_decode(json_encode($config['opt']), true) : [];
            // 深度合并
            $config = $this->helper->array_merge_deep(['optimize'=>[
                'image'=>['open'=>true,'ratio'=>50],
            ]], $config);
            
            
            // 开启了图片压缩
            if (($config['optimize']['image']['open'] === 'true' or $config['optimize']['image']['open'] === true) ? true : false) {
                // 图片后缀
                $img_pop = ['jpg', 'jpeg', 'png', 'bmp', 'wbmp','gif'];
                // 图片压缩
                if (in_array($pop, $img_pop)) if ($upload['code'] == 200) {
                    // 得到本地资源路径
                    $url = str_replace($this->helper->domain() . '/', '', $upload['data']);
                    (new Image($url, 1 - ((int)$config['optimize']['image']['ratio'] / 100)))->compress($url);
                }
            }
            
            return $upload;
        }
    }
    
    /** 
     * @name 上传个人头像
     */
    public function uploadHead()
    {
        $account_id = Session::get('login_account')['id'];
        
        $item = (new Tool())->upload('image', ['storage', 'users/uid-' . $account_id, [time()]]);
        
        if ($item['code'] == 200){
            
            $users = Users::find($account_id);
            $users->head_img = $item['data'];
            $users->save();
            
            $url = str_replace($this->helper->domain() . '/', '', $item['data']);
            (new Image($url, 0.5))->compress($url);
            
            unset($users['password']);
            // 更新用户登录信息
            Session::set('login_account', $users);
        }
        
        return $item;
    }
    
    /** 
     * @name 上传站点头像
     */
    public function uploadSiteHead()
    {
        $item = (new Tool())->upload('image', ['storage', 'users', [time()]]);
        
        if ($item['code'] == 200) {
            
            $site = Options::where(['keys'=>'site'])->find();
            $opt  = json_decode(json_encode($site->opt), true);
            $opt['image'] = $item['data'];
            $site->opt = json_encode($opt, JSON_UNESCAPED_UNICODE);
            $site->save();
            
            $url = str_replace($this->helper->domain() . '/', '', $item['data']);
            (new Image($url, 0.5))->compress($url);
        }
        
        return $item;
    }
    
    /** 
     * @name 获取class全部方法
     */
    public function GetMethods(Request $request)
    {
        if ($request->isPost()) {
            
            $param = $request->param();
            
            $data = [];
            $code = 400;
            $msg  = 'ok';
            
            $class_name = (empty($param['class'])) ? '' : $param['class'];
            
            if (!empty($class_name)) {
                $code = 200;
                $data = (new Tool)->MethodsALL($class_name);
            }
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 设置用户状态
     */
    public function MusicIsShow(Request $request)
    {
        if ($request->isPost())
        {
            $param = $request->param();
            
            $data = [];
            $code = 200;
            
            $item = Music::find($param['id']);
            
            if($param['status'] == 1){
                
                $item->is_show  = 1;
                $data['status']  = 1;
                $msg = '该歌单被启用！';
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $item->is_show  = 0;
                $data['status']  = 0;
                $msg = '该歌单被禁用！';
            }
            $item->save();
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 清理缓存
     */
    public function clearCache(Request $request)
    {
        if ($request->isPost())
        {
            $data  = [];
            $code  = 200;
            $msg   = 'ok';
            
            $param = $request->param();
            
            // 获取需要被删除的缓存
            $file_name = array_filter(explode(',', $param['file_name'] ?? ''));
            
            // 处理格式 - 获取被删除的文件路径
            foreach ($file_name as $value) {
                
                $item_path = $this->File->listDirInfo('../runtime/'.$value, true);
                
                // 删除文件 - 防止删除失败
                if (!empty($item_path)) foreach ($item_path as $val) $this->File->unlinkFile($val, true);
                
                // 删除非空目录
                $this->File->removeDir('../runtime/'.$value, true);
                
                // 是否删除 session
                if ($value == 'session') Session::clear();
            }
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 邮箱服务测试
     */
    public function testEmail(Request $request)
    {
        if ($request->isPost())
        {
            $data  = [];
            $code  = 400;
            $msg   = '配置有误';
            
            $param = $request->param();
            
            if (empty($param['smtp']))          $msg = '{smtp} - 服务器地址不得为空！';
            else if (empty($param['encry']))    $msg = '{encry} - 加密方式不得为空！';
            else if (empty($param['port']))     $msg = '{port} - 端口号不得为空！';
            else if (empty($param['encoded']))  $msg = '{encoded} - 编码方式不得为空！';
            else if (empty($param['nickname'])) $msg = '{nickname} - 发件人昵称不得为空！';
            else if (empty($param['email']))    $msg = '{email} - 邮箱帐号不得为空！';
            else if (empty($param['password'])) $msg = '{password} - 邮箱服务密码不得为空！';
            else if (empty($param['title']))    $msg = '{title} - 邮件标题不得为空！';
            else if (empty($param['content']))  $msg = '{content} - 邮件内容不得为空！';
            else {
                
                try {
                    
                    $mail = new PHPMailer;
                    
                    // $mail->SMTPDebug = 1;
                    
                    $mail->isSMTP();
                    
                    $mail->SMTPAuth   = true;
                    $mail->Host       = $param['smtp'];
                    $mail->SMTPSecure = $param['encry'];
                    $mail->Port       = $param['port'];
                    $mail->Hostname   = '';
                    $mail->CharSet    = $param['encoded'];
                    $mail->FromName   = $param['nickname'];
                    $mail->Username   = $param['email'];
                    $mail->Password   = $param['password'];
                    $mail->From       = $param['email'];
                    
                    $mail->isHTML(true);
                    if (is_string($param['email_cc'])) $mail->addAddress(empty($param['email_cc']) ? $param['email'] : $param['email_cc'], '');
                    else if (is_array($param['email_cc'])) foreach ($param['email_cc'] as $val) $mail->addAddress(empty($val) ? $param['email'] : $val,'');
                    $mail->Subject    = $param['title'];
                    $mail->Body       = $param['content'];
                    
                    $status = $mail->send();
                    
                    // $result = false;
                    
                    // 简单的判断与提示信息
                    if ($status) {
                        $data = true;
                        $code = 200;
                    } else $data = false;
                    
                } catch (\Exception $e) {
                    
                    $msg = $e->getMessage();
                }
                
                $data = $param;
            }
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 导入文章
     */
    public function importArticle(Request $request)
    {
        if ($request->isPost()) {
            
            $data  = [];
            $code  = 400;
            $msg   = '上传失败';
            
            $param = $request->param();
            
            // 取原文件名
            $name  = explode('.', $_FILES['file']['name'] ?? '');
            // 去除后缀
            array_pop($name);
            // 过滤非法字符 - 数组转字符串
            $name  = str_replace([
                '.', '\\', '/', ':', '*', '`', '?', '<', '>', '%', '&', '$', '#', ' '
            ], '', implode('.', $name));
            
            // 保存文件
            $upload = $this->tool->upload('file', ['storage','cache', [$name]], 'one', 'file|fileExt:md');
            
            if ($upload['code'] == 200) {
                
                // 上传文件的本地路径
                $url  = str_replace($this->tool->domain() . '/', '', $upload['data']);
                // 文件内容
                $content = $this->File->readFile($url);
                // 删除文件
                $this->File->unlinkFile($url);
                
                $article = new Article;
                $article->title    = $name;
                $article->content  = $content;
                $article->users_id = Session::get('login_account')['id'];
                $article->save();
                
                $code = 200;
                $msg  = '导入完成！';
                
                // 清除缓存
                Cache::tag(['article','group'])->clear();
            }
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 导入页面
     */
    public function importPage(Request $request)
    {
        if ($request->isPost()) {
            
            $data  = [];
            $code  = 400;
            $msg   = '上传失败';
            
            $param = $request->param();
            
            // 取原文件名
            $name  = explode('.', $_FILES['file']['name'] ?? '');
            // 去除后缀
            array_pop($name);
            // 过滤非法字符 - 数组转字符串
            $name  = str_replace([
                '.', '\\', '/', ':', '*', '`', '?', '<', '>', '%', '&', '$', '#', ' '
            ], '', implode('.', $name));
            
            // 保存文件
            $upload = $this->tool->upload('file', ['storage','cache', [$name]], 'one', 'file|fileExt:md');
            
            if ($upload['code'] == 200) {
                
                // 上传文件的本地路径
                $url  = str_replace($this->tool->domain() . '/', '', $upload['data']);
                // 文件内容
                $content = $this->File->readFile($url);
                // 删除文件
                $this->File->unlinkFile($url);
                
                $page = new Page;
                $page->title    = $name;
                $page->content  = $content;
                $page->alias    = $this->helper->VerifyCode(10, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
                $page->save();
                
                $code = 200;
                $msg  = '导入完成！';
                
                // 清除缓存
                Cache::tag(['page','group'])->clear();
            }
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 读取文件
     */
    public function readFile(Request $request)
    {
        if ($request->isPost()) {
            
            $data  = [];
            $code  = 400;
            $msg   = '读取失败';
            
            $param = $request->param();
            
            // 取原文件名
            $name  = explode('.', $_FILES['file']['name'] ?? '');
            // 去除后缀
            array_pop($name);
            // 过滤非法字符 - 数组转字符串
            $name  = str_replace([
                '.', '\\', '/', ':', '*', '`', '?', '<', '>', '%', '&', '$', '#', ' '
            ], '', implode('.', $name));
            
            // 保存文件
            $upload = $this->tool->upload('file', ['storage','cache', [$name]], 'one', 'file|fileExt:md');
            
            if ($upload['code'] == 200) {
                
                // 上传文件的本地路径
                $url  = str_replace($this->tool->domain() . '/', '', $upload['data']);
                // 文件名称
                $data['name']    = $name;
                // 文件内容
                $data['content'] = $this->File->readFile($url);
                // 删除文件
                $this->File->unlinkFile($url);
                
                $code = 200;
                $msg  = '读取完成';
            }
            
            return $this->json($data, $msg, $code);
        }
    }
    
    // END
}