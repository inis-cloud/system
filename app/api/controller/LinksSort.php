<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\Request;
use think\facade\Cache;
use app\model\LinksSort as LinksSortModel;

class LinksSort extends Base
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
        $param = $request->param();
        
        if (empty($param['page']))  $param['page']  = 1;
        if (empty($param['limit'])) $param['limit'] = 5;
        if (empty($param['order'])) $param['order'] = 'create_time desc';
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        $opt = [
            'page'   =>  (int)$param['page'], 
            'limit'  =>  (int)$param['limit'],
            'order'  =>  (string)$param['order'],
        ];
        
        if (empty($param['id'])) {
            
            // 设置缓存名称
            $cache_name = 'links-sort?page='.$param['page'].'&limit='.$param['limit'].'&order='.$param['order'];
            
            // 检查是否存在请求的缓存数据
            if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
            else {
                // 获取数据库数据
                $data = LinksSortModel::ExpandAll(null, $opt);
                Cache::tag(['links-sort'])->set($cache_name, json_encode($data));
            }
            
            $code = 200;
            $msg  = '无数据！';
            // 逆向思维，节省代码行数
            if (empty($data)) $code = 204;
            else $msg = '数据请求成功！';
            
        } else {
            
            $data = [];
            
            // 设置缓存名称
            $cache_name = 'links-sort?id='.$param['id'].'&page='.$param['page'].'&limit='.$param['limit'].'&order='.$param['order'];
            
            // 检查是否存在请求的缓存数据
            if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
            else {
                // 获取数据库数据
                $data = LinksSortModel::links((int)$param['id'], $opt);
                Cache::tag(['links-sort',$cache_name])->set($cache_name, json_encode($data));
            }
            
            $code = 200;
            $msg  = '无数据！';
            // 逆向思维，节省代码行数
            if (empty($data)) $code = 204;
            else $msg = '数据请求成功！';
        }
        
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
     * @param  int  $id
     * @return \think\Response
     */
    public function read(Request $request, $id)
    {
        // 获取请求参数
        $param = $request->param();
        
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
        $cache_name = 'links-sort/sql?page='.$page.'&limit='.$limit.'&order='.$order.'&where='.$where.'&whereOr='.$whereOr;
        
        // SQL API
        if ($id == 'sql') {
            
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
                $data = LinksSortModel::ExpandAll(null, $opt);
                Cache::tag(['links-sort',$cache_name])->set($cache_name, json_encode($data));
            }
        }
        
        return $this->create($data, $msg, $code);
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}