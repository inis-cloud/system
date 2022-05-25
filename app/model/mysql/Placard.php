<?php

namespace app\model\mysql;

use think\Model;
use inis\utils\{helper};

class Placard extends Model
{
    // 封装拓展字段数据 - 返回全部
    public static function ExpandAll($id = null, array $opt = [])
    {
        // 第二参数默认配置
        $config = ['page'=>1,'limit'=>5,'order'=>'create_time asc','withoutField'=>[],'whereOr'=>[],'where'=>[]];
        
        // 重组第二参数
        foreach ($config as $key => $val) if(!in_array($key,$opt)) $config[$key] = $val;
        foreach ($opt as $key => $val) $config[$key] = $val;
        
        // 总数量
        $count  = count(self::whereOr($config['whereOr'])->where($config['where'])->field(['id'])->select());
        $data['page']   = ceil($count/$config['limit']);
        $data['count']  = $count;
        
        // 防止分页请求超出页码
        if($config['page'] > $data['page']) $config['page'] = $data['page'];
        
        $placard = self::whereOr($config['whereOr'])->withAttr('expand', function ($value, $data){
            
            return $value;
            
        })->where($config['where'])->withoutField($config['withoutField'])->order($config['order'])->page($config['page'])->limit($config['limit'])->select($id);
        
        $data['data'] = $placard;
        
        // 只有单条数据
        if (!empty($id) and is_numeric($id)) $result = $placard[0];
        else if (is_array($id)) $result = $placard;
        else $result = $data;
        
        return $result;
    }
    
    // OPT字段获取器 - 获取前修改
    public function getOptAttr($value)
    {
        $value = !empty($value) ? json_decode((is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value)) : $value;
        return !empty($value) ? $value : ['jump'=>'outside','url'=>null,'article_id'=>null];
    }
}