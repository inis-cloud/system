<?php
declare (strict_types = 1);

namespace app\api\controller\default;

use think\Request;
use think\facade\{Cache, Lang};

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
        $msg    = Lang::get('参数不存在！');
        $result = [];
        
        $mode   = !empty($param['mode']) ? $param['mode'] : 'ip';
        
        // 存在的方法
        $method = ['ip'];
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($request);
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
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = Lang::get('参数不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['ip','geocode','weather'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($request);
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
    
    // IP获取定位信息
    public function ip($request)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data = [];
        $code = 400;
        $msg  = Lang::get('无数据！');
        
        $ip   = !empty($param['ip']) ? $param['ip'] : $this->utils->get->ip()['ip'];
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'location'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name), true);
        else {
            
            // 获取数据
            $result = $this->utils->curl->get($this->config['official']['api'] . 'gothe', ['ip'=>$ip], ['origin'=>$this->helper->domain()]);
            
            if ($result['code'] == 200) $data = $result['data'];
            else $data = $result;
            
            if ($this->ApiCache) Cache::tag(['gothe', $cache_name])->set($cache_name, json_encode($data));
            
        }
        
        $code = 200;
        $msg  = Lang::get('无数据！');
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = Lang::get('数据请求成功！');
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 经纬度获取定位信息
    public function geocode($request)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data = [];
        $code = 400;
        $msg  = Lang::get('无数据！');
        
        // 判断有没有提交经纬度信息，如果没有，从IP中获取经纬度信息
        $location  = !empty($param['location']) ? $param['location'] : (($this->ip($request)['code'] == 200) ? $this->ip($request)['data']['location'] : null);
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'geocode'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name), true);
        else {
            
            // 获取数据
            $result = $this->helper->get($this->config['official']['api'] . 'gothe', [
                'mode'    => 'geocode',
                'location'=> $location
            ], ['origin'=>$this->helper->domain()]);
            
            if ($result['code'] == 200) $data = $result['data'];
            else $data = $result;
            
            if ($this->ApiCache) Cache::tag(['gothe',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = Lang::get('无数据！');
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = Lang::get('数据请求成功！');
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取天气信息
    public function weather($request)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data = [];
        $code = 400;
        $msg  = Lang::get('无数据！');
        
        $type  = !empty($param['type']) ? $param['type'] : 'base';
        // 判断有没有提交城市编码信息，如果没有，通过IP定位获取城市编码
        $adcode= !empty($param['adcode']) ? $param['adcode'] : (($this->geocode($request)['code'] == 200) ? $this->geocode($request)['data']['regeocode']['addressComponent']['adcode'] : null);
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'weather'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name), true);
        else {
            
            // 获取数据
            $result = $this->helper->get($this->config['official']['api'] . 'gothe', [
                'mode'  => 'weather',
                'city'  => $adcode,
                'type'  => $type
            ], ['origin'=> $this->helper->domain()]);
            
            if ($result['code'] == 200) $data = $result['data'];
            else $data = $result;
            
            if ($this->ApiCache) Cache::tag(['gothe',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = Lang::get('无数据！');
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = Lang::get('数据请求成功！');
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}
