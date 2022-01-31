<?php
// +----------------------------------------------------------------------
// | inis 安装引导
// +----------------------------------------------------------------------
// | 检查 PHPH 和 数据库版本 以及 必要条件是否满足
// +----------------------------------------------------------------------

namespace app\install\controller;

use app\Request;
use app\BaseController;
use inis\utils\{File, helper};
use think\facade\{Db, View, Config, Session};

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
        
        define('__ASSETS__'    , '/index/assets/');
        define('__ADMIN_JS__'  , '/index/assets/js/');
        define('__ADMIN_CSS__' , '/index/assets/css/');
        define('__ADMIN_IMG__' , '/index/assets/images/');
        define('__ADMIN_LIBS__', '/index/assets/libs/');
    }
    
    // 安装引导首页
    public function index(Request $request)
    {
        if ($request->isPost())
        {
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            $data['php']   = [
                'need'     => '7.4',
                'version'  => PHP_VERSION,
                'check'    => (version_compare(PHP_VERSION,'7.4','ge')) ? true : false
            ];
            $data['exten'] = get_loaded_extensions();
            
            try {
                
                $version = Db::query('select version()');
                foreach ($version as $val) foreach ($val as $k => $v) $version = ($k == 'version()') ? $v : null;
                $version = explode('-', $version)[0];
                
                $sql_ver_check    = $this->helper->VersionCompare($version, '5.5');
                
                $data['mysql'] = [
                    'need'     => '5.5',
                    'version'  => $version,
                    'check'    => ($sql_ver_check >= 0) ? true : false,
                ];
                
            } catch (ValidateException $e) {
                
               $data['mysql'] = [
                    'need'     => '5.5',
                    'version'  => null,
                    'check'    => null,
                ];
                
            } catch (\Exception $e) {
                
                $data['mysql'] = [
                    'need'     => '5.5',
                    'version'  => null,
                    'check'    => null,
                ];
            }
            
            return $this->create($data, $msg, $code);
        }
        
        return View::engine('php')->fetch('/index');
    }
}