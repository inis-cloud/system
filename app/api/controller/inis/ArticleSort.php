<?php
declare (strict_types = 1);

namespace app\api\controller\inis;

use think\Request;
use think\facade\{Cache, Validate};
use app\model\mysql\{ArticleSort as ArticleSortModel};

class ArticleSort extends Base
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
        
        $mode = (empty($param['id'])) ? 'all' : 'one';
        
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
        //
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
        $method = ['sql'];
        
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
    
    // 获取一条数据
    public function one($param)
    {
        $data = [];
        $code = 400;
        $msg  = '无数据';
        
        if (empty($param['page']))  $param['page']  = 1;
        if (empty($param['limit'])) $param['limit'] = 5;
        if (empty($param['order'])) $param['order'] = 'create_time asc';
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        $opt = [
            'page'   =>  (int)$param['page'], 
            'limit'  =>  (int)$param['limit'],
            'order'  =>  (string)$param['order'],
        ];
        
        // 设置缓存名称
        $cache_name = 'article-sort?id='.$param['id'].'&page='.$param['page'].'&limit='.$param['limit'].'&order='.$param['order'];
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
            
            // 获取数据库数据
            $data = ArticleSortModel::article((int)$param['id'], $opt);
            
            // 屏蔽密码
            if (!empty($data['expand']['data'])) foreach ($data['expand']['data'] as $key => $val) {
                if (!empty($val['opt']) and isset($val['opt']->password)) unset($val['opt']->password);
            }
            
            Cache::tag(['article-sort',$cache_name])->set($cache_name, json_encode($data));
        }
        
        if (!Validate::isInteger($param['id'])) $msg = 'ID参数不合法！';
        else if (empty($data)) $code = 204;
        else {
            $msg  = '数据请求成功！';
            $code = 200;
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取全部数据
    public function all($param)
    {
        if (empty($param['page']))  $param['page']  = 1;
        if (empty($param['limit'])) $param['limit'] = 5;
        if (empty($param['order'])) $param['order'] = 'create_time asc';
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        $opt = [
            'page'   =>  (int)$param['page'], 
            'limit'  =>  (int)$param['limit'],
            'order'  =>  (string)$param['order'],
        ];
        
        // 设置缓存名称
        $cache_name = 'article-sort?page='.$param['page'].'&limit='.$param['limit'].'&order='.$param['order'];
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
            
            // 获取数据库数据
            $data = ArticleSortModel::ExpandAll(null, $opt);
            Cache::tag(['article-sort'])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
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
        
        $opt  = [
            'page' => $page,
            'limit'=> $limit,
            'order'=> $order,
            'where'=> [],
            'whereOr'=> [],
        ];
        
        // 设置缓存名称
        $cache_name = 'article-sort/sql?page='.$page.'&limit='.$limit.'&order='.$order.'&where='.$where.'&whereOr='.$whereOr;
        
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
            $data = ArticleSortModel::ExpandAll(null, $opt);
            Cache::tag(['article-sort',$cache_name])->set($cache_name, json_encode($data));
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}
