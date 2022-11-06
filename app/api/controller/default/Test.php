<?php
declare (strict_types = 1);

namespace app\api\controller\default;

use think\Request;
use app\utils\File;
use inis\utils\{helper, utils, Image};
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
        // 获取请求参数
        $param = $request->param();
        
        $data = [];
        $code = 200;
        $msg  = Lang::get('数据请求成功！');

        $data   = [
            'param' => $param,
            'header'=> $request->header(),
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
        $data  = [];
        $code  = 200;
        $msg   = 'ok';

        // 获取请求参数
        $param = $request->param();

        $data = (new utils)->get->domain();

        // try {
        //     $file = new File();
        //     $data = $file->compress(0)->ext('jpg,png,gif,jpeg')->size(1024 * 1024 * 2)->upload(time());
        // } catch (\Throwable $th) {
        //     $msg  = $th->getMessage();
        //     $code = $th->getCode();
        // }

        // $url = 'uploads/1667464671.jpg';
        // (new Image($url, 0.8))->compress($url);

        
        // $data = $file->upload();

        // $data   = [
        //     'param' => $param,
        //     'header'=> $request->header(),
        // ];
        // $code   = 400;
        // $msg    = Lang::get('ok！');

        // // ThinkPHP 6.1 文件上传
        // $file = request()->file('file');
        // // 文件大小
        // $data['size'] = $file->getSize();
        // // 文件名
        // $data['name'] = $file->getOriginalName();
        // // 文件类型
        // $data['type'] = $file->getOriginalExtension();
        // // 文件路径
        // $file->move('uploads', $file->getOriginalName());
        
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
