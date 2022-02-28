<?php
// +----------------------------------------------------------------------
// | 页面 查 方法
// +----------------------------------------------------------------------
// | 只返回页面初始数据
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\Request;
use think\facade\{View, Session, Config};
use app\model\mysql\{Tag, Page, Links, Users, Music, Banner, Placard, Options, Article, Comments, LinksSort, ArticleSort};

class Index extends Base
{
    /** 
     * @name 首页
     */
    public function home(Request $request)
    {
        if ($request->isPost()) {
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
            
            return $this->create($data, $msg, $code);
        }
        
        return View::fetch('/pages/home');
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
            if (empty($param['page']))  $param['page']  = 1;
            if (empty($param['limit'])) $param['limit'] = 10;
            if (empty($param['order'])) $param['order'] = 'last_login_time desc,create_time acs';
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
            
            return $this->create($data, $msg, $code);
        }
        
        return View::fetch('/pages/manage-users');
    }

    /** 
     * @name 编辑个人资料
     */
    public function EditProfile()
    {
        return View::fetch('/pages/edit-profile');
    }

    /** 
     * @name 站点设置
     */
    public function system(Request $request)
    {
        if ($request->isPost()) {
            
            $security = Options::where(['keys'=>'config:security'])->findOrEmpty();
            
            // 安全配置初始化
            if ($security->isEmpty()) {
                
                $security->keys  = 'config:security';
                $security->value = '*';
                $security->opt   = json_encode([
                    'token' => ['open'  => 0,'value' => '','status'=> 0],
                    'domain'=> ['status'=> 0]
                ], JSON_UNESCAPED_UNICODE);
                
                $security->save();
            }
            
            $site = Options::where(['keys'=>'site'])->findOrEmpty();
            // 如果不存在，则新增该字段
            if ($site->isEmpty()) {
                
                $site->keys = 'site';
                $site->opt  = json_encode([
                    'title'      => 'INIS API',
                    'keywords'   => 'INIS API,inis博客系统,inis程序,inis系统',
                    'description'=> 'inis · 新一代博客系统！这是市面上为数不多的新一代博客系统，整站封装，全站分离，真正意义上的前后端分离。每一行代码都用心设计，用最少的代码量和最优雅架构设计，实现最完美的系统，让你拥有更极致的体验。复杂的研究留给我们，简单的体验留给用户！',
                    'image'      => '//q.qlogo.cn/g?b=qq&nk=97783391&s=640',
                    'favicon'    => '//q.qlogo.cn/g?b=qq&nk=97783391&s=640',
                    'url'        => '',
                    'copy'       => '备案号'
                ], JSON_UNESCAPED_UNICODE);
                
                $site->save();
            }
            
            $data['site']   = $site['opt'];
            $data['token']  = $security->opt->token;
            $data['domain'] = ['value'=>str_replace(",","\n", $security->value),'status'=>$security->opt->domain];
            
            $code = 200;
            $msg  = 'ok';
            
            return $this->create($data, $msg, $code);
        }
        
        return View::fetch('/pages/system');
    }
    
    /** 
     * @name 撰写文章
     */
    public function WriteArticle(Request $request)
    {
        if ($request->isPost()) {
            
            // 获取请求参数
            $param = $request->param();
            
            $sort  = ArticleSort::field(['id','name'])->select();
            
            $tag   = Tag::field(['id','name'])->select();
            
            $data  = ['sort'=>$sort,'tag'=>$tag];
            
            if(!empty($param['id'])) $data['article'] = Article::ExpandAll($param['id'], ['withoutField'=>null,'where'=>null]);
            
            $code  = 200;
            $msg   = 'ok';
            
            return $this->create($data, $msg, $code);
        }

        return View::fetch('/pages/write-article');
    }

    /** 
     * @name 管理文章
     */
    public function ManageArticle(Request $request)
    {
        if ($request->isPost()) {
            
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
            
            return $this->create($data, $msg, $code);
        }
        
        return View::fetch('/pages/manage-article');
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
            
            return $this->create($data, $msg, $code);
        }
        
        return View::fetch('/pages/write-page');
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
            
            return $this->create($data, $msg, $code);
        }
        
        return View::fetch('/pages/manage-page');
    }

    /** 
     * @name 文章分类
     */
    public function ManageArticleSort(Request $request)
    {
        if ($request->isPost()) {
            
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
            
            return $this->create($data, $msg, $code);
        }
        
        return View::fetch('/pages/manage-article-sort');
    }

    /** 
     * @name 管理友链
     */
    public function ManageLinks(Request $request)
    {
        if ($request->isPost()) {
            
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
            
            return $this->create($data, $msg, $code);
        }
        
        return View::fetch('/pages/manage-links');
    }

    /** 
     * @name 管理标签
     */
    public function ManageTag(Request $request)
    {
        if ($request->isPost()) {
            
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
            if (!empty($param['id'])) $data['edit'] = Tag::find($param['id']);
            
            $code = 200;
            $msg  = 'ok';
            
            return $this->create($data, $msg, $code);
        }
        return View::fetch('/pages/manage-tag');
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
            
            return $this->create($data, $msg, $code);
        }
        
        return View::fetch('/pages/manage-links-sort');
    }
    
    /** 
     * @name 管理轮播
     */
    public function ManageBanner(Request $request)
    {
        if ($request->isPost()) {
            
            $param = $request->param();
            
            if (empty($param['page']))  $param['page']  = 1;
            if (empty($param['limit'])) $param['limit'] = 10;
            if (empty($param['order'])) $param['order'] = '';
            
            $opt = [
                'page'   =>  (int)$param['page'], 
                'limit'  =>  (int)$param['limit'],
                'order'  =>  (string)$param['order'],
            ];
            
            // 获取数据
            $banner  = Banner::ExpandAll(null, $opt);
            $article = Article::where(['is_show'=>1])->order('last_update_time','desc')->field(['id','title','last_update_time'])->select(); 
            
            $data = ['banner'=>$banner,'article'=>$article];
            
            // 查询操作
            if (!empty($param['id'])) {
                $data['edit'] = Banner::ExpandAll($param['id']);
            }
            
            $code = 200;
            $msg  = 'ok';
            
            return $this->create($data, $msg, $code);
        }
        
        return View::fetch('/pages/manage-banner');
    }
    
    /** 
     * @name 评论管理
     */
    public function ManageComments(Request $request)
    {
        if ($request->isPost()) {
            
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
            
            return $this->create($data, $msg, $code);
        }
        
        return View::fetch('/pages/manage-comments');
    }
    
    /** 
     * @name 配置服务
     */
    public function serve(Request $request)
    {
        if ($request->isPost()) {
            
            $data  = [];
            $code  = 200;
            $msg   = 'ok';
            
            // 获取请求参数
            $param = $request->param();
            
            // 邮件服务
            $email_serve = Options::where(['keys'=>'config:email-serve'])->findOrEmpty();
            // 初始化数据
            if ($email_serve->isEmpty()) {
                
                $email_serve->keys  = 'config:email-serve';
                $email_serve->opt   = json_encode([
                    "port"    => "587",
                    "smtp"    => "smtp.qq.com",
                    "email"   => "",
                    "encry"   => "tls",
                    "encoded" => "UTF-8",
                    "email_cc"=> "",
                    "nickname"=> "兔子",
                    "password"=> "",
                    "to_email"=> "",
                    "title"   => "测试邮件服务",
                    "content" => "当您看到这条邮件信息时，表示您的邮件服务配置成功"
                ], JSON_UNESCAPED_UNICODE);
                $email_serve->value = json_encode([
                    'template_1'=>'<div style="width:550px;height:auto;border-radius:5px;margin:0 auto;box-shadow: 0 0 35px 0 rgb(154 161 171 / 15%);position:relative">
                            <div style="background-image:url(//test.inis.cn/api/file/random?id=master);width:550px;height:250px;background-size:cover;background-repeat:no-repeat;border-radius:5px 5px 0px 0px"></div>
                            <div style="background-color:white;line-height:180%;padding:0 15px 12px;width:520px;margin:10px auto;color:#555555;font-size:12px;margin-bottom:0px">
                                    <h2 style="font-size:14px;font-weight:normal;padding:13px 0 10px 8px">您的
                                            <a style="text-decoration:none;color:#ff7272">《{article}》</a>有了新的评论：</h2>
                                    <div style="padding:0 12px 0 12px;margin-top:18px">
                                            <p>
                                                    <strong>{nickname}</strong>给您的评论：</p>
                                            <p style="background-color:#f5f5f5;border:0px solid #DDD;border-radius:5px;padding:10px 15px;margin:18px 0">{text}</p>
                                            <p>详细信息：</p>
                                            <p style="background-color:#f5f5f5;border:0px solid #DDD;border-radius:5px;padding:10px 15px;margin:18px 0">IP：{ip}
                                                    <br/>邮箱：
                                                    <a href=mailto:{email}style="text-decoration:none;color:#ff7272">{email}</a></p>
                                    </div>
                            </div>
                            <a href={admin_url}target=_blank style="text-decoration:none;color:rgb(255,255,255);width:40%;text-align:center;background-color:rgb(255,114,114);height:40px;line-height:40px;box-shadow:3px 3px 3px rgba(0,0,0,0.3);display:block;margin:auto">查看回复的完整內容</a>
                            <div style="color:#8c8c8c;font-size:10px;width:100%;text-align:center;padding-bottom:1px">
                                    <p>©2019-2022 Copyright{site}</p>
                            </div>
                    </div>',
                    'template_2'=>'<div style="width:550px;height:auto;border-radius:5px;margin:0 auto;box-shadow: 0 0 35px 0 rgb(154 161 171 / 15%);relative;padding-bottom:5px">
                            <div style="background-image:url(//test.inis.cn/api/file/random?id=users);width:550px;height:300px;background-size:cover;background-repeat:no-repeat;border-radius:5px 5px 0px 0px"></div>
                            <div style="width:200px;height:40px;background-color:rgb(255,114,114);margin-top:-20px;margin-left:20px;box-shadow:3px 3px 3px rgba(0,0,0,0.3);color:rgb(255,255,255);text-align:center;line-height:40px">亲爱的{nickname}</div>
                            <div style="background-color:white;line-height:180%;padding:0 15px 12px;width:520px;margin:30px auto;color:#555555;font-size:12px;margin-bottom:0px">
                                    <h2 style="font-size:14px;font-weight:normal;padding:13px 0 10px 8px">您在
                                            <a href={site_url}target=_blank style="text-decoration:none;color:#ff7272">《{article}》</a>的评论有了新的回复：</h2>
                                    <div style="padding:0 12px 0 12px;margin-top:18px">
                                            <p>您的评论：</p>
                                            <p style="background-color:#f5f5f5;border:0px solid #DDD;border-radius:5px;padding:10px 15px;margin:18px 0">{text}</p>
                                            <p>
                                                    <strong>{author}</strong>给您的回复：</p>
                                            <p style="background-color:#f5f5f5;border:0px solid #DDD;border-radius:5px;padding:10px 15px;margin:18px 0">{content}</p></div>
                            </div>
                            <div style="word-wrap:break-word;margin-top:-30px;color:#8c8c8c;font-size:10px;width:100%;text-align:center;">
                                    <p style=padding:20px>萤火虫消失之后，那光的轨迹仍久久地印在我的脑际。那微弱浅淡的光点，仿佛迷失方向的魂灵，在漆黑厚重的夜幕中彷徨。——《挪威的森林》村上春树</p></div>
                            <a href={site_url}target=_blank style="text-decoration:none; color:#FFF;width: 40%;text-align: center;background-color:#ff7272;height: 40px;line-height: 35px;box-shadow: 3px 3px 3px rgba(0, 0, 0, 0.30);margin: -10px auto;display: block;">查看回复的完整內容</a>
                            <div style="color:#8c8c8c;font-size:10px;width:100%;text-align:center;">
                                    <p style=margin-top:30px>本邮件为系统自动发送，请勿直接回复~</p></div>
                            <div style="color:#8c8c8c;font-size:10px;width:100%;text-align:center;">
                                    <p>©2019-2022 Copyright{site}</p>
                            </div>
                    </div>',
                    'template_3'=>'<div style="margin: 0 auto;width: 800px;box-shadow: 0 0 35px 0 rgb(154 161 171 / 15%);">
                        <table border="0" cellspacing="0" cellpadding="0" width="800" bgcolor="#0092ff" height="66">
                                <tbody>
                                        <tr>
                                                <td width="50"></td>
                                                <td width="750">
                                                        <img style="WIDTH: 135px" src="{domain}/admin/images/logo.png"></td>
                                        </tr>
                                </tbody>
                        </table>
                        <table style="FONT-FAMILY: 黑体; FONT-SIZE: 10pt" border="0" cellspacing="0" cellpadding="50" width="800">
                                <tbody>
                                        <tr>
                                                <td width="800">
                                                        <div>
                                                                <div style="FONT-SIZE: 11pt">{email}，您好！</div>
                                                                <br>
                                                                <div style="FONT-SIZE: 11pt">以下是您用于验证身份的验证码，请在
                                                                        <span style="color:red">{valid_time}内</span>输入并完成验证。如非本人操作，请忽略此邮件。</div>
                                                                <br>
                                                                <br>
                                                                <div>
                                                                        <span style="COLOR: #0094ff; FONT-SIZE: 40pt">{code}</span></div>
                                                                <br>
                                                                <br>
                                                                <hr style="BORDER-BOTTOM: #808080 0px dashed; BORDER-LEFT: #808080 0px dashed; HEIGHT: 1px; BORDER-TOP: #808080 1px dashed; BORDER-RIGHT: #808080 0px dashed">
                                                                <br>
                                                                <div style="COLOR: #808080">此邮件由系统自动发出，系统不接受回信，因此请勿直接回复。
                                                                        <br>安全使用您的帐号注意事项：
                                                                        <br>1、请不要在其他网站上使用相同的邮箱和密码进行注册。
                                                                        <br>2、请不要告知任何人您的帐号密码信息。
                                                                        <br>
                                                                        <br>如果您错误的收到本电子邮件，请您忽略上述内容。</div>
                                                                <br>
                                                                <hr style="BORDER-BOTTOM: #808080 0px dashed; BORDER-LEFT: #808080 0px dashed; HEIGHT: 1px; BORDER-TOP: #808080 1px dashed; BORDER-RIGHT: #808080 0px dashed">
                                                                <div>
                                                                        <br></div>
                                                                <div style="TEXT-ALIGN: right; FONT-SIZE: 11pt">{site}</div>
                                                                <div style="TEXT-ALIGN: right; FONT-SIZE: 11pt">{time}</div></div>
                                                </td>
                                        </tr>
                                </tbody>
                        </table>
                </div>'
                ], JSON_UNESCAPED_UNICODE);
                
                $email_serve->save();
            }
            
            unset($email_serve->keys);
            $email_serve->opt->email_cc = (!empty($email_serve->opt->email_cc)) ? str_replace(",","\n", $email_serve->opt->email_cc) : [];
            $email_serve->value = (!empty($email_serve->value)) ? json_decode($email_serve->value) : [];
            
            // 系统配置
            $system_config = Options::where(['keys'=>'config:system'])->findOrEmpty();
            // 初始化数据
            if ($system_config->isEmpty()) {
                
                $system_config = new Options;
                $system_config->keys = 'config:system';
                $system_config->opt  = json_encode([
                    'article'  => [             // 文章配置
                        'comments'  => [        // 文章下评论配置
                            'show'  => true,    // 显示评论
                            'allow' => true,    // 允许评论
                        ]
                    ],
                    'optimize' => [
                        'cdn'  => $this->config['official']['cdn'],
                        'image'=> [
                            'open' =>true,
                            'ratio'=>0.5
                        ]
                    ]
                ], JSON_UNESCAPED_UNICODE);
                
                $system_config->save();
            }
            
            $data = ['email_serve'=>$email_serve,'system_config'=>$system_config];
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /** 
     * @name 文件系统
     */
    public function filesystem()
    {
        return View::fetch('/pages/filesystem');
    }
    
    /*
     * #name 管理音乐
     */
    public function ManageMusic(Request $request)
    {
        if ($request->isPost()) {
            
            // 获取请求参数
            $param = $request->param();
            
            if (empty($param['page']))  $param['page']  = 1;
            if (empty($param['limit'])) $param['limit'] = 10;
            if (empty($param['order'])) $param['order'] = 'create_time desc';
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
            
            return $this->create($data, $msg, $code);
        }
        return View::fetch('/pages/manage-music');
    }
    
    /*
     * #name 站长信息
     */
    public function webmaster(Request $request)
    {
        if ($request->isPost()) {
            
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
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /*
     * #name 管理公告
     */
    public function ManagePlacard(Request $request)
    {
        if ($request->isPost()) {
            
            // 获取请求参数
            $param = $request->param();
            
            if (empty($param['page']))  $param['page']  = 1;
            if (empty($param['limit'])) $param['limit'] = 10;
            if (empty($param['order'])) $param['order'] = 'create_time desc';
            $search = (empty($param['search'])) ? '' : $param['search'];
            
            $map1 = ['title'  , 'like', '%'.$search.'%'];
            $map2 = ['content', 'like', '%'.$search.'%'];
            
            $opt = [
                'page'   =>  (int)$param['page'], 
                'limit'  =>  (int)$param['limit'],
                'order'  =>  (string)$param['order'],
                'whereOr'=>  [$map1,$map2],
            ];
            
            // 获取数据
            $placard = Placard::ExpandAll(null, $opt);
            $article = Article::where(['is_show'=>1])->order('last_update_time','desc')->field(['id','title','last_update_time'])->select(); 
            
            $data = ['placard'=>$placard,'sort'=>$this->config['placard'],'article'=>$article];
            
            // 查询操作
            if (!empty($param['id'])) {
                $data['edit'] = Placard::ExpandAll($param['id'], ['where'=>null]);
            }
            
            $code = 200;
            $msg  = 'ok';
            
            return $this->create($data, $msg, $code);
        }
        
        return View::fetch('/pages/manage-placard');
    }
    
    /*
     * #name 小程序
     */
    public function applets(Request $request)
    {
        if ($request->isPost()) {
            
            $data  = [];
            $code  = 200;
            $msg   = 'ok';
            
            // 获取请求参数
            $param = $request->param();
            
            $options  = Options::where(['keys'=>'config:applets'])->findOrEmpty();
            
            $config   = [
                'show'  =>  ['comments'=>true]
            ];
            
            $default_config   = [
                'qq'    => $config,
                'wechat'=> $config,
                'other' => $config
            ];
            
            if ($options->isEmpty()) {
                
                $options = new Options;
                $options->keys = 'config:applets';
                $options->opt = json_encode($default_config, JSON_UNESCAPED_UNICODE);
                
                $options->save();
            }
            
            $data = $options;
            
            return $this->create($data, $msg, $code);
        }
    }
    
    /*
     * #name API商城
     */
    public function apiStore(Request $request)
    {
        if ($request->isPost()) {
            
            $data  = [];
            $code  = 200;
            $msg   = 'ok';
            
            // 获取请求参数
            $param = $request->param();
            
            $param['page']  = !empty($param['page'])  ? (int)$param['page']  : 1;
            $param['limit'] = !empty($param['limit']) ? (int)$param['limit'] : 5;
            
            // 内置API信息
            $internal       = Config::get('apis');
            $count          = count($internal);
            $data['page']   = ceil($count / $param['limit']);
            $data['count']  = $count;
            
            // 防止分页请求超出页码
            if ($param['page'] <= 0) $param['page'] = 1;
            if ($param['page'] > $data['page']) $param['page'] = $data['page'];
            
            // 起始
            $start = ($param['page'] * $param['limit']) - $param['limit'];
            $data['data'] = array_slice($internal, $start, $param['limit']);
            
            return $this->create($data, $msg, $code);
        }
        
        return View::fetch('/pages/api-store');
    }
    
    /*
     * #name 已经安装的API
     */
    public function installedApi(Request $request)
    {
        if ($request->isPost()) {
            
            $data  = [];
            $code  = 200;
            $msg   = 'ok';
            
            // 获取请求参数
            $param = $request->param();
            
            $item  = Options::where(['keys'=>'config:plugin-api'])->findOrEmpty();
            $apis  = (!$item->isEmpty()) ? json_decode(json_encode($item->opt), true) : [];
            
            $param['page']  = !empty($param['page'])  ? (int)$param['page']  : 1;
            $param['limit'] = !empty($param['limit']) ? (int)$param['limit'] : 5;
            
            $count          = count($apis);
            $data['page']   = ceil($count / $param['limit']);
            $data['count']  = $count;
            
            // 防止分页请求超出页码
            if ($param['page'] <= 0) $param['page'] = 1;
            if ($param['page'] > $data['page']) $param['page'] = $data['page'];
            
            // 起始
            $start = ($param['page'] * $param['limit']) - $param['limit'];
            $data['data'] = array_slice($apis, $start, $param['limit']);
            $data['installed']['id'] = array_column($apis, 'id');
            $data['installed']['version'] = array_map(function ($apis){
                return ['id'=>$apis['id'],'version'=>$apis['version']];
            }, $apis);
            
            return $this->create($data, $msg, $code);
        }
    }
    
    // 冗余处理 - 获取废弃本地图片
    public function trash()
    {
        $exist = [];
        // 获取系统内存在的资源
        $exist = array_merge($exist, $this->File->listDirInfo('storage/article',true));
        $exist = array_merge($exist, $this->File->listDirInfo('storage/banner',true));
        $exist = array_merge($exist, $this->File->listDirInfo('storage/users',true));
        
        // 获取全部已用资源 - 包括外链资源
        $used  = [];
        $useds = [];
        
        $article  = Article::field(['img_src','content'])->select();
        $url_rule = '/!\\[.*\\]\\((.+)\\)/';
        foreach ($article as $key => $val) {
            preg_match_all($url_rule, $val['content'], $array);
            $useds = array_merge($useds, [str_replace(['https://','http://','//',$this->helper->domain() . '/'], '', $val['img_src'])]);
            if (!empty($array[1])) foreach ($array[1] as $k => $v) $useds = array_merge($useds, [str_replace(['https://','http://','//', str_replace(['https:','http:','//'], '', $this->helper->domain()) . '/'], '', $v)]);
        }
        
        $banner   = Banner::field(['img'])->select();
        foreach ($banner as $key => $val) $useds = array_merge($useds, [str_replace(['https://','http://','//', str_replace(['https:','http:','//'], '', $this->helper->domain()) . '/'], '', $val['img'])]);
        
        $users = Users::field(['head_img'])->select();
        foreach ($users as $key => $val) $useds = array_merge($useds, [str_replace(['https://','http://','//', str_replace(['https:','http:','//'], '', $this->helper->domain()) . '/'], '', $val['head_img'])]);
        
        $site_image = Options::where(['keys'=>'site'])->find();
        $useds[] = str_replace(['https:','http:','//', str_replace(['https:','http:','//'], '', $this->helper->domain()) . '/'], '', $site_image->opt->image);
        
        // 统计出本地已用资源
        foreach ($useds as $key => $val) if (substr($val, 0, 7) == 'storage') $used[] = $val;
        
        // 求差，计算出未使用的资源
        $diff = array_diff($exist, $used);
        
        return array_merge($diff);
    }
    
    // 冗余处理
    public function redundancy(Request $request)
    {
        if ($request->isPost()) 
        {
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            $param = $request->param();
            
            $clear = !empty($param['clear']) ? (($param['clear'] == 'true' or $param['clear'] === true) ? true : false) : false;
            
            // 清理冗余资源
            if ($clear) foreach ($this->trash() as $key => $val) $this->File->unlinkFile($val);
            
            $data['image'] = $this->trash();
            
            return $this->create($data, $msg, $code);
        }
    }

    // END
}
