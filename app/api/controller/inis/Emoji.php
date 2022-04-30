<?php
declare (strict_types = 1);

namespace app\api\controller\inis;

use think\Request;
use think\facade\{Cache};

class Emoji extends Base
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
        $method = ['all','list','one'];
        
        $mode   = !empty($param['mode']) ? $param['mode'] : 'all';
        
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
        // $param  = $request->param();
        
        // $data   = [];
        // $code   = 400;
        // $msg    = '参数不存在！';
        // $result = [];
        
        // // 存在的方法
        // $method = [];
        
        // $mode   = !empty($param['mode']) ? $param['mode']  : 'set';
        
        // // 动态方法且方法存在
        // if (in_array($mode, $method)) $result = $this->$mode($param);
        // // 动态返回结果
        // if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // return $this->create($data, $msg, $code);
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
        $method = ['all','list','one'];
        
        if (empty($IID)) $IID = 'all';
        
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
    
    // 获取单个表情包
    public function one($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        if (empty($param['name'])) $msg = '请输入需要获取的表情包，用参数 name=<value> 表示';
        else {
            
            // 设置缓存名称
            $cache_name = 'emoji/one?name=' . $param['name'];
            
            // 检查是否存在请求的缓存数据
            if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
            else {
                $data = $this->files($param['name']);
                Cache::tag(['emoji',$cache_name])->set($cache_name, json_encode($data));
            }
            
            $code = 200;
            $msg  = '无数据！';
            // 逆向思维，节省代码行数
            if (empty($data)) $code = 204;
            else $msg = '数据请求成功！';
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取全部表情包
    public function all($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        // 设置缓存名称
        $cache_name = 'emoji/all';
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
            foreach ($this->dirs() as $val) $data = array_merge($data, $this->files($val));
            Cache::tag(['emoji',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 读取存在的表情包列表
    public function list($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        // 设置缓存名称
        $cache_name = 'emoji/list';
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
            $data = $this->dirs();
            Cache::tag(['emoji',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 读取目录下的全部文件夹
    public function dirs($path = 'storage/random/emoji')
    {
        return array_filter($this->File->getDir($path)['dir'], function ($v) {
            return $v != '.' && $v != '..';
        });
    }
    
    // 读取目录下的全部文件
    public function files($name = '', $prefix = 'storage/random/emoji/')
    {
        $result = [];
        $domain = $this->helper->domain();
        $files  = $this->File->getDir($prefix . $name)['file'];
        foreach ($files as $val) {
            $item = explode('.', $val)[0];
            $url  = $domain . '/' . $prefix.$name.'/'.$val;
            $result[$item] = $url;
        }
        return $result;
    }
}
