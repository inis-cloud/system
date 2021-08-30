<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\Request;
use think\facade\Cache;

class Other extends Base
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
        // $result = [];
        
        // $mode   = !empty($param['mode']) ? $param['mode'] : 'location';
        
        // // 存在的方法
        // $method = ['location'];
        
        // // 动态方法且方法存在
        // if (in_array($mode, $method)) $result = $this->$mode($request);
        // // 动态返回结果
        // if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
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
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['ua'];
        
        // 动态方法且方法存在
        if (in_array($id, $method)) $result = $this->$id($request);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
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
    
    // 获取客户端信息
    public function ua($request)
    {
        // 获取请求参数
        $param  = $request->param();
        $header = $request->header();
        
        $data = [];
        $code = 400;
        $msg  = '无数据！';
        
        $ua   = !empty($param['ua']) ? $param['ua'] : (!empty($header['user-agent']) ? $header['user-agent'] : null);
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        // 设置缓存名称
        $cache_name = 'other?ua='.$ua;
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
        
            if (!empty($ua)) {
                $data = [
                    'os'      => $this->helper->GetClientOS($ua),
                    'mobile'  => $this->helper->GetClientMobile($ua),
                    'browser' => $this->helper->GetClientBrowser($ua),
                ];
                $code = 200;
            } else $msg = 'header缺少user-agent信息';
            
            Cache::tag(['other',$cache_name])->set($cache_name, json_encode($data));
        }
        
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
}
