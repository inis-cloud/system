<?php
declare (strict_types = 1);

namespace app\api\controller\sql;

use think\Request;
use think\facade\{Cache};
use app\model\sql\{Article as Model};

class Article extends Base
{
    protected $middleware = [];

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
        $method = ['save','remove'];

        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 清除缓存
        Cache::tag('article')->clear();
        
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
        $method = ['find','select'];
        
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
        Cache::tag('article')->clear();
        
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
        Cache::tag('article')->clear();
        
        return $this->json($data, $msg, $code);
    }
    
    // 获取一条数据
    public function one($param)
    {
        $data = [];
        $code = 400;
        $msg  = lang('无数据！');
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取全部数据
    public function select($param)
    {
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'article'], $this->param()));
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->Cache) $result = Cache::get($cache_name);
        else {

            // 强制性只能获取自己的数据
            // $where  = !empty($this->param('where', [])) ? $this->param('where', []) : [];
            // $where  = array_merge($where, [['uid', 'like', request()->user->id]]);
            // $where  = request()->user->level != 'admin' ? $where : $this->param('where', []);
            $result = Model::all($this->param('ids', null), [
                'page'        => $this->param('page'   , 1),
                'limit'       => $this->param('limit'  , 10),
                'where'       => $this->param('where', []),
                'field'       => $this->param('field'  , []),
                'whereOr'     => $this->param('whereOr', []),
                'order'       => $this->param('order'  , 'id asc'),
                'withoutField'=> $this->param('withoutField', []),
                'withTrashed' => $this->param('withTrashed', false),
                'onlyTrashed' => $this->param('onlyTrashed', false),
                'whereNull'   => $this->param('whereNull'),
                'whereNotNull'=> $this->param('whereNotNull'),
            ]);

            $result = json_encode($result, JSON_UNESCAPED_UNICODE);
            if ($this->Cache) Cache::tag(['article'])->set($cache_name, $result);
        }

        $data = json_decode($result, true);

        $code = 200;
        $msg  = lang('无数据！');
        // 逆向思维，节省代码行数
        if (empty($data['data'])) $code = 204;
        else $msg  = lang('不客气呀！OK 啦！');

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}
