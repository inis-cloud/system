<?php

namespace app\model\sqlite;

use think\Model;
use inis\utils\{helper};

class Visit extends Model
{
    // 数据库
    protected $connection = 'sqlite';
    
    // 封装拓展字段数据 - 返回全部
    public static function ExpandAll($id = null, array $opt = [])
    {
        // 第二参数默认配置
        $config = ['page'=>1,'limit'=>5,'order'=>'create_time ase','withoutField'=>[],'whereOr'=>[],'where'=>[]];
        
        // 重组第二参数
        foreach ($config as $key => $val) if (!in_array($key,$opt)) $config[$key] = $val;
        foreach ($opt as $key => $val) $config[$key] = $val;
        
        // 友链总数量
        $count  = count(self::whereOr($config['whereOr'])->where($config['where'])->field(['id'])->select());
        $data['page']   = ceil($count/$config['limit']);
        $data['count']  = $count;
        
        // 防止分页请求超出页码
        if($config['page'] > $data['page']) $config['page'] = $data['page'];
        
        $items = self::whereOr($config['whereOr'])->withAttr('expand', function ($value, $data){
            
            $opt = self::getOptAttr($data['opt']);
            
            foreach ($opt as $key => $val) if ($key != 'total') {
                $value['total'][$key] = 0;
                foreach ($val as $k => $v) $value['total'][$key] += $v['visit'];
            }
            $value['total']['all'] = $opt['total'][0]['visit'];
            $value['time']  = date('Y-m-d H:i:s',$data['time']);
            
            return $value;
            
        })->where($config['where'])->withoutField($config['withoutField'])->order($config['order'])->page($config['page'])->limit($config['limit'])->select($id);
        
        $data['data'] = $items;
        
        // 只有单条数据
        if (!empty($id) and is_numeric($id)) $result = $items[0];
        else if (is_array($id)) $result = $items;
        else $result = $data;
        
        return $result;
    }
    
    // 自定义保存数据
    // 用法
    // Visit::record([
    //     'pages'=>[['id'=>'index','visit'=>1],['id'=>'test','visit'=>1],['id'=>3,'visit'=>1]],
    //     'theme'=>[['id'=>4,'visit'=>1],['id'=>1,'visit'=>1]]
    // ]);
    public static function record(array $opt = [])
    {
        // 今天开始时间戳
        $today = strtotime(date('Y-m-d', time()));
        
        $items = self::where(['time'=>$today])->findOrEmpty();
        
        if ($items->isEmpty()) {
            
            $items->time = $today;
            $item = $opt;
            
        } else {
            
            $item = $items->opt;
            
            foreach ($opt as $key => $val) {
                // 不存在 - 新增
                if (!in_array($key, array_keys($items->opt))) $item[$key] = $val;
                // 存在 - 增加
                else foreach ($val as $k => $v) foreach ($v as $x => $y) {
                    if ($x == 'id') if ((new helper)->InArray(['id', $y], $item[$key])) {
                        foreach ($item[$key] as $a => $b) if ($b['id'] == $y) $item[$key][$a]['visit'] += 1;
                    } else $item[$key][] = $v;
                }
            }
        }
        
        $items->opt  = json_encode($item, JSON_UNESCAPED_UNICODE);
        
        $items->save();
    }
    
    // OPT字段获取器 - 获取前修改
    public static function getOptAttr($value)
    {
        $value = (!empty($value)) ? json_decode((is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value)) : $value;
        return objectToArray($value);
    }
}