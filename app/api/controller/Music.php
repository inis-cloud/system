<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\Request;
use inis\utils\helper;
use think\facade\Cache;
use inis\music\Music as iMusic;
use app\model\Music as MusicModel;

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
        $music  = new iMusic;
        $helper = new helper;
        
        // 获取请求参数
        $param = $request->param();
        
        if(empty($param['page']))  $param['page']  = 1;
        if(empty($param['limit'])) $param['limit'] = 5;
        if(empty($param['order'])) $param['order'] = 'create_time asc';
        if(empty($param['shuffle'])) $param['shuffle'] = 'false';
        
        $mode    = (empty($param['mode'])) ? '' : $param['mode'];
        $who     = (empty($param['who'])) ? 'tencent' : $param['who'];
        $shuffle = (empty($param['shuffle']) or $param['shuffle'] == 'false') ? false : true;
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        $opt = [
            'page'   =>  (int)$param['page'], 
            'limit'  =>  (int)$param['limit'],
            'order'  =>  (string)$param['order'],
        ];
        
        $data = [];
        
        if (empty($param['id'])) {
            
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
            
        } else {
            
            // 设置缓存名称
            $cache_name = 'music?id='.$param['id'].'&mode='.$mode;
            
            // 检查是否存在请求的缓存数据
            if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
            else {
                
                if (empty($mode)) {
                    
                    $data = MusicModel::ExpandAll($param['id']);
                    
                } else if ($mode == 'list') {
                    
                    $data['info'] = MusicModel::ExpandAll($param['id']);
                    
                    // 重定向URL
                    $music_url = $helper->GetRedirectUrl($data['info']['url']);
                    // 解析歌单ID
                    $music_id  = $helper->UrlPartsToArr($music_url)['id'];
                    // 获取歌单
                    $data['songs'] = $music->GetInfo($music_id, $data['info']['expand']['type'], 'collect', $shuffle);
                    
                } else if ($mode == 'song') {
                    
                    $data = $music->GetInfo($param['id'], $who, 'song');
                }
                
                Cache::tag(['music',$cache_name])->set($cache_name, json_encode($data));
            }
            
            $code = 200;
            $msg  = '无数据！';
            // 逆向思维，节省代码行数
            if (empty($data)) $code = 204;
            else $msg = '数据请求成功！';
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
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read(Request $request, $id)
    {
        //
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
}
