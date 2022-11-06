<?php
declare (strict_types = 1);

namespace app\api\controller\default;

use think\Request;
use think\facade\{Cache, Lang};
use app\model\mysql\{Links as LinksModel, LinksSort};

class Links extends Base
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
        $method = ['one','all'];
        
        $mode   = (empty($param['id'])) ? 'all' : 'one';
        
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
        $method = ['save','remove','apply'];

        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 清除缓存
        Cache::tag('links')->clear();
        
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
        $data   = [];
        $code   = 400;
        $msg    = lang('参数不存在！');
        $result = [];
        
        // 获取请求参数
        $param = $request->param();

        // 存在的方法
        $method = ['sql','one','all'];
        
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
        Cache::tag('links')->clear();
        
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
        Cache::tag('links')->clear();
        
        return $this->json($data, $msg, $code);
    }
    
    // 获取一条数据
    public function one($param)
    {
        $data = [];
        $code = 400;
        $msg  = lang('无数据！');
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'links'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            // 获取数据库数据
            $data = LinksModel::ExpandAll($param['id']);
            if ($this->ApiCache) Cache::tag(['links',$cache_name])->set($cache_name, json_encode($data));
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
        $cache_name = json_encode(array_merge(['IAPI'=>'links'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            
            // 获取数据库数据
            $data = LinksModel::ExpandAll(null, $opt);
            if ($this->ApiCache) Cache::tag(['links'])->set($cache_name, json_encode($data));
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
        $obtain = ['name','url','head_img','description','sort_id','is_show','opt','longtext'];
        
        if (empty($param['id'])) $item = new LinksModel;
        else {
            $item = LinksModel::findOrEmpty((int)$param['id']);
            if ($item->isEmpty()) return ['data'=>[],'code'=>204,'msg'=>lang('无数据！')];
        }
        
        // 解决 TP6 抢占 name 参数的问题
        if (!empty($param['named'])) $param['name'] = $param['named'];

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
            $code = 200;
            $item->save();
            $data = (int)$item->id;
        }
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }

    // 申请友链
    public function apply($param)
    {
        $data   = ['申请友链'];
        $code   = 400;
        $msg    = lang('成功！');

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
        
        // // 允许用户提交并存储的字段
        // $obtain = ['name','url','head_img','description','sort_id','is_show'];

        // $data   = LinksSort::select();
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
                LinksModel::destroy($id);
            }
        }
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }
    
    // SQL接口
    public function sql($param)
    {
        $where   = (empty($param['where']))   ? [] : $param['where'];
        $whereOr = (empty($param['whereOr'])) ? [] : $param['whereOr'];
        $page    = (!empty($param['page']))   ? $param['page']  : 1;
        $limit   = (!empty($param['limit']))  ? $param['limit'] : 5;
        $order   = (!empty($param['order']))  ? $param['order'] : 'create_time desc';
        
        $data = [];
        $code = 200;
        $msg  = lang('成功！');
        
        $opt  = [
            'page' => $page,
            'limit'=> $limit,
            'order'=> $order,
            'where'=> $where,
            'whereOr'=> $whereOr,
        ];
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'links/sql'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            $data = LinksModel::ExpandAll(null, $opt);
            if ($this->ApiCache) Cache::tag(['links',$cache_name])->set($cache_name, json_encode($data));
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}
