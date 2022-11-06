<?php

namespace app\admin\controller;

use app\Request;
use inis\utils\{File};
use app\utils\{File as FileUtil};

class FileSystem extends Base
{
    protected $File;
    
    // 构造器
    public function __construct()
    {
        $this->File = new File;
    }
    
    /** 
     * @name 获取文件信息
     */
    public function getDir(Request $request)
    {
        if ($request->isPost())
        {
            $param = $request->param();
            
            // 被获取的路径
            $path = (empty($param['path'])) ? './' : $param['path'];
            
            // 避免提权，强制定义当前目录 ./ - 防跨站
            $path = explode('/', $path ?? './');
            // 过滤返回上一级操作
            foreach ($path as $key => $val) if ($val == '..') unset($path[$key]);
            // 过滤空数组并合并数组
            $path = implode('/', array_merge(array_filter($path)));
            $path = (empty($path)) ? './' : $path . '/';
            
            // 文件图片路径
            $ico_path = '/admin/svg/filesystem/';
            
            // 返回指定路径的文件夹信息，其中包含指定路径中的文件和目录
            $dir_info = $this->File->dirInfo($path);
            
            // 去除 . ..
            unset($dir_info[0]);
            unset($dir_info[1]);
            
            $file_info = [];
            
            // 文件类型
            $obtain = ['svg','jpg','jpeg','png','gif','ttf','woff','woff2','php','js','css','json','html','doc','docx','txt'];
            
            // 封装路径文件和文件夹数据
            foreach ($dir_info as $val) {
                
                $arr['name'] = $val;
                $arr['info'] = $this->File->listInfo($path.$val);
                
                if ($arr['info']['type'] == 'dir') $arr['info']['ico'] = $ico_path.'yellow-folder.svg';
                else if (in_array($arr['info']['ext'],$obtain)) foreach ($obtain as $val) {
                    
                    if ($arr['info']['ext'] == $val) $arr['info']['ico'] = $ico_path.$val.'.svg';
                    
                } else $arr['info']['ico'] = $ico_path.'other.svg';
                
                array_push($file_info,$arr);
            }
            
            // 重新排序
            if (!empty($file_info)) {
                
                $dir_data  = [];
                $file_data = [];
                
                foreach ($file_info as $key => $val) {
                    if ($val['info']['type'] == 'dir') array_push($dir_data,$val);
                    else array_push($file_data,$val);
                }
                
                $file_info = array_merge($dir_data,$file_data);
            }
            
            $data = [
                'path'=>$path,
                'info'=>$file_info,
                'disk'=>[
                    'total'=>disk_total_space('./'),
                    'free' =>disk_free_space('./')
                ]
            ];
            $code = 200;
            $msg  = 'ok';
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 编辑文件名称
     */
    public function editName(Request $request)
    {
        if ($request->isPost())
        {
            $param = $request->param();
            
            $old = $param['path'].$param['old_name'];
            $new = $param['path'].$param['new_name'];
            
            $this->File->rename($old,$new);
            
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 文件详情信息
     */
    public function fileInfo(Request $request)
    {
        if ($request->isPost())
        {
            $param = $request->param();
            
            $data = [];
            
            if (!empty($param['file']) and !empty($param['type'])) {
                
                $type = $param['type'];
                
                // 文件路径 + 文件名
                $path_file = $param['path'].$param['file'];
                
            } else {
                
                $type = 'dir';
                $path_file = $param['path'];
            }
            
            if ($type == 'dir') $data['size'] = $this->File->getDirInfo($path_file);
            else $data['size'] = $this->File->openInfo($path_file);
            
            $data['other'] = $this->File->listInfo($path_file);
            
            $code = 200;
            $msg  = 'ok';
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 新建文件或文件夹
     */
    public function addFile(Request $request)
    {
        if ($request->isPost())
        {
            $param = $request->param();
            
            $file = (empty($param['file'])) ? '新建文件' : $param['file'];
            $path = (empty($param['path'])) ? './' : $param['path'];
            $type = (empty($param['type'])) ? true : $param['type'];
            
            $path_file = $path.$file;
            
            // 新建文件或文件夹
            if ($type == 'true') $this->File->createFile($path_file);
            else $this->File->createDir($path_file);
            
            // 设置权限
            $this->File->changeFile($path_file, 'mode', 0755);
            
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 删除文件或文件夹
     */
    public function delFile(Request $request)
    {
        if ($request->isPost())
        {
            $param = $request->param();
            
            $data = [];
            $code = 400;
            $msg  = 'ok';
            
            if (empty($param['file'])) $msg = '请选择需要被删除的文件！';
            else {
                
                $path_file = $param['path'].$param['file'];
                
                // 删除文件
                if ($param['type'] == 'file') {
                    $this->File->unlinkFile($path_file);
                } else {  // 删除文件夹
                    $this->File->removeDir($path_file,true);
                }
                $code = 200;
            }
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 上传文件
     */
    public function uploadFileOne(Request $request)
    {
        $data = [];
        $code = 200;
        $msg  = 'ok';

        $name = explode('.', $_FILES['file']['name'] ?? '');
        array_pop($name);
        $name = implode('.',$name);
        // $tmp_name = $_FILES['file']['tmp_name'];
        // $error = $_FILES['file']['error'];
        
        $param = $request->param();
        
        $path  = explode('/', $param['path'] ?? './');
        foreach ($path as $key => $val) {
            if ($val == '.' or $val == '..') unset($path[$key]);
        }
        $path  = implode('/', $path);
        $path  = empty($path) ? './' : $path;

        try {

            $item   = new FileUtil();
            $data   = $item->disk($path)->size(1024 * 1024 * 20)->upload($name);

        } catch (\Throwable $th) {

            $msg  = $th->getMessage();
            $code = $th->getCode();
        }

        return $this->json($data, $msg, $code);
    }
    
    /** 
     * @name 读取文件内容
     */
    public function read(Request $request)
    {
        if ($request->isPost())
        {
            $param = $request->param();
            
            $data  = [];
            $code  = 400;
            $msg   = 'ok';
            
            $file_path = !empty($param['path']) ? $param['path'] : null;
            
            if (empty($file_path)) $msg = "文件路径不得为空！";
            else {
                
                // 重新定义路径，防止提权
                $path_array = array_filter(explode('/', $file_path ?? ''));
                $path_file  = implode('/', $path_array);
                
                $code = 200;
                $data['data'] = $this->File->readFile($path_file);
                $data['info'] = $this->File->listInfo($path_file);
            }
            
            return $this->json($data, $msg, $code);
        }
    }
    
    /** 
     * @name 写入文件内容
     */
    public function write(Request $request)
    {
        if ($request->isPost())
        {
            $param = $request->param();
            
            $data  = [];
            $code  = 400;
            $msg   = 'ok';
            
            $file_path = !empty($param['path']) ? $param['path'] : null;
            $text = !empty($param['text']) ? $param['text'] : null;
            
            if (empty($file_path)) $msg = "文件路径不得为空！";
            else {
                
                // 重新定义路径，防止提权
                $path_array = array_filter(explode('/', $file_path ?? ''));
                $path_file  = implode('/', $path_array);
                
                $this->File->writeFile($path_file, $text);
                
                $code = 200;
            }
            
            return $this->json($data, $msg, $code);
        }
    }
    
    // END
}