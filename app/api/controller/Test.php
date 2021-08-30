<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\Request;
use think\facade\Db;
use app\model\Users;
use app\model\Links;
use app\model\Banner;
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
// use think\facade\Request;

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
        
        $file = !empty($param['file']) ? $param['file'] : null;
        
        $path = 'storage/random-img/';
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
                $data = $this->helper->RandArray($data);
                header("Location: " . $this->helper->domain().'/'.$data);
                
            } else if (in_array($file.'.txt', $list)) {  // 存在这个txt文件
            
                $data = file($path.$file.'.txt');
                $data = $this->helper->RandArray($data);
                header("Location: " . $data);
                
            } else {
                $code = 204;
                $msg  = '不存在该文件或文件夹';
            }
            
        } else {
            
            $item = $this->helper->RandArray($list);
            $info = $this->File->listInfo($path.$item);
            
            if ($info['type'] == 'dir') {
                
                $data = $this->File->listDirInfo($path.$item);
                $data = $this->helper->RandArray($data);
                header("Location: " . $this->helper->domain().'/'.$data);
                
            } else {
                
                $data = file($path.$item);
                $data = $this->helper->RandArray($data);
                header("Location: " . $data);
            }
        }
        
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
