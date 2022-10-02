<?php

use think\facade\Route;

Route::group('comm', function (){
    Route::rule(':NAME', 'Comm/:NAME');
});

Route::group('test', function (){
    Route::rule(':NAME', 'Test/:NAME')->allowCrossDomain([
        'Access-Control-Allow-Origin'        => '*',
        'Access-Control-Allow-Credentials'   => 'true'
    ]);;
});

Route::group('api', function (){
    Route::rule(':NAME', 'Api/:NAME');
});

Route::group('method', function (){
   Route::rule(':NAME', 'Method/:NAME');
});

Route::group('handle', function (){
    Route::rule(':NAME', 'Handle/:NAME');
});

Route::group('chart', function (){
    Route::rule(':NAME', 'Chart/:NAME');
});

Route::group('file', function (){
    Route::rule(':NAME', 'FileSystem/:NAME');
});

Route::group('update', function (){
   Route::rule(':NAME', 'Update/:NAME');
});

Route::group(function (){
    Route::any('/', 'Index/home');
    Route::rule(':NAME1-:NAME2-:NAME3', 'Index/:NAME1:NAME2:NAME3');
    Route::rule(':NAME1-:NAME2', 'Index/:NAME1:NAME2');
    Route::rule(':NAME', 'Index/:NAME');
});