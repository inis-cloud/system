<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2022 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: FileLog class - 升级版文件数据库类
// +----------------------------------------------------------------------
// | Scenes: 应用在大额高频日志存储等场景
// +----------------------------------------------------------------------
namespace inis\utils;

/**
 * Class FileLog
 * @package inis\utils
 */
class FileLog
{
    private $CONFIG = [
        // 分页配置
        'page'      => 1,
        // 每页条数
        'limit'     => null,
        // 排序语句
        'order'     => ['id' => 'asc'],
        // 条件语句
        'where'     => [],
        // 条件语句
        'whereOr'   => [],
        // 拓展语句
        'withAttr'  => [],
        // 文件路径
        'filePath'  => '',
        // 自动添加时间
        'autoTime'  => true,
        // 临时数据
        'runtime'   => [],
    ];

    // 实例化
    public function __construct($CONFIG = [])
    {
        $this->CONFIG = $this->arrayDeepMerge($this->CONFIG, $CONFIG);
    }

    // init创建文件
    public function init()
    {
        $filePath = $this->CONFIG['filePath'];
        // 文件不存在则创建
        if (!$this->has($filePath)) {
            // 创建文件
            $this->createFile($filePath);
        }
    }

    // 返回配置数据
    public function getConfig()
    {
        return $this->CONFIG;
    }

    /**
     * 设置文件路径
     * @param string $filePath
     * @return $this
     */
    public function path($filePath = '')
    {
        $this->CONFIG['filePath'] = $filePath;
	    return $this;
    }

    /**
     * 设置分页
     * @param int $limit
     * @return $this
     */
    public function page($page = 1)
    {
        $this->CONFIG['page'] = $page;
        return $this;
    }

    /**
     * 设置限制条数
     * @param int $limit
     * @return $this
     */
    public function limit($limit = null)
    {
        $this->CONFIG['limit'] = $limit;
        return $this;
    }

    /**
     * order排序
     * @param string $order
     * @return $this
     */
    public function order($order = 'id asc')
    {
        if (is_array($order)) {
            $this->CONFIG['order'] = $order;
        } else {
            $result = [];
            $item   = explode(',', $order);
            foreach ($item as $key => $val) {
                $value = array_merge(array_filter(explode(' ', $val)));
                $result[$value[0]] = isset($value[1]) ? $value[1] : 'asc';
            }
            $this->CONFIG['order'] = $result;
        }
        return $this;
    }

    /**
     * 条件查询
     * @param array $data
     * @return $this
     */
    public function where($where = [], $op = null, $value = false)
    {
        // 第一参数为数组
        if (is_array($where)) {
            
            // 空数组 - 不执行where
            if (!empty($where)) {
                
                // 一维数组
                if (count($where) == count($where, 1)) foreach ($where as $k => $v) {
                    $this->CONFIG['where'] = array_merge($this->CONFIG['where'], [[$k,'=',$v]]);
                }
                // 二维数组
                else foreach ($where as $val) {
                    $this->CONFIG['where'] = array_merge($this->CONFIG['where'], [[$val[0], $val[1], $val[2]]]);
                }
            }
            
        }
        else if ($value === false) return $this->where($where, '=', $op);
        else $this->CONFIG['where'] = array_merge($this->CONFIG['where'], [[$where, $op, $value]]);
        
        return $this;
    }

    /**
     * 条件查询
     * @param array $whereOr
     * @return $this
     */
    public function whereOr($whereOr = [], $opt = null, $value = false)
    {
        // 第一参数为数组
        if (is_array($whereOr)) {
            
            // 空数组 - 不执行where
            if (!empty($whereOr)) {
                
                // 一维数组
                if (count($whereOr) == count($whereOr, 1)) foreach ($whereOr as $k => $v) {
                    $this->CONFIG['whereOr'] = array_merge($this->CONFIG['whereOr'], [[$k,'=',$v]]);
                }
                // 二维数组
                else foreach ($whereOr as $val) {
                    $this->CONFIG['whereOr'] = array_merge($this->CONFIG['whereOr'], [[$val[0], $val[1], $val[2]]]);
                }
            }
            
        }
        else if ($value === false) return $this->whereOr($whereOr, '=', $opt);
        else $this->CONFIG['whereOr'] = array_merge($this->CONFIG['whereOr'], [[$whereOr, $opt, $value]]);
        
        return $this;
    }

    /**
     * 查询多条
     * @return array
     */
    public function select()
    {
        $this->init();
        $data     = [];
        $filePath = $this->CONFIG['filePath'];
	    
	    if (is_array($filePath))       $pathArray   = $filePath;
	    else if (is_string($filePath)) $pathArray[] = $filePath;
	    foreach ($pathArray as $val)   $data = array_merge($data, file($val));

        // 没有设置limit，则默认为全部
        if (empty($this->CONFIG['limit'])) $this->CONFIG['limit'] = count($data);

        // 计算起始位置
        $start = ($this->CONFIG['page'] - 1) * $this->CONFIG['limit'];
        // 计算结束位置
        $end   = $this->CONFIG['page'] * $this->CONFIG['limit'];
        // 对数据进行数据转码
        $array = [];
        $result= [];
        foreach ($data as $key => $val) $array[] = json_decode($val, true);

        // 往缓存存数据
        $this->CONFIG['runtime'] = $this->whereOrData($array);
        // 执行where
        $this->whereData();
        // 获取符合条件后剩余的数据
        $array = $this->CONFIG['runtime'];

        // 排序
        $order = [];
        // 排序字段
        foreach ($this->CONFIG['order'] as $key => $val) {
            $order[] = array_column($array, $key);
            $order[] = ($val == 'desc') ? SORT_DESC : SORT_ASC;
        }
        // 开始排序
        call_user_func_array('array_multisort', array_merge($order, [&$array]));

        foreach ($array as $key => $val) {
            if ($key >= $start && $key < $end) $result[] = $val;
            // 如果超出范围，则跳出循环
            else if ($key >= $end) break;
        }

        // 缓存没用了，清理掉
        $this->CONFIG['runtime'] = [];

        foreach ($result as $key => $val) {
            // 处理拓展字段，只能有一个
            if (!empty($this->CONFIG['withAttr'])) {
                // 覆盖原有的字段
                $result[$key][$this->CONFIG['withAttr']['name']] = $this->CONFIG['withAttr']['function'](
                    // 传递原来的值
                    isset($result[$key][$this->CONFIG['withAttr']['name']]) ? $result[$key][$this->CONFIG['withAttr']['name']] : null,
                    // 传递原来的全部数据
                    $val
                );
            }
        }

        return $result;
    }

    /**
     * 查询单条
     * @return array
     */
    public function find($id = null)
    {
        // 初始化
        $this->init();
        $item     = [];
        $result   = false;
        $filePath = $this->CONFIG['filePath'];

        // 对数据进行数据转码
        foreach (file($filePath) as $key => $val) $item[] = json_decode($val, true);

        // 往缓存存数据
        $this->CONFIG['runtime'] = $this->whereOrData($item);
        // 执行where
        $this->whereData();
        // 获取符合条件后剩余的数据
        $item = $this->CONFIG['runtime'];

        // 如果没有传入id，则默认为第一条
        if ($id !== null) {
            if (!empty($item)) foreach ($item as $key => $val) {
                if ($val['id'] == $id) {
                    $result = $val;
                    // 结束循环
                    break;
                } else $result = false;
            }
        } else {
            
            // 排序
            $order = [];
            // 排序字段
            foreach ($this->CONFIG['order'] as $key => $val) {
                $order[] = array_column($item, $key);
                $order[] = ($val == 'desc') ? SORT_DESC : SORT_ASC;
            }

            // 开始排序
            call_user_func_array('array_multisort', array_merge($order, [&$item]));
            
            $result = empty($item) ? false : $item[0];
        }

        // 缓存没用了，清理掉
        $this->CONFIG['runtime'] = [];

        // 把数据丢到上下文中，方便后续save()方法使用
        if (!empty($result)) foreach ($result as $key => $val) {
            $this->$key = $val;
        }

        // 处理拓展字段，只能有一个
        if (!empty($this->CONFIG['withAttr'])) {
            // 覆盖原有的字段
            $result[$this->CONFIG['withAttr']['name']] = $this->CONFIG['withAttr']['function'](
                // 传递原来的值
                isset($result[$this->CONFIG['withAttr']['name']]) ? $result[$this->CONFIG['withAttr']['name']] : null,
                // 传递原来的全部数据
                $result
            );
        }

        return $result;
    }

    /**
     * line查询
     * @param string $line 查询第几行？
     */
    public function line($line = 1)
    {
        $this->init();
        $result   = [];
        $filePath = $this->CONFIG['filePath'];

        // 对数据进行数据转码
        foreach (file($filePath) as $key => $val) $result[] = json_decode($val, true);

        // 往缓存存数据
        $this->CONFIG['runtime'] = $this->whereOrData($result);
        // 执行where
        $this->whereData();
        // 获取符合条件后剩余的数据
        $result = $this->CONFIG['runtime'];

        if (empty($result)) $result = false;
        else if ($line > count($result)) $result = false;
        else if ($line === 0) $result = false;
        else $result = $result[$line - 1];

        // 缓存没用了，清理掉
        $this->CONFIG['runtime'] = [];
        
        // 把数据丢到上下文中，方便后续save()方法使用
        if (!empty($result)) foreach ($result as $key => $val) {
            $this->$key = $val;
        }

        // 处理拓展字段，只能有一个
        if (!empty($this->CONFIG['withAttr'])) {
            // 覆盖原有的字段
            $result[$this->CONFIG['withAttr']['name']] = $this->CONFIG['withAttr']['function'](
                // 传递原来的值
                isset($result[$this->CONFIG['withAttr']['name']]) ? $result[$this->CONFIG['withAttr']['name']] : null,
                // 传递原来的全部数据
                $result
            );
        }

        return $result;
    }

    /**
     * 保存数据
     * @param array $data 数据
     */
    public function save($data = [])
    {
        $result = [];
        $this->init();
        // 数据为空，从上下文获取数据
        if (empty($data)) {
            foreach ($this as $key => $val) {
                if (in_array($key, ['CONFIG'])) continue;
                else $data[$key] = $val;
            }
            // // 更新数据
            // if (isset($this->id)) {}
            // // 新增数据
            // else $this->insert($data);

            // 如果设置了id，则更新数据
            if (isset($this->id)) {
                // 懒得写了，以后用到再写 ... ...
            }
            // 否则新增数据
            else $this->insert($data);
        }
        // 数据不为空，直接赋值
        else {
            // 判断 $data 是否为二维数组
            if ($this->twoArray($data)) {
                foreach ($data as $key => $val) $this->insert($val);
            } else $this->insert($data);
        }

        return $result;
    }

    /**
     * 插入数据
     * @param array $data
     * @return bool
     */
    public function insert(array $data = [], $anchor = 'end')
    {
        // 初始化
        $this->init();
        $result = false;
        // 判断 $data 是否为二维数组
        if ($this->twoArray($data)) {
            foreach ($data as $key => $val) $this->insert($val, $anchor);
        } else {

            $id = $this->order(['id'=>'desc'])->find();

            // 添加内置的数据
            $start = [
                'id' => !empty($id) ? $id['id'] + 1 : 1,
            ];
            $end   = $this->CONFIG['autoTime'] ? [
                'create_time'=>time(),
                'update_time'=>time(),
            ] : [];

            // 文件末尾插入
            if ($anchor == 'end') {
                // 打开文件
                $file = fopen($this->CONFIG['filePath'], 'a');
                // 在末尾插入数据，不转码中文，换行
                fwrite($file, json_encode(array_merge($start, $data, $end), JSON_UNESCAPED_UNICODE) . PHP_EOL);
                // 关闭文件
                fclose($file);
            }
            // 文件开头插入
            else if ($anchor == 'start') {
                // 打开文件
                $file = fopen($this->CONFIG['filePath'], 'r');
                // 读取文件内容
                $content = fread($file, filesize($this->CONFIG['filePath']));
                // 关闭文件
                fclose($file);
                // 打开文件
                $file = fopen($this->CONFIG['filePath'], 'w');
                // 在开头插入数据，不转码中文，换行
                fwrite($file, json_encode(array_merge($start, $data, $end), JSON_UNESCAPED_UNICODE) . PHP_EOL . $content);
                // 关闭文件
                fclose($file);
            }
        }

        return $result;
    }

    /**
     * 根据条件筛选数据
     * @param array $array
     * @return array
     */
    private function whereData()
    {
        // 根据条件递归查询 - 直到条件为空为止
        if (!empty($this->CONFIG['where'])) {
            // 执行 count($this->CONFIG['where']) 次
            foreach ($this->CONFIG['where'] as $key => $val) {
                // 将执行过的条件剔除
                unset($this->CONFIG['where'][$key]);
                // 执行查询
                $this->cullWhereData($val[0], $val[1], $val[2]);
            }
            // 递归调用自身，直到条件为空
            $this->whereData();
        }
    }

    /**
     * 根据条件剔除不符合的数据
     * @param string $key  键名
     * @param string $opt  操作符
     * @param string $val  值
     */
    private function cullWhereData($key, $opt, $val)
    {
        // 循环数据
        foreach ($this->CONFIG['runtime'] as $k => $v) {
            // 如果键名不存在，则剔除
            if (!isset($v[$key])) unset($this->CONFIG['runtime'][$k]);
            else {
                // 如果操作符为等于
                if ($opt == '=') {
                    // 如果值不等于条件值，则剔除
                    if ($v[$key] != $val and $val !== '') unset($this->CONFIG['runtime'][$k]);
                } else if ($opt == '>') {
                    // 如果值小于条件值，则剔除
                    if ($v[$key] <= $val and $val !== '') unset($this->CONFIG['runtime'][$k]);
                } else if ($opt == '<') {
                    // 如果值大于条件值，则剔除
                    if ($v[$key] >= $val and $val !== '') unset($this->CONFIG['runtime'][$k]);
                } else if ($opt == '!=' or $opt == '<>') {
                    // 如果值等于条件值，则剔除
                    if ($v[$key] == $val and $val !== '') unset($this->CONFIG['runtime'][$k]);
                } else if ($opt == '>='){
                    // 如果值小于等于条件值，则剔除
                    if ($v[$key] < $val and $val !== '')  unset($this->CONFIG['runtime'][$k]);
                } else if ($opt == '<='){
                    // 如果值大于等于条件值，则剔除
                    if ($v[$key] > $val and $val !== '')  unset($this->CONFIG['runtime'][$k]);
                }
            }
        }
    }

    /**
     * 根据条件筛选数据
     * @param array $array
     * @return array
     */
    private function whereOrData(array $array = [])
    {
        return array_merge(array_filter(array_map(function ($item) {
            // where条件不为空
            if (!empty($this->CONFIG['whereOr'])) foreach ($this->CONFIG['whereOr'] as $key => $val) {
                // 如果当前数组的key设置了
                if (isset($item[$val[0]])) {
                    // 等于运算
                    if ($val[1] === '=') {
                        if ($val[2] === '') return $item;
                        else if ($item[$val[0]] == $val[2]) return $item;
                    }
                    // 不等于运算
                    else if ($val[1] === '<>' or $val[1] === '!=') {
                        if ($val[2] === '') return $item;
                        else if ($item[$val[0]] != $val[2]) return $item;
                    }
                    // 大于运算
                    else if ($val[1] === '>') {
                        if ($val[2] === '') return $item;
                        else if ($item[$val[0]] > $val[2]) return $item;
                    }
                    // 小于运算
                    else if ($val[1] === '<') {
                        if ($val[2] === '') return $item;
                        else if ($item[$val[0]] < $val[2]) return $item;
                    }
                    // 大于等于运算
                    else if ($val[1] === '>=') {
                        if ($val[2] === '') return $item;
                        else if ($item[$val[0]] >= $val[2]) return $item;
                    }
                    // 小于等于运算
                    else if ($val[1] === '<=') {
                        if ($val[2] === '') return $item;
                        else if ($item[$val[0]] <= $val[2]) return $item;
                    }
                    // 包含运算
                    else if ($val[1] === 'like') {
                        if ($val[2] === '') return $item;
                        else if (strpos($item[$val[0]], $val[2]) !== false) return $item;
                    }
                }
            }
            // where条件为空，原数据返回
            else return $item;
        }, $array)));
    }

    public function test()
    {
        return $this->CONFIG;
    }

    /**
     * 根据条件筛选数据
     * @param string $field
     * @return function $function
     */
    public function withAttr(string $field = 'withAttr', $function = null)
    {
        $this->CONFIG['withAttr'] = ['name'=>$field, 'function'=>$function];
        return $this;
    }


    /**
     * 判断路径下的文件是否存在
     * @param string $filePath 文件路径
     * @return bool
     */
    public function has($filePath = '')
    {
        // 默认文件路径处理
        $filePath = !empty($filePath) ? $filePath : $this->CONFIG['filePath'];
        if (is_array($filePath)) {
            foreach ($filePath as $val) if (!is_file($val)) return false;
            return true;
        } else if (is_string($filePath)) return is_file($filePath);
    }

    /**
     * 创建文件
     * @param string $filePath 文件路径
     * @param bool $cover 是否覆盖原文件
     * @return bool
     */
    public function createFile(string $filePath = '', bool $cover = true)
    {
        // 默认文件路径处理
        $filePath = !empty($filePath) ? $this->dirReplace($filePath) : $this->dirReplace($this->CONFIG['filePath']);
        // 判断文件路径是否存在
        if (file_exists($filePath) && $cover) $this->delFile($filePath);
        // 创建多级目录
        $this->createDIR(dirname($filePath));
        // 创建文件并返回结果
        return touch($filePath, time(), time());
    }

    /**
     * 创建多级目录
     * @param string $dir
     * @param int $chmod
     * @return boolean
     */
    public function createDIR(string $dir = '', int $chmod = 0777)
    {
        // 递归创建目录
        return is_dir($dir) or ($this->createDIR(dirname($dir)) and mkdir($dir, $chmod));
    }

    /**
     * 删除文件
     * @param string $filePath
     * @return boolean
     */
    public function delFile(string $filePath = '') 
    {
        // 默认文件路径处理
        $filePath = !empty($filePath) ? $this->dirReplace($filePath) : $this->dirReplace($this->CONFIG['filePath']);
        // 文件存在时删除，不存在时不做任何操作
        if (file_exists($filePath)) return unlink($filePath);
    }

    /**
     * 替换相应的字符
     * @param string $filePath 路径
     * @return string
     */
    private function dirReplace($filePath)
    {
        // 判断是否为绝对路径
        return str_replace('//','/',str_replace('\\','/',$filePath));
    }

    /**
     * 数组深度合并
     * @param array $array1
     * @param array $array2
     * @return array
     */
    private function arrayDeepMerge(...$arrays) {
    	$result = [];
    	while ($arrays) {
    		$array = array_shift($arrays);
    		if (!$array) continue;
    		foreach ($array as $key => $val) {
    			if (is_string($key)) {
    				if (is_array($val) && array_key_exists($key, $result) && is_array($result[$key])) {
    					$result[$key] = $this->arrayDeepMerge(...[$result[$key], $val]);
    				} else $result[$key] = $val;
    			} else $result[] = $val;
    		}
    	}
    	return $result;
    }

    /**
     * 是否为二维数组
     * @param array $array
     * @return bool
     */
    private function twoArray(array $array = [])
    {
        $list = [];

        foreach ($array as $key => $val) {
            if (is_array($val)) $list[] = 2;
            else if (is_string($val) or is_int($val)) $list[] = 1;
        }
        
        return (in_array(1, $list) or empty($list)) ? false : true;
    }
}