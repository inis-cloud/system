<?php
declare (strict_types = 1);

namespace app\api\controller\default;

use think\Request;
use think\facade\{Cache as tCache, Lang};

class Cache extends Base
{
    
    protected $middleware = [];

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
        $msg    = lang('方法不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['has','get'];
        
        $mode   = !empty($param['mode']) ? $param['mode'] : 'get';
        
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
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = lang('参数不存在！');
        $result = [];

        // 存在的方法
        $method = ['set'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
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
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = lang('方法不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['has','get'];
        
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
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = lang('方法不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['clear','delete'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->json($data, $msg, $code);
    }
    
    // 判断缓存是否存在
    public function has($param)
    {
        $data = [];
        $code = 400;
        $msg  = lang('数据请求成功！');
        
        if (empty($param['name'])) $msg = lang('请提交需要查询的缓存名称，用参数 name 表示！');
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
        $msg  = lang('数据请求成功！');
        
        if (empty($param['name'])) $msg = lang('请提交需要获取的缓存名称，用参数 name 表示！');
        else {
            
            if (!tCache::has($param['name'])) {
                
                $code = 204;
                $msg  = lang('缓存不存在或已过期！');
                
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
        $msg  = lang('缓存设置成功！');
        
        if (empty($param['name']))      $msg = lang('请提交需要获取的缓存名称，用参数 name 表示！');
        else if (empty($param['data'])) $msg = lang('请提交需要缓存的数据，用参数 data 表示！');
        else {
            
            $second = !empty($param['second']) ? $param['second'] : 7200;
            
            $code = 200;
            $data = tCache::set($param['name'], json_encode($param['data'], JSON_UNESCAPED_UNICODE), (int)$second);
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 清除所有缓存
    public function clear($param)
    {
        $data = [];
        $code = 200;
        $msg  = lang('缓存清理成功！');

        if (!in_array(request()->user->level, ['admin'])) return ['data'=>[],'code'=>403,'msg'=>lang('无权限！')];

        tCache::clear();
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 删除指定缓存
    public function delete($param)
    {
        $data = [];
        $code = 200;
        $msg  = lang('缓存删除成功！');

        if (!in_array(request()->user->level, ['admin'])) return ['data'=>[],'code'=>403,'msg'=>lang('无权限！')];

        if (empty($param['name'])) return ['data'=>[],'code'=>400,'msg'=>lang('请提交需要获取的缓存名称，用参数 name 表示！')];
        else Cache::delete($param['name']);
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}
