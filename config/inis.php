<?php
// +----------------------------------------------------------------------
// | INIS 设置
// +----------------------------------------------------------------------
use inis\utils\helper;

$helper = new helper;

return [
    
    'api_cache'          =>  true,                      // 开启API缓存
    'jwt_key'            =>  'inis-!@#$%*&',            // JWT KEY - 用于校验 TOKEN 是否合法
    'valid_time'         =>  5 * 60,                    // 验证码有效时间 - 单位秒
    
    // 登录配置
    'login' => [
        'error_time'         =>  30 * 60,               // 登录限制多少时间内错误 - 单位秒
        'error_count'        =>  5,                     // 限制规定时间内的错误次数 - 防止普通暴力撞库
        'auto_lock_account'  =>  true,                  // 帐号自动锁定 - 防止代理IP暴力撞库
        'account_error_count'=>  10,                    // 帐号错误次数 - 防止代理IP暴力撞库
        'default_password'   =>  'inis666.',            // 默认密码，用于创建账号时未填写密码的默认值
    ],
    
    // 随机接口配置
    'random'    =>  [
        // 文章随机图配置
        'article'      =>  [
            'enable'   =>  true,                         // 是否启用随机图
            'path'     =>  $helper->domain() . '/api/file/random',  // $helper->domain() 获取的是当前域名    
        ],
        'path'         =>  'storage/random/',
    ],
    
    // 官方授权系统
    'official'     =>  [
        'api'      =>  'https://inis.cc/api/',          // 用于检查更新
        'cdn'      =>  'https://cdn.inis.cc/'           // 静态文件加速
    ],
    
    'version'      => '1.2.7',                          // inis 版本号 - 请不要自行更改，后果自负
];
