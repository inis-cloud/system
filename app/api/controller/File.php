<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\Request;
use think\facade\Cache;

class File extends Base
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
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = 'ok';
        $result = [];
        
        $mode = !empty($param['mode']) ? $param['mode'] : null;
        $user = $this->parseJWT($param['login-token'])['data'];
        
        if ($mode == 'upload') {
            $result = $this->upload($param, $user);
            foreach ($result as $key => $val) $$key = $val;
        }
        
        return $this->create($data, $msg, $code);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read(Request $request, $id)
    {
        // 获取请求参数
        $param = $request->param();
        
        return $this->create($data, $msg, $code);
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
    
    // 上传文件
    public function upload($param, $user)
    {
        
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        $name = explode('.', $_FILES['file']['name']);
        array_pop($name);
        $name = implode('.',$name);
        
        $upload = $this->tool->upload('file', ['storage','users/files/uid-'.$user->id.'/'.date("Y-m"), [$name]], 'one', 'file|fileExt:jpg,jpeg,png,gif,webp,svg,ico,zip,gz,mp3,mp4,avi|fileSize:20971520');
        
        foreach ($upload as $key => $val) $$key = $val;
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}
