<?php
declare (strict_types = 1);

namespace app\api\controller\inis;

use think\Request;
use think\facade\{Cache};

class Location extends Base
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
        
        $mode   = !empty($param['mode']) ? $param['mode'] : 'location';
        
        // 存在的方法
        $method = ['location'];
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($request);
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
        $msg    = '参数不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['geocode','weather'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($request);
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
    
    // IP获取定位信息
    public function location($request)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data = [];
        $code = 400;
        $msg  = '无数据';
        
        $ip   = !empty($param['ip']) ? $param['ip'] : $this->helper->GetClientIP();
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        // 设置缓存名称
        $cache_name = 'location?ip='.$ip;
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name), true);
        else {
            
            // 获取数据
            $result = $this->helper->get($this->config['official']['api'] . 'gothe', ['ip'=>$ip], ['origin'=>$this->helper->domain()]);
            
            if ($result['code'] == 200) $data = $result['data'];
            else $data = $result;
            
            Cache::tag(['gothe',$cache_name])->set($cache_name, json_encode($data));
            
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 经纬度获取定位信息
    public function geocode($request)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data = [];
        $code = 400;
        $msg  = '无数据';
        
        // 判断有没有提交经纬度信息，如果没有，从IP中获取经纬度信息
        $location  = !empty($param['location']) ? $param['location'] : (($this->location($request)['code'] == 200) ? $this->location($request)['data']['location'] : null);
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        // 设置缓存名称
        $cache_name = 'geocode?location='.$location;
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name), true);
        else {
            
            // 获取数据
            $result = $this->helper->get($this->config['official']['api'] . 'gothe', [
                'mode'    => 'geocode',
                'location'=> $location
            ], ['origin'=>$this->helper->domain()]);
            
            if ($result['code'] == 200) $data = $result['data'];
            else $data = $result;
            
            Cache::tag(['gothe',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取天气信息
    public function weather($request)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data = [];
        $code = 400;
        $msg  = '无数据';
        
        $type  = !empty($param['type']) ? $param['type'] : 'base';
        // 判断有没有提交城市编码信息，如果没有，通过IP定位获取城市编码
        $adcode= !empty($param['adcode']) ? $param['adcode'] : (($this->geocode($request)['code'] == 200) ? $this->geocode($request)['data']['regeocode']['addressComponent']['adcode'] : null);
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        // 设置缓存名称
        $cache_name = 'weather?city='.$adcode.'&type='.$type;
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name), true);
        else {
            
            // 获取数据
            $result = $this->helper->get($this->config['official']['api'] . 'gothe', [
                'mode'  => 'weather',
                'city'  => $adcode,
                'type'  => $type
            ], ['origin'=> $this->helper->domain()]);
            
            if ($result['code'] == 200) $data = $result['data'];
            else $data = $result;
            
            Cache::tag(['gothe',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}
