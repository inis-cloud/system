<?php
declare (strict_types = 1);

namespace app\api\controller\inis;

use think\Request;
use think\facade\{Cache, Lang};

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
        $msg    = Lang::get('方法不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['all','list','one'];
        
        $mode   = !empty($param['mode']) ? $param['mode'] : 'all';
        
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
        $msg    = Lang::get('方法不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['all','list','one'];
        
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
        //
    }
    
    // 获取单个表情包
    public function one($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        if (empty($param['name'])) $msg = Lang::get('请输入需要获取的表情包，用参数 name=<value> 表示！');
        else {
            
            // 设置缓存名称
            $cache_name = json_encode(array_merge(['IAPI'=>'emoji/one'], $param));
            
            // 检查是否存在请求的缓存数据
            if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
            else {
                $data = $this->files($param['name']);
                if ($this->ApiCache) Cache::tag(['emoji',$cache_name])->set($cache_name, json_encode($data));
            }
            
            $code = 200;
            $msg  = Lang::get('无数据！');
            // 逆向思维，节省代码行数
            if (empty($data)) $code = 204;
            else $msg = Lang::get('数据请求成功！');
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取全部表情包
    public function all($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'emoji/all'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            foreach ($this->dirs() as $val) $data = array_merge($data, $this->files($val));
            if ($this->ApiCache) Cache::tag(['emoji',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = Lang::get('无数据！');
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = Lang::get('数据请求成功！');
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 读取存在的表情包列表
    public function list($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'emoji/list'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            $data = array_merge($this->dirs());
            if ($this->ApiCache) Cache::tag(['emoji',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = Lang::get('无数据！');
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = Lang::get('数据请求成功！');
        
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
