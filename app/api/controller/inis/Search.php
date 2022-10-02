<?php
declare (strict_types = 1);

namespace app\api\controller\inis;

use think\Request;
use think\facade\{Cache, Lang};
use app\model\sqlite\{Search as iSearch};
use app\model\mysql\{Article, Comments, Page, Links};

class Search extends Base
{
    /**
     * 显示资源列表
     *
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
        $method = ['article','complex','record','comments','page','links'];
        
        $mode   = empty($param['mode']) ? 'article' : $param['mode'];
        
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
        $data   = [];
        $code   = 400;
        $msg    = lang('参数不存在！');
        $result = [];
        
        // 获取请求参数
        $param = $request->param();
        
        // 存在的方法
        $method = ['article','complex','record','comments','page','links','sql'];
        
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
    
    // 搜索文章
    public function article($param)
    {
        $data = [];
        $code = 200;
        $msg  = lang('数据请求成功！');
        
        if (empty($param['page']))  $param['page']  = 1;
        if (empty($param['limit'])) $param['limit'] = 5;
        if (empty($param['order'])) $param['order'] = 'create_time desc';
        // 自动存储搜索记录
        $auto = empty($param['record']) or $param['record'] == 'true' ? true : false;
        
        $value= (empty($param['value']))   ? '' : $param['value'];
        $sid  = (empty($param['sort_id'])) ? '' : $param['sort_id'];
        
        $opt  = [
            'page'  =>  (int)$param['page'],
            'limit' =>  (int)$param['limit'],
            'order' =>  (string)$param['order']
        ];
        
        $data = Article::search($value, $opt, (int)$sid);
        
        // 非空存储搜索记录
        if (!empty($value) and $auto) {
            $record = iSearch::where(['name'=>$value])->findOrEmpty();
            if ($record->isEmpty()) {
                $record->name = $value;
                $record->count= 1;
            } else $record->count++;
            $record->save();
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 搜索记录
    public function record($param)
    {
        $data = [];
        $code = 200;
        $msg  = lang('数据请求成功！');
        
        if (empty($param['page']))  $param['page']  = 1;
        if (empty($param['limit'])) $param['limit'] = 5;
        if (empty($param['order'])) $param['order'] = 'count desc';
        
        $opt  = [
            'page'  =>  (int)$param['page'],
            'limit' =>  (int)$param['limit'],
            'order' =>  (string)$param['order']
        ];
        
        $data = iSearch::ExpandAll(null, $opt);
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 组合式API
    public function complex($param)
    {
        $data = [];
        $code = 200;
        $msg  = lang('数据请求成功！');
        
        // 搜索关键词
        $value= !empty($param['value']) ? $param['value'] : '';
        // 自动存储搜索记录
        $auto = empty($param['record']) or $param['record'] == 'true' ? true : false;
        
        // 查询范围
        $field= !empty($param['field']) ? $param['field'] : ['article','record','comments','page','links'];
        
        // 默认配置处理
        $param['config'] = (!empty($param['config'])) ? $param['config'] : [];
        if (empty($param['config']['record']))   $param['config']['record']   = ['page'=>1,'limit'=>5,'order'=>'count desc'];
        if (empty($param['config']['article']))  $param['config']['article']  = ['page'=>1,'limit'=>5,'order'=>'create_time desc','sort_id'=>null];
        if (empty($param['config']['comments'])) $param['config']['comments'] = ['page'=>1,'limit'=>5,'order'=>'create_time desc'];
        if (empty($param['config']['page']))     $param['config']['page']     = ['page'=>1,'limit'=>5,'order'=>'create_time desc'];
        if (empty($param['config']['links']))    $param['config']['links']    = ['page'=>1,'limit'=>5,'order'=>'create_time desc'];
        
        // 组合配置信息
        $config = [
            'record'  =>array_merge(['page'=>1,'limit'=>5,'order'=>'count desc'], $param['config']['record']),
            'article' =>array_merge(['page'=>1,'limit'=>5,'order'=>'create_time desc','value'=>null,'sort_id'=>null], $param['config']['article']),
            'comments'=>array_merge(['page'=>1,'limit'=>5,'order'=>'create_time desc'], $param['config']['comments']),
            'page'    =>array_merge(['page'=>1,'limit'=>5,'order'=>'create_time desc'], $param['config']['page']),
            'links'   =>array_merge(['page'=>1,'limit'=>5,'order'=>'create_time desc'], $param['config']['links']),
        ];
        
        // 搜索文章
        if (in_array('article', $field)) $data['article'] = Article::search($value, [
            'page'  => (int)$config['article']['page'],
            'limit' => (int)$config['article']['limit'],
            'order' => (string)$config['article']['order'],
            'where' => [['is_show','=',1]],
        ], (int)$config['article']['sort_id']);
        
        // 搜索评论
        if (in_array('comments', $field)) $data['comments'] = Comments::ExpandAll(null, [
            'page'  => (int)$config['comments']['page'],
            'limit' => (int)$config['comments']['limit'],
            'order' => (string)$config['comments']['order'],
            'where' => [['content','like','%'.$value.'%'],['is_show','=',1]]
        ]);
        
        // 搜索页面
        if (in_array('page', $field)) $data['page'] = Page::ExpandAll(null, [
            'page'   => (int)$config['page']['page'],
            'limit'  => (int)$config['page']['limit'],
            'order'  => (string)$config['page']['order'],
            'whereOr'=> [
                ['title'  , 'like', '%'.$value.'%'],
                ['content', 'like', '%'.$value.'%']
            ],
            'where'  => [['is_show','=',1]]
        ]);
        
        // 搜索友链
        if (in_array('links', $field)) $data['links'] = Links::ExpandAll(null, [
            'page'   => (int)$config['links']['page'],
            'limit'  => (int)$config['links']['limit'],
            'order'  => (string)$config['links']['order'],
            'whereOr'=> [
                ['name'       , 'like', '%'.$value.'%'],
                ['url'        , 'like', '%'.$value.'%'],
                ['description', 'like', '%'.$value.'%']
            ],
            'where'  => [['is_show','=',1]],
        ]);
        
        // 搜索记录
        if (in_array('record', $field)) $data['record'] = iSearch::ExpandAll(null, [
            'page'  => (int)$config['record']['page'],
            'limit' => (int)$config['record']['limit'],
            'order' => (string)$config['record']['order']
        ]);
        
        // 非空存储搜索记录
        if (!empty($value) and $auto) {
            $record = iSearch::where(['name'=>$value])->findOrEmpty();
            if ($record->isEmpty()) {
                $record->name = $value;
                $record->count= 1;
            } else $record->count++;
            $record->save();
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 评论搜索
    public function comments($param)
    {
        $data = [];
        $code = 200;
        $msg  = lang('数据请求成功！');
        
        if (empty($param['page']))  $param['page']  = 1;
        if (empty($param['limit'])) $param['limit'] = 5;
        if (empty($param['order'])) $param['order'] = 'create_time desc';
        
        $value= (empty($param['value'])) ? '' : $param['value'];
        // 自动存储搜索记录
        $auto = empty($param['record']) or $param['record'] == 'true' ? true : false;
        
        $opt  = [
            'page'  => (int)$param['page'],
            'limit' => (int)$param['limit'],
            'order' => (string)$param['order'],
            'where' => [['content','like','%'.$value.'%']]
        ];
        
        $data = Comments::ExpandAll(null, $opt);
        
        // 非空存储搜索记录
        if (!empty($value) and $auto) {
            $record = iSearch::where(['name'=>$value])->findOrEmpty();
            if ($record->isEmpty()) {
                $record->name = $value;
                $record->count= 1;
            } else $record->count++;
            $record->save();
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 页面搜索
    public function page($param)
    {
        $data = [];
        $code = 200;
        $msg  = lang('数据请求成功！');
        
        if (empty($param['page']))  $param['page']  = 1;
        if (empty($param['limit'])) $param['limit'] = 5;
        if (empty($param['order'])) $param['order'] = 'create_time desc';
        
        $value= (empty($param['value'])) ? '' : $param['value'];
        // 自动存储搜索记录
        $auto = empty($param['record']) or $param['record'] == 'true' ? true : false;
        
        $opt  = [
            'page'   => (int)$param['page'],
            'limit'  => (int)$param['limit'],
            'order'  => (string)$param['order'],
            'whereOr'=> [
                ['title'  , 'like', '%'.$value.'%'],
                ['content', 'like', '%'.$value.'%']
            ]
        ];
        
        $data = Page::ExpandAll(null, $opt);
        
        // 非空存储搜索记录
        if (!empty($value) and $auto) {
            $record = iSearch::where(['name'=>$value])->findOrEmpty();
            if ($record->isEmpty()) {
                $record->name = $value;
                $record->count= 1;
            } else $record->count++;
            $record->save();
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 友链搜索
    public function links($param)
    {
        $data = [];
        $code = 200;
        $msg  = lang('数据请求成功！');
        
        if (empty($param['page']))  $param['page']  = 1;
        if (empty($param['limit'])) $param['limit'] = 5;
        if (empty($param['order'])) $param['order'] = 'create_time desc';
        
        $value= (empty($param['value']))   ? '' : $param['value'];
        // 自动存储搜索记录
        $auto = empty($param['record']) or $param['record'] == 'true' ? true : false;
        
        $opt  = [
            'page'   => (int)$param['page'],
            'limit'  => (int)$param['limit'],
            'order'  => (string)$param['order'],
            'whereOr'=> [
                ['name'       , 'like', '%'.$value.'%'],
                ['url'        , 'like', '%'.$value.'%'],
                ['description', 'like', '%'.$value.'%']
            ],
            'where'  => [['is_show','=',1]],
        ];
        
        $data = Links::ExpandAll(null, $opt);
        
        // 非空存储搜索记录
        if (!empty($value) and $auto) {
            $record = iSearch::where(['name'=>$value])->findOrEmpty();
            if ($record->isEmpty()) {
                $record->name = $value;
                $record->count= 1;
            } else $record->count++;
            $record->save();
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
        $msg  = lang('成功！');
        
        $opt  = [
            'page' => $page,
            'limit'=> $limit,
            'order'=> $order,
            'where'=> $where,
            'whereOr'=> $whereOr,
        ];
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'search/sql','where'=>$where], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            $data = iSearch::ExpandAll(null, $opt);
            if ($this->ApiCache) Cache::tag(['search',$cache_name])->set($cache_name, json_encode($data));
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}
