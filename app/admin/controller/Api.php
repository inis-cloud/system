<?php
// +----------------------------------------------------------------------
// | 后台 API
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\Request;
use think\facade\{Cache};
use inis\utils\{markdown};
use app\model\mysql\{Tag, Page, Links, Users, Music, Banner, Placard, Options, Article, AuthRule, Comments, LinksSort, ArticleSort};

class Api extends Base
{
    public function comments(Request $request)
    {
        $data  = [];
        $code  = 200;
        $msg   = 'ok';
        
        $param = $request->param();
        
        if ($request->isGet()) {
            
            $page  = (!empty($param['page']))  ? $param['page']  : 1;
            $limit = (!empty($param['limit'])) ? $param['limit'] : 5;
            $order = (!empty($param['order'])) ? $param['order'] : 'create_time desc';
            
            // 是否开启了缓存
            $api_cache = config('inis.api.cache');
            // 是否获取缓存
            $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
            
            $opt = [
                'page'   =>  $page, 
                'limit'  =>  $limit,
                'order'  =>  $order,
                'where'  =>  [['email','<>',$this->user['email']]]
            ];
            
            // 设置缓存名称
            $cache_name = 'comments?page='.$page.'&limit='.$limit.'&order='.$order;
            
            // 检查是否存在请求的缓存数据
            if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
            else {
                
                // 获取全部评论
                $data = Comments::ExpandAll(null, $opt);
                
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
        }
        
        return $this->json($data, $msg, $code);
    }
    
    public function user(Request $request)
    {
        $data  = [];
        $code  = 200;
        $msg   = 'ok';
        
        $param = $request->param();
        
        if ($request->isGet()) $data = $this->user;
        
        return $this->json($data, $msg, $code);
    }
}