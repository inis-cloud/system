<?php

namespace app\admin\controller;

use app\BaseController;
use think\{Response, Request};
use inis\utils\{helper, File};
use app\model\mysql\{Options};
use app\admin\middleware\LoginCheck;
use think\facade\{Config, View, Session};

abstract class Base extends BaseController
{
    protected $File;
    protected $helper     = [];
    protected $config     = [];
    protected $middleware = [LoginCheck::class];
    private mixed $user;

    public function initialize()
    {
        $this->File   = new File;
        $this->helper = new helper;
        $this->config = Config::get('inis');
        
        // 系统配置
        $system_config = Options::where(['keys'=>'config:system'])->findOrEmpty();
        // 内置的CDN
        $in_cdn = $this->config['official']['cdn'];
        // 用户自己的CDN
        $u_cdn  = (!empty($system_config['opt']->optimize)) ? $system_config['opt']->optimize->cdn : '';
        // 处理后的CDN
        $cdn = array_values(array_filter(explode('/', str_replace(['https','http',':'], '', $u_cdn))));
        
        // 使用本地资源
        if (count($cdn) == 0) $cdn = '';
        // 处理CDN为合适的格式
        else if (count($cdn) == 1) $cdn = $this->helper->CustomProcessApi($u_cdn,'system/default');
        // 用户自己的CDN
        else $cdn = $u_cdn;
        // 去除末位 / 
        if (!empty($cdn) and (substr($cdn, -1) == '/')) $cdn = substr($cdn, 0, strlen($cdn)-1);
        
        // 站点信息
        $site = Options::where(['keys'=>'site'])->findOrEmpty();
        $sites= (!$site->isEmpty()) ? $site['opt'] : (object)[
            'title'       => 'inis博客系统',
            'keywords'    => 'INIS API,inis博客系统,inis程序,inis系统,inis blog',
            'description' => 'inis · 新一代博客系统！这是市面上为数不多的新一代博客系统，整站封装，全站分离，真正意义上的前后端分离。每一行代码都用心设计，用最少的代码量和最优雅架构设计，实现最完美的系统，让你拥有更极致的体验。复杂的研究留给我们，简单的体验留给用户！',
            'favicon'     => $this->helper->domain() . '/admin/images/logo_sm.png',
            'copy'        => '备案号'
        ];
        
        $this->user   = json_decode(!empty(Session::get('login_account')) ? Session::get('login_account') : '', true);
        
        // 后台开启了 token 验证
        $token = Options::where(['keys'=>'config:security'])->value('opt');
        $token = json_decode($token ?? '[]', true);
        $token = $token['token']['value'] ?? '';

        // 开启了自动更新
        $autoUpdate = Options::where(['keys'=>'config:system'])->value('opt');
        $autoUpdate = json_decode($autoUpdate ?? '', true);
        $autoUpdate = $autoUpdate['system']['autoUpdate'] ?? false;
        $autoUpdate = $autoUpdate == 'true' or $autoUpdate === true ? true : false;

        View::assign([
            'CONFIG'         => (object)[
                'ROOT'       => '/admin/',
                'CDN'        => empty($cdn) ? $in_cdn : $cdn . '/',
                'VERSION'    => $this->config['version'],
                'SITE'       => $sites,
                'TOKEN'      => $token,
                'USER'       => (object)$this->user,
                'API'        => Config::get('inis.official.api', 'https://inis.cc/api/'),
                'AUTOUPDATE' => $autoUpdate
            ]
        ]);
    }

    // 生成标准API格式
    public function json($data = [], string $msg = '', int $code = 200, array $config = [], string $type = 'json') : Response
    {
        // 标准API结构生成
        $result = [
            // 状态码
            'code'  =>  $code,
            // 消息
            'msg'   =>  $msg,
            // 数据
            'data'  =>  $data
        ];
        
        // 合并其他数据数据
        $result = !empty($config) ? array_merge($result, $config) : $result;
        
        return Response::create($result, $type);
    }
    
    // 404 - 方法不存在的错误
    public function __call($name, $args)
    {
        return redirect((string) url('/admin'));
    }
}