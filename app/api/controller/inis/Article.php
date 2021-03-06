<?php
declare (strict_types = 1);

namespace app\api\controller\inis;

use Parsedown;
use think\Request;
use inis\utils\{markdown};
use think\facade\{Cache, Validate, Config};
// use app\model\sqlite\{Visit as iVisit};
use app\model\mysql\{Tag, Visit, Article as ArticleModel, Comments};

class Article extends Base
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
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['one','all'];
        
        $mode   = (empty($param['id'])) ? 'all' : 'one';
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 记录访问量
        // iVisit::record([
        //     'api' =>[['id'=>'article-'.$mode,'visit'=>1]],
        //     'total'=>[['id'=>'all','visit'=>1]]
        // ]);
        
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
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['saves','remove','move'];
        
        $mode   = (empty($param['mode'])) ? 'saves' : $param['mode'];
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 清除缓存
        Cache::tag('article')->clear();
        
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
        $method = ['sql','next'];
        
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
    
    // typecho 迁移至 inis
    public function move($param)
    {
        $result = ['data'=>[],'code'=>403,'msg'=>'ok'];
        
        if (empty($param['login-token'])) $result['msg'] = 'login-token 未提交';
        else {
            
            // 允许用户提交并存储的字段
            $obtain = ['id','title','content','description','img_src','font_count','sort_id','tag_id','is_show','is_top','views','create_time','update_time'];
            
            $article = new ArticleModel;
            
            // 判断字段是否允许存储，防提权
            foreach ($param as $key => $val) if (in_array($key, $obtain)) {
                // 分类ID转字符串存储
                if ($key == 'sort_id') {
                    
                    if(is_array($val))        $article->$key = '|' . implode('|', array_filter($val)) . '|';
                    else if (is_string($val)) $article->$key = '|' . implode('|', array_filter(explode(',', $val))) . '|';
                    
                } else $article->$key = $val;
            }
            
            $article->users_id         = $this->user['data']->id;
            $article->last_update_time = time();
            
            if ($this->user['data']->level == 'admin') {
                $article->save();
                $result['code'] = 200;
            } else $result['msg'] = '无权限';
        }
        
        return $result;
    }
    
    // 记录浏览量
    public function visit($param)
    {
        $article = ArticleModel::find($param['id']);
        // 浏览量自增
        $article->views++;
        $article->save();
        
        $today = strtotime(date('Y-m-d',time()));
        
        $visit = Visit::where(['create_time'=>$today])->findOrEmpty();
        if ($visit->isEmpty()) {
            $visit = new Visit;
            $visit->opt = json_encode(['article'=>[],'page'=>[]]);
            $visit->create_time = $today;
        }
        
        if (!empty($visit->opt)) $opt = json_decode($visit->opt);
        
        if (!isset($opt->article)) $opt->article = [['id'=>(int)$param['id'],'visit'=>1]];
        else {
            
            if ($this->helper->InArray(['id',(int)$param['id']], $opt->article)) foreach ($opt->article as $key => $val) {
                if ($val->id == (int)$param['id']) $val->visit += 1;
            } else $opt->article[] = ['id'=>(int)$param['id'],'visit'=>1];
        }
        
        $visit->opt = json_encode($opt);
        
        $visit->save();
    }
    
    // 获取一篇文章
    public function one($param)
    {
        $data = [];
        $code = 400;
        $msg  = '无数据';
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        // 设置缓存名称
        $cache_name = 'article?id='.$param['id'];
        // 校验数据是否存在
        $check = ArticleModel::find($param['id']);
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = Cache::get($cache_name);
        else {
            // 获取数据库数据
            if (empty($check)) $data = [];
            else $data = ArticleModel::ExpandAll((int)$param['id'], ['withoutField'=>null]);
            $data = json_encode($data);
            Cache::tag(['article',$cache_name])->set($cache_name, $data);
        }
        
        $data = json_decode($data, true);
        
        // 数据存在，文章数据转换
        if (!empty($check)) {
            if (empty($param['mode'])) $param['mode'] = 'html';
            // setBreaksEnabled(true) 自动换行 setMarkupEscaped(true) 转义HTML setUrlsLinked(false) 防止自动链接
            if ($param['mode'] == 'html' || $param['mode'] == 'htm') $data['content'] = Parsedown::instance()->setUrlsLinked(false)->text($data['content']);
            // 解析自定义标签
            $data['content'] = markdown::parse($data['content']);
            // 转化时间戳
            $data['last_update_time'] = date('Y-m-d H:i:s', (int)$data['last_update_time']);
        }
        
        if (!Validate::isInteger($param['id'])) $msg  = 'ID参数不合法！';
        else if (empty($data)) $code = 204;
        else {
            
            // 权限判断，防止不合理获取数据
            if (!empty($data['opt'])) {
                
                if ($data['opt']['auth'] == 'password') {
                    
                    // 获取修改前的OPT字段数据
                    $oldOpt  = ArticleModel::field(['opt'])->find($data['id']);
                    $oldOpt  = json_decode($oldOpt->getData('opt'), true);
                    $password= !empty($oldOpt['password']) ? $oldOpt['password'] : '';
                    
                    if (empty($param['password'])) {
                        $code = 405;
                        $data = [];
                        $msg  = '当前文章需要输入密码才能访问';
                    } else if ($param['password'] != $password) {
                        $code = 403;
                        $data = [];
                        $msg  = '密码错误';
                    } else {
                        $msg  = '数据请求成功！';
                        $code = 200;
                        // 浏览量自增
                        $this->visit($param);
                    }
                    
                } else if ((empty($this->user) or $this->user['code'] != 200) and ($data['opt']['auth'] == 'login' or $data['opt']['auth'] == 'private')) {
                    
                    $code = 403;
                    $data = [];
                    $msg  = '未经授权';
                    
                } else {
                    
                    $msg  = '数据请求成功！';
                    $code = 200;
                    // 浏览量自增
                    $this->visit($param);
                }
                
            } else {
                
                $msg  = '数据请求成功！';
                $code = 200;
                // 浏览量自增
                $this->visit($param);
            }
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取全部文章
    public function all($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        $uid  = null;
        
        if (empty($param['page']))  $param['page']  = 1;
        if (empty($param['limit'])) $param['limit'] = 5;
        if (empty($param['order'])) $param['order'] = 'is_top desc, create_time desc';
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        // 搜索功能
        $search = (empty($param['search'])) ? '' : $param['search'];
        $map1   = ['title'   , 'like', '%'.$search.'%'];
        $map2   = ['content' , 'like', '%'.$search.'%'];
        
        // 防止登录后有权限的文章不隐藏
        if (!empty($this->user)) if (!empty($this->user['code']) == 200) $uid = $this->user['data']['id'];
        
        // 设置缓存名称
        $cache_name = 'article?page='.$param['page'].'&limit='.$param['limit'].'&order='.$param['order'].'&search='.$search.'&uid='.$uid;
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
            
            $opt = [
                'page'   =>  (int)$param['page'], 
                'limit'  =>  (int)$param['limit'],
                'order'  =>  (string)$param['order'],
                'where'  =>  [
                    function ($query) use ($map1, $map2) {
                        $query->where([$map1])->whereOr([$map2]);
                    },
                    ['is_show','=',1],
                    ['delete_time','=',null],
                ],
                'is_all'=>  false,
                'token' =>  $this->user,
                'api'   =>  true
            ];
            
            // 获取数据库数据
            $data = ArticleModel::ExpandAll(null, $opt);
            
            // 屏蔽密码 和
            // if (!empty($data['data'])) foreach ($data['data'] as $key => $val) {
                // $opt = $val['opt'];
                // unset($opt['password']);
                // $data['data'][$key]->opt = json_encode($opt);
                // $data['data'][$key]->opt = 'aaa';
            // }
            
            Cache::tag(['article'])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 保存文章
    public function saves($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        // 允许用户提交并存储的字段
        $obtain = ['title','content','description','img_src','font_count','sort_id','tag_id','is_show','is_top','opt'];
        
        if (empty($param['id'])) $article = new ArticleModel;
        else $article = ArticleModel::find((int)$param['id']);
        
        // 判断字段是否允许存储，防提权
        foreach ($param as $key => $val) if (in_array($key, $obtain)) {
            // 分类ID转字符串存储
            if ($key == 'sort_id') {
                
                if(is_array($val))        $article->$key = '|' . implode('|', array_filter($val)) . '|';
                else if (is_string($val)) $article->$key = '|' . implode('|', array_filter(explode(',', $val))) . '|';
                
            } else $article->$key = $val;
        }
        
        $article->users_id         = $this->user['data']->id;
        $article->last_update_time = time();
        
        if (empty($param['tag_id']))   $param['tag_id']   = [];
        if (empty($param['tag_name'])) $param['tag_name'] = [];
        
        // 权限处理 - 防止修改非自己的数据
        if (!empty($param['id']) and !in_array($this->user['data']->level, ['admin'])) {
            
            $array_id     = [];
            $user_article = ArticleModel::where(['users_id'=>$this->user['data']->id])->field(['id'])->select();
            foreach ($user_article as $val) array_push($array_id, $val->id);
            
            // 是自己的数据才允许修改
            if (in_array((int)$param['id'], $array_id) and (int)$this->user['data']->status == 1) {
                
                $code = 200;
                $article->save();
                // 插入标签数据
                (new Tag)->TagSave((int)$article->id, $param['tag_id'], $param['tag_name']);
                
            } else {
                
                $code = 403;
                $msg  = "无权限！";
            }
            
        } else if ((int)$this->user['data']->status == 1) {
            
            $code = 200;
            $article->save();
            
            // 插入标签数据
            (new Tag)->TagSave((int)$article->id, $param['tag_id'], $param['tag_name']);
            
        } else $msg  = "无权限！";
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 删除数据
    public function remove($param)
    {
        $data = [];
        $code = 403;
        $msg  = 'ok';
        
        if (empty($param['id'])) {
                
            $code = 404;
            $msg  = '请提交需要删除的文章ID';
            
        } else {
            
            $destroy = (!empty($param['destroy']) and $param['destroy'] == true) ? true : false;
            
            // 提交需要被删除的文章ID
            $article_id = array_filter(explode(',', $param['id']));
            // 属于用户的文章ID
            $user_article_id = ArticleModel::where(['users_id'=>$this->user['data']->id])->field(['id'])->column(['id']);
            
            // 管理员权限
            if (in_array($this->user['data']->level, ['admin'])) ArticleModel::destroy($article_id, $destroy);
            else {
                $del_id = [];
                // 非管理员只能删除属于自己的文章
                foreach ($article_id as $val) if (in_array($val, $user_article_id)) $del_id[] = $val;
                if (!empty($del_id)) ArticleModel::destroy($del_id, $destroy);
            }
            
            $code = 200;
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
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
        $uid  = null;
        
        $opt  = [
            'page'   =>  $page,
            'limit'  =>  $limit,
            'order'  =>  $order,
            'where'  =>  [],
            'whereOr'=>  [],
            'is_all' =>  false,
            'token'  =>  $this->user,
            'api'    =>  true
        ];
        
        // 防止登录后有权限的文章不隐藏
        if (!empty($this->user)) if (!empty($this->user['code']) == 200) $uid = $this->user['data']['id'];
        
        // 设置缓存名称
        $cache_name = 'article/sql?page='.$page.'&limit='.$limit.'&order='.$order.'&where='.$where.'&whereOr='.$whereOr.'&uid='.$uid;
        
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
            
            $data = ArticleModel::ExpandAll(null, $opt);
            
            // 屏蔽密码
            if (!empty($data['data'])) foreach ($data['data'] as $key => $val) {
                $opt = $val['opt'];
                unset($opt['password']);
                $data['data'][$key]->opt = json_encode($opt);
                if (!isset($opt)) $data['data'][$key]->opt = json_encode(['empty'=>1]);
            }
            
            Cache::tag(['article',$cache_name])->set($cache_name, json_encode($data));
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取 下一篇 - 上一篇
    public function next($param)
    {
        $data = [];
        $code = 400;
        $msg  = '无数据';
        
        if (empty($param['id'])) $msg = '请提交当前文章ID！';
        else {
            
            // 是否开启了缓存
            $api_cache = $this->config['api_cache'];
            // 是否获取缓存
            $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
            
            // 设置缓存名称
            $cache_name = 'article/next?id='.$param['id'];
            
            // 检查是否存在请求的缓存数据
            if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name), true);
            else {
                
                $map1 = ['id','>',$param['id']];
                $map2 = ['id','<',$param['id']];
                $map3 = ['is_show','=',1];
                
                $field= ['id','title','description','img_src','views','create_time','update_time','last_update_time'];
                
                // 上一篇
                $data['prev'] = ArticleModel::where([$map2, $map3])->field($field)->find();
                if (!empty($data['prev']))  $data['prev']['expand'] = $this->imagesCover($data['prev']);
                
                // 下一篇
                $data['next'] = ArticleModel::where([$map1, $map3])->field($field)->find();
                if (!empty($data['next']))  $data['next']['expand'] = $this->imagesCover($data['next']);
                
                Cache::tag(['links',$cache_name])->set($cache_name, json_encode($data));
            }
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    function imagesCover($data)
    {
        $result = [];
        // 随机图 - 开启随机图 - 判断封面是否为空
        $conf = Config::get('inis.random.article');
        
        if ($conf['enable']) {
            
            // 判断是否存在唯一的随机数，防止返回随机结果一致
            $path = (strpos($conf['path'], '?')) ? $conf['path'] . '&id=' . $data['id'] : $conf['path'] . '?id=' . $data['id'];
            $result['img_src'] = (!empty($data['img_src'])) ? $data['img_src'] : $path;
            
        } else $result['img_src'] = $data['img_src'];
        
        // 如果文章内容被屏蔽了
        if (empty($data['content'])) $content = ArticleModel::field(['content'])->find($data['id'])['content'];
        // 正规匹配文章内的图片
        $images = markdown::matchImg(markdown::parse(Parsedown::instance()->setUrlsLinked(false)->text($content)));
        // 重新组合文章内的图片
        $result['images'] = array_map(function ($item){
            return ['alt'=>$item['alt'],'src'=>$item['src']];
        }, $images);
        
        // 统计评论
        $result['comments']['count'] = sizeof(Comments::where(['article_id'=>$data['id']])->field(['article_id'])->select());
        
        return $result;
    }
}
