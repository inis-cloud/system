<?php
// +----------------------------------------------------------------------
// | INIS 设置
// +----------------------------------------------------------------------

return [
    
    'api_cache'          =>  false,                      // 开启API缓存
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
    
    // 文章随机图
    'random_img'   =>  [
        'enable'   =>  true,                            // 是否启用随机图
        'type'     =>  "link",                          // 外链：link  本地：local
        'path'     =>  "storage/random-img/img.txt",    // 外链模式需要带.txt文件名（如：storage/random-img/img.txt）， 本地模式指向文件夹即可（如：storage/random-img/）
    ],
    
    'version'      => '1.2.0',                          // inis 版本号 - 请不要自行更改，后果自负
];
