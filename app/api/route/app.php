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
use think\facade\{Route};

// 自定义headers参数
$allowCrossDomain = [
    'Access-Control-Allow-Headers' => 'token, login-token, authorization, version',
];

// 公共模块
Route::group('comm', function () {
    Route::rule(':INAME1', 'default.Comm/:INAME1');
})->allowCrossDomain($allowCrossDomain);

// 第三方API分组路由
Route::group(function () {
    $rule = [
        'read'  =>['GET'   , '/:IID', 'read'],
        'update'=>['PUT'   , ''     , 'update'],
        'delete'=>['DELETE', ''     , 'delete'],
        'patch' =>['PATCH' , ''     , 'patch'],
    ];
    // 文件方式
    Route::resource(':INAME-api', 'plugins.:INAME')->rest($rule);
    // 文件夹方式
    Route::resource(':INAME-:IDIR-api', 'app\api\controller\plugins\:IDIR\:INAME')->rest($rule);
})->allowCrossDomain($allowCrossDomain)->middleware(['api']);

// INIS API 分组路由
Route::group(function () {

    $rule1  = [
        'read'  =>['GET'   , '/:IID', 'IGET'],
        'update'=>['PUT'   , '/:IID', 'IPUT'],
        'save'  =>['POST'  , '/:IID', 'IPOST'],
        'delete'=>['DELETE', '/:IID', 'IDELETE'],
        'patch' =>['PATCH' , '/:IID', 'IPATCH'],
    ];
    $rule2 = [
        'read'  =>['GET'   , '', 'IGET'],
        'update'=>['PUT'   , '', 'IPUT'],
        'save'  =>['POST'  , '', 'IPOST'],
        'delete'=>['DELETE', '', 'IDELETE'],
        'patch' =>['PATCH' , '', 'IPATCH'],
    ];

    $allow = env('api.allow', []);
    if (!is_array($allow)) $allow = explode(',' ?? '', $allow);
    // 兼容性处理，防止有人作死
    $allow = array_merge(array_unique(array_filter(array_merge(!empty($allow) ? $allow : [], ['default']))));

    $version = request()->header('version', null);

    foreach ($allow as $key => $val) {

        // 通过API后缀切换版本，如：/api/links[-sql]/all
        $suffix = $val == 'default' ? '' : '-' . $val;

        // 通过请求头切换版本，如：/api/links/all (headers => version: sql)
        if (!empty($version)) if (in_array($version, $allow)) {
            $suffix = '';
            $val    = $version;
        }

        Route::resource(':INAME1' . $suffix, $val . '.:INAME1')->rest($rule1);
        Route::resource(':INAME1-:INAME2' . $suffix, $val . '.:INAME1-:INAME2')->rest($rule1);

        $append = ['IID' => request()->param('mode', 'def')];
    
        // 兼容旧版 - v1.8.0 之前
        Route::resource(':INAME1' . $suffix, $val . '.:INAME1')->rest($rule2)->append($append);
        Route::resource(':INAME1-:INAME2' . $suffix, $val . '.:INAME1-:INAME2')->rest($rule2)->append($append);
    }

})->allowCrossDomain($allowCrossDomain)->miss(function(){
    return json([
        'code' => 404,
        'data' => [],
        'msg'  => lang('啊咧~我们可没有这个地址！')
    ]);
});