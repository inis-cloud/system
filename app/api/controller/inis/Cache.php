<?php
declare (strict_types = 1);

namespace app\api\controller\inis;

use think\Request;
use think\facade\{Cache as tCache};

class Cache extends Base
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
        $msg    = '方法不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['has','get'];
        
        $mode   = !empty($param['mode']) ? $param['mode'] : 'get';
        
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
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['set'];
        
        $mode   = !empty($param['mode']) ? $param['mode']  : 'set';
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
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
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '方法不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['has','get'];
        
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
    
    // 判断缓存是否存在
    public function has($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        if (empty($param['name'])) $msg = '请提交需要查询的缓存名称，用参数 name 表示';
        else {
            
            $code = 200;
            $data['exist'] = tCache::has($param['name']);
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取缓存
    public function get($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        if (empty($param['name'])) $msg = '请提交需要获取的缓存名称，用参数 name 表示';
        else {
            
            if (!tCache::has($param['name'])) {
                
                $code = 204;
                $msg  = '缓存不存在或已过期';
                
            } else {
                
                $code = 200;
                $data = json_decode(tCache::get($param['name']));
            }
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取缓存
    public function set($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        if (empty($param['name']))      $msg = '请提交需要获取的缓存名称，用参数 name 表示';
        else if (empty($param['data'])) $msg = '请提交需要缓存的数据，用参数 data 表示';
        else {
            
            $second = !empty($param['second']) ? (int)$param['second'] : 7200;
            
            $code = 200;
            $data = tCache::set($param['name'], json_encode($param['data'], JSON_UNESCAPED_UNICODE), $second);
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}
