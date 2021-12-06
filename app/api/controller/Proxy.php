<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\Request;
use think\facade\Cache;

class Proxy extends Base
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
        $method = ['get'];
        
        $mode   = (empty($param['mode'])) ? 'get' : $param['mode'];
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = ($mode == 'get') ? $this->curl($param, 'get') : $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->create($data, $msg, $code);
    }
    
    public function test($param)
    {
        return ['data'=>'$data','code'=>204,'msg'=>'$msg'];;
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
        $method = ['post'];
        
        $mode   = (empty($param['mode'])) ? 'post' : $param['mode'];
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = ($mode == 'post') ? $this->curl($param, 'post') : $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
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
        $param  = $request->param();
        
        if ($id == 'delete') $id = 'del';
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        // 存在的方法
        $curl = ['get','post','put','del','patch'];
        $method = [];
        
        // 动态方法且方法存在
        if (in_array($id, $curl)) $result = $this->curl($param, $id);
        else if (in_array($id, $method)) $result = $this->$id($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->create($data, $msg, $code);
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function update(Request $request)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['put'];
        
        $mode   = (empty($param['mode'])) ? 'put' : $param['mode'];
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = ($mode == 'put') ? $this->curl($param, 'put') : $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->create($data, $msg, $code);
    }

    /**
     * 删除指定资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function delete(Request $request)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['del'];
        
        $mode   = (empty($param['mode'])) ? 'del' : $param['mode'];
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = ($mode == 'del') ? $this->curl($param, 'del') : $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->create($data, $msg, $code);
    }
    
    /**
     * 补丁指定资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function patches(Request $request)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['patch'];
        
        $mode   = (empty($param['mode'])) ? 'patch' : $param['mode'];
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = ($mode == 'patch') ? $this->curl($param, 'patch') : $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->create($data, $msg, $code);
    }
    
    public function curl($param, $method = 'get')
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        $url     = !empty($param['p_url']) ? $param['p_url'] : null;
        $headers = !empty($param['p_headers']) ? $param['p_headers'] : [];
        
        // 处理ThinkPHP占用name参数问题
        if (!empty($param['p_name'])) {
            $param['name'] = $param['p_name'];
            unset($param['p_name']);
        } else unset($param['name']);
        
        if (empty($url)) $msg = '参数p_url（需要代理请求的URL地址）不得为空！';
        else {
            
            $code = 200;
            unset($param['p_url']);
            unset($param['p_headers']);
            
            $data = $this->helper->$method($url, $param, $headers);
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}