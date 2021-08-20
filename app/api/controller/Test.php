<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\Request;
use think\facade\Db;
use app\model\Users;
use app\model\Links;
use app\model\Visit;
use think\facade\Log;
use app\model\Article;
use inis\utils\helper;
use app\model\Comments;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Validate;
use think\facade\Filesystem;
use app\index\controller\Tool;

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
        // 获取请求参数
        $param = $request->param();
        
        $data = [];
        $code = 200;
        $msg  = 'ok';
        
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
        $param = $request->param();
        
        return $this->create($param, 'ok', 200);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
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
