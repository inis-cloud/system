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

Route::group(function () {
    Route::resource(':name', ':name');
    Route::resource('links-sort'  , 'LinksSort');
    Route::resource('article-sort', 'ArticleSort');
    Route::resource('verify-code' , 'VerifyCode');
});

// // 用户数据
// Route::resource('users', 'Users');

// // 配置数据
// Route::resource('options', 'Options');

// // 文章分类数据
// Route::resource('article-sort', 'ArticleSort');

// // 文章数据
// Route::resource('article', 'Article');

// // 标签数据
// Route::resource('tag', 'Tag');

// // 友链分类
// Route::resource('links-sort', 'LinksSort');

// // 友链分类
// Route::resource('links', 'Links');

// // 评论
// Route::resource('comments','Comments');

// // 搜索
// Route::resource('search', 'Search');


// 测试模块
// Route::resource('test', 'Test');