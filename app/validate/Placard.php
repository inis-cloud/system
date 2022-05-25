<?php

namespace app\validate;

use think\Validate;

class Placard extends Validate
{
    protected $rule =   [
        'title'      => 'require|max:64',
    ];
    
    protected $message  =   [
        'title.require' => '标题必须',
        'name.max'      => '标题最多不能超过64个字符',
    ];
}