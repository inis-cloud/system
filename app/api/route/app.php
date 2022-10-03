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
    Route::rule(':INAME1', 'inis.Comm/:INAME1');
});

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

// INIS API 分组路由
Route::group(function () {

    Route::resource(':INAME1', 'inis.:INAME1')->rest([
        'read'  =>['GET'   , '/:IID', 'IGET'],
        'update'=>['PUT'   , '/:IID', 'IPUT'],
        'save'  =>['POST'  , '/:IID', 'IPOST'],
        'delete'=>['DELETE', '/:IID', 'IDELETE'],
        'patch' =>['PATCH' , '/:IID', 'IPATCH'],
    ]);
    Route::resource(':INAME1-:INAME2', 'inis.:INAME1-:INAME2')->rest([
        'read'  =>['GET'   , '/:IID', 'IGET'],
        'update'=>['PUT'   , '/:IID', 'IPUT'],
        'save'  =>['POST'  , '/:IID', 'IPOST'],
        'delete'=>['DELETE', '/:IID', 'IDELETE'],
        'patch' =>['PATCH' , '/:IID', 'IPATCH'],
    ]);

    // 兼容旧版 - v1.8.0 之前
    Route::resource(':INAME1', 'inis.:INAME1')->rest([
        'read'  =>['GET'   , '', 'IGET'],
        'update'=>['PUT'   , '', 'IPUT'],
        'save'  =>['POST'  , '', 'IPOST'],
        'delete'=>['DELETE', '', 'IDELETE'],
        'patch' =>['PATCH' , '', 'IPATCH'],
    ])->append([
        'IID' => request()->param('mode', 'def')
    ]);
    Route::resource(':INAME1-:INAME2', 'inis.:INAME1-:INAME2')->rest([
        'read'  =>['GET'   , '', 'IGET'],
        'update'=>['PUT'   , '', 'IPUT'],
        'save'  =>['POST'  , '', 'IPOST'],
        'delete'=>['DELETE', '', 'IDELETE'],
        'patch' =>['PATCH' , '', 'IPATCH'],
    ])->append([
        'IID' => request()->param('mode', 'def')
    ]);

})->allowCrossDomain([
    // 自定义headers参数
    'Access-Control-Allow-Headers' => 'token, login-token, authorization',
])->miss(function(){
    return json([
        'code' => 404,
        'data' => [],
        'msg'  => lang('啊咧~我们可没有这个地址！')
    ]);
});