<?php
declare (strict_types = 1);

namespace app\api\controller\inis;

use think\Request;
use think\facade\{Cache, Lang};
use app\model\mysql\{Tag, Page, Visit, Users, Links, Music, Article, Comments, LinksSort, ArticleSort};

class Group extends Base
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
        $msg    = Lang::get('参数不存在！');
        $result = [];
        
        $mode   = !empty($param['mode']) ? $param['mode'] : 'count';
        
        // 存在的方法
        $method = ['count','detail','visit'];
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
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
        
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $IID
     * @return \think\Response
     */
    public function IGET(Request $request, $IID)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = Lang::get('参数不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['count','detail','visit'];
        
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
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $IID
     * @return \think\Response
     */
    public function IDELETE(Request $request, $IID)
    {
        //
    }
    
    // 统计
    public function count($param)
    {
        $data = [];
        $code = 200;
        $msg  = Lang::get('无数据！');
        
        $where  = [];
        $time   = time();
        $day    = !empty($param['day'])    ? $param['day']    : null;
        $field  = !empty($param['field'])  ? $param['field']  : null;
        $detail = (empty($param['detail']) or $param['detail'] != 'true') ? false : true;
        
        $page   = !empty($param['page'])  ? $param['page']  : 1;
        $limit  = !empty($param['limit']) ? $param['limit'] : 5;
        $order  = !empty($param['order']) ? $param['order'] : 'create_time asc';
        
        $opt = [
            'page'   =>  (int)$page, 
            'limit'  =>  (int)$limit,
            'order'  =>  (string)$order,
            'where'  =>  $where,
        ];
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'group'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            
            // 查询 $day 天内的数据
            if (!empty($day) and (int)$day != 0) {
                $where = ['create_time','>=',$time - (86400 * $day)];
                $opt['where'][] = $where;
            }
            
            // 处理自定义获取的接口
            $field = (is_string($field)) ? array_filter(explode(',', $field)): $field;
            if (empty($field)) $field = ['links', 'article', 'users', 'page', 'comments', 'music', 'tag', 'linksSort', 'articleSort', 'other'];
            
            // 转小写
            foreach ($field as $key => $val) $field[$key] = strtolower($val);
            
            // 动态查询，减少数据库负担
            if (in_array('tag', $field))         $tag          = Tag::ExpandAll(null, $opt);
            if (in_array('other', $field))       $other        = ['views'=>Article::sum('views'),'word'=>Article::sum('font_count')];
            if (in_array('page', $field))        $page         = Page::ExpandAll(null, $opt);
            if (in_array('links', $field))       $links        = Links::ExpandAll(null, $opt);
            if (in_array('users', $field))       $users        = Users::ExpandAll(null, $opt);
            if (in_array('music', $field))       $music        = Music::ExpandAll(null, $opt);
            if (in_array('article', $field))     $article      = Article::ExpandAll(null, $opt);
            if (in_array('comments', $field))    $comments     = Comments::ExpandAll(null, $opt);
            if (in_array('article', $field))     $article      = Article::ExpandAll(null, $opt);
            if (in_array('linkssort', $field))   $linkssort    = LinksSort::ExpandAll(null, $opt);
            if (in_array('articlesort', $field)) $articlesort  = ArticleSort::ExpandAll(null, $opt);
            
            // 动态返回数据
            foreach ($field as $key => $val) {
                
                if (!$detail and isset($$val)) unset($$val['data'], $$val['page']);
                $data[$val] = isset($$val) ? $$val : null;
            }
            
            if ($this->ApiCache) Cache::tag(['group'])->set($cache_name, json_encode($data));
        }
        
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = Lang::get('数据请求成功！');
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }
    
    // 详细统计
    public function detail($param)
    {
        $data = [];
        $code = 200;
        $msg  = Lang::get('无数据！');
        
        $date      = !empty($param['date']) ? $param['date'] : '';
        $field     = !empty($param['field'])  ? $param['field']  : null;
        $timestamp = !empty($param['timestamp']) ? $param['timestamp'] : '';
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'group'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            
            // 日期格式参数
            $date      = array_filter(explode(',', $date));
            // 时间戳格式参数
            $timestamp = array_filter(explode(',', $timestamp));
            
            // 处理自定义获取的接口
            $field     = (is_string($field)) ? array_filter(explode(',', $field)): $field;
            if (empty($field)) $field = ['users', 'article', 'links', 'comments', 'tag', 'visit'];
            
            $max = time();
            // 获取7天数据
            $min = $max - (7 * 86400);
            
            if (!empty($date)) {
                if (isset($date[0])) $min = strtotime($date[0]);
                if (isset($date[1])) $max = strtotime($date[1]);
            }
            if (!empty($timestamp)) {
                if (isset($timestamp[0])) $min = $timestamp[0];
                if (isset($timestamp[1])) $max = $timestamp[1];
            }
            
            // 计算两时间戳之间的差值
            $days = $this->helper->DiffTime($min,$max);
            
            // 每隔一天赋值给数组
            for ($i=0; $i<$days['day']; $i++) {
                
                $map1  = ['create_time', '>=', $min + ($i * 86400)];
                $map2  = ['create_time', '<' , $min + (($i + 1) * 86400)];
                
                // 动态查询，减少数据库负担
                if (in_array('users', $field)) $data['users'][] = [
                    date('Y-m-d', $min + $i * 86400),
                    Users::where([$map1, $map2])->count()
                ];
                if (in_array('article', $field)) $data['article'][] = [
                    date('Y-m-d', $min + $i * 86400),
                    Article::where([$map1, $map2])->count()
                ];
                if (in_array('comments', $field)) $data['comments'][] = [
                    date('Y-m-d', $min + $i * 86400),
                    Comments::where([$map1, $map2])->count()
                ];
                if (in_array('links', $field)) $data['links'][] = [
                    date('Y-m-d', $min + $i * 86400),
                    Links::where([$map1, $map2])->count()
                ];
                if (in_array('tag', $field)) $data['tag'][] = [
                    date('Y-m-d', $min + $i * 86400),
                    Tag::where([$map1, $map2])->count()
                ];
                if (in_array('visit', $field)) {
                    
                    $counts = 0; 
                    $dates  = date('Y-m-d', $min + $i * 86400);
                    $strtotimes = strtotime($dates);
                    
                    $visit = Visit::where(['create_time'=>$strtotimes])->findOrEmpty();
                    
                    if (!$visit->isEmpty() and !empty($visit->opt)) {
                        $opt = json_decode($visit->opt);
                        if (!empty($opt->article)) foreach ($opt->article as $val) $counts += $val->visit;
                        if (!empty($opt->page)) foreach ($opt->page as $val) $counts += $val->visit;
                    }
                    
                    $data['visit'][] = [$dates,$counts];
                }
            }
            
            if ($this->ApiCache) Cache::tag(['group'])->set($cache_name, json_encode($data));
        }
        
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = Lang::get('数据请求成功！');
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }
    
    public function visit($param)
    {
        $data = [];
        $code = 200;
        $msg  = Lang::get('无数据！');
        
        $date      = !empty($param['date']) ? $param['date'] : '';
        $field     = !empty($param['field'])  ? $param['field']  : null;
        $timestamp = !empty($param['timestamp']) ? $param['timestamp'] : '';

        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'group'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
        
            // 日期格式参数
            $date      = array_filter(explode(',', $date));
            // 时间戳格式参数
            $timestamp = array_filter(explode(',', $timestamp));
            
            // 处理自定义获取的接口
            $field     = (is_string($field)) ? array_filter(explode(',', $field)): $field;
            if (empty($field)) $field = ['users', 'article', 'links', 'comments', 'tag', 'visit'];
            
            $max = time();
            // 获取7天数据
            $min = $max - (7 * 86400);
            
            if (!empty($date)) {
                if (isset($date[0])) $min = strtotime($date[0]);
                if (isset($date[1])) $max = strtotime($date[1]);
            }
            if (!empty($timestamp)) {
                if (isset($timestamp[0])) $min = $timestamp[0];
                if (isset($timestamp[1])) $max = $timestamp[1];
            }
            
            // 计算两时间戳之间的差值
            $days = $this->helper->DiffTime($min,$max);
            
            // 每隔一天赋值给数组
            for ($i=0; $i<$days['day']; $i++) {
                
                $map1  = ['create_time', '>=', $min + ($i * 86400)];
                $map2  = ['create_time', '<' , $min + (($i + 1) * 86400)];
                
                $pages      = [];
                $articles   = []; 
                $dates      = date('Y-m-d', $min + $i * 86400);
                $strtotimes = strtotime($dates);
                
                $visit = Visit::where(['create_time'=>$strtotimes])->findOrEmpty();
                
                if (!$visit->isEmpty() and !empty($visit->opt)) {
                    
                    $opt = json_decode($visit->opt);
                    
                    if (!empty($opt->page)) foreach ($opt->page as $val) {
                        
                        $page  = Page::field(['id','title'])->findOrEmpty($val->id);
                        
                        if (!$page->isEmpty()) {
                            $page['visit'] = $val->visit;
                            $pages[] = $page;
                        }
                    }
                    
                    if (!empty($opt->article)) foreach ($opt->article as $val) {
                        
                        $article  = Article::field(['id','title'])->findOrEmpty($val->id);
                        
                        if (!$article->isEmpty()) {
                            $article['visit'] = $val->visit;
                            $articles[] = $article;
                        }
                    }
                }
                
                $data['article'][] = ['date'=>$dates,'count'=>$articles];
                $data['page'][] = ['date'=>$dates,'count'=>$pages];
            }
            
            if ($this->ApiCache) Cache::tag(['group'])->set($cache_name, json_encode($data));
        }
        
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = Lang::get('数据请求成功！');
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }
}
