<?php

use think\facade\Route;

Route::group('comm', function (){
    Route::rule(':name', 'Comm/:name');
});

Route::group('test', function (){
    Route::rule(':name', 'Test/:name')->allowCrossDomain([
        'Access-Control-Allow-Origin'        => '*',
        'Access-Control-Allow-Credentials'   => 'true'
    ]);;
});

Route::group('api', function (){
    Route::rule(':name', 'Api/:name');
});

Route::group('method', function (){
   Route::rule(':name', 'Method/:name');
});

Route::group('handle', function (){
    Route::rule(':name', 'Handle/:name');
});

Route::group('chart', function (){
    Route::rule(':name', 'Chart/:name');
});

Route::group('file', function (){
    Route::rule(':name', 'FileSystem/:name');
});

Route::group('update', function (){
   Route::rule(':name', 'Update/:name');
});

Route::group(function (){
    Route::any('/', 'Index/home');
    Route::rule(':name1-:name2-:name3', 'Index/:name1:name2:name3');
    Route::rule(':name1-:name2', 'Index/:name1:name2');
    Route::rule(':name', 'Index/:name');
});