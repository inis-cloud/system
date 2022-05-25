<?php

namespace app\validate;

use think\Validate;

class LinksSort extends Validate
{
    protected $rule =   [
        'name'      => 'require|unique:links_sort',
    ];
    
    protected $message  =   [
        'name.require'  => '分类名称不能为空！',
        'name.unique'   => '分类名称已存在！',
    ];
}