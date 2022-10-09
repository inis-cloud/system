<?php
declare (strict_types = 1);

namespace app\api\controller\inis;

use think\Request;
use inis\utils\{helper, utils};
use app\admin\controller\Tool;
use think\facade\{Db, Cache, Config, Validate, Filesystem, Log, Lang};
use app\model\mysql\{Users, Links, Banner, Visit, Article, Comments};

class Test extends Base
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
        
        $data = [];
        $code = 201;
        $msg  = Lang::get('数据请求成功！');
        
        // 获取请求参数
        $param = $request->param();
        $header= $request->header();

        $data = [
            'param' => $param,
            'header'=> $header,
        ];

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
        $param = $request->param();

        $data   = [
            'param' => $param,
            'header'=> $request->header(),
        ];
        $code   = 400;
        $msg    = Lang::get('ok！');
        
        return $this->json($data, $msg, $code, ['method'=>'POST']);
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
        $param = $request->param();

        $data   = [$IID];
        $code   = 400;
        $msg    = Lang::get('参数不存在！');
        
        return $this->json($param, $msg, $code, ['method'=>'GET']);
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
        $param = $request->param();

        $data   = [$IID];
        $code   = 400;
        $msg    = Lang::get('参数不存在！');
        
        return $this->json($param, $msg, $code, ['method'=>'PUT']);
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
        $param = $request->param();

        $data   = [$IID];
        $code   = 400;
        $msg    = Lang::get('参数不存在！');
        
        return $this->json($param, $msg, $code, ['method'=>'DELETE']);
    }
    
    // SQL语句
    public function sql($param)
    {
        $data = ['sql'];
        $code = 400;
        $msg  = Lang::get('数据请求成功！');
        
        return ['data'=>$param,'code'=>$code,'msg'=>$msg];
    }
}
