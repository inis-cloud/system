<?php
declare (strict_types = 1);

namespace app\api\controller\inis;

use think\Request;
use think\facade\{Cache, Lang};
use app\model\mysql\{Options as OptionsModel};

class Options extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = lang('参数不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['one','all'];
        
        $mode   = (empty($param['key'])) ? 'all' : 'one';
        
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
        $method = ['save','remove'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 清除缓存
        Cache::tag('options')->clear();
        
        return $this->json($data, $msg, $code);
    }

    /**
     * 显示指定的资源
     *
     * @param  string  $IID
     * @return \think\Response
     */
    public function IGET(Request $request, $IID)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = lang('参数不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['one','all','list'];
        
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
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = lang('参数不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['save'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 清除缓存
        Cache::tag('options')->clear();
        
        return $this->json($data, $msg, $code);
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
        $msg    = lang('参数不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['remove'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 清除缓存
        Cache::tag('options')->clear();
        
        return $this->json($data, $msg, $code);
    }
    
    // 获取一条数据
    public function one($param)
    {
        $data = [];
        $code = 400;
        $msg  = lang('无数据！');
        
        $key = (!empty($param['key'])) ? $param['key'] : '';
        
        // 禁止访问字段
        $prohibit = ['config:security','config:email-serve'];
        
        if (in_array($key, $prohibit)) {
            
            $data = [];
            $msg  = '未经授权！';
            $code = 403;
            
        } else {
            
            // 设置缓存名称
            $cache_name = json_encode(array_merge(['IAPI'=>'options'], $param));
            
            // 检查是否存在请求的缓存数据
            if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
            else {
                // 获取数据库数据
                $data = OptionsModel::where('keys', $key)->find();
                if ($this->ApiCache) Cache::tag(['options',$cache_name])->set($cache_name, json_encode($data));
            }
            
            $code = 200;
            $msg  = lang('无数据！');
            // 逆向思维，节省代码行数
            if (empty($data)) $code = 204;
            else $msg = lang('成功！');
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取全部数据
    public function all($param)
    {
        $data = [];
        $code = 400;
        $msg  = lang('无数据！');
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'options'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            
            // 站点信息 和 作者信息
            $array = ['site','webmaster'];
            
            foreach ($array as $val) {
                
                $item = OptionsModel::where(['keys'=>$val])->findOrEmpty();
                
                if (!$item->isEmpty()) {
                    unset($item['keys']);
                    $data[$val] = $item;
                } else $data[$val] = [];
            }
            
            if ($this->ApiCache) Cache::tag(['options'])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = lang('无数据！');
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = lang('成功！');
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 新增或者修改数据
    public function save($param)
    {
        $data = [];
        $code = 400;
        $msg  = lang('成功！');
        
        $opt   = !empty($param['opt'])   ? $param['opt']   : null;
        $keys  = !empty($param['keys'])  ? $param['keys']  : null;
        $value = !empty($param['value']) ? $param['value'] : null;
        
        // 是否覆盖
        $cover = !empty($param['cover']) ? (($param['cover'] == 'true') ? true : false) : false;
        
        if (empty($keys)) $msg = lang('请提交 keys！');
        else {
            
            $options = OptionsModel::where(['keys'=>$keys])->findOrEmpty();
            
            // 不存在 - 新增
            if ($options->isEmpty()) $options = new OptionsModel;
            
            // 字符串格式对象转数组
            // if (is_string($opt)) {
            //     $opt  = json_decode(str_replace(["'"], ["\""], $opt));
            //     if (empty($opt)) $msg = lang('参数 opt 字符串格式的对象格式有错误，请检查。正确格式如："{"test":"测试","other":"其他"}" <--长得像对象的字符串！');
            // }

            // 字符对象
            if (!empty($opt)) {
                // 校验字符串对象合法性
                $opt = is_string($opt) ? $this->helper->stringJson($opt) : $opt;
                // 不编码中文
                $opt = json_decode(json_encode($opt, JSON_UNESCAPED_UNICODE), true);
            }
            
            // 最终的JSON数据
            $json = array_merge(json_decode(json_encode($options->opt, JSON_UNESCAPED_UNICODE), true) ?? [], $opt);
            
            // 覆盖
            if ($cover) $json = $opt;
            
            // 处理 value 数据
            if (!$options->isEmpty()) $value = is_null($value) ? $options->value : $value;
            if ($options->isEmpty())  $options->keys = $keys;
            
            $options->opt   = json_encode($json, JSON_UNESCAPED_UNICODE);
            $options->value = $value;
            
            // 是否拥有权限
            if (in_array(request()->user->level, ['admin'])) {
                $code = 200;
                $options->save();
            } else {
                $code = 403;
                $msg  = lang('无权限！');
            }
        }
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }
    
    // 删除数据
    public function remove($param)
    {
        $data = [];
        $code = 400;
        $msg  = lang('成功！');
        
        $keys = !empty($param['keys']) ? $param['keys']  : null;
        
        if (empty($keys)) $msg = lang('请提交 keys！');
        else {
            
            $options = OptionsModel::where(['keys'=>$keys])->findOrEmpty();
            
            // 存在该条数据
            if (!$options->isEmpty() and in_array(request()->user->level, ['admin'])) {
                
                $code = 200;
                $options->delete();
                
            } else if ($options->isEmpty()) {
                $code = 204;
                $msg  = lang('无数据！');
            } else {
                $code = 403;
                $msg  = lang('无权限！');
            }
        }
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }
    
    // 配置字段列表
    public function list($param)
    {
        $data = [];
        $code = 400;
        $msg  = lang('无数据！');
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'options/list'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            // 获取数据库数据
            $data = OptionsModel::column('keys');
            if ($this->ApiCache) Cache::tag(['options',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = lang('无数据！');
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = lang('成功！');
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}
