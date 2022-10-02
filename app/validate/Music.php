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
        'title.max'     => '标题最多不能超过64个字符！',
        'url.require'   => '请提交歌单分享地址！',
        'url.url'       => '歌单地址格式不正确！',
    ];
}