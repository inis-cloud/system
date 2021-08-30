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
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['all'];
        
        $mode   = (empty($param['mode'])) ? 'all' : $param['mode'];
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        $this->create($data, $msg, $code);
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
        $user = $this->parseJWT($param['login-token']);
        
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
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['random','words'];
        
        // 动态方法且方法存在
        if (in_array($id, $method)) $result = $this->$id($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
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
        
        $upload = $this->tool->upload('file', ['storage','users/files/uid-'.$user['data']->id.'/'.date("Y-m"), [$name]], 'one', 'file|fileExt:jpg,jpeg,png,gif,webp,svg,ico,zip,gz,mp3,mp4,avi|fileSize:20971520');
        
        foreach ($upload as $key => $val) $$key = $val;
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    public function all($param)
    {
        $result = ['data'=>$param,'code'=>200,'msg'=>'ok'];
        return $result;
    }
    
    // 随机图
    public function random($param)
    {
        $data = [];
        $code = 200;
        $msg  = 'ok';
        
        $file = !empty($param['file']) ? $param['file'] : null;
        $json = (empty($param['json']) or $param['json'] == 'false') ? false : true;
        
        $path = $this->config['random']['path'] . 'images/';
        $list = $this->File->dirInfo($path);
        
        $filter = ['.','..','使用说明.txt'];
        // 过滤特殊文件
        foreach ($list as $key => $val) if (in_array($val, $filter)) unset($list[$key]);
        
        // 重新组合存在的文件或文件夹列表
        $list = array_merge($list);
        
        // 指定随机zi
        if (!empty($file)) {
            
            // 存在这个目录
            if (in_array($file, $list)) {
                
                $data = $this->File->listDirInfo($path.$file);
                $data = str_replace(PHP_EOL,'', $this->helper->RandArray($data));
                $data = $this->helper->domain().'/'.$data;
                
                if (!$json) header("Location: " . $data);
                
            } else if (in_array($file.'.txt', $list)) {  // 存在这个txt文件
            
                $data = file($path.$file.'.txt');
                $data = str_replace(PHP_EOL,'', $this->helper->RandArray($data));
                
                if (!$json) header("Location: " . $data);
                
            } else {
                $code = 204;
                $msg  = '不存在该文件或文件夹';
            }
            
        } else {
            
            $item = $this->helper->RandArray($list);
            $info = $this->File->listInfo($path.$item);
            
            if ($info['type'] == 'dir') {
                
                $data = $this->File->listDirInfo($path.$item);
                $data = str_replace(PHP_EOL,'', $this->helper->RandArray($data));
                $data = $this->helper->domain().'/'.$data;
                
            } else {
                
                $data = file($path.$item);
                $data = str_replace(PHP_EOL,'', $this->helper->RandArray($data));
            }
            
            if (!$json) header("Location: " . $data);
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 随机文本
    public function words($param)
    {
        $data = [];
        $code = 200;
        $msg  = 'ok';
        
        $file = !empty($param['file']) ? $param['file'] : null;
        
        $path = $this->config['random']['path'] . 'words/';
        $list = $this->File->dirInfo($path);
        
        $filter = ['.','..','使用说明.txt'];
        // 过滤特殊文件
        foreach ($list as $key => $val) if (in_array($val, $filter)) unset($list[$key]);
        
        // 重新组合存在的文件或文件夹列表
        $list = array_merge($list);
        
        // 指定随机zi
        if (!empty($file)) {
            
            // 存在这个文件
            if (in_array($file.'.txt', $list)) {  // 存在这个txt文件
            
                $data = file($path.$file.'.txt');
                $data = str_replace(PHP_EOL,'', $this->helper->RandArray($data));
                
            } else {
                $code = 204;
                $msg  = '不存在该文件';
            }
            
        } else {
            
            $item = $this->helper->RandArray($list);
            $info = $this->File->listInfo($path.$item);
            
            $data = file($path.$item);
            $data = str_replace(PHP_EOL,'', $this->helper->RandArray($data));
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}
