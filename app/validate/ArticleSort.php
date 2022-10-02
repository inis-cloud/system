<?php

namespace app\validate;

use think\Validate;

class ArticleSort extends Validate
{
    protected $rule =   [
        'name'      => 'require|unique:article_sort',
    ];
    
    protected $message  =   [
        'name.require'  => '文章分类名称不能为空！',
        'name.unique'   => '文章分类名称已存在！',
    ];
}