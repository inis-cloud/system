<?php

namespace app\validate;

use think\Validate;

class Page extends Validate
{
    protected $rule =   [
        'title'     => 'require|max:25',
        'alias'     => 'unique:page|alphaNum|require',
    ];
    
    protected $message  =   [
        'title.require'    => '页面标题不能是空的！',
        'title.max'        => '页面标题最多不能超过25个字符！',
        'alias.unique'     => '页面别名已经存在了！',
        'alias.alphaNum'   => '页面别名只能是字母和数字！',
        'alias.require'    => '别名不能为空！',
    ];
}