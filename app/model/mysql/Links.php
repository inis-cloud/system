<?php

namespace app\model\mysql;

use think\Model;

class Links extends Model
{
    // 封装拓展字段数据 - 返回全部
    public static function ExpandAll($id = null, array $opt = [])
    {
        // 第二参数默认配置
        $config = ['page'=>1,'limit'=>5,'order'=>'create_time ase','withoutField'=>[],'whereOr'=>[],'where'=>['is_show'=>1]];
        
        // 重组第二参数
        foreach ($config as $key => $val) if(!in_array($key,$opt)) $config[$key] = $val;
        foreach ($opt as $key => $val) $config[$key] = $val;
        
        // 友链总数量
        $count  = count(self::whereOr($config['whereOr'])->where($config['where'])->field(['id'])->select());
        $data['page']   = ceil($count/$config['limit']);
        $data['count']  = $count;
        
        // 防止分页请求超出页码
        if($config['page'] > $data['page']) $config['page'] = $data['page'];
        
        $links = self::whereOr($config['whereOr'])->withAttr('sort', function ($value){
            
            return LinksSort::where(['slug'=>$value])->field(['name'])->find()['name'];
            
        })->where($config['where'])->withoutField($config['withoutField'])->order($config['order'])->page($config['page'])->limit($config['limit'])->select($id);
        
        $data['data'] = $links;
        
        // 只有单条数据
        if (!empty($id) and is_numeric($id)) $result = $links[0];
        else if (is_array($id)) $result = $links;
        else $result = $data;
        
        return $result;
    }

    // URL修改器
    public function getUrlAttr($value)
    {
        if(empty($value)) return 'javascript:void(0);';
        else return $value;
    }
    
    // OPT字段获取器 - 获取前修改
    public function getOptAttr($value)
    {
        $value = (!empty($value)) ? json_decode((is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value)) : $value;
        return $value;
    }
}