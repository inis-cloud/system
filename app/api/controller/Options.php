<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\Request;
use think\facade\Cache;
use app\model\Options as OptionsModel;

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
        $param = $request->param();
        
        $key = (!empty($param['key'])) ? $param['key'] : '';
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        if (empty($key)) {
            
            // 设置缓存名称
            $cache_name = 'options';
            
            // 检查是否存在请求的缓存数据
            if (Cache::has($cache_name) and $api_cache and $cache == 'true') $data = json_decode(Cache::get($cache_name));
            else {
                // 获取数据库数据
                $opt = (new OptionsModel)->GetOpt();
                // 允许获取的默认字段
                $obtain = ['title','keywords','description','copy','site_ico','site_img','site_url'];
                // 遍历获取默认的字段
                foreach ($obtain as $val) $data[$val] = $opt[$val];
                Cache::tag(['options'])->set($cache_name, json_encode($data));
            }
            
            $code = 200;
            $msg  = '无数据！';
            // 逆向思维，节省代码行数
            if (empty($data)) $code = 204;
            else $msg = '数据请求成功！';
            
        } else {
            
            // 禁止访问字段
            $prohibit = ['site_conf','domain','email_serve'];
            
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
                    $data = OptionsModel::where('keys', $key)->findOrEmpty();
                    Cache::tag(['options',$cache_name])->set($cache_name, json_encode($data));
                }
                
                $code = 200;
                $msg  = '无数据！';
                // 逆向思维，节省代码行数
                if (empty($data)) $code = 204;
                else $msg = '数据请求成功！';
            }
        }
        
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
        $data   = [];
        $code   = 400;
        $msg    = 'ok';
        $result = [];
        
        // 获取请求参数
        $param = $request->param();
        
        // 解析用户 token
        $user = $this->parseJWT($param['login-token'])['data'];
        $mode = !empty($param['mode'])  ? $param['mode']  : null;
        
        // 新增或者修改数据
        if (empty($mode)) $result = $this->saves($param,$user);
        // 删除数据
        else if ($mode == 'delete') $result = $this->remove($param,$user);
        
        if (!empty($result)) {
            foreach ($result as $key => $val) $$key = $val;
        }
        
        // 清除缓存
        Cache::tag('options')->clear();
        
        return $this->create($data, $msg, $code);
    }

    /**
     * 显示指定的资源
     *
     * @param  string  $id
     * @return \think\Response
     */
    public function read(Request $request, $id)
    {
        
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
    
    // 新增或者修改数据
    public function saves($param, $user)
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
            if (in_array($user->level, ['admin'])) {
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
    public function remove($param, $user)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        $keys = !empty($param['keys']) ? $param['keys']  : null;
        
        if (empty($keys)) $msg = '请提交 keys';
        else {
            
            $options = OptionsModel::where(['keys'=>$keys])->findOrEmpty();
            
            // 存在该条数据
            if (!$options->isEmpty() and in_array($user->level, ['admin'])) {
                
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
}
