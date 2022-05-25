<?php

namespace app\validate;

use think\Validate;

class Music extends Validate
{
    protected $rule =   [
        'title'     => 'max:64',
        'url'       => 'require|url',
    ];
    
    protected $message  =   [
        'title.max'     => '标题最多不能超过64个字符',
        'url.require'   => '链接必须',
        'url.url'       => '链接格式不正确',
    ];
}