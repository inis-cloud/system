<?php

namespace app\validate;

use think\Validate;

class LinksSort extends Validate
{
    protected $rule =   [
        'name'      => 'require|unique:links_sort',
    ];
    
    protected $message  =   [
        'name.require'  => '友链分组名称不能为空！',
        'name.unique'   => '友链分组名称已存在！',
    ];
}