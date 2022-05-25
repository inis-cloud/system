<?php

namespace app\validate;

use think\Validate;

class Banner extends Validate
{
    protected $rule =   [
        'img'       => 'require|url',
    ];
    
    protected $message  =   [
        'img.require'   => '图片地址必须填写！',
        'img.url'       => '图片地址格式错误！',
    ];
}