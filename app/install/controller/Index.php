<?php
// +----------------------------------------------------------------------
// | inis 安装引导
// +----------------------------------------------------------------------
// | 检查 PHPH 和 数据库版本 以及 必要条件是否满足
// +----------------------------------------------------------------------

namespace app\install\controller;

use app\Request;
use inis\utils\File;
use think\facade\Db;
use inis\utils\helper;
use think\facade\View;
use app\BaseController;
use think\facade\Config;
use think\facade\Session;

//  extends Base
class Index extends BaseController
{
    protected $File;
    // inis 配置
    protected $config;
    protected $helper;
    
    public function __construct()
    {
        $this->File   = new File;
        $this->helper = new helper;
        $this->config = Config::get('inis.version');
    }
    
    // 安装引导首页
    public function index(Request $request)
    {
        if ($request->isPost())
        {
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            $php['version'] = PHP_VERSION;
            $php['check']   = (version_compare(PHP_VERSION,'7.4','ge')) ? true : false;
            
            try {
                
                $version = Db::query('select version()');
                foreach ($version as $val) foreach ($val as $k => $v) $version = ($k == 'version()') ? $v : null;
                $version = explode('-', $version)[0];
                
                $MySQL['version'] = $version;
                $sql_ver_check    = $this->helper->VersionCompare($version, '5.5');
                $MySQL['check']   = ($sql_ver_check >= 0) ? true : false;
                
            } catch (ValidateException $e) {
                $MySQL['version'] = null;
                $MySQL['check']   = null;
                // return json($e->getError());
            } catch (\Exception $e) {
                $MySQL['version'] = null;
                $MySQL['check']   = null;
                // return json($e->getMessage());
            }
            
            $data = [
                'php'       => $php,
                'MySQL'     => $MySQL,
                'inis'      => $this->config,
            ];
            
            return $this->create($data,$code,$msg);
        }
        
        return View::engine('php')->fetch('/check');
    }
    
    // 安装引导下一步
    public function next(Request $request)
    {
         if ($request->isPost())
        {
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            $data = [
                'inis' => $this->config,
            ];
            
            return $this->create($data,$code,$msg);
        }
        
        return View::engine('php')->fetch('/next');
    }
    
}