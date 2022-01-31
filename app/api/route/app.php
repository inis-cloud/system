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
    Route::rule(':INAME', 'inis.Comm/:INAME');
});

// INIS API 分组路由
Route::group(function () {
    
    // 取消资源路由中的默认参数
    Route::resource('proxy', 'inis.Proxy')->rest([
        'read'   => ['GET'   , '/:IID', 'read'],
        'update' => ['PUT'   , ''     , 'update'],
        'delete' => ['DELETE', ''     , 'delete'],
        'patch'  => ['PATCH' , ''     , 'patch'],
    ]);
    
    // 动态路由
    Route::resource(':INAME', 'inis.:INAME')->rest([
        'read'  =>['GET'   , '/:IID', 'read'],
        'update'=>['PUT'   , '/:IID', 'update'],
        'delete'=>['DELETE', '/:IID', 'delete']
    ]);
    Route::resource('links-sort'    , 'inis.LinksSort')->rest([
        'read'  =>['GET'   , '/:IID', 'read'],
        'update'=>['PUT'   , '/:IID', 'update'],
        'delete'=>['DELETE', '/:IID', 'delete']
    ]);
    Route::resource('article-sort'  , 'inis.ArticleSort')->rest([
        'read'  =>['GET'   , '/:IID', 'read'],
        'update'=>['PUT'   , '/:IID', 'update'],
        'delete'=>['DELETE', '/:IID', 'delete']
    ]);
    Route::resource('verify-code'   , 'inis.VerifyCode')->rest([
        'read'  =>['GET'   , '/:IID', 'read'],
        'update'=>['PUT'   , '/:IID', 'update'],
        'delete'=>['DELETE', '/:IID', 'delete']
    ]);
    
})->allowCrossDomain([
    // 自定义headers参数
    'Access-Control-Allow-Headers' => 'token, login-token',
]);

// 第三方API分组路由
Route::group(function () {
    // 文件方式
    Route::resource(':INAME-api', 'plugins.:INAME')->rest([
        'read'  =>['GET'   , '/:IID', 'read'],
        'update'=>['PUT'   , ''     , 'update'],
        'delete'=>['DELETE', ''     , 'delete'],
        'patch' =>['PATCH' , ''     , 'patch'],
    ]);
    // 文件夹方式
    Route::resource(':INAME-:IDIR-api', 'app\api\controller\plugins\:IDIR\:INAME')->rest([
        'read'  =>['GET'   , '/:IID', 'read'],
        'update'=>['PUT'   , ''     , 'update'],
        'delete'=>['DELETE', ''     , 'delete'],
        'patch' =>['PATCH' , ''     , 'patch'],
    ]);
})->allowCrossDomain([
    // 自定义headers参数
    'Access-Control-Allow-Headers' => 'token, login-token, authorization',
])->middleware([\app\api\middleware\api::class]);