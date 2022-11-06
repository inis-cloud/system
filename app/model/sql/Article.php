<?php

namespace app\model\sql;

use Parsedown;
use think\facade\{Config};
use think\{Model, Response};
use inis\utils\{helper, markdown};
use think\model\concern\SoftDelete;

class Article extends Model
{
    
    // 设置软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    // 封装拓展字段数据 - 返回全部
    public static function all($id = null, array $config = [])
    {
        $opts = array_merge([
            'page'   => 1,
            'limit'  => 5,
            'order'  => 'id asc',
            'field'  => [],
            'where'  => [],
            'whereOr'=> [],
            'withoutField'=> [],
            'withTrashed' => false,
            'onlyTrashed' => false,
            'whereNull'   => '',
            'whereNotNull'=> '',
        ], $config);

        $map1 = !$opts['withTrashed'] && !$opts['onlyTrashed'];
        $map2 = $opts['onlyTrashed'] ? self::onlyTrashed()->where($opts['where']) : self::withTrashed()->where($opts['where']);
        $item = $map1 ? self::where($opts['where']) : $map2;

        $item->whereOr($opts['whereOr']);
        $item->order($opts['order']);
        $item->withoutField($opts['withoutField']);
        $item->field($opts['field']);
        if (!empty($opts['whereNull']))    $item->whereNull($opts['whereNull']);
        if (!empty($opts['whereNotNull'])) $item->whereNotNull($opts['whereNotNull']);

        $total= $item->count();

        // 防止分页请求超出页码
        $opts['page'] = $opts['page'] > ceil($total / $opts['limit']) ? ceil($total / $opts['limit']) : $opts['page'];
        
        $item->page($opts['page']);
        $item->limit($opts['limit']);
        $result = [
            'page'  => ceil($total / $opts['limit']),
            'count' => $total,
            'data'  => $item->select($id)->each(function($item){
                
            }),
        ];

        return $result;
    }

    // 封装拓展字段数据 - 返回指定
    public static function one($id = null, array $config = [])
    {
        $opts = array_merge([
            'order'  => 'id asc',
            'field'  => [],
            'where'  => [],
            'whereOr'=> [],
            'withoutField'=> [],
            'withTrashed' => false,
            'onlyTrashed' => false,
        ], $config);

        $map1 = !$opts['withTrashed'] && !$opts['onlyTrashed'];
        $map2 = $opts['onlyTrashed'] ? self::onlyTrashed()->where($opts['where']) : self::withTrashed()->where($opts['where']);
        $item = $map1 ? self::where($opts['where']) : $map2;

        $item->whereOr($opts['whereOr']);
        $item->order($opts['order']);
        $item->withoutField($opts['withoutField']);
        $item->field($opts['field']);

        $result = $item->findOrEmpty($id);

        if ($result->isEmpty()) return $result;

        $result->expand = [
            'user' => Users::one($result->uid, [
                'field' => ['id', 'nickname', 'email', 'avatar', 'grade'],
            ]),
        ];

        return $result;
    }

    // OPT字段获取器 - 获取前修改
    public function getOptAttr($value)
    {
        $value = !empty($value) ? json_decode($value ?? '[]', true) : [];
        $value = array_merge([], $value ?? []);
        return $value;
    }
}