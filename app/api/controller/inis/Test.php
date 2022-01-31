<?php
declare (strict_types = 1);

namespace app\api\controller\inis;

use think\Request;
use inis\utils\{helper};
use app\index\controller\Tool;
use think\facade\{Db, Cache, Config, Validate, Filesystem, Log};
use app\model\mysql\{Users, Links, Banner, Visit, Article, Comments};

class Test extends Base
{
    /**
     * 显示资源列表
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function index(Request $request)
    {
        
        $data = [];
        $code = 200;
        $msg  = 'ok';
        
        // 获取请求参数
        $param = $request->param();
        
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
        $data = [];
        $code = 200;
        $msg  = 'ok';
        
        // 获取请求参数
        $param = $request->param();
        
        return $this->create($param, $msg, $code, ['method'=>'POST']);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $IID
     * @return \think\Response
     */
    public function read(Request $request, $IID)
    {
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        // 获取请求参数
        $param = $request->param();
        
        // 存在的方法
        $method = ['sql'];
        
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
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        
        // 获取请求参数
        $param = $request->param();
        
        return $this->create($param, $msg, $code, ['method'=>'PUT']);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $IID
     * @return \think\Response
     */
    public function delete(Request $request, $IID)
    {
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        
        // 获取请求参数
        $param = $request->param();
        
        return $this->create($param, $msg, $code, ['method'=>'DELETE']);
    }
    
    public function sql($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        return ['data'=>$param,'code'=>$code,'msg'=>$msg];
    }
}
