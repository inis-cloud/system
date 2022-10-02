<?php

namespace app\validate;

use think\Validate;

class Banner extends Validate
{
    protected $rule =   [
        'img'       => 'require|url',
    ];
    
    protected $message  =   [
        'img.require'   => '请提交图片地址！',
        'img.url'       => '图片地址格式不正确！',
    ];
}