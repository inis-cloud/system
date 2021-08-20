<?php
// 全局中间件定义文件
return [
    // 全局请求缓存
    // \think\middleware\CheckRequestCache::class,

    // 多语言加载
    // \think\middleware\LoadLangPack::class,

    // Session初始化
    // \think\middleware\SessionInit::class,

    // 登录验证中间件
    // \app\index\middleware\LoginCheck::class
    
    // 安装验证中间件
    \app\middleware\Install::class
];


