<?php

namespace app\model\mysql;

use think\Model;
use inis\utils\{helper};

class Music extends Model
{
    // 封装拓展字段数据 - 返回全部
    public static function ExpandAll($id = null, array $opt = [])
    {
        // 第二参数默认配置
        $config = ['page'=>1,'limit'=>5,'order'=>'create_time asc','withoutField'=>[],'whereOr'=>[],'where'=>['is_show'=>1]];
        
        // 重组第二参数
        foreach ($config as $key => $val) if(!in_array($key,$opt)) $config[$key] = $val;
        foreach ($opt as $key => $val) $config[$key] = $val;
        
        // 总数量
        $count  = count(self::whereOr($config['whereOr'])->where($config['where'])->field(['id'])->select());
        $data['page']   = ceil($count/$config['limit']);
        $data['count']  = $count;
        
        // 防止分页请求超出页码
        if($config['page'] > $data['page']) $config['page'] = $data['page'];
        
        $music = self::whereOr($config['whereOr'])->withAttr('expand', function ($value, $data){
            
            $helper = new helper;
            
            $top_domain = $helper->GetTopDomain($data['url']);
            
            if ($top_domain == 'qq.com') $type = 'tencent';
            else if ($top_domain == '163.com') $type = 'netease';
            else $type = 'other';
            
            $value['type'] = $type;
            
            return $value;
            
        })->where($config['where'])->withoutField($config['withoutField'])->order($config['order'])->page($config['page'])->limit($config['limit'])->select($id);
        
        $data['data'] = $music;
        
        // 只有单条数据
        if (!empty($id) and is_numeric($id)) $result = $music[0];
        else if (is_array($id)) $result = $music;
        else $result = $data;
        
        return $result;
    }
    
    // OPT字段获取器 - 获取前修改
    public function getOptAttr($value)
    {
        $value = !empty($value) ? json_decode($value, true) : [];
        $value = array_merge([], $value ?? []);
        return (object)$value;
    }
}