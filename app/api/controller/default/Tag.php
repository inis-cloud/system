<?php
declare (strict_types = 1);

namespace app\api\controller\default;

use think\Request;
use think\facade\{Cache, Lang};
use app\model\mysql\{Tag as TagModel};

class Tag extends Base
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
        
        // 存在的方法
        $method = ['article','all'];
        
        $mode = (!isset($param['id']) and !isset($param['name'])) ? 'all' : 'article';
        
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
        $msg    = Lang::get('参数不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['save','remove'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 清除缓存
        Cache::tag('tag')->clear();
        
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
        $msg    = Lang::get('参数不存在！');
        $result = [];
        
        // 获取请求参数
        $param = $request->param();
        
        // 存在的方法
        $method = ['one','all','sql','article'];
        
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
        $data   = [];
        $code   = 400;
        $msg    = Lang::get('参数不存在！');
        $result = [];
        
        // 获取请求参数
        $param = $request->param();
        
        // 存在的方法
        $method = ['save'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
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
        $data   = [];
        $code   = 400;
        $msg    = Lang::get('参数不存在！');
        $result = [];
        
        // 获取请求参数
        $param = $request->param();
        
        // 存在的方法
        $method = ['remove'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->json($data, $msg, $code);
    }
    
    // 获取标签下的文章
    public function article($param)
    {
        $data = [];
        $code = 400;
        $msg  = Lang::get('无数据！');
        
        if (empty($param['page']))  $param['page']  = 1;
        if (empty($param['limit'])) $param['limit'] = 5;
        if (empty($param['order'])) $param['order'] = 'create_time desc';
        
        $where = [];
        // 允许通过 where 进行查询的默认字段
        $whereFind = ['id','name'];
        foreach ($param as $key => $val) if (in_array($key, $whereFind)) $where[] = [$key,'=',$val];
        
        $opt = [
            'page'   =>  (int)$param['page'], 
            'limit'  =>  (int)$param['limit'],
            'order'  =>  (string)$param['order'],
            'where'  =>  $where
        ];
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'tag','where'=>$where], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            
            // 获取数据库数据
            $data = TagModel::article(null, $opt);
            
            // 屏蔽密码
            if (!empty($data['expand']['data'])) foreach ($data['expand']['data'] as $key => $val) {
                if (!empty($val['opt']) and isset($val['opt']->password)) unset($val['opt']->password);
            }
            
            if ($this->ApiCache) Cache::tag(['tag',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = Lang::get('无数据！');
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = Lang::get('成功！');
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取全部数据
    public function all($param)
    {
        $data = [];
        $code = 400;
        $msg  = Lang::get('无数据！');
        
        if (empty($param['page']))  $param['page']  = 1;
        if (empty($param['limit'])) $param['limit'] = 5;
        if (empty($param['order'])) $param['order'] = 'create_time desc';
        
        $opt = [
            'page'   =>  (int)$param['page'], 
            'limit'  =>  (int)$param['limit'],
            'order'  =>  (string)$param['order'],
        ];
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'tag'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            // 获取数据库数据
            $data = TagModel::ExpandAll(null, $opt);
            if ($this->ApiCache) Cache::tag(['tag'])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = Lang::get('无数据！');
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = Lang::get('成功！');
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 获取一条数据
    public function one($param)
    {
        $data = [];
        $code = 400;
        $msg  = Lang::get('无数据！');

        if (empty($param['id'])) return ['data'=>[],'code'=>400,'msg'=>lang('请提交 id！')];
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'tag'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            // 获取数据库数据
            $data = TagModel::ExpandAll((int)$param['id']);
            if ($this->ApiCache) Cache::tag(['tag',$cache_name])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = Lang::get('无数据！');
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = Lang::get('成功！');
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 保存数据
    public function save($param)
    {
        $data = [];
        $code = 200;
        $msg  = Lang::get('成功！');
        
        // 允许用户提交并存储的字段
        $obtain = ['id','name','is_show','opt','named','longtext'];
        
        if (empty($param['id'])) $item = new TagModel;
        else {
            $item = TagModel::findOrEmpty((int)$param['id']);
            if ($item->isEmpty()) return ['data'=>[],'code'=>204,'msg'=>lang('无数据！')];
        }

        // 字符对象
        if (!empty($param['opt'])) {
            // 校验字符串对象合法性
            $param['opt'] = is_string($param['opt']) ? $this->helper->stringJson($param['opt']) : $param['opt'];
            // 不编码中文
            $param['opt'] = json_encode($param['opt'], JSON_UNESCAPED_UNICODE);
        }

        // 判断字段是否允许存储，防提权
        foreach ($param as $key => $val) if (in_array($key, $obtain)) {
            // 分类ID转字符串存储
            if ($key == 'named') $item->name = $val;
            else $item->$key = $val;
        }
        
        $item->save();

        $data = (int)$item->id;
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 删除数据
    public function remove($param)
    {
        $data = [];
        $code = 400;
        $msg  = Lang::get('成功！');
        
        if (empty($param['id'])) $msg = Lang::get('请提交需要删除的标签ID！');
        else {
            
            $id = array_filter(explode(',', $param['id']));
            
            TagModel::destroy($id);
            
            $code = 200;
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
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
        $msg  = Lang::get('成功！');
        
        $opt  = [
            'page' => $page,
            'limit'=> $limit,
            'order'=> $order,
            'where'=> $where,
            'whereOr'=> $whereOr,
        ];
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'tag/sql','where'=>$where], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            $data = TagModel::ExpandAll(null, $opt);
            if ($this->ApiCache) Cache::tag(['tag',$cache_name])->set($cache_name, json_encode($data));
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}
