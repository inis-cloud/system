<?php
declare (strict_types = 1);

namespace app\api\controller\inis;

use think\Request;
use think\facade\{Cache};
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
        $msg    = '参数不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['one','all'];
        
        $mode   = (empty($param['key'])) ? 'all' : 'one';
        
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
        
        $mode   = empty($param['mode']) ? 'saves' : $param['mode'];
        
        // 存在的方法
        $method = ['saves','remove'];
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 清除缓存
        Cache::tag('options')->clear();
        
        return $this->create($data, $msg, $code);
    }

    /**
     * 显示指定的资源
     *
     * @param  string  $IID
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
        $method = ['list'];
        
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
    
    // 获取一条数据
    public function one($param)
    {
        $data = [];
        $code = 400;
        $msg  = '无数据';
        
        $key = (!empty($param['key'])) ? $param['key'] : '';
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        // 禁止访问字段
        $prohibit = ['config:security','config:email-serve'];
        
        if (in_array($key, $prohibit)) {
            
            $data = [];
            $msg  = '未经授权！';
            $code = 403;
            
        } else {
            
            // 设置缓存名称
            $cache_name = 'options?key='.$key;
            
            // 检查是否存在请求的缓存数据
            if (Cache::has($cache_name) and $api_cache and $cache == 'true') $data = json_decode(Cache::get($cache_name));
            else {
                // 获取数据库数据
                $data = OptionsModel::where('keys', $key)->find();
                Cache::tag(['options',$cache_name])->set($cache_name, json_encode($data));
            }
            
            $code = 200;
            $msg  = '无数据！';
            // 逆向思维，节省代码行数
            if (empty($data)) $code = 204;
            else $msg = '数据请求成功！';
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取全部数据
    public function all($param)
    {
        $data = [];
        $code = 400;
        $msg  = '无数据';
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        // 设置缓存名称
        $cache_name = 'options';
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache == 'true') $data = json_decode(Cache::get($cache_name));
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
            
            Cache::tag(['options'])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 新增或者修改数据
    public function saves($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        $opt   = !empty($param['opt'])   ? $param['opt']   : null;
        $keys  = !empty($param['keys'])  ? $param['keys']  : null;
        $value = !empty($param['value']) ? $param['value'] : null;
        
        // 是否覆盖
        $cover = !empty($param['cover']) ? (($param['cover'] == 'true') ? true : false) : false;
        
        if (empty($keys)) $msg = '请提交 keys';
        else {
            
            $options = OptionsModel::where(['keys'=>$keys])->findOrEmpty();
            
            // 不存在 - 新增
            if ($options->isEmpty()) $options = new OptionsModel;
            
            // 字符串格式对象转数组
            if (is_string($opt)) {
                $opt  = json_decode(str_replace(["'"], ["\""], $opt));
                if (empty($opt)) $msg = '参数 opt 字符串格式的对象格式有错误，请检查。正确格式如："{"test":"测试","other":"其他"}" <--长得像对象的字符串';
            }
            
            // 最终的JSON数据
            $json = [];
            
            // 修改 且 JSON 数据不为空
            if (!$options->isEmpty() and !empty($opt)) {
                
                $json = $options->opt;
                foreach ($opt as $key => $val) $json->$key = $val;
                
            } else $json = $opt;
            
            // 覆盖
            if ($cover) $json = $opt;
            
            // 处理 value 数据
            if (!$options->isEmpty()) $value = is_null($value) ? $options->value : $value;
            if ($options->isEmpty())  $options->keys = $keys;
            
            $options->opt   = json_encode($json, JSON_UNESCAPED_UNICODE);
            $options->value = $value;
            
            // 是否拥有权限
            if (in_array($this->user['data']->level, ['admin'])) {
                $code = 200;
                $options->save();
            } else {
                $code = 403;
                $msg  = '无权限';
            }
        }
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }
    
    // 删除数据
    public function remove($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        $keys = !empty($param['keys']) ? $param['keys']  : null;
        
        if (empty($keys)) $msg = '请提交 keys';
        else {
            
            $options = OptionsModel::where(['keys'=>$keys])->findOrEmpty();
            
            // 存在该条数据
            if (!$options->isEmpty() and in_array($this->user['data']->level, ['admin'])) {
                
                $code = 200;
                $options->delete();
                
            } else if ($options->isEmpty()) {
                $code = 204;
                $msg  = '无数据';
            } else {
                $code = 403;
                $msg  = '无权限';
            }
        }
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }
    
    // 配置字段列表
    public function list($param)
    {
        $data = [];
        $code = 400;
        $msg  = '无数据';
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        // 设置缓存名称
        $cache_name = 'options/list';
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache == 'true') $data = json_decode(Cache::get($cache_name));
        else {
            // 获取数据库数据
            $data = OptionsModel::column('keys');
            Cache::tag(['options',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}
