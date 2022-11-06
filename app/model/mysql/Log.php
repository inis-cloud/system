<?php

namespace app\model\mysql;

use think\Model;

class Log extends Model
{
    // OPT字段获取器 - 获取前修改
    public function getOptAttr($value)
    {
        $value = !empty($value) ? json_decode($value, true) : [];
        $value = array_merge([], $value ?? []);
        return (object)$value;
    }
}