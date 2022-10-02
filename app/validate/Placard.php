<?php

namespace app\validate;

use think\Validate;

class Placard extends Validate
{
    protected $rule =   [
        'title'      => 'require|max:64',
    ];
    
    protected $message  =   [
        'title.require' => '公告标题不能是空的！',
        'title.max'     => '公告的标题最多不能超过64个字符！',
    ];
}