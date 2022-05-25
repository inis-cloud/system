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
        'title.require'    => '标题必须',
        'title.max'        => '标题最多不能超过25个字符',
        'alias.unique'     => '别名已存在',
        'alias.alphaNum'   => '别名只能是字母和数字',
        'alias.require'    => '别名不能为空',
    ];
}