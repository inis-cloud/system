<?php
// +----------------------------------------------------------------------
// | INIS 设置
// +----------------------------------------------------------------------

use inis\utils\{utils};

$utils  = new utils;

return [

    'api'   =>  [
        'log'            =>  true,                      // 开启API日志
        'cache'          =>  false,                      // 开启API缓存
    ],
    'valid_time'         =>  5 * 60,                    // 验证码有效时间 - 单位秒
    'jwt'   =>  [
        'key'            =>  'inis-api',                // KEY - 用于校验 TOKEN 是否合法
        'encrypt'        =>  'HS256',                   // 加密方式
        'array'          =>  ['HS256','HS384','HS512'], // 可用加密方式
    ],
    
    // 登录配置
    'login' => [
        'expired'            =>  1 * 24 * 60 * 60,      // 登录过期时间 - 单位秒
        'error_time'         =>  15 * 60,               // 登录限制多少时间内错误 - 单位秒
        'error_count'        =>  10,                    // 限制规定时间内的错误次数 - 防止普通暴力撞库
        'auto_lock_account'  =>  true,                  // 帐号自动锁定 - 防止代理IP暴力撞库
        'account_error_count'=>  10,                    // 帐号错误次数 - 防止代理IP暴力撞库
        'default_password'   =>  'inis666.',            // 默认密码，用于创建账号时未填写密码的默认值
    ],
    
    // 随机接口配置
    'random'    =>  [
        // 文章随机图配置
        'article'      =>  [
            'enable'   =>  true,                                    // 是否启用随机图
            'path'     =>  $utils->get->domain('/api/file/random'), // $utils->get->domain('/api/file/random') 获取的是当前域名    
        ],
        'path'         =>  'storage/random/',                       // 配置随机路径
    ],
    
    // 公告配置
    'placard'   => [
        'all'   =>  '全部',
        'web'   =>  '网站',
        'qq'    =>  'QQ小程序',
        'wechat'=>  '微信小程序'
    ],
    
    // 官方授权系统
    'official'     =>  [
        'api'      =>  'https://inis.cc/api/',                         // 用于检查更新 - 千万不要作死
        'cdn'      =>  'https://cdn.inis.cc/system/default/'           // 静态文件加速
    ],

    'openapi'=>[
        'baidu'=>[
            'appid'=> 123456,
            'key'  => '123456',
            'api'  => 'https://api.fanyi.baidu.com/api/'
        ],
        'reptile'=>[
            'hot_search'  => 'https://www.0xu.cn'
        ]
    ],
    
    'version'      => '1.9.1',                          // inis 版本号 - 请不要自行更改，后果自负
];
