<?php

namespace app\model;

use think\Model;

class Options extends Model
{

    public function GetOpt()
    {
        return self::column('value','keys');
    }
    
    // OPT字段获取器 - 获取前修改
    public function getOptAttr($value)
    {
        $value = (!empty($value)) ? json_decode((is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value)) : $value;
        return $value;
    }
}