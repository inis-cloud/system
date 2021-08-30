<?php

namespace app\index\controller;

use app\Request;
use app\model\Tag;
use app\model\Page;
use app\model\Links;
use app\model\Users;
use app\model\Music;
use inis\utils\File;
use think\facade\Db;
use app\model\Options;
use app\model\Article;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Cookie;
use app\model\LinksSort;
use think\facade\Session;
use app\model\ArticleSort;
use PHPMailer\PHPMailer\PHPMailer;

class Handle extends Base
{
    // 工具类
    protected $tool;
    protected $File;
    protected $DBUPDATE;

    protected $token_prefix = 'inis.';
    
    public function __construct()
    {
        $this->tool = new Tool;
        $this->File = new File;
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
            
            $options = Options::where(['keys'=>'site_conf'])->find();
            
            if($param['status'] == 1){
                
                $token = md5($this->token_prefix.time());
                $data['status'] = 1;
                $data['token']  = $token;
                $msg  = 'Token验证 开启 成功！';
                
                $options->opt->token->status = 1;
                $options->opt->token->value  = $token;
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $data['status'] = 0;
                $data['token']  = '';
                $msg  = 'Token验证 关闭 成功！';
                
                $options->opt->token->status = 0;
                $options->opt->token->value = '';
            }
            $options->opt = json_encode($options->opt, JSON_UNESCAPED_UNICODE);
            $options->save();
            
            return $this->create($data,$code,$msg);
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
            
            $options = Options::where(['keys'=>'site_conf'])->find();
            
            if($param['status'] == 1){
                
                $data['status'] = 1;
                $msg  = '允许通过API方式获取Token 开启 成功！';
                
                $options->opt->token->open = 1;
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $data['status'] = 0;
                $msg  = '允许通过API方式获取Token 关闭 成功！';
                
                $options->opt->token->open = 0;
            }
            $options->opt = json_encode($options->opt, JSON_UNESCAPED_UNICODE);
            $options->save();
            
            return $this->create($data,$code,$msg);
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
            
            $options = Options::where(['keys'=>'site_conf'])->find();
            
            if($param['code'] == 1){
                $options->opt->token->value = $token;
                $options->opt = json_encode($options->opt, JSON_UNESCAPED_UNICODE);
                $options->save();
                
                $data = ['token'=>$token];
                $code = 200;
                $msg  = '刷新Token 成功！';
            }else{
                $data = [];
                $code = 400;
                $msg  = '刷新Token 失败！';
            }
            
            return $this->create($data,$code,$msg);
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
            
            $options = Options::where(['keys'=>'site_conf'])->find();
            
            if($param['status'] == 1){
                
                $data['status'] = 1;
                $msg  = '白名单 开启 成功！';
                $options->opt->domain = ['status'=>1];
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $data['status'] = 0;
                $msg  = '白名单 关闭 成功！';
                $options->opt->domain = ['status'=>0];
            }
            $options->opt = json_encode($options->opt, JSON_UNESCAPED_UNICODE);
            $options->save();
            
            return $this->create($options->opt->domain,$code,$msg);
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
            
            $article = Article::find($param['id']);
            
            if($param['status'] == 1){
                
                $article->is_top = 1;
                $data['status']  = 1;
                $msg = '文章置顶开启成功！';
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $article->is_top = 0;
                $data['status']  = 0;
                $msg = '文章置顶关闭成功！';
            }
            $article->save();
            
            // 清除缓存
            Cache::tag('article')->clear();
            
            return $this->create($data,$code,$msg);
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
            
            $article = Article::find($param['id']);
            
            if($param['status'] == 1){
                
                $article->is_show = 1;
                $data['status']   = 1;
                $msg = '文章显示开启成功！';
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $article->is_show = 0;
                $data['status']   = 0;
                $msg = '文章显示关闭成功！';
            }
            $article->save();
            
            // 清除缓存
            Cache::tag('article')->clear();
            
            return $this->create($data,$code,$msg);
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
            
            $page = Page::find($param['id']);
            
            if($param['status'] == 1){
                
                $page->is_show = 1;
                $data['status']   = 1;
                $msg = '页面显示开启成功！';
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $page->is_show = 0;
                $data['status']   = 0;
                $msg = '页面显示关闭成功！';
            }
            $page->save();
            
            // 清除缓存
            Cache::tag('page')->clear();
            
            return $this->create($data,$code,$msg);
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
            
            $article_sort = ArticleSort::find($param['id']);
            
            if($param['status'] == 1){
                
                $article_sort->is_show = 1;
                $data['status']   = 1;
                $msg = '文章分类显示开启成功！';
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $article_sort->is_show = 0;
                $data['status']   = 0;
                $msg = '文章分类显示关闭成功！';
            }
            $article_sort->save();
            
            // 清除缓存
            Cache::tag('article-sort')->clear();
            
            return $this->create($data,$code,$msg);
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
            
            $users = Users::find($param['id']);
            
            if($param['status'] == 1){
                
                $users->status   = 1;
                $data['status']  = 1;
                $msg = '该用户被启用！';
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $users->status   = 0;
                $data['status']  = 0;
                $msg = '该用户被禁用！';
            }
            $users->save();
            
            return $this->create($data,$code,$msg);
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
            
            $links = Links::find($param['id']);
            
            if($param['status'] == 1){
                
                $links->is_show = 1;
                $data['status']   = 1;
                $msg = '友链显示开启成功！';
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $links->is_show = 0;
                $data['status']   = 0;
                $msg = '友链显示关闭成功！';
            }
            $links->save();
            
            // 清除缓存
            Cache::tag('links')->clear();
            
            return $this->create($data,$code,$msg);
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
            
            $links_sort = LinksSort::find($param['id']);
            
            if($param['status'] == 1){
                
                $links_sort->is_show = 1;
                $data['status']   = 1;
                $msg = '友链分类显示开启成功！';
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $links_sort->is_show = 0;
                $data['status']   = 0;
                $msg = '友链分类显示关闭成功！';
            }
            $links_sort->save();
            
            // 清除缓存
            Cache::tag('links-sort')->clear();
            
            return $this->create($data,$code,$msg);
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
            
            $tag = Tag::find($param['id']);
            
            if($param['status'] == 1){
                
                $tag->is_show = 1;
                $data['status']   = 1;
                $msg = '友链显示开启成功！';
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $tag->is_show = 0;
                $data['status']   = 0;
                $msg = '友链显示关闭成功！';
            }
            $tag->save();
            
            // 清除缓存
            Cache::tag('tag')->clear();
            
            return $this->create($data,$code,$msg);
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
            
            $options = Options::where(['keys'=>'email_serve'])->findOrEmpty();
            
            if ($options->isEmpty()) $options->keys = 'email_serve';
            $options->opt->email_cc = $param['email_cc'];
            
            $options->opt = json_encode($options->opt, JSON_UNESCAPED_UNICODE);
            $options->save();
            
            return $this->create($data,$code,$msg);
        }
    }
    
    /** 
     * @name 上传图片到本地
     */
    public function uploadImage()
    {
        $upload = (new Tool)->upload('image', '', 'more');
        
        return $upload;
    }
    
    /** 
     * @name 上传资源
     */
    public function upload(Request $request)
    {
        if ($request->isPost()) {
            
            $param = $request->param();
            $mode  = (empty($param['mode'])) ? '' : $param['mode'];
            
            $name = explode('.', $_FILES['file']['name']);
            array_pop($name);
            $name = implode('.',$name);
            
            // 登录用户的ID
            $users_id  = Session::get('login_account')['id'];
            
            if (empty($mode)) $upload = $this->tool->upload('file', ['storage','users/head/uid-'.$users_id, [$name]]);
            elseif ($mode == 'banner') $upload = $this->tool->upload('file', ['storage','banner', [$name]], 'one', 'file|fileExt:jpg,jpeg,png,gif,webp|fileSize:20971520');
            elseif ($mode == 'pay') $upload = $this->tool->upload('file', ['storage','users/pay/uid-'.$users_id, [$name]]);
            elseif ($mode == 'article') $upload = $this->tool->upload('file', ['storage','article/uid-'.$users_id.'/'.date("Y-m"), [$name]], 'one', 'file|fileExt:jpg,jpeg,png,gif,webp|fileSize:20971520');
            elseif ($mode == 'file') $upload = $this->tool->upload('file', ['storage','users/files/uid-'.$users_id.'/'.date("Y-m"), [$name]], 'one', 'file|fileExt:jpg,jpeg,png,gif,webp,svg,ico,zip,gz,mp3,mp4,avi|fileSize:20971520');
            
            return $upload;
        }
    }
    
    /** 
     * @name 上传个人头像
     */
    public function uploadHead()
    {
        $account_id = Session::get('login_account')['id'];
        
        $upload = (new Tool())->upload('image', ['storage', 'users/uid-'.$account_id, ['my-head']]);
        
        if ($upload['code'] == 200){
            
            $users = Users::find($account_id);
            
            $users->head_img = $upload['data'];
            
            $users->save();
            
            unset($users['password']);
            // 更新用户登录信息
            Session::set('login_account', $users);
            Cookie::set('login_account', json_encode($users));
        }
        
        return $upload;
    }
    
    /** 
     * @name 上传站点头像
     */
    public function uploadSiteHead()
    {
        $upload = (new Tool())->upload('image', ['storage', 'users', ['site-head']]);
        
        if($upload['code'] == 200){
            
            Options::update(['value'=>$upload['data']],['keys'=>'site_img']);
        }
        
        return $upload;
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
            
            return $this->create($data,$code,$msg);
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
            
            $music = Music::find($param['id']);
            
            if($param['status'] == 1){
                
                $music->is_show  = 1;
                $data['status']  = 1;
                $msg = '该歌单被启用！';
                
            } else if($param['status'] == 0 or empty($param['status'])) {
                
                $music->is_show  = 0;
                $data['status']  = 0;
                $msg = '该歌单被禁用！';
            }
            $music->save();
            
            return $this->create($data,$code,$msg);
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
            $file_name = array_filter(explode(',', $param['file_name']));
            
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
            
            return $this->create($data,$code,$msg);
        }
    }
    
    public function downloadUpdate(Request $request)
    {
        if ($request->isPost())
        {
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            $param = $request->param();
            
            $url = (!empty($param['file_path'])) ? $param['file_path'] : null;
            
            $this->File->download($url,'../','inis update.zip',0);
            
            return $this->create($data,$code,$msg);
        }
    }
    
    public function unzipUpdate(Request $request)
    {
        if ($request->isPost())
        {
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            $zip = new \ZipArchive;
            
            $file_path = '../inis update.zip';
            $DBUPDATE  = $this->DBUPDATE;
            
            if ($zip->open($file_path) === true) {
                
                // 将压缩包文件解压到根目录下
                $zip->extractTo('../');
                
                // 关闭zip文件
                $zip->close();
                
                if (!empty($DBUPDATE)) {
                    // 执行额外SQL语句
                    if (!empty($DBUPDATE['query'])) foreach ($DBUPDATE['query'] as $val) Db::execute($val);
                    // 导入数据
                    if (!empty($DBUPDATE['data']))  foreach ($DBUPDATE['data']  as $key => $val) Db::name($key)->limit(1)->insertAll($val);
                }
            }
            
            $this->File->unlinkFile($file_path);
            $this->File->unlinkFile('../config/dbupdate.php');
            
            return $this->create($data,$code,$msg);
        }
    }
    
    /** 
     * @name 邮箱服务测试
     */
    public function testEmail(Request $request)
    {
        if ($request->isPost())
        {
            $data = [];
            $code = 400;
            $msg  = 'error';
            
            $param = $request->param();
            
            if (empty($param['smtp']))          $msg = '{smtp} - 服务器地址不得为空！';
            else if (empty($param['encry']))    $msg = '{encry} - 加密方式不得为空！';
            else if (empty($param['port']))     $msg = '{port} - 端口号不得为空！';
            else if (empty($param['encoded']))  $msg = '{encoded} - 编码方式不得为空！';
            else if (empty($param['nickname'])) $msg = '{nickname} - 发件人昵称不得为空！';
            else if (empty($param['email']))    $msg = '{email} - 邮箱帐号不得为空！';
            else if (empty($param['password'])) $msg = '{password} - 邮箱服务密码不得为空！';
            else if (empty($param['to_email'])) $msg = '{to_email} - 收件人邮箱不得为空！';
            else if (empty($param['title']))    $msg = '{title} - 邮件标题不得为空！';
            else if (empty($param['content']))  $msg = '{content} - 邮件内容不得为空！';
            else {
                
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
                $mail->addAddress($param['to_email'],'');
                $mail->Subject    = $param['title'];
                $mail->Body       = $param['content'];
                
                $status = $mail->send();
                
                // $result = false;
                
                // 简单的判断与提示信息
                if ($status) {
                    $data = true;
                    $code = 200;
                } else $data = false;
            }
            
            return $this->create($data,$code,$msg);
        }
    }
    
    // END
}