<?php

namespace app\index\controller;

use app\Request;
use inis\utils\File;
use think\facade\Config;

class Chart extends Base
{
    protected $File;
    // inis 配置
    protected $config;
    
    public function __construct()
    {
        $this->File   = new File;
        $this->config = Config::get('inis');
        
    }

    // 系统缓存
    public function SystemCache(Request $request)
    {
        if ($request->isPost())
        {
            $data  = [];
            $code  = 200;
            $msg   = 'ok';
            
            $param = $request->param();
            
            $path = '../runtime';
            
            $runtime = $this->File->dirInfo($path);
            
            unset($runtime[0]);
            unset($runtime[1]);
            
            foreach ($runtime as $key => $val) {
                $data[$val] = $this->File->getDirInfo($path.'/'.$val);
            }
            
            return $this->create($data,$code,$msg);
        }
    }
    
    // 暴露版本号
    public function version(Request $request)
    {
        if ($request->isPost())
        {
            $data  = [];
            $code  = 200;
            $msg   = 'ok';
            
            $data['version'] = !empty($this->config['version']) ? $this->config['version'] : '1.0';
            
            return $this->create($data,$code,$msg);
        }
    }
}