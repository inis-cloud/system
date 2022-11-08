<?php
declare (strict_types = 1);

namespace app\api\controller\default;

use Parsedown;
use think\Request;
use inis\utils\{markdown};
use think\facade\{Cache, Validate, Config, Lang, Log};
use app\model\mysql\{Tag, Visit, Article as ArticleModel, Comments, ArticleSort};

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
        $msg    = lang('参数不存在！');
        $result = [];

        // 存在的方法
        $method = ['one','all'];

        $mode   = empty($param['id']) ? 'all' : 'one';

        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;

        // 记录访问量
        // iVisit::record([
        //     'api' =>[['id'=>'article-'.$mode,'visit'=>1]],
        //     'total'=>[['id'=>'all','visit'=>1]]
        // ]);

        return $this->json($data, $msg, $code);
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
        $msg    = lang('参数不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['save','move'];

        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 清除缓存
        Cache::tag('article')->clear();
        
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
        $data   = [];
        $code   = 400;
        $msg    = lang('参数不存在！');
        $result = [];
        
        // 获取请求参数
        $param = $request->param();
        
        // 存在的方法
        $method = ['one','all','sql','next'];
        
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
        $data   = [];
        $code   = 400;
        $msg    = lang('参数不存在！');
        $result = [];
        
        // 获取请求参数
        $param = $request->param();
        
        // 存在的方法
        $method = ['save'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->json($data, $msg, $code);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $IID
     * @return \think\Response
     */
    public function IDELETE(Request $request, $IID)
    {
        $data   = [];
        $code   = 400;
        $msg    = lang('参数不存在！');
        $result = [];
        
        // 获取请求参数
        $param = $request->param();
        
        // 存在的方法
        $method = ['remove'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->json($data, $msg, $code);
    }
    
    // typecho 迁移至 inis
    public function move($param)
    {
        $result = ['data'=>[],'code'=>403,'msg'=>'成功！'];
        
        if (empty($param['login-token'])) $result['msg'] = lang('login-token 未提交！');
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
            
            $article->users_id         = request()->user->id;
            $article->last_update_time = time();
            
            if (request()->user->level == 'admin') {
                $article->save();
                $result['code'] = 200;
            } else $result['msg'] = lang('无权限！');
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
            $visit->json_time = $today;
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
        $msg  = lang('无数据！');
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'article'], $param));
        // 校验数据是否存在
        $check = ArticleModel::find($param['id']);
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = Cache::get($cache_name);
        else {
            // 获取数据库数据
            if (empty($check)) $data = [];
            else $data = ArticleModel::ExpandAll((int)$param['id'], ['withoutField'=>null]);
            $data = json_encode($data);
            if ($this->ApiCache) Cache::tag(['article',$cache_name])->set($cache_name, $data);
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
        
        if (!Validate::isInteger($param['id'])) $msg  = lang('ID参数不合法！');
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
                        $msg  = lang('当前文章需要输入密码才能访问！');
                    } else if ($param['password'] != $password) {
                        $code = 403;
                        $data = [];
                        $msg  = lang('密码错误！');
                    } else {
                        $msg  = lang('数据请求成功！');
                        $code = 200;
                        // 浏览量自增
                        $this->visit($param);
                    }
                    
                } else if ((empty($this->user) or $this->user['code'] != 200) and ($data['opt']['auth'] == 'login' or $data['opt']['auth'] == 'private')) {
                    
                    $code = 403;
                    $data = [];
                    $msg  = lang('未经授权！');
                    
                } else {
                    
                    $msg  = lang('数据请求成功！');
                    $code = 200;
                    // 浏览量自增
                    $this->visit($param);
                }
                
            } else {
                
                $msg  = lang('数据请求成功！');
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
        $msg  = '成功！';

        if (empty($param['page']))  $param['page']  = 1;
        if (empty($param['limit'])) $param['limit'] = 5;
        if (empty($param['order'])) $param['order'] = 'is_top desc, create_time desc';
        
        // 搜索功能
        $search = (empty($param['search'])) ? '' : $param['search'];
        $map1   = ['title'   , 'like', '%'.$search.'%'];
        $map2   = ['content' , 'like', '%'.$search.'%'];
        
        // 防止登录后有权限的文章不隐藏
        $uid = !empty(request()->user) ? request()->user->id : null;
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'article','uid'=>$uid], $param));

        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
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
            
            if ($this->ApiCache) Cache::tag(['article'])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = lang('无数据！');
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg  = lang('数据请求成功！');

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 保存文章
    public function save($param)
    {
        $data = [];
        $code = 400;
        $msg  = '成功！';
        
        // 允许用户提交并存储的字段
        $obtain = ['title','content','description','img_src','font_count','sort_id','tag_id','is_show','is_top','opt'];
        
        if (empty($param['id'])) $item = new ArticleModel;
        else {
            $item = ArticleModel::findOrEmpty((int)$param['id']);
            if ($item->isEmpty()) return ['data'=>[],'code'=>204,'msg'=>lang('无数据！')];
        }
        
        // 判断字段是否允许存储，防提权
        foreach ($param as $key => $val) if (in_array($key, $obtain)) {
            // 分类ID转字符串存储
            if ($key == 'sort_id') {
                
                if(is_array($val))        $item->$key = '|' . implode('|', array_filter($val)) . '|';
                else if (is_string($val)) $item->$key = '|' . implode('|', array_filter(explode(',', $val))) . '|';
                
            } else $item->$key = $val;
        }
        
        $item->users_id         = request()->user->id;
        $item->last_update_time = time();
        
        if (empty($param['tag_id']))   $param['tag_id']   = [];
        if (empty($param['tag_name'])) $param['tag_name'] = [];
        
        // 权限处理 - 防止修改非自己的数据
        if (!empty($param['id']) and !in_array(request()->user->level, ['admin'])) {
            
            $ids   = [];
            $allow = ArticleModel::where(['users_id'=>request()->user->id])->field(['id'])->select();
            foreach ($allow as $val) array_push($ids, $val->id);
            
            // 是自己的数据才允许修改
            if (in_array((int)$param['id'], $ids) and (int)request()->user->status == 1) {
                
                $code = 200;
                $item->save();
                // 插入标签数据
                (new Tag)->TagSave((int)$item->id, $param['tag_id'], $param['tag_name']);
                
            } else {
                
                $code = 403;
                $msg  = lang("无权限！");
            }
            
        } else if ((int)request()->user->status == 1) {
            
            $code = 200;
            $item->save();

            $data = (int)$item->id;
            
            // 插入标签数据
            (new Tag)->TagSave((int)$item->id, $param['tag_id'], $param['tag_name']);
            
        } else $msg  = lang("无权限！");
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 删除数据
    public function remove($param)
    {
        $data = [];
        $code = 403;
        $msg  = '成功！';
        
        if (empty($param['id'])) {
                
            $code = 404;
            $msg  = lang('请提交需要删除的文章ID！');
            
        } else {
            
            $destroy = (!empty($param['destroy']) and $param['destroy'] == true) ? true : false;
            
            // 提交需要被删除的文章ID
            $id    = array_filter(explode(',', $param['id']));
            // 属于用户的文章ID
            $allow = ArticleModel::where(['users_id'=>request()->user->id])->field(['id'])->column(['id']);
            
            // 管理员权限
            if (in_array(request()->user->level, ['admin'])) ArticleModel::destroy($id, $destroy);
            else {
                $ids = [];
                // 非管理员只能删除属于自己的文章
                foreach ($id as $val) if (in_array($val, $allow)) $ids[] = $val;
                if (!empty($ids)) ArticleModel::destroy($ids, $destroy);
            }
            
            $code = 200;
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // SQL接口
    public function sql($param)
    {
        $where   = (empty($param['where']))   ? [] : $param['where'];
        $whereOr = (empty($param['whereOr'])) ? [] : $param['whereOr'];
        $page    = (!empty($param['page']))   ? $param['page']  : 1;
        $limit   = (!empty($param['limit']))  ? $param['limit'] : 5;
        $order   = (!empty($param['order']))  ? $param['order'] : 'create_time desc';
        
        $data = [];
        $code = 200;
        $msg  = '成功！';

        $opt  = [
            'page'   =>  $page,
            'limit'  =>  $limit,
            'order'  =>  $order,
            'where'  =>  $where,
            'whereOr'=>  $whereOr,
            'is_all' =>  false,
            'token'  =>  $this->user,
            'api'    =>  true
        ];
        
        // 防止登录后有权限的文章不隐藏
        $uid = !empty(request()->user) ? request()->user->id : null;
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'article/sql','uid'=>$uid], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            
            $data = ArticleModel::ExpandAll(null, $opt);
            
            // 屏蔽密码
            if (!empty($data['data'])) foreach ($data['data'] as $key => $val) {
                $opt = $val['opt'];
                unset($opt['password']);
                $data['data'][$key]->opt = json_encode($opt);
                if (!isset($opt)) $data['data'][$key]->opt = json_encode(['empty'=>1]);
            }
            
            if ($this->ApiCache) Cache::tag(['article',$cache_name])->set($cache_name, json_encode($data));
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取 下一篇 - 上一篇
    public function next($param)
    {
        $data = [];
        $code = 400;
        $msg  = lang('无数据！');

        $param['id'] = !empty($param['id']) ? $param['id'] : 1;
            
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'article/next'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name), true);
        else {
            
            $map1 = ['id','>',$param['id']];
            $map2 = ['id','<',$param['id']];
            $map3 = ['is_show','=',1];
            
            $field= ['id','title','description','img_src','views','sort_id','create_time','update_time','last_update_time'];
            
            // 上一篇
            $data['prev'] = ArticleModel::where([$map2, $map3])->order('id desc')->field($field)->find();
            if (!empty($data['prev']))  $data['prev']['expand'] = $this->imagesCover($data['prev']);
            
            // 下一篇
            $data['next'] = ArticleModel::where([$map1, $map3])->field($field)->find();
            if (!empty($data['next']))  $data['next']['expand'] = $this->imagesCover($data['next']);
            
            if ($this->ApiCache) Cache::tag(['article',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = lang('无数据！');
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = lang('数据请求成功！');
        
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
        // 分类ID
        $sort = array_merge(array_filter(explode('|', $data['sort_id'] ?? '')));
        $result['sort'] = !empty($sort) ? ArticleSort::where('is_show', 1)->field(['id','name','description','opt'])->select($sort) : [];
        
        return $result;
    }
}
