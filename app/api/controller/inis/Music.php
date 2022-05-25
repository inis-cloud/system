<?php
declare (strict_types = 1);

namespace app\api\controller\inis;

use think\Request;
use think\facade\{Cache};
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
        $msg    = '参数不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['one','all','list','song'];
        
        $mode   = (empty($param['id'])) ? 'all' : ((isset($param['id']) and !isset($param['mode'])) ? 'one' : $param['mode']);
        
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
        
        // 存在的方法
        $method = ['saves','remove'];
        
        $mode   = !empty($param['mode']) ? $param['mode']  : 'saves';
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 清除缓存
        Cache::tag('music')->clear();
        
        return $this->create($data, $msg, $code);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $IID
     * @return \think\Response
     */
    public function read(Request $request, $IID)
    {
        //
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
    
    // 获取单条数据
    public function one($param)
    {
        $data = [];
        $code = 400;
        $msg  = '无数据';
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        // 设置缓存名称
        $cache_name = 'music?id='.$param['id'];
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
            $data = MusicModel::ExpandAll($param['id']);
            Cache::tag(['music',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取全部数据
    public function all($param)
    {
        $data = [];
        $code = 400;
        $msg  = '无数据';
        
        if (empty($param['page']))  $param['page']  = 1;
        if (empty($param['limit'])) $param['limit'] = 5;
        if (empty($param['order'])) $param['order'] = 'create_time asc';
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        $opt = [
            'page'   =>  (int)$param['page'], 
            'limit'  =>  (int)$param['limit'],
            'order'  =>  (string)$param['order'],
        ];
        
        // 设置缓存名称
        $cache_name = 'music?page='.$param['page'].'&limit='.$param['limit'].'&order='.$param['order'];
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
            
            // 获取数据库数据
            $data = MusicModel::ExpandAll(null, $opt);
            Cache::tag(['music'])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取播放列表数据
    public function list($param)
    {
        $data = [];
        $code = 400;
        $msg  = '无数据';
        
        $music  = new iMusic;
        
        if (empty($param['shuffle'])) $param['shuffle'] = 'false';
        $shuffle = (empty($param['shuffle']) or $param['shuffle'] == 'false') ? false : true;
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        
        // 设置缓存名称
        $cache_name = 'music?id='.$param['id'];
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
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
            
            Cache::tag(['music',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = '无数据！';
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取播放地址
    public function song($param)
    {
        $data  = [];
        $code  = 400;
        $msg   = '无数据';
        
        $music = new iMusic;
        
        $who   = (empty($param['who'])) ? 'tencent' : $param['who'];
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        // 设置缓存名称
        $cache_name = 'music?id='.$param['id'].'&who='.$who;
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
            $data = $music->GetInfo($param['id'], $who, 'song');
            Cache::tag(['music',$cache_name])->set($cache_name, json_encode($data));
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
        $data   = [];
        $code   = 400;
        $msg    = 'ok';
        
        // 允许用户提交并存储的字段
        $obtain = ['title','description','url','head_img','is_show','opt','longtext'];
        $item   = isset($param['id']) ? MusicModel::findOrEmpty((int)$param['id']) : new MusicModel;

        try {

            // 修改数据
            if (isset($param['id'])) {
                // 数组合并不为空的数据
                $param = array_merge(array_filter($item->getData(), function($val){
                    return !empty($val);
                }), array_intersect_key($param, array_flip($obtain)));
            }
                
            validate(vMusic::class)->check($param);

            // 存储数据
            foreach ($param as $key => $val) {
                // 判断字段是否允许存储，防提权
                if (in_array($key, $obtain)) {
                    if ($key == 'opt') $item->opt = json_encode($val, JSON_UNESCAPED_UNICODE);
                    else $item->$key = $val;
                }
            }

            // 权限判断
            if (!in_array($this->user['data']->level, ['admin'])) $msg = '无权限';
            else if ($this->user['data']->status != 1) $msg = '账号被禁用';
            else {
                $code = 200;
                $item->save();
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
        $msg  = 'ok';
        
        $id = !empty($param['id']) ? $param['id']  : null;
        
        if (empty($id)) $msg = '请提交 id';
        else {
            
            $id = array_filter(explode(',', $id));

            // 权限判断
            if (!in_array($this->user['data']->level, ['admin'])) $msg = '无权限';
            else if ($this->user['data']->status != 1) $msg = '账号被禁用';
            else {
                $code = 200;
                MusicModel::destroy($id);
            }
        }
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }
}
