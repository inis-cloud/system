<?php
// +----------------------------------------------------------------------
// | 页面 查 方法
// +----------------------------------------------------------------------
// | 只返回页面初始数据
// +----------------------------------------------------------------------

namespace app\index\controller;

use app\Request;
use app\model\Tag;
use app\model\Page;
use app\model\Links;
use app\model\Users;
use app\model\Music;
use app\model\Banner;
use app\model\Options;
use app\model\Article;
use think\facade\View;
use app\model\AuthRule;
use app\model\Comments;
use app\model\LinksSort;
use think\facade\Session;
use app\model\ArticleSort;

class Index extends Base
{
    /** 
     * @name 首页
     */
    public function index(Request $request)
    {
        if($request->isPost()){
            // 总评论数量
            $comments = sizeof(Comments::field(['id'])->select());
            // 自己参与的评论数量
            $my_reply = sizeof(Comments::where('users_id','=', Session::get('login_account')['id'])->field(['id'])->select());
            
            // 统计数据
            $count = [
                'comments'    => $comments,
                'my_reply'    => $my_reply,
                // 回复率
                // 'reply_ratio' => (($comments - $my_reply) / $my_reply) * 100,
                'article'     => sizeof(Article::field(['id'])->select()),
                'article_sort'=> sizeof(ArticleSort::field(['id'])->select()),
                'tag'         => sizeof(Tag::field(['id'])->select()),
                'links'       => sizeof(Links::field(['id'])->select()),
                'links_sort'  => sizeof(LinksSort::field(['id'])->select()),
            ];
            
            // 获取热门文章
            $popular  = Article::withAttr('expand',function ($value, $data){
                
                $user = Users::field(['nickname','description','email','address_url','head_img'])->find($data['users_id'])->toArray();
                
                $tag  = Tag::select(explode("|", $data['tag_id']));
                
                $sort = ArticleSort::select(explode("|", $data['sort_id']));
                
                foreach ($tag  as $key => $val) $value['tag'][$key]   = $val['name'];
                foreach ($sort as $key => $val) $value['sort'][$key]  = $val['name'];
                // 文章作者信息
                foreach ($user as $key => $val) $value['author'][$key]=$val;
                
                // 统计评论
                $value['comments'] = sizeof(Comments::where(['article_id'=>$data['id']])->field(['article_id'])->select());
                
                return $value;
                
            })->withoutField(['content'])->where('views','>','20')->order('views','desc')->limit(5)->select();
            
            $data = ['count'=>$count,'popular'=>$popular];
            $code = 200;
            $msg  = 'ok';
            
            return $this->create($data,$code,$msg);
        }
        
        return View::engine('php')->fetch('/index');
    }
    
    /** 
     * @name 管理用户
     */
    public function ManageUsers(Request $request)
    {
        if ($request->isPost()) {
            
            $param = $request->param();
            
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            /* 全部用户数据 */
            if(empty($param['page']))  $param['page']  = 1;
            if(empty($param['limit'])) $param['limit'] = 10;
            if(empty($param['order'])) $param['order'] = 'last_login_time desc,create_time acs';
            $search = (empty($param['search'])) ? '' : $param['search'];
            
            $map1 = ['nickname', 'like', '%'.$search.'%'];
            $map2 = ['account' , 'like', '%'.$search.'%'];
            $map3 = ['email'   , 'like', '%'.$search.'%'];
            
            $opt = [
                'page'   =>  (int)$param['page'], 
                'limit'  =>  (int)$param['limit'],
                'order'  =>  (string)$param['order'],
                'whereOr'=>  [$map1,$map2,$map3],
            ];
            
            $users = Users::ExpandAll(null, $opt);
            
            $sex = [
                ['id'=>0,'text'=>'女'],
                ['id'=>1,'text'=>'男'],
                ['id'=>3,'text'=>'保密']
            ];
            
            $enable = [
                ['id'=>1,'text'=>'启用'],
                ['id'=>0,'text'=>'禁用']
            ];
            
            // 查询操作
            if (!empty($param['id'])) {
                
                $data['edit']  = Users::withoutField(['password','opt'])->find($param['id']);
                
                // 性别选择数据
                foreach ($sex as $key => $val) {
                    if ($val['text'] == $data['edit']['sex']) $val['selected'] = true;
                    $data['sex'][] = $val;
                }
                // 禁用启用选择数据
                foreach ($enable as $key => $val) {
                    if ($val['id'] == $data['edit']['status']) $val['selected'] = true;
                    $data['enable'][] = $val;
                }
                
            } else {
                $data['sex']    = $sex;
                $data['enable'] = $enable;
            }
            
            
            $data['users'] = $users;
            
            return $this->create($data,$code,$msg);
        }
        
        return View::engine('php')->fetch('/page/manage-users');
    }

    /** 
     * @name 个人资料展示
     */
    public function profile()
    {
        return View::engine('php')->fetch('/page/profile');
    }

    /** 
     * @name 编辑个人资料
     */
    public function EditProfile()
    {
        return View::engine('php')->fetch('/page/edit-profile');
    }

    /** 
     * @name 站点设置
     */
    public function options(Request $request)
    {
        if($request->isPost()){
            
            $site_conf = Options::where(['keys'=>'site_conf'])->findOrEmpty();
            
            // if (!$site_conf->isEmpty()) $site_conf->opt = json_decode($site_conf->opt);
            
            if(empty($site_conf->opt->domain))        $site_conf->opt->domain->status = 0;
            if(empty($site_conf->opt->token->open))   $site_conf->opt->token->open    = 0;
            if(empty($site_conf->opt->token->status)) $site_conf->opt->token->status  = 0;
            
            $opt = (new Options)->GetOpt();
            $obtain = ['title','keywords','description','site_img','site_url','site_ico','copy'];
            
            foreach ($obtain as $key => $val) $data[$val] = $opt[$val];
            
            $data['token']  = $site_conf->opt->token;
            $data['domain'] = ['value'=>str_replace(",","\n", $opt['domain']),'status'=>$site_conf->opt->domain];
            
            $code = 200;
            $msg  = 'ok';
            
            return $this->create($data,$code,$msg);
        }
        
        return View::engine('php')->fetch('/page/options');
    }

    /** 
     * @name 撰写文章
     */
    public function WriteArticle(Request $request)
    {
        if($request->isPost()){
            
            // 获取请求参数
            $param = $request->param();
            
            $sort  = ArticleSort::field(['id','name'])->select();
            
            $tag   = Tag::field(['id','name'])->select();
            
            $data  = ['sort'=>$sort,'tag'=>$tag];
            
            if(!empty($param['id'])) $data['article'] = Article::ExpandAll($param['id'], ['withoutField'=>null,'where'=>null]);
            
            $code  = 200;
            $msg   = 'ok';
            
            return $this->create($data,$code,$msg);
        }

        return View::engine('php')->fetch('/page/write-article');
    }

    /** 
     * @name 管理文章
     */
    public function ManageArticle(Request $request)
    {
        if($request->isPost()){
            
            // 获取请求参数
            $param = $request->param();
            
            /* 全部文章数据 */
            if(empty($param['article_page']))  $param['article_page']  = 1;
            if(empty($param['article_limit'])) $param['article_limit'] = 10;
            if(empty($param['article_order'])) $param['article_order'] = 'create_time desc';
            $all_search = (empty($param['all_search'])) ? '' : $param['all_search'];
            
            $all_map1 = ['title'   , 'like', '%'.$all_search.'%'];
            $all_map2 = ['content' , 'like', '%'.$all_search.'%'];
            
            $all_opt = [
                'page'    =>  (int)$param['article_page'],
                'limit'   =>  (int)$param['article_limit'],
                'order'   =>  (string)$param['article_order'],
                'where'   =>  [
                    function ($query) use ($all_map1, $all_map2) {
                        $query->where([$all_map1])->whereOr([$all_map2]);
                    },
                    ['delete_time','=',null]
                ],
            ];
            $article['data'] = Article::ExpandAll(null, $all_opt);
            
            
            
            
            /* 我的文章数据 */
            if(empty($param['my_page']))  $param['my_page']  = 1;
            if(empty($param['my_limit'])) $param['my_limit'] = 10;
            if(empty($param['my_order'])) $param['my_order'] = 'create_time desc';
            $my_search = (empty($param['my_search'])) ? '' : $param['my_search'];
            $users_id  = Session::get('login_account')['id'];
            $my_map1   = ['title'   , 'like', '%'.$my_search.'%'];
            $my_map2   = ['content' , 'like', '%'.$my_search.'%'];
            $my_map3   = ['users_id' , '=', $users_id];
            
            $my_opt = [
                'page'    =>  (int)$param['my_page'],
                'limit'   =>  (int)$param['my_limit'],
                'order'   =>  (string)$param['my_order'],
                'where'   =>  [
                    function ($query) use ($my_map1, $my_map2) {
                        $query->where([$my_map1])->whereOr([$my_map2]);
                    },
                    $my_map3,
                    ['delete_time','=',null]
                ],
            ];
            $my_article['data'] = Article::ExpandAll(null, $my_opt);
            
            
            
            
            /* 删除文章数据 */
            if(empty($param['del_page']))  $param['del_page']  = 1;
            if(empty($param['del_limit'])) $param['del_limit'] = 10;
            if(empty($param['del_order'])) $param['del_order'] = 'create_time desc';
            
            $del_count = count(Article::onlyTrashed()->field(['id'])->select());
            
            $del_article['page'] = ceil($del_count/$param['del_limit']);
            $del_article['count']= $del_count;
            
            // 防止分页请求超出页码
            if($param['del_page'] > $del_article['page']) $param['del_page'] = $del_article['page'];
            
            $del_article['data'] = Article::ExpandArticle((int)$param['del_page'], (int)$param['del_limit'], (string)$param['del_order'], ['trashed'=>true]);
            
            $data = ['article'=>$article,'my_article'=>$my_article,'del_article'=>$del_article];
            $code = 200;
            $msg  = 'ok';
            
            return $this->create($data,$code,$msg);
        }
        
        return View::engine('php')->fetch('/page/manage-article');
    }
    
    /** 
     * @name 新建页面
     */
    public function WritePage(Request $request)
    {
        if ($request->isPost()) {
            
            // 获取请求参数
            $param = $request->param();
            
            $data  = [];
            $code  = 200;
            $msg   = 'ok';
            
            if (!empty($param['id'])) $data = Page::ExpandAll($param['id'], ['where'=>[]]);
            
            return $this->create($data,$code,$msg);
        }
        
        return View::engine('php')->fetch('/page/write-page');
    }
    
    /** 
     * @name 管理页面
     */
    public function ManagePage(Request $request)
    {
        if ($request->isPost()) {
            
            $param = $request->param();
            
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            /* 全部数据 */
            if(empty($param['page']))  $param['page']  = 1;
            if(empty($param['limit'])) $param['limit'] = 10;
            if(empty($param['order'])) $param['order'] = 'create_time acs';
            $search = (empty($param['search'])) ? '' : $param['search'];
            
            $map1  = ['title', 'like', '%'.$search.'%'];
            
            $opt = [
                'page'   =>  (int)$param['page'], 
                'limit'  =>  (int)$param['limit'],
                'order'  =>  (string)$param['order'],
                'where'  =>  [],
                'whereOr'=>  [$map1],
            ];
            
            $page['data'] = Page::ExpandAll(null, $opt);
            
            $data['page'] = $page;
            
            return $this->create($data,$code,$msg);
        }
        
        return View::engine('php')->fetch('/page/manage-page');
    }

    /** 
     * @name 文章分类
     */
    public function ManageArticleSort(Request $request)
    {
        if($request->isPost()){
            
            // 获取请求参数
            $param = $request->param();
            
            /* 全部分类数据 */
            if(empty($param['page']))  $param['page']  = 1;
            if(empty($param['limit'])) $param['limit'] = 10;
            if(empty($param['order'])) $param['order'] = 'create_time desc';
            
            $opt = [
                'page'   =>  (int)$param['page'], 
                'limit'  =>  (int)$param['limit'],
                'order'  =>  (string)$param['order'],
                'where'  =>  []
            ];
            
            $data['sort'] = ArticleSort::ExpandAll(null, $opt);
            $data['sort_title'] = '新增分类';
            
            // 查询操作
            if(!empty($param['id'])){
                
                // 修改前查询操作
                $data['sort_title'] = '修改分类';
                $data['edit_sort']  = ArticleSort::find($param['id']);
            }
            
            $code = 200;
            $msg  = 'ok';
            
            return $this->create($data,$code,$msg);
        }
        
        return View::engine('php')->fetch('/page/manage-article-sort');
    }

    /** 
     * @name 管理友链
     */
    public function ManageLinks(Request $request)
    {
        if($request->isPost()){
            
            // 获取请求参数
            $param = $request->param();
            
            if(empty($param['page']))  $param['page']  = 1;
            if(empty($param['limit'])) $param['limit'] = 10;
            if(empty($param['order'])) $param['order'] = 'create_time desc';
            
            $opt = [
                'page'   =>  (int)$param['page'], 
                'limit'  =>  (int)$param['limit'],
                'order'  =>  (string)$param['order'],
                'where'  =>  [],
            ];
            
            // 获取数据
            $links = Links::ExpandAll(null, $opt);
            
            $sort = LinksSort::field(['id','name'])->select();
            
            $data = ['links'=>$links,'sort'=>$sort];
            
            // 查询操作
            if(!empty($param['id'])){
                
                // 数据封装
                $edit = Links::withAttr('sort_id',function ($value,$data){
                    
                    $sort = LinksSort::field(['id','name'])->select();
                    
                    foreach ($sort as $key => $val) ($value == $val['id']) ? $value = ['name'=>$val['name'],'id'=>$val['id']] : $value;
                    
                    return $value;
                    
                })->find($param['id']);
                
                $data['edit'] = $edit;
            }
            
            $code = 200;
            $msg  = 'ok';
            
            return $this->create($data,$code,$msg);
        }
        
        return View::engine('php')->fetch('/page/manage-links');
    }

    /** 
     * @name 管理标签
     */
    public function ManageTag(Request $request)
    {
        if($request->isPost()){
            
            // 获取请求参数
            $param = $request->param();
            
            /* 全部文章数据 */
            if(empty($param['page']))  $param['page']  = 1;
            if(empty($param['limit'])) $param['limit'] = 10;
            if(empty($param['order'])) $param['order'] = 'create_time desc';
            
            $opt = [
                'page'   =>  (int)$param['page'], 
                'limit'  =>  (int)$param['limit'],
                'order'  =>  (string)$param['order'],
                'where'  =>  []
            ];
            
            // 获取数据
            $tag = Tag::ExpandAll(null, $opt);
            
            $data = ['tag'=>$tag];
            
            // 查询操作
            if(!empty($param['id'])) $data['edit'] = Tag::find($param['id']);
            
            $code = 200;
            $msg  = 'ok';
            
            return $this->create($data,$code,$msg);
        }
        return View::engine('php')->fetch('/page/manage-tag');
    }

    /** 
     * @name 友链分组
     */
    public function ManageLinksSort(Request $request)
    {
        if($request->isPost()){
            
            // 获取请求参数
            $param = $request->param();
            
             /* 全部友链数据 */
            if(empty($param['page']))  $param['page']  = 1;
            if(empty($param['limit'])) $param['limit'] = 10;
            if(empty($param['order'])) $param['order'] = 'create_time desc';
            
            $opt = [
                'page'   =>  (int)$param['page'], 
                'limit'  =>  (int)$param['limit'],
                'order'  =>  (string)$param['order'],
                'where'  =>  []
            ];
            
            $sort = LinksSort::ExpandAll(null, $opt);
            
            $data = ['sort'=>$sort];
            
            // 查询操作
            if(!empty($param['id'])) $data['edit'] = LinksSort::find($param['id']);
            
            $code = 200;
            $msg  = 'ok';
            
            return $this->create($data,$code,$msg);
        }
        
        return View::engine('php')->fetch('/page/manage-links-sort');
    }
    
    /** 
     * @name 管理轮播
     */
    public function ManageBanner(Request $request)
    {
        if($request->isPost()){
            
            // 获取请求参数
            $param = $request->param();
            
            /* 全部文章数据 */
            if(empty($param['page']))  $param['page']  = 1;
            if(empty($param['limit'])) $param['limit'] = 10;
            if(empty($param['order'])) $param['order'] = '';
            
            $opt = [
                'page'   =>  (int)$param['page'], 
                'limit'  =>  (int)$param['limit'],
                'order'  =>  (string)$param['order'],
            ];
            
            // 获取数据
            $banner = Banner::ExpandAll(null, $opt);
            
            $data = ['banner'=>$banner];
            
            // 查询操作
            if (!empty($param['id'])) {
                $data['edit'] = Banner::ExpandAll($param['id']);
            }
            
            $code = 200;
            $msg  = 'ok';
            
            return $this->create($data,$code,$msg);
        }
        
        return View::engine('php')->fetch('/page/manage-banner');
    }
    
    /** 
     * @name 评论管理
     */
    public function ManageComments(Request $request)
    {
        if($request->isPost()){
            
            // 获取请求参数
            $param = $request->param();
            
            if(empty($param['page']))  $param['page']  = 1;
            if(empty($param['limit'])) $param['limit'] = 10;
            if(empty($param['order'])) $param['order'] = 'create_time desc';
            $search = (empty($param['search'])) ? '' : $param['search'];
            
            $map1 = ['email'   , 'like', '%'.$search.'%'];
            $map2 = ['content' , 'like', '%'.$search.'%'];
            $map3 = ['nickname', 'like', '%'.$search.'%'];
            $map4 = ['ip'      , 'like', '%'.$search.'%'];
            
            $opt = [
                'page'   =>  (int)$param['page'], 
                'limit'  =>  (int)$param['limit'],
                'order'  =>  (string)$param['order'],
                'where'  =>  [],
                'whereOr'=>  [$map1, $map2, $map3, $map4]
            ];
            
            $comments = Comments::ExpandAll(null, $opt);
            
            // 查询操作
            if (empty($param['article_id'])) $data['edit'] = Comments::find($param['id']);
            else $comments = Comments::ExpandAll((int)$param['article_id'], $opt);
            
            $data['comments'] = $comments;
            
            $code = 200;
            $msg  = 'ok';
            
            return $this->create($data,$code,$msg);
        }
        
        return View::engine('php')->fetch('/page/manage-comments');
    }
    
    /** 
     * @name 配置服务
     */
    public function configure(Request $request)
    {
        if($request->isPost()){
            
            // 获取请求参数
            $param = $request->param();
            
            $code = 200;
            $msg  = 'ok';
            
            $options = Options::where(['keys'=>'email_serve'])->findOrEmpty();
            
            if ($options->isEmpty()) {
                // 如果不存在，则新增该字段
                $options->keys = 'email_serve';
                $options->save();
            }
            
            $email_serve = $options['opt'];
            if(!empty($email_serve->email_cc)) $email_serve->email_cc = str_replace(",","\n", $email_serve->email_cc);
            
            // 邮箱模板数据
            $email_template_1 = Options::where(['keys'=>'email_template_1'])->findOrEmpty();
            $email_template_2 = Options::where(['keys'=>'email_template_2'])->findOrEmpty();
            $email_template_3 = Options::where(['keys'=>'email_template_3'])->findOrEmpty();
            $email_serve->template_1 = $email_template_1->value;
            $email_serve->template_2 = $email_template_2->value;
            $email_serve->template_3 = $email_template_3->value;
            
            $data = ['email_serve'=>$email_serve];
            
            return $this->create($data,$code,$msg);
        }
        
        return View::engine('php')->fetch('/page/configure');
    }
    
    /** 
     * @name 权限规则
     */
    public function AuthRule(Request $request)
    {
        if ($request->isPost()){
            
            $param = $request->param();
            
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            $attri[] = ['id'=>0,'title'=>'无'];
            $pid_0 = AuthRule::where(['pid'=>0])->field(['id','title'])->select();
            foreach ($pid_0 as $val) $attri[] = $val;
            
            $auth_rule = AuthRule::select();
            
            $data['auth_rule'] = $auth_rule;
            $data['attri'] = $attri;
            
            return $this->create($data,$code,$msg);
        }
        return View::engine('php')->fetch('/page/auth-rule');
    }
    
    /** 
     * @name 权限配置
     */
    public function authority()
    {
        return View::engine('php')->fetch('/page/authority');
    }
    
    /** 
     * @name 文件系统
     */
    public function filesystem()
    {
        return View::engine('php')->fetch('/page/filesystem');
    }
    
    /*
     * #name 管理音乐
     */
    public function music(Request $request)
    {
        if ($request->isPost()){
            
            // 获取请求参数
            $param = $request->param();
            
            if(empty($param['page']))  $param['page']  = 1;
            if(empty($param['limit'])) $param['limit'] = 10;
            if(empty($param['order'])) $param['order'] = 'create_time desc';
            $search = (empty($param['search'])) ? '' : $param['search'];
            
            $map1 = ['title', 'like', '%'.$search.'%'];
            $map2 = ['description' , 'like', '%'.$search.'%'];
            
            $opt = [
                'page'   =>  (int)$param['page'], 
                'limit'  =>  (int)$param['limit'],
                'order'  =>  (string)$param['order'],
                'whereOr'=>  [$map1,$map2],
            ];
            
            // 获取数据
            $music = Music::ExpandAll(null, $opt);
            
            $data = ['music'=>$music];
            
            // 查询操作
            if (!empty($param['id'])) {
                $data['edit'] = Music::ExpandAll($param['id'], ['where'=>null]);
            }
            
            $code = 200;
            $msg  = 'ok';
            
            return $this->create($data,$code,$msg);
        }
        return View::engine('php')->fetch('/page/music');
    }
    
    /*
     * #name 站长信息
     */
    public function webmaster(Request $request)
    {
        if ($request->isPost()){
            
            // 获取请求参数
            $param = $request->param();
            
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            $options  = Options::where(['keys'=>'webmaster'])->findOrEmpty();
            
            if ($options->isEmpty()) {
                
                $options = new Options;
                $options->keys = 'webmaster';
                $users   = Users::where(['level'=>'admin'])->field(['id'])->column(['id']);
                if (!empty($users)) $options->opt = json_encode(['users_id'=>$users[0]], JSON_UNESCAPED_UNICODE);
                
                $options->save();
            }
            
            $users = Users::where(['level'=>'admin'])->field(['id','nickname'])->select();
            
            $data  = ['info'=>$options,'users'=>$users];
            
            return $this->create($data,$code,$msg);
        }
    }

    // END
}
