<?php
// +----------------------------------------------------------------------
// | 页面 增 - 删 - 改 方法
// +----------------------------------------------------------------------
// | 页面数据操作方法
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\Request;
use inis\utils\{File};
use app\model\sqlite\{Search};
use think\exception\ValidateException;
use app\validate\Users as UsersValidate;
use think\facade\{Config, Cache, Session, Validate};
use app\model\mysql\{Tag, Page, Links, Users, Music, Banner, Placard, Options, Article, AuthRule, Comments, LinksSort, ArticleSort};

class Method extends Base
{
    // inis 配置
    protected $config;
    
    public function __construct()
    {
        $this->config = Config::get('inis');
    }
    
    /** 
     * @name 编辑站点设置
     */
    public function EditOptions(Request $request)
    {
        if ($request->isPost()) {
            
            $param = $request->param();
            
            foreach ($param as $key => $val) Options::update(['value'=>$val],['keys'=>$key]);
            
            // 清除缓存
            Cache::tag('options')->clear();
            
            $data = [];
            $code = 200;
            $msg  = '保存成功！';
            
            return $this->create($data, $msg, $code);
        }
    }

    /** 
     * @name 修改个人资料
     */
    public function EditProfile(Request $request)
    {
        if ($request->isPost()) {
            
            $param = $request->param();
            
            $password1 = !empty($param['password1']) ? $param['password1'] : null;
            $password2 = !empty($param['password2']) ? $param['password2'] : null;
            
            $users = Users::find($param['id']);
            
            $data = [];
            $code = 200;
            $url  = '';
            $msg  = 'ok';
            
            // 是否清除登录
            $clear_login = false;
            
            // 允许用户提交并存储的字段
            $obtain = ['account','address_url','description','email','head_img','nickname','phone','sex','opt','wechat_pay','alipay','qq_pay'];
            
            if (!empty($password2) and !empty($password2) and ($password1 == $password2)) {
                
                // 验证新密码是否与修改前的密码一致 - return true
                $check = password_verify(md5($param['password1']), $users['password']);
                
                if (!$check) {
                    $clear_login = true;
                    $users->password = password_hash(md5($password1), PASSWORD_BCRYPT);
                } else {
                    $code  = 400;
                    $msg   = '新密码与修改前密码一致，无须修改！';
                }
            }
            
            try {
                
                $check = validate(UsersValidate::class)->scene('admin_edit')->check($param);
                
                if ($check) {
                    
                    $email = Users::where(['email'=>$param['email']])->findOrEmpty();
                    $phone = Users::where(['phone'=>$param['phone']])->findOrEmpty();
                    
                    if (!$email->isEmpty() and $param['email'] != Session::get('login_account')['email']) {
                        $code  = 400;
                        $msg = '邮箱已存在！';
                    }
                    if (!$phone->isEmpty() and $param['phone'] != Session::get('login_account')['phone']) {
                        $code  = 400;
                        $msg = '手机已存在！';
                    }
                    
                    if ($param['account'] != Session::get('login_account')['account']) {
                        
                        $account = Users::where('account', $param['account'])->findOrEmpty();
                        
                        if(!$account->isEmpty()) $msg = '该账号已存在，请重新输入！';
                        else $users->account = $param['account'];
                    }
                    
                    // 判断字段是否允许存储，防提权
                    foreach ($param as $key => $val) if (in_array($key, $obtain)) {
                        if ($key == 'wechat_pay' or $key == 'alipay' or $key == 'qq_pay') {
                            $users->opt->$key = $val;
                        } else $users->$key = $val;
                    }
                    
                    // 不转换中文编码
                    $users->opt = json_encode($users->opt, JSON_UNESCAPED_UNICODE);
                    $users->save();
                    
                    // 清除缓存
                    Cache::tag(['users','group'])->clear();
                    
                    if ($clear_login) {
                        
                        // 清除用户登录信息
                        Session::clear();
                        
                        $msg  = '请重新登录！';
                        $url  = '/admin/comm/login';
                        
                    } else {
                        
                        unset($users['password']);
                        // 更新用户登录信息
                        Session::set('login_account', $users);
                    }
                }
                
            } catch (ValidateException $exception){
                // 错误返回
                $code = 400;
                $msg  = $exception->getError();
            }
            
            return $this->create($data, $msg, $code, ['url'=>$url]);
        }
    }
    
    /** 
     * @name 新增和修改用户信息
     */
    public function SaveUsers(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            // 允许用户提交并存储的字段
            $obtain = ['account','password','address_url','description','email','head_img','level','nickname','phone','sex','status','remarks'];
            
            if (empty($param['id'])) {
                
                $users = new Users;
                
                if (empty($param['password'])) {
                    // 获取默认密码
                    $password = $this->config['login']['default_password'];
                    $param['password'] = $password;
                    $code = 201;
                    $msg  = '已为您创建默认密码：<span style="color:red">'.$password.'</span>';
                }
                
            } else $users = Users::find($param['id']);
            
            // 存储数据
            foreach ($param as $key => $val) {
                // 判断字段是否允许存储，防提权
                if (in_array($key, $obtain)) {
                    if ($key == 'password') $users->password = password_hash(md5($val), PASSWORD_BCRYPT);
                    else $users->$key = $val;
                }
            }
            
            $users->save();
            
            // 清除缓存
            Cache::tag(['users','group'])->clear();
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /** 
     * @name 删除用户信息
     */
    public function DeleteUsers(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            $id = (empty($param['id'])) ? '' : $param['id'];
            
            // 字符串转数组并去空处理
            $id_arr = array_filter(explode(',', $id));
            
            $login_account = Session::get('login_account');
            
            // 判断删除的用户中是否包含当前登录的帐号
            if (in_array($login_account['id'], $id_arr)) {
                // 过滤当前登录帐号ID
                foreach ($id_arr as $key => $val) {
                    if ($val == $login_account['id']) unset($id_arr[$key]);
                }
                // 前提条件失败
                $code = 412;
                if (count($id_arr) == 0) {
                    $msg  = '<span style="color:red">禁止删除当前登录帐号！</span>';
                } else {
                    $msg  = '需要删除的帐号中已为您<span style="color:red">过滤当前登录帐号</span>！';
                }
            } else {
                if (count($id_arr) == 0) {
                    $code = 400;
                    $msg  = '<span style="color:red">请选择需要被删除的用户！</span>';
                }
            }
            
            Users::destroy($id_arr);
            
            // 清除缓存
            Cache::tag(['users','group'])->clear();
            
            return $this->create($data, $msg, $code);
        }
    }

    /** 
     * @name 新增和修改文章
     */
    public function SaveArticle(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            if (empty($param['tag_id']))  $param['tag_id']  = [];
            if (empty($param['sort_id'])) $param['sort_id'] = [];
            if (empty($param['title']))   $param['title']   = '未命名文章';
            
            // 允许用户提交并存储的字段
            $obtain = ['title','content','description','img_src','font_count','sort_id','tag_name','tag_id','last_update_time','opt'];
            
            // 修改文章
            if (!empty($param['id']))  $article = Article::find($param['id']);
            else {
                
                $article = new Article;
                
                $article->users_id = Session::get('login_account')['id'];
            }
            
            // 存储数据
            foreach ($param as $key => $val) {
                // 判断字段是否允许存储，防提权
                if (in_array($key, $obtain)) {
                    // 分类ID转字符串存储
                    if ($key == 'sort_id')  $article->$key = '|'.implode("|",str_replace(',','|',$val)).'|';
                    else if ($key == 'opt') $article->$key = json_encode($val, JSON_UNESCAPED_UNICODE);
                    else $article->$key = $val;
                }
            }
            // 最后修改时间
            $article->last_update_time = time();
            $article->save();
            
            // 插入标签数据
            (new Tag)->TagSave($article->id, $param['tag_id'], array_filter(
                (empty($param['tag_name'])) ? [] : $param['tag_name'] 
            ));
            
            // 清除缓存
            Cache::tag(['article','group', 'article-sort', 'tag'])->clear();
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /** 
     * @name 新增和修改页面
     */
    public function SavePage(Request $request)
    {
        if ($request->isPost()) {
            
            $param = $request->param();
            
            $data = [];
            $code = 400;
            $msg  = '别名已存在！';
            
            if (empty($param['title'])) $param['title'] = '未命名页面';
            
            // 允许用户提交并存储的字段
            $obtain = ['title','content','alias'];
            
            // 修改页面
            if (!empty($param['id'])) $page = Page::find($param['id']);
            else $page = new Page;
            
            // 别名
            $alias = Page::where(['alias'=>$param['alias']])->findOrEmpty();
            
            if ($param['alias'] == $alias->alias) {
                if (empty($param['id']) or $alias->id != $param['id']) {
                    
                    $code = 400;
                    $msg  = '别名已存在！';
                    
                } else {
                    
                    // 存储数据
                    foreach ($param as $key => $val) {
                        // 判断字段是否允许存储，防提权
                        if (in_array($key, $obtain)) $page->$key = $val;
                    }
                    
                    $page->save();
                    
                    $code = 200;
                    $msg  = 'ok';
                }
                
            } else {
                
                // 存储数据
                foreach ($param as $key => $val) {
                    // 判断字段是否允许存储，防提权
                    if (in_array($key, $obtain)) $page->$key = $val;
                }
                
                $page->save();
                
                $code = 200;
                $msg  = 'ok';
            }
            
            // 清除缓存
            Cache::tag(['page','group'])->clear();
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /** 
     * @name 删除用户信息
     */
    public function DeletePage(Request $request)
    {
        if ($request->isPost()) {
            
            $param = $request->param();
            
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            $id = (empty($param['id'])) ? '' : $param['id'];
            
            // 字符串转数组并去空处理
            $id_arr = array_filter(explode(',', $id));
            
            Page::destroy($id_arr);
            
            // 清除缓存
            Cache::tag(['page','group'])->clear();
            
            return $this->create($data, $msg, $code);
        }
    }

    /** 
     * @name 新增和修改文章分类
     */
    public function SaveArticleSort(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            if(empty($param['description'])) $param['description'] = '这是一个默认的分类描述';
            
            $data = [];
            $code = 400;
            $msg  = '错误！';
            
            // 允许用户提交并存储的字段
            $obtain = ['name','description','opt'];
            
            $param['name'] = $param['sort_name'];
            
            // 判断分类名数据库是否已存在
            $sort_name = ArticleSort::where('name', $param['name'])->findOrEmpty();
            
            // 修改分类
            if (!empty($param['id'])) $sort = ArticleSort::find($param['id']);
            else $sort = new ArticleSort;
            
            // 如果 分类名 或 缩略名 发生改变
            if($param['sort_name'] != $sort['name']){
                
                if (!$sort_name->isEmpty()) $msg = '分类名已存在，请重新输入！';
                else {
                    
                    // 存储数据 - 判断字段是否允许存储，防提权
                    foreach ($param as $key => $val) if (in_array($key, $obtain)) {
                        if ($key == 'opt') {
                            $sort->$key = json_encode($val, JSON_UNESCAPED_UNICODE);
                        } else $sort->$key = $val;
                    }
                    
                    $code = 200;
                    $msg  = '保存成功呀！';
                }
            } else {
                
                // 存储数据 - 判断字段是否允许存储，防提权
                foreach ($param as $key => $val) if (in_array($key, $obtain)) {
                    if ($key == 'opt') {
                        $sort->$key = json_encode($val, JSON_UNESCAPED_UNICODE);
                    } else $sort->$key = $val;
                }
                
                $code = 200;
                $msg  = '保存成功呀！';
            }
            
            if (!$sort_name->isEmpty()) $msg = '分类名已存在，请重新输入！';
            else {
                
                // 存储数据 - 判断字段是否允许存储，防提权
                foreach ($param as $key => $val) if (in_array($key, $obtain)) {
                    if ($key == 'opt') {
                        $sort->$key = json_encode($val, JSON_UNESCAPED_UNICODE);
                    } else $sort->$key = $val;
                }
                
                $code = 200;
                $msg  = '保存成功！';
            }
            
            $sort->save();
            
            // 清除缓存
            Cache::tag(['article-sort','group'])->clear();
            
            return $this->create($data, $msg, $code);
        }
    }

    /** 
     * @name 删除文章分类
     */
    public function DeleteArticleSort(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            ArticleSort::destroy($param['id']);
            
            // 清除缓存
            Cache::tag(['article-sort','group'])->clear();
            
            $data = [];
            $code = 200;
            $msg  = '删除成功！';
            
            return $this->create($data, $msg, $code);
        }
    }

    /** 
     * @name 新增和修改友链分组
     */
    public function SaveLinksSort(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            if(empty($param['description'])) $param['description'] = '这是一个默认的分组描述';
            
            $data = [];
            $code = 400;
            $msg  = '错误！';
            
            // 允许用户提交并存储的字段
            $obtain = ['name','description'];
            
            $param['name'] = $param['sort_name'];
            
            // 判断分类名数据库是否已存在
            $sort_name = LinksSort::where('name', $param['name'])->findOrEmpty();
            
            // 修改
            if(!empty($param['id'])) $sort = LinksSort::find($param['id']);
            else $sort = new LinksSort;
            
            // 如果 分类名 或 缩略名 发生改变
            if($param['sort_name'] != $sort['name']){
                
                if (!$sort_name->isEmpty()) $msg = '分类名已存在，请重新输入！';
                else{
                    
                    // 存储数据 - 判断字段是否允许存储，防提权
                    foreach ($param as $key => $val) if (in_array($key, $obtain)) $sort->$key = $val;
                    
                    $code = 200;
                    $msg  = '保存成功呀！';
                }
            }else{
                // 存储数据 - 判断字段是否允许存储，防提权
                foreach ($param as $key => $val) if (in_array($key, $obtain)) $sort->$key = $val;
                
                $code = 200;
                $msg  = '保存成功呀！';
            }
            
            if (!$sort_name->isEmpty()) $msg = '分类名已存在，请重新输入！';
            else{
                
                // 存储数据 - 判断字段是否允许存储，防提权
                foreach ($param as $key => $val) if (in_array($key, $obtain)) $sort->$key = $val;
                
                $code = 200;
                $msg  = '保存成功！';
            }
            
            $sort->save();
            
            // 清除缓存
            Cache::tag(['links-sort','group','links'])->clear();
            
            return $this->create($data, $msg, $code);
        }
    }

    /** 
     * @name 删除友链分组
     */
    public function DeleteLinksSort(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            LinksSort::destroy($param['id']);
            
            // 清除缓存
            Cache::tag(['links-sort','group','links'])->clear();
            
            $data = [];
            $code = 200;
            $msg  = '删除成功！';
        }
        
        return $this->create($data, $msg, $code);
    }

    /** 
     * @name 新增和修改友链
     */
    public function SaveLinks(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            $data = [];
            $code = 400;
            $msg  = '错误！';
            
            // 允许用户提交并存储的字段
            $obtain = ['name','description','url','head_img','sort_id'];
            
            $param['name'] = $param['link_name'];
            
            // 判断友链名是否已存在
            $link_name = Links::where('name', $param['name'])->findOrEmpty();
            
            if (!empty($param['id'])) $links = Links::find($param['id']);
            else $links = new Links;
            
            if ($param['name'] != $links['name']) {
                
                if (!$link_name->isEmpty()) $msg = '友链名称已存在，请重新输入！';
                else {
                    if (empty($param['sort_id'])) $param['sort_id'] = $links->id;
                    // 存储数据 - 判断字段是否允许存储，防提权
                    foreach ($param as $key => $val) if (in_array($key, $obtain)) $links->$key = $val;
                    $code = 200;
                    $msg  = '修改成功！';
                }
            } else {
                foreach ($param as $key => $val) if (in_array($key, $obtain)) $links->$key = $val;
                $code = 200;
                $msg  = '保存成功！';
            }
            
            $links->save();
            
            // 清除缓存
            Cache::tag(['links','group','links-sort'])->clear();
            
            return $this->create($data, $msg, $code);
        }
    }

    /** 
     * @name 删除友链
     */
    public function DeleteLinks(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            Links::destroy($param['id']);
            
            // 清除缓存
            Cache::tag(['links','group','links-sort'])->clear();
            
            $data = [];
            $code = 200;
            $msg  = '删除成功！';
        }
        
        return $this->create($data, $msg, $code);
    }
    
    /** 
     * @name 新增和修改轮播
     */
    public function SaveBanner(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            $data = [];
            $code = 400;
            $msg  = '错误！';
            
            // 允许用户提交并存储的字段
            $obtain = ['title','description','url','img','opt'];
            
            if (!empty($param['id'])) $banner = Banner::find($param['id']);
            else $banner = new Banner;
            
            // 存储数据 - 判断字段是否允许存储，防提权
            foreach ($param as $key => $val) if (in_array($key, $obtain)) {
                if ($key == 'opt') $banner->$key = json_encode($val, JSON_UNESCAPED_UNICODE);
                else $banner->$key = $val;
            }
            $code = 200;
            $msg  = '保存成功！';
            
            $banner->save();
            
            // 清除缓存
            Cache::tag(['banner','group'])->clear();
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /** 
     * @name 删除轮播
     */
    public function DeleteBanner(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            Banner::destroy($param['id']);
            
            // 清除缓存
            Cache::tag(['banner','group'])->clear();
            
            $data = [];
            $code = 200;
            $msg  = '删除成功！';
        }
        
        return $this->create($data, $msg, $code);
    }

    /** 
     * @name 新增和修改标签
     */
    public function SaveTag(Request $request)
    {
        if ($request->isPost()){

            $param = $request->param();
            
            $data = [];
            $code = 400;
            $msg  = '错误！';
            
            $param['name'] = $param['tag_name'];
            
            // 允许用户提交并存储的字段
            $obtain = ['name'];
            
            // 判断标签名数据库是否已存在
            $tag_name = Tag::where('name', $param['name'])->findOrEmpty();
            
            if (!empty($param['id'])) $tag = Tag::find($param['id']);
            else $tag = new Tag;
            
            if ($param['name'] != $tag['name']) {
                
                if (!$tag_name->isEmpty()) $msg = '标签名称已存在，请重新输入！';
                else {
                    // 存储数据 - 判断字段是否允许存储，防提权
                    foreach ($param as $key => $val) if (in_array($key, $obtain)) $tag->$key = $val;
                    $code = 200;
                    $msg  = '修改成功！';
                }
            } else {
                foreach ($param as $key => $val) if (in_array($key, $obtain)) $tag->$key = $val;
                $code = 200;
                $msg  = '保存成功！';
            }
            
            $tag->save();
            
            // 清除缓存
            Cache::tag(['tag','group'])->clear();

            return $this->create($data, $msg, $code);
        }
    }

    /** 
     * @name 删除标签
     */
    public function DeleteTag(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            // 删除文章下的标签
            Tag::DelTag($param['id']);
            
            // 删除标签
            Tag::destroy($param['id']);
            
            // 清除缓存
            Cache::tag(['tag','group'])->clear();
            
            $data = [];
            $code = 200;
            $msg  = '删除成功！';
            
            return $this->create($data, $msg, $code);
        }
    }

    /** 
     * @name 删除文章
     */
    public function DeleteArticle(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            if (!empty($param['model']) and $param['model'] == true) $model = true;
            else $model = false;
            
            Article::destroy($param['id'], $model);
            
            // 清除缓存
            Cache::tag(['article','group','article-sort','tag'])->clear();
            
            $data = [];
            $code = 200;
            $msg  = '删除成功！';
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /** 
     * @name 恢复文章
     */
    public function RecoverArticle(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            $article = Article::onlyTrashed()->find($param['id']);
            $article->restore();
            
            // 清除缓存
            Cache::tag(['article','group'])->clear();
            
            $data = [];
            $code = 200;
            $msg  = '恢复成功！';
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /** 
     * @name 修改评论
     */
    public function SaveComments(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            $data = [];
            $code = 400;
            $msg  = 'ID不能为空！';
            
            // 允许用户提交并存储的字段
            $obtain = ['url','email','content','nickname'];
            
            if (!empty($param['id'])) {
                
                $comments = Comments::find($param['id']);
                
                // 存储数据 - 判断字段是否允许存储，防提权
                foreach ($param as $key => $val) if (in_array($key, $obtain)) $comments->$key = $val;
                
                $comments->save();
                
                // 清除缓存
                Cache::tag(['comments','group'])->clear();
                
                $code = 200;
                $msg  = '更新成功！';
            }
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /** 
     * @name 删除评论
     */
    public function DeleteComments(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            $id = (empty($param['id'])) ? '' : $param['id'];
            
            // 字符串转数组并去空处理
            $id_arr = array_filter(explode(',', $id));
            
            Comments::destroy($id_arr);
            
            // 清除缓存
            Cache::tag(['comments','group'])->clear();
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /** 
     * @name 新增和修改权限规则
     */
    public function SaveAuthRule(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            // 允许用户提交并存储的字段
            $obtain = ['title','route','pid'];
            
            if (empty($param['id'])) {
                
                $check = AuthRule::where(['route'=>$param['route']])->findOrEmpty();
                if (!$check->isEmpty()) {
                    $code = 400;
                    $msg  = '此规则已存在！';
                } else $auth_rule = new AuthRule;
                
            } else $auth_rule = AuthRule::find($param['id']);
            
            if ($code == 200) {
                // 存储数据
                foreach ($param as $key => $val) {
                    // 判断字段是否允许存储，防提权
                    if (in_array($key, $obtain)) {
                        $auth_rule->$key = $val;
                    }
                }
                $auth_rule->save();
            }
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /** 
     * @name 新增和修改音乐
     */
    public function SaveMusic(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            // 允许用户提交并存储的字段
            $obtain = ['title','description','url','head_img'];
            
            if (empty($param['id'])) $music = new Music;
            else $music = Music::find($param['id']);
            
            // 存储数据
            foreach ($param as $key => $val) {
                // 判断字段是否允许存储，防提权
                if (in_array($key, $obtain)) $music->$key = $val;
            }
            
            $music->save();
            
            // 清理缓存
            Cache::tag(['music','group'])->clear();
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /** 
     * @name 删除音乐
     */
    public function deleteMusic(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            $id = (empty($param['id'])) ? '' : $param['id'];
            
            // 字符串转数组并去空处理
            $id_arr = array_filter(explode(',', $id));
            
            $login_account = Session::get('login_account');
            
            Music::destroy($id_arr);
            
            // 清除缓存
            Cache::tag(['music','group'])->clear();
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /** 
     * @name 保存站长信息
     */
    public function saveMaster(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            $options  = Options::where(['keys'=>'webmaster'])->find();
            
            unset($param['name']);
            
            // 不转换中文编码
            $options->opt = json_encode($param, JSON_UNESCAPED_UNICODE);
            
            $options->save();
            
            // 清除缓存
            Cache::tag('options')->clear();
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /** 
     * @name 新增和修改公告
     */
    public function SavePlacard(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            // 允许用户提交并存储的字段
            $obtain = ['title','type','content','opt'];
            
            if (empty($param['id'])) $placard = new Placard;
            else $placard = Placard::find($param['id']);
            
            // 存储数据
            foreach ($param as $key => $val) {
                // 判断字段是否允许存储，防提权
                if (in_array($key, $obtain)) {
                    if ($key == 'opt') $placard->$key = json_encode($val, JSON_UNESCAPED_UNICODE);
                    else $placard->$key = $val;
                }
            }
            
            $placard->save();
            
            // 清理缓存
            Cache::tag(['placard','group'])->clear();
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /** 
     * @name 删除公告
     */
    public function deletePlacard(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            $id = (empty($param['id'])) ? '' : $param['id'];
            
            // 字符串转数组并去空处理
            $id_arr = array_filter(explode(',', $id));
            
            Placard::destroy($id_arr);
            
            // 清除缓存
            Cache::tag(['placard','group'])->clear();
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /** 
     * @name 保存配置
     */
    public function SaveOptions(Request $request)
    {
        if ($request->isPost()){
            
            $data = [];
            $code = 400;
            $msg  = 'ok';
            
            $param = $request->param();
            
            if (empty($param['key'])) $msg = 'key不得为空！';
            else {
                
                // 存在的keys
                $keys = ['site','config:security','config:applets','config:system','config:email-serve'];
                
                // 允许用户提交并存储的字段
                $obtain = ['opt','value'];
                
                // 动态方法 且 keys 存在
                if (in_array($param['key'], $keys)) {
                    
                    $options = Options::where(['keys'=>$param['key']])->find();
                    
                    // 存储数据
                    foreach ($param as $key => $val) {
                        // 判断字段是否允许存储，防提权
                        if (in_array($key, $obtain)) {
                            if ($key == 'opt') $options->$key = json_encode($val, JSON_UNESCAPED_UNICODE);
                            else $options->$key = $val;
                        }
                    }
                    
                    $options->save();
                    
                    $code = 200;
                    $data = $options;
                }
                
                // 清理有关文章的缓存
                if ($param['key'] == 'config:system') Cache::tag(['article', 'article-sort', 'tag'])->clear();
                // 清除缓存
                Cache::tag(['options'])->clear();
            }
            
            return $this->create($data, $msg, $code);
        }
    }
    
    public function saveOptObj(Request $request)
    {
        if ($request->isPost()){
            
            $data  = [];
            $code  = 400;
            $msg   = 'ok';
            
            $param = $request->param();
            
            if (empty($param['keys'])) $msg = 'key不得为空！';
            else {
                
                // 存在的keys
                $keys   = ['config:email-serve'];
                
                // 允许用户提交并存储的字段
                $obtain = ['opt','value'];
                
                // 动态方法 且 keys 存在
                if (in_array($param['keys'], $keys)) {
                    
                    $items = Options::where(['keys'=>$param['keys']])->find();
                    
                    // 存储数据
                    foreach ($param as $key => $val) {
                        // 判断字段是否允许存储，防提权
                        if (in_array($key, $obtain)) $items->$key = json_encode($val, JSON_UNESCAPED_UNICODE);
                    }
                    
                    $code = 200;
                    $items->save();
                    $data = $items;
                }
            }
            
            // 清除tag标签的缓存数据
            Cache::tag('options')->clear();
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /** 
     * @name 安装API
     */
    public function installApi(Request $request)
    {
        if ($request->isPost()){
            
            $data  = [];
            $code  = 200;
            $msg   = 'ok';
            
            $param = $request->param();
            unset($param['name']);
            
            try {
                
                $File = new File;
                
                // 下载API文件
                $File->downloadFile($param['url'], '../app/api/controller/plugins/', 'api.zip');
                
                $data['id']   = $param['id'];
                $param['path']= [];
                
                $item = Options::where(['keys'=>'config:plugin-api'])->findOrEmpty();
                
                // 配置为空
                if ($item->isEmpty()) {
                    
                    $item = new Options;
                    $item->keys = 'config:plugin-api';
                    $item->opt  = json_encode([$param], JSON_UNESCAPED_UNICODE);
                    
                } else {
                    
                    $plugins = json_decode(json_encode($item->opt), true);
                    
                    if (!empty($plugins)) foreach ($plugins as $key => $val) {
                        
                        // 安装
                        if ($param['id'] != $val['id']) $plugins[] = $param;
                        // 升级
                        else if ($param['id'] == $val['id']) {
                            
                            // 更新版本号
                            $param['version'] = !empty($param['new_version']) ? $param['new_version'] : $val['version'];
                            
                            unset($param['new_version']);
                            
                            $plugins[$key]    = $param;
                        }
                        
                        $item->opt = json_encode($plugins, JSON_UNESCAPED_UNICODE);
                        
                    } else $item->opt  = json_encode([$param], JSON_UNESCAPED_UNICODE);
                }
                
                $item->save();
                
            } catch (\Exception $e) {
                
                $code = 400;
                $msg  = $e->getMessage();
            }
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /** 
     * @name 解压API
     */
    public function unzipApi(Request $request)
    {
        if ($request->isPost()){
            
            $data  = [];
            $code  = 200;
            $msg   = 'ok';
            
            $param = $request->param();
            
            $File  = new File;
            $zip   = new \ZipArchive;
            
            try {
                
                // 压缩包内的文件路径
                $files = [];
                // 压缩包根目录文件夹名
                $dirs  = [];
                $path  = '../app/api/controller/plugins/api.zip';
                
                if ($zip->open($path) === true) {
                    
                    // 获取压缩包内的文件路径
                    for ($i = 0; $i < $zip->count(); $i++) $files[] = $zip->getNameIndex($i);
                    if (!empty($files)) foreach ($files as $key => $val) {
                        $item = explode('/', $val);
                        if (count($item) >= 2) $dirs[] = $item[0];
                    }
                    // 文件夹去重
                    if (!empty($dirs)) $dirs = array_unique($dirs);
                    
                    // 将压缩包文件解压到API插件目录下
                    $zip->extractTo('../app/api/controller/plugins/');
                    
                    // 关闭zip文件
                    $zip->close();
                }
                
                // 删除文件
                $File->unlinkFile($path);
                
                $item    = Options::where(['keys'=>'config:plugin-api'])->find();
                $plugins = json_decode(json_encode($item->opt), true);
                
                foreach ($plugins as $key => $val) {
                    if ($param['id'] == $val['id']) {
                        $plugins[$key]['path']['dir']  = $dirs;
                        $plugins[$key]['path']['file'] = $files;
                        $item->opt = json_encode($plugins, JSON_UNESCAPED_UNICODE);
                    } 
                }
                
                $item->save();
                
            } catch (\Exception $e) {
                
                $code = 400;
                $msg  = $e->getMessage();
            }
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /** 
     * @name 卸载API
     */
    public function uninstallApi(Request $request)
    {
        if ($request->isPost()){
            
            $data  = [];
            $code  = 200;
            $msg   = 'ok';
            
            $param = $request->param();
            
            $File  = new File;
            $path  = '../app/api/controller/plugins/';
            
            $item    = Options::where(['keys'=>'config:plugin-api'])->find();
            $plugins = json_decode(json_encode($item->opt), true);
            
            foreach ($plugins as $key => $val) {
                
                if ((int)$param['id'] == (int)$val['id']) {
                    
                    // 删除非空目录
                    if (!empty($val['path']['dir']))  foreach ($val['path']['dir']  as $k => $v) $File->removeDir($path . $v, true);
                    // 卸载前删除全部文件
                    if (!empty($val['path']['file'])) foreach ($val['path']['file'] as $k => $v) if (substr($v, 0 - strlen('/')) != '/') $File->unlinkFile($path . $v);
                    
                    if (count($plugins) >= 2) unset($plugins[$key]);
                    else $plugins = [];
                } 
                
                $item->opt = json_encode(array_merge($plugins), JSON_UNESCAPED_UNICODE);
            }
            
            $item->save();
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /** 
     * @name 新增和修改搜索记录
     */
    public function SaveSearch(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            // 允许用户提交并存储的字段
            $obtain = ['name'];
            $param['name'] = !empty($param['named']) ? $param['named'] : $param['name'];
            
            if (empty($param['id'])) $item = new Search;
            else $item = Search::find($param['id']);
            
            // 存储数据
            foreach ($param as $key => $val) {
                // 判断字段是否允许存储，防提权
                if (in_array($key, $obtain)) $item->$key = $val;
            }
            
            $item->save();
            
            // 清理缓存
            Cache::tag(['search','group'])->clear();
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /** 
     * @name 删除搜索记录
     */
    public function deleteSearch(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            $data  = [];
            $code  = 200;
            $msg   = 'ok';
            
            $id    = (empty($param['id'])) ? '' : $param['id'];
            
            // 字符串转数组并去空处理
            $array = array_filter(explode(',', $id));
            
            Search::destroy($array);
            
            // 清除缓存
            Cache::tag(['search','group'])->clear();
            
            return $this->create($data, $msg, $code);
        }
    }
    
    // END
}