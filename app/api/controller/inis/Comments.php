<?php
declare (strict_types = 1);

namespace app\api\controller\inis;

use think\Request;
use think\facade\{Cache};
use inis\utils\{markdown};
use app\model\mysql\{Users, Options, Article, Comments as CommentsModel};

/**
 * Class Comments
 * @package app\api\controller
 */
class Comments extends Base
{
    /**
     * 显示资源列表
     *
     * \think\Request  $request
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
        $method = ['one','all','comments','group','type','sql'];
        
        if (!empty($param['article_id'])) $mode = 'comments';
        else if (!empty($param['id'])) $mode = 'one';
        else $mode = (empty($param['mode'])) ? 'all' : $param['mode'];
        
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
        // 获取数据
        $param  = $request->param();
        // 获取头部信息
        $header = $request->header();
        
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        $mode = (!empty($param['mode'])) ? $param['mode'] : null;
        
        // 登录时的 Token
        $login_token  = !empty($header['login-token']) ? $header['login-token'] : (!empty($param['login-token']) ? $param['login-token'] : []);
        
        $param['agent'] = $header['user-agent'];
        $param['ip']    = $this->helper->GetClientIP();
        
        // 允许用户提交并存储的字段
        $obtain = ['pid','content','nickname','email','url','ip','type','agent','users_id','article_id','opt'];
        
        // 判断是否登录后评论
        if (!empty($login_token)) {
            
            // 存在的方法
            $method = ['move','edit','remove'];
            
            // 动态方法且方法存在
            if (in_array($mode, $method)) {
                
                $result = $this->$mode($param);
                // 动态返回结果
                if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
                
            } else {
                
                $comment = new CommentsModel;
                
                // 登录有效
                if ($this->user['code'] == 200) {
                    
                    // 判断字段是否允许存储，防提权
                    foreach ($param as $key => $val) if (in_array($key, $obtain)) {
                        $comment->$key = $val;
                    }
                    
                    $comment->users_id = $this->user['data']['id'];
                    $comment->email    = $this->user['data']['email'];
                    $comment->nickname = $this->user['data']['nickname'];
                    $comment->url      = $this->user['data']['address_url'];
                    
                    // 不转换中文编码
                    $comment->opt = json_encode($comment->opt, JSON_UNESCAPED_UNICODE);
                    $comment->save();
                    
                    $code    = 200;
                    $msg     = 'ok';
                    
                    $param['email']    = $this->user['data']['email'];
                    $param['nickname'] = $this->user['data']['nickname'];
                    $param['url']      = $this->user['data']['address_url'];
                    
                    // 评论通知
                    self::notice($param);
                    
                } else $msg = $this->user['msg'];    // 登录无效
            }
            
        } else {    // 未登录提交的评论
            
            if (empty($mode)) {
                
                // 判断PID是否存在 - 非必传字段
                if (empty($param['pid'])) $param['pid'] = 0;
                // 判断选传字段是否存在 - 非必传字段
                if (empty($param['url'])) $param['url'] = null;
                
                // 邮箱验证规则
                $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/";
                
                if (empty($param['content']) or empty($param['nickname']) or empty($param['email'])) {
                    
                    $msg  = '必传字段 { content、nickname、email } 不得为空！';
                    
                } else if (!is_numeric($param['pid'])){
                    
                    $msg  = 'pid不是合法的ID，必须是数字！';
                    
                } else if (!preg_match($pattern, $param['email'])){
                    
                    $msg  = '邮箱格式不合法，评论失败！';
                    
                } else {
                    
                    $comment = new CommentsModel;
                    
                    // 自动插入users_id
                    if (!empty($param['email']) or !empty($param['nickname'])) {
                        
                        $user = Users::where(['email'=>$param['email']])->field(['id'])->findOrEmpty();
                        
                        if (!$user->isEmpty()) $param['users_id'] = $user->id;
                        else {
                            
                            $map1 = ['address_url','=',$param['url']];
                            $map2 = ['nickname', 'like', '%'.$param['nickname'].'%'];
                            
                            $user = Users::where([$map1,$map2])->field(['id'])->findOrEmpty();
                            if (!$user->isEmpty()) $param['users_id'] = $user->id;
                        }
                    }
                    
                    // 判断字段是否允许存储，防提权
                    foreach ($param as $key => $val) if (in_array($key, $obtain)) {
                        $comment->$key = $val;
                    }
                    
                    // 不转换中文编码
                    $comment->opt = json_encode($comment->opt, JSON_UNESCAPED_UNICODE);
                    $comment->save();
                    
                    // 评论通知
                    self::notice($param);
                    
                    $data = $comment;
                    
                    $code = 200;
                }
                
            } else if ($mode == 'like') {   // 点赞
                
                if (!empty($param['id']) and is_numeric($param['id'])) {
                    
                    $comment = CommentsModel::find((int)$param['id']);
                    $comment->opt->great++;
                    // 不转换中文编码
                    $comment->opt = json_encode($comment->opt, JSON_UNESCAPED_UNICODE);
                    $comment->save();
                    
                    $code = 200;
                }
            }
        }
        
        // 清除缓存
        Cache::tag('comments')->clear();
        
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
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        // 获取请求参数
        $param = $request->param();
        
        // 存在的方法
        $method = ['sql','type'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->create($data, $msg, $code);
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
    
    // SQL接口
    public function sql($param)
    {
        $where   = (empty($param['where']))   ? '' : $param['where'];
        $whereOr = (empty($param['whereOr'])) ? '' : $param['whereOr'];
        $page    = (!empty($param['page']))   ? $param['page']  : 1;
        $limit   = (!empty($param['limit']))  ? $param['limit'] : 5;
        $order   = (!empty($param['order']))  ? $param['order'] : 'create_time desc';
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        $data = [];
        $code = 200;
        $msg  = 'ok';
        
        $opt  = [
            'page' => $page,
            'limit'=> $limit,
            'order'=> $order,
            'where'=> [],
            'whereOr'=> [],
        ];
        
        // 设置缓存名称
        $cache_name = 'comments/sql?page='.$page.'&limit='.$limit.'&order='.$order.'&where='.$where.'&whereOr='.$whereOr;
        
        if (!empty($where)) {
            
            if (strstr($where, ';')) {      // 以 ; 号隔开参数
                
                $where = array_filter(explode(';', $where));
                
                foreach ($where as $val) {
                    
                    if (strstr($val, ',')) {
                        $item = explode(',',$val);
                        array_push($opt['where'],[$item[0],$item[1],$item[2]]);
                    } else {
                        $item = explode('=',$val);
                        array_push($opt['where'],[$item[0],'=',$item[1]]);
                    }
                }
                
            } else $opt['where'] = $where;  // 原生写法，以 and 隔开参数
        }
        
        if (!empty($whereOr)) {
            $whereOr = array_filter(explode(';', $whereOr));
            foreach ($whereOr as $val) {
                $item = explode(',',$val);
                $opt['whereOr'][] = [$item[0],$item[1],$item[2]];
            }
        }
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
            $data = CommentsModel::ExpandAll(null, $opt);
            Cache::tag(['comments',$cache_name])->set($cache_name, json_encode($data));
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 发起评论通知推送
    public function notice(array $param = [])
    {
        // 获取邮箱服务配置信息
        $options  = Options::where(['keys'=>'config:email-serve'])->findOrEmpty();
        // 获取用户邮件模板
        $templates= (!empty($options->value)) ? json_decode($options->value) : [];
        $template = (!empty($templates)) ? $templates->template_2 : '';
        // 获取站点名称
        $site     = Options::field(['opt'])->where(['keys'=>'site'])->find()['opt']->title;
        // 获取抄送邮箱
        $email    = array_unique(array_filter(explode(',',$options['opt']->email_cc)));
        // 获取站点地址
        $admin_url= $this->tool->domain();
        
        $tags     = ['{ip}','{nickname}','{text}','{email}','{site}','{admin_url}','{content}'];
        $replace  = [$param['ip'],$param['nickname'],$param['content'],$param['email'],$site,$admin_url,$param['content']];
        
        if (!empty($param['article_id'])) {
            
            $article  = '文章不存在！';
            $article  = Article::field(['title','users_id'])->findOrEmpty($param['article_id']);
            if (!empty($article)) {
                $users_id = $article['users_id'];
                $article  = $article['title'];
                $author   = Users::field(['nickname'])->findOrEmpty($users_id);
                $author   = (!empty($author)) ? $author['nickname'] : '已删除的用户';
            }
            array_push($tags, '{article}', '{author}');
            array_push($replace, $article, $author);
        } else {
            array_push($tags, '{article}', '{author}');
            array_push($replace, $site, $site);
        }
        
        // 站长评论
        if (in_array($param['email'], $email)) {
            
            // 判断是否站长回复
            if (!empty($param['pid']) and (int)$param['pid'] != 0) {
                // 获取被回复的用户
                $email_cc = $email;
                $email    = CommentsModel::field(['email','content'])->findOrEmpty($param['pid']);
                if (!$email->isEmpty()) {
                    $replace[2] = $email['content'];
                    // 发送评论信息到邮箱
                    if (!empty($email['email'] and !in_array($email['email'], $email_cc))) {
                        // 模板变量替换
                        $template = str_replace($tags,$replace,$template);
                        // 发送评论信息到邮箱
                        $this->tool->sendMail([
                            'email'  =>$email['email'],
                            'title'  =>$site.'评论通知',
                            'content'=>$template
                        ]);
                    }
                }
            }
        }
        // 非站长评论
        else {
            // 获取站长邮件模板
            $template = (!empty($templates)) ? $templates->template_1 : '';
            // 模板变量替换
            $template = str_replace($tags,$replace,$template);
            // 发送评论信息到邮箱
            $this->tool->sendMail([
                'email'  =>$email,
                'title'  =>$site.'评论通知',
                'content'=>$template
            ]);
            
            // 用户间评论
            if (!empty($param['pid']) and (int)$param['pid'] != 0) {
                $email_cc = $email;
                $email    = CommentsModel::field(['email','content'])->findOrEmpty($param['pid']);
                if (!$email->isEmpty()) {
                    $replace[2] = $email['content'];
                    // 发送评论信息到邮箱
                    if (!empty($email['email'] and !in_array($email['email'], $email_cc))) {
                        // 获取用户邮件模板
                        $template = (!empty($templates)) ? $templates->template_2 : '';
                        foreach ($tags as $key => $val) if ($val == '{author}') $replace[$key] = $param['nickname'];
                        // 模板变量替换
                        $template = str_replace($tags,$replace,$template);
                        // 发送评论信息到邮箱
                        $this->tool->sendMail([
                            'email'  =>$email['email'],
                            'title'  =>$site.'评论通知',
                            'content'=>$template
                        ]);
                    }
                }
            }
        }
    }
    
    // typecho 迁移至 inis
    public function move(array $param = [])
    {
        $result = ['data'=>[],'code'=>403,'msg'=>'ok'];
        
        if (empty($param['login-token'])) $result['msg'] = 'login-token 未提交';
        else {
            
            // 允许用户提交并存储的字段
            $obtain = ['id','url','content','email','agent','ip','article_id','pid','nickname','create_time'];
            
            $comment = new CommentsModel;
            
            // 判断字段是否允许存储，防提权
            foreach ($param as $key => $val) if (in_array($key, $obtain)) $comment->$key = $val;
            
            if ($this->user['data']->level == 'admin') {
                $comment->save();
                $result['code'] = 200;
            } else $result['msg'] = '无权限';
        }
        
        return $result;
    }
    
    // 编辑评论
    public function edit($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        if (empty($param['id'])) $msg = 'ID不能为空';
        else {
            
            $comment = CommentsModel::findOrEmpty((int)$param['id']);
            
            // 允许用户提交并存储的字段
            $obtain  = ['content','nickname','email','url','opt','expand'];
            
            // 判断字段是否允许存储，防提权
            if (!$comment->isEmpty()) foreach ($param as $key => $val) {
                if (in_array($key, $obtain)) $comment->$key = $val;
            } else {
                $code = 204;
                $msg  = '无数据';
            }
            
            if (in_array($this->user['data']->level, ['admin'])) {
                
                $code = 200;
                // 不转换中文编码
                $comment->opt = json_encode($comment->opt, JSON_UNESCAPED_UNICODE);
                $comment->save();
                
            } else {
                $code = 403;
                $msg  = '无权限';
            }
        }
        
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
                CommentsModel::destroy($id);
                
            } else {
                
                $code = 403;
                $msg  = '无权限';
            }
        }
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }
    
    // 文章下的评论
    public function comments($param)
    {
        $data  = [];
        
        $page  = (!empty($param['page']))  ? $param['page']  : 1;
        $limit = (!empty($param['limit'])) ? $param['limit'] : 5;
        $order = (!empty($param['order'])) ? $param['order'] : 'create_time desc';
        $tree  = (empty($param['tree'])  or $param['tree']  == 'true') ? true : false;
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        $opt = [
            'tree'   =>  $tree,
            'page'   =>  $page, 
            'limit'  =>  $limit,
            'order'  =>  $order,
            'where'  =>  []
        ];
        
        // 设置缓存名称
        $cache_name = 'comments?article_id='.$param['article_id'].'&page='.$page.'&limit='.$limit.'&order='.$order.'&tree='.$tree;
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
            unset($opt['where']);
            // 获取数据库数据
            $data = Article::comments((int)$param['article_id'], $opt);
            Cache::tag(['comments',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取单条评论
    public function one($param)
    {
        $tree  = (empty($param['tree'])  or $param['tree']  == 'true') ? true : false;
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        $data = [];
            
        // 设置缓存名称
        $cache_name = 'comments?id='.$param['id'].'&tree='.$tree;
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
            // 获取数据库数据
            $data = CommentsModel::ExpandAll((int)$param['id']);
            $data['son'] = CommentsModel::FindSon((int)$param['id'], $tree);
            
            if (!$tree) $data['son'] = $this->helper->BubbSort($data['son'], 'create_time');
            
            Cache::tag(['comments',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取全部评论
    public function all($param)
    {
        $data = [];
        
        $page  = (!empty($param['page']))  ? $param['page']  : 1;
        $limit = (!empty($param['limit'])) ? $param['limit'] : 5;
        $order = (!empty($param['order'])) ? $param['order'] : 'create_time desc';
        $type  = (empty($param['type'])) ? null : $param['type'];
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        $opt = [
            'page'   =>  $page, 
            'limit'  =>  $limit,
            'order'  =>  $order,
            'where'  =>  []
        ];
        
        if (!empty($type)) $opt['where'] = ['pid'=>0,'type'=>$type];
        
        // 设置缓存名称
        $cache_name = 'comments?page='.$page.'&limit='.$limit.'&order='.$order.'&type='.$type;
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
            
            // 获取全部评论
            $data = CommentsModel::ExpandAll(null, $opt);
            
            foreach ($data['data'] as $key => $val) {
                // 解析自定义标签
                $val->content = markdown::parse($val->content);
            }
            
            Cache::tag(['comments'])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取聚合评论
    public function group($param)
    {
        $data  = [];
        
        $page  = (!empty($param['page']))  ? $param['page']  : 1;
        $limit = (!empty($param['limit'])) ? $param['limit'] : 5;
        $order = (!empty($param['order'])) ? $param['order'] : 'create_time desc';
        $mode  = (empty($param['mode'])) ? false : $param['mode'];
        $tree  = (empty($param['tree'])  or $param['tree']  == 'true') ? true : false;
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        $opt = [
            'tree'   =>  $tree,
            'page'   =>  $page, 
            'limit'  =>  $limit,
            'order'  =>  $order,
            'where'  =>  []
        ];
        
        // 设置缓存名称
        $cache_name = 'comments?page='.$page.'&limit='.$limit.'&order='.$order.'&mode='.$mode.'&tree='.$tree;
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
        
            $opt['order'] = [];
            $opt['group'] = ['email'];
            $opt['where'] = [];
            $opt['field'] = ['id','email','nickname','url','expand','agent','article_id'];
            $opt['limit'] = (!empty($param['limit'])) ? (int)$param['limit'] : 20;
            $order = (empty($param['order'])) ? 'desc' : $param['order'];
            
            $data = CommentsModel::ExpandAll(null, $opt);
            foreach ($data['data'] as $key => $val) {
                $val['count'] = CommentsModel::where(['email'=>$val['email']])->count();
            }
            $data['data'] = $this->helper->BubbSort($data['data'], 'count', $order);
            
            Cache::tag(['comments'])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取属性评论
    public function type($param)
    {
        $data  = [];
        
        $page  = (!empty($param['page']))  ? $param['page']  : 1;
        $limit = (!empty($param['limit'])) ? $param['limit'] : 5;
        $order = (!empty($param['order'])) ? $param['order'] : 'create_time desc';
        $tree  = (empty($param['tree'])  or $param['tree']  == 'true') ? true : false;
        $type  = (empty($param['type'])) ? null : $param['type'];
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        $opt = [
            'tree'   =>  $tree,
            'page'   =>  $page, 
            'limit'  =>  $limit,
            'order'  =>  $order,
            'where'  =>  []
        ];
        
        if (!empty($type)) $opt['where'] = ['pid'=>0,'type'=>$type];
        
        // 设置缓存名称
        $cache_name = 'comments?page='.$page.'&limit='.$limit.'&order='.$order.'&type='.$type.'&tree='.$tree;
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
            
            $opt['where'] = ['type'=>$type,'pid'=>0];
            $opt['order'] = 'create_time desc';
            $order = (empty($param['order'])) ? 'desc' : $param['order'];
            
            $data = CommentsModel::ExpandAll(null, $opt);
            
            foreach ($data['data'] as $key => $val) {
                // 解析自定义标签
                $val->content = markdown::parse($val->content);
            }
            
            // 找孙子
            foreach ($data['data'] as $key => $val) $data['data'][$key]['son'] = CommentsModel::FindSon($val['id'], $tree);
            // 冒泡排序
            if (!$tree) foreach ($data['data'] as $key => $val) $data['data'][$key]['son'] = $this->helper->BubbSort($val['son'], 'create_time', $order);
            
            Cache::tag(['comments'])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}
