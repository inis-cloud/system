<?php

namespace app\index\controller;

use app\model\Options;
use app\BaseController;
use think\facade\Config;
use app\index\middleware\LoginCheck;

abstract class Base extends BaseController
{
    protected $middleware = [LoginCheck::class];
    
    public function initialize()
    {
        $config = Config::get('inis');
        
        // 定义模板路径
        define('__CDN__'       , $config['official']['cdn']);
        define('__ASSETS__'    , '/index/assets/');
        define('__ADMIN_JS__'  , '/index/assets/js/');
        define('__ADMIN_CSS__' , '/index/assets/css/');
        define('__ADMIN_IMG__' , '/index/assets/images/');
        define('__ADMIN_LIBS__', '/index/assets/libs/');
        
        // 配置站点信息
        $opt = (new Options)->GetOpt();
        define('__SITE_ICO__'        , $opt['site_ico']);
        define('__SITE_TITLE__'      , !empty($opt['title']) ? $opt['title'] : 'INIS' );
        define('__SITE_KEYWORDS__'   , !empty($opt['keywords']) ? $opt['keywords'] : 'api,inis api' );
        define('__SITE_DESCRIPTION__', !empty($opt['description']) ? $opt['description'] : 'api,inis api' );
    }
}