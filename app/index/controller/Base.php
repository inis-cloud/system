<?php

namespace app\index\controller;

use app\BaseController;
use inis\utils\{helper};
use think\facade\{Config};
use app\model\mysql\{Options};
use app\index\middleware\LoginCheck;

abstract class Base extends BaseController
{
    protected $helper = [];
    protected $config = [];
    protected $middleware = [LoginCheck::class];
    
    public function initialize()
    {
        $this->helper = new helper;
        $this->config = Config::get('inis');
        
        // 系统配置
        $system_config = Options::where(['keys'=>'config:system'])->findOrEmpty();
        // 内置的CDN
        $in_cdn = $this->config['official']['cdn'];
        // 用户自己的CDN
        $u_cdn  = (!empty($system_config['opt']->optimize)) ? $system_config['opt']->optimize->cdn : '';
        // 处理后的CDN
        $cdn = array_values(array_filter(explode('/',str_replace(['https','http',':'],'',$u_cdn))));
        
        // 使用本地资源
        if (count($cdn) == 0) $cdn = '';
        // 处理CDN为合适的格式
        else if (count($cdn) == 1) $cdn = $this->helper->CustomProcessApi($u_cdn,'system/default');
        // 用户自己的CDN
        else $cdn = $u_cdn;
        // 去除末位 / 
        if (!empty($cdn) and (substr($cdn, -1) == '/')) $cdn = substr($cdn, 0, strlen($cdn)-1);
        
        // 定义模板路径
        define('__CDN__'       , (empty($cdn) ? $in_cdn : $cdn . '/'));
        define('__ASSETS__'    , '/index/assets/');
        define('__ADMIN_JS__'  , '/index/assets/js/');
        define('__ADMIN_CSS__' , '/index/assets/css/');
        define('__ADMIN_IMG__' , '/index/assets/images/');
        define('__ADMIN_LIBS__', '/index/assets/libs/');
        define('__VERSION__'   , $this->config['version']);
        
        // 站点信息
        $site = Options::where(['keys'=>'site'])->findOrEmpty();
        define('__SITE_ICO__'        , !empty($site) ? $site['opt']->favicon      : '');
        define('__SITE_TITLE__'      , !empty($site) ? $site['opt']->title        : 'INIS');
        define('__SITE_KEYWORDS__'   , !empty($site) ? $site['opt']->keywords     : 'api,inis api');
        define('__SITE_DESCRIPTION__', !empty($site) ? $site['opt']->description  : 'api,inis api');
    }
}