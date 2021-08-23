<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\Request;
use think\facade\Cache;
use app\model\Tag as TagModel;

class Tag extends Base
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
        
        $user   = !empty($param['login-token']) ? $this->parseJWT($param['login-token']) : [];
        
        // 存在的方法
        $method = ['one','all'];
        
        $mode   = (empty($param['id'])) ? 'all' : 'one';
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param, $user);
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
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['saves','remove'];
        
        $mode   = (empty($param['mode'])) ? 'saves' : $param['mode'];
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 清除缓存
        Cache::tag('tag')->clear();
        
        return $this->create($data, $msg, $code);
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
        $cache_name = 'tag/sql?page='.$page.'&limit='.$limit.'&order='.$order.'&where='.$where.'&whereOr='.$whereOr;
        
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
                $data = TagModel::ExpandAll(null, $opt);
                Cache::tag(['tag',$cache_name])->set($cache_name, json_encode($data));
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
    
    // 获取单条数据
    public function one($param)
    {
        $data = [];
        $code = 400;
        $msg  = '无数据';
        
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
        
        // 设置缓存名称
        $cache_name = 'tag?id='.$param['id'].'&page='.$param['page'].'&limit='.$param['limit'].'&order='.$param['order'];
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
            
            // 获取数据库数据
            $data = TagModel::article((int)$param['id'], $opt);
            
            // 屏蔽密码
            if (!empty($data['expand']['data'])) foreach ($data['expand']['data'] as $key => $val) {
                if (!empty($val['opt']) and isset($val['opt']->password)) unset($val['opt']->password);
            }
            
            Cache::tag(['tag',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取全部数据
    public function all($param)
    {
        $data = [];
        $code = 400;
        $msg  = '无数据';
        
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
        
        // 设置缓存名称
        $cache_name = 'tag?page='.$param['page'].'&limit='.$param['limit'].'&order='.$param['order'];
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
            // 获取数据库数据
            $data = TagModel::ExpandAll(null, $opt);
            Cache::tag(['tag'])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 保存数据
    public function saves()
    {
        $data = [];
        $code = 200;
        $msg  = 'ok';
        
        // 允许用户提交并存储的字段
        $obtain = ['id','named','is_show','opt'];
        
        if (empty($param['id'])) $tag = new TagModel;
        else $tag = TagModel::find((int)$param['id']);
        
        // 判断字段是否允许存储，防提权
        foreach ($param as $key => $val) if (in_array($key, $obtain)) {
            // 分类ID转字符串存储
            if ($key == 'named') $tag->name = $val;
            else $tag->$key = $val;
        }
        
        $tag->save();
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 删除数据
    public function remove($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        if (empty($param['id'])) $msg = '请提交需要删除的标签ID';
        else {
            
            $id = array_filter(explode(',', $param['id']));
            
            TagModel::destroy($id);
            
            $code = 200;
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}
