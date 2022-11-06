<?php
declare (strict_types = 1);

namespace app\api\controller\default;

use think\Request;
use think\facade\{Cache, Lang};
use inis\music\Music as iMusic;
use think\exception\ValidateException;
use app\validate\{Music as vMusic};
use app\model\mysql\{Music as MusicModel};

class Music extends Base
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
        $msg    = lang('参数不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['one','all','list','song'];
        
        $mode   = (empty($param['id'])) ? 'all' : ((isset($param['id']) and !isset($param['mode'])) ? 'one' : $param['mode']);
        
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
        
        if (!empty($param['mode'])) $IID = $param['mode'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 清除缓存
        Cache::tag('music')->clear();
        
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
        $msg    = lang('参数不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['one','all','list','song'];
        
        if (!empty($param['mode'])) $IID = $param['mode'];
        
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
        
        if (!empty($param['mode'])) $IID = $param['mode'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;

        // 清除缓存
        Cache::tag('music')->clear();
        
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

        if (!empty($param['mode'])) $IID = $param['mode'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;

        // 清除缓存
        Cache::tag('music')->clear();
        
        return $this->json($data, $msg, $code);
    }
    
    // 获取单条数据
    public function one($param)
    {
        $data = [];
        $code = 400;
        $msg  = lang('无数据！');
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'music'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            $data = MusicModel::ExpandAll($param['id']);
            if ($this->ApiCache) Cache::tag(['music',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = lang('无数据！');
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = lang('成功！');
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取全部数据
    public function all($param)
    {
        $data = [];
        $code = 400;
        $msg  = lang('无数据！');
        
        if (empty($param['page']))  $param['page']  = 1;
        if (empty($param['limit'])) $param['limit'] = 5;
        if (empty($param['order'])) $param['order'] = 'create_time asc';
        
        $opt = [
            'page'   =>  (int)$param['page'], 
            'limit'  =>  (int)$param['limit'],
            'order'  =>  (string)$param['order'],
        ];
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'music'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            
            // 获取数据库数据
            $data = MusicModel::ExpandAll(null, $opt);
            if ($this->ApiCache) Cache::tag(['music'])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = lang('无数据！');
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = lang('成功！');
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取播放列表数据
    public function list($param)
    {
        $data = [];
        $code = 400;
        $msg  = lang('无数据！');
        
        $music  = new iMusic;
        
        if (empty($param['shuffle'])) $param['shuffle'] = 'false';
        $shuffle = (empty($param['shuffle']) or $param['shuffle'] == 'false') ? false : true;
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'music'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            
            $data['info'] = MusicModel::ExpandAll($param['id']);
            
            // 重定向URL
            $music_url = $this->helper->GetRedirectUrl($data['info']['url']);
            // 解析歌单ID
            $array     = $this->helper->UrlPartsToArr($music_url);
            
            // 判断最终URL是否包含ID
            if (isset($array['id'])) $music_id = $array['id'];
            else {
                // URL不包含ID，获取URL末尾参数
                if (strpos($music_url, 'qq.com')) {
                    $array    = explode('/', $music_url);
                    $music_id = array_pop($array);
                }
            }
            // 获取歌单
            $data['songs'] = $music->GetInfo($music_id, $data['info']['expand']['type'], 'collect', $shuffle);
            
            if ($this->ApiCache) Cache::tag(['music',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = lang('无数据！');
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = lang('成功！');
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取播放地址
    public function song($param)
    {
        $data  = [];
        $code  = 400;
        $msg   = lang('无数据！');
        
        $music = new iMusic;
        
        $who   = (empty($param['who'])) ? 'tencent' : $param['who'];
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'music'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            $data = $music->GetInfo($param['id'], $who, 'song');
            if ($this->ApiCache) Cache::tag(['music',$cache_name])->set($cache_name, json_encode($data));
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
        $data   = [];
        $code   = 400;
        $msg    = lang('成功！');
        
        // 允许用户提交并存储的字段
        $obtain = ['title','description','url','head_img','is_show','opt','longtext'];

        if (empty($param['id'])) $item = new MusicModel;
        else {
            $item = MusicModel::findOrEmpty((int)$param['id']);
            if ($item->isEmpty()) return ['data'=>[],'code'=>204,'msg'=>lang('无数据！')];
        }

        try {

            // 修改数据
            if (isset($param['id'])) {
                // 数组合并不为空的数据
                $param = array_merge(array_filter($item->getData(), function($val){
                    return !empty($val);
                }), array_intersect_key($param, array_flip($obtain)));
            }
                
            validate(vMusic::class)->check($param);

            // 字符对象
            if (!empty($param['opt'])) {
                // 校验字符串对象合法性
                $param['opt'] = is_string($param['opt']) ? $this->helper->stringJson($param['opt']) : $param['opt'];
                // 不编码中文
                $param['opt'] = json_encode($param['opt'], JSON_UNESCAPED_UNICODE);
            }

            // 存储数据
            foreach ($param as $key => $val) {
                // 判断字段是否允许存储，防提权
                if (in_array($key, $obtain)) $item->$key = $val;
            }

            // 权限判断
            if (!in_array(request()->user->level, ['admin'])) $msg = lang('无权限！');
            else if (request()->user->status != 1) $msg = lang('账号被禁用！');
            else {

                $item->save();
                
                $code = 200;
                $data = (int)$item->id;
            }
            
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $msg  = $e->getError();
        }
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }

    // 删除数据
    public function remove($param)
    {
        $data = [];
        $code = 400;
        $msg  = lang('成功！');
        
        $id = !empty($param['id']) ? $param['id']  : null;
        
        if (empty($id)) $msg = lang('请提交 id！');
        else {
            
            $id = array_filter(explode(',', $id));

            // 权限判断
            if (!in_array(request()->user->level, ['admin'])) $msg = lang('无权限！');
            else if (request()->user->status != 1) $msg = lang('账号被禁用！');
            else {
                $code = 200;
                MusicModel::destroy($id);
            }
        }
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }
}
