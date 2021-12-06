<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

// 公共模块
Route::group('comm', function (){
    Route::rule(':name', 'Comm/:name');
});

// 分组路由
Route::group(function () {
    
    // 取消资源路由中的默认参数
    Route::resource('proxy', 'Proxy')->rest([
        'update' => ['PUT', '', 'update'],
        'delete' => ['DELETE', '', 'delete'],
        'patches'=> ['PATCH', '', 'patches'],
    ]);
    
    // 动态路由
    Route::resource(':name', ':name');
    Route::resource('links-sort'  , 'LinksSort');
    Route::resource('article-sort', 'ArticleSort');
    Route::resource('verify-code' , 'VerifyCode');
    
})->allowCrossDomain([
    // 自定义headers参数
    'Access-Control-Allow-Headers' => 'token, login-token',
]);
