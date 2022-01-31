<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: FileDB class - 文件数据库
// +----------------------------------------------------------------------

namespace inis\utils;

/**
 * Class FileDB
 * @package inis\utils
 */
class FileDB
{
    // 选择表
    private $table      = '';
    // 表存储路径
    private $DBFile     = '';
    // 头表存储路径
    private $HeadFile   = '';
    private $where      = [];
    private $info       = null;
    private $sort       = '';
    
    // 选择表
    public static function table($table, $DBPath = 'FileDB')
    {
        $DB = new FileDB($table, $DBPath);
        
        return $DB;
    }
    
    // 构造函数 FileDB __construct
    public function __construct($table, $DBPath = 'FileDB')
    {
        $this->table = $table;
        
        if (strpos($DBPath, '/') === false) {
            
            $this->DBFile   = './' . $DBPath . '/' . $table . '.idb';
            $this->HeadFile = './' . $DBPath . '/' . $table . '.idbh';
            
        } else {
            
            $this->DBFile   = $DBPath . '/' . $table . '.idb';
            $this->HeadFile = $DBPath . '/' . $table . '.idbh';
        }
        
        if (!file_exists($this->DBFile)) $this->init();
        
        // 获取信息
        $content = @file_get_contents($this->HeadFile);
        $this->info = json_decode($content, true);
    }

    // 初始化
    private function init()
    {
        $content = [
            'version'     => 'FileDB v1.0',
            'table'       => $this->table,
            'last_id'     => 0,
            'count'       => 0,
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s'),
        ];
        
        file_put_contents($this->HeadFile, json_encode($content));
        file_put_contents($this->DBFile, '');
    }

    // 获取信息
    public function info()
    {
        return $this->info;
    }
    
    // 保存信息
    private function saveInfo()
    {
        return file_put_contents($this->HeadFile, json_encode($this->info));
    }

    // 获取列表
    public function select()
    {
        $result = [];
        
        $handle = @fopen($this->DBFile, 'r');
        while (($buffer = fgets($handle)) !== false) {
            $item = $this->parseWhere($buffer);
            if ($item !== null) $result[] = $item;
        }
        fclose($handle);
        
        // 处理排序
        if ($this->sort != '') {
            $sort_key = $sort = trim($this->sort);
            $sort_attr = SORT_REGULAR;
            
            if (strtolower(substr($sort, -5)) == ' desc') {
                $sort_attr = $sort_attr|SORT_DESC;
                $sort_key = substr($sort, 0, -5);
            }
            
            $sort_data = [];
            
            foreach ($result as $key => $arr) $sort_data[$key] = $arr[$sort_key];
              
            array_multisort($sort_data, $sort_attr, $result);
        }
        
        return $result;
    }
    
    // 
    public function order($sort)
    {
        $this->sort = $sort;
        
        return $this;
    }

    // 获取单条值
    public function find($id = null)
    {
        if (empty($id)) {
            
            $result = $this->select();
            $result = (count($result) > 0) ? $result[0] : [];
            
        } else {
            
            $result = null;
            
            $id = intval($id);
            $index = 0;
            $handle = @fopen($this->DBFile, 'r');
            while (($buffer = fgets($handle)) !== false) {
                
                $index++;
                
                if ($index == $id) {
                    $result = trim($buffer);
                    $result = json_decode($result, true);
                    break;
                }
            }
            
            fclose($handle);
        }
        
        return $result;
    }

    // 条件过滤
    public function where($data, $op = null, $value = null)
    {
        // 第一参数为数组
        if (is_array($data)) {
            
            // 空数组 - 不执行where
            if (empty($data)) return;
            else {
                
                // 一维数组
                if (count($data) == count($data, 1)) foreach ($data as $k => $v) $this->where[] = [$k, '=', $v];
                // 二维数组
                else foreach ($data as $val) $this->where[] = [$val[0], $val[1], $val[2]];
            }
            
        } else if (empty($value)) return $this->where($data, '=', $op);
        else $this->where[] = [$data, $op, $value];
        
        return $this;
    }

    // 解析条件过滤
    private function parseWhere($str)
    {
        $str = trim($str);
        
        if (empty($str)) return null;
        
        $item = json_decode($str, true);
        
        if ($item === false) return null;
        
        if (!empty($this->where)) {
            
            $bo = true;
            
            foreach ($this->where as $r) {
                
                $k = $r[0];
                $v = $r[2];
                
                if (!isset($item[$k])) return null;
                
                switch ($r[1]) {
                    case '=':
                        if ($item[$k] != $v) return null;
                        break;
                    case '>':
                        if ($item[$k] <= $v) return null;
                        break;
                    case '<':
                        if ($item[$k] >= $v) return null;
                        break;
                }
            }
        }
        
        return $item;
    }

    // 数量限制
    public function selectLimit($start, $length = 0)
    {
        if ($length === 0) {
            $length = $start;
            $start = 0;
        }
        
        if ($length <= 0) return [];
        
        $result = [];
        
        $index = 0;
        $count = 0;
        $handle = @fopen($this->DBFile, 'r');
        
        while (($buffer = fgets($handle)) !== false) {
            
            $item = $this->parseWhere($buffer);
            
            if ($item !== null) {
                
                $index++;
                
                if ($index > $start) {
                    $count++;
                    $result[] = $item;
                }
                
                if ($count >= $length) break;
            }
        }
        
        fclose($handle);
        
        return $result;
    }

    // 数量查询
    public function count()
    {
        $count = 0;
        
        $handle = @fopen($this->DBFile, 'r');
        
        while (($buffer = fgets($handle)) !== false) {
            
            $item = $this->parseWhere($buffer);
            
            if ($item !== null) $count++;
        }
        
        fclose($handle);
        
        return $count;
    }

    // 插入单条数据
    public function insert($item)
    {
        $this->info['last_id']++;
        $this->info['count']++;
        
        $item['id'] = $this->info['last_id'];
        
        file_put_contents($this->DBFile, json_encode($item) . PHP_EOL, FILE_APPEND|LOCK_EX);
        
        $this->saveInfo();
        
        return $item['id'];
    }

    // 批量插入
    public function insertAll($list)
    {
        $content = '';
        
        foreach ($list as $r) {
            
            $this->info['last_id']++;
            $this->info['count']++;
            
            $r['id'] = $this->info['last_id'];
            
            $content .= json_encode($r) . PHP_EOL;
        }
        
        file_put_contents($this->DBFile, $content, FILE_APPEND|LOCK_EX);
        
        $this->saveInfo();
        
        return count($list);
    }

    // 条件更新
    private function updateWhere($update)
    {
        $bo = false;
        
        $handle_writer = @fopen($this->DBFile . 'w', 'w');
        $handle_reader = @fopen($this->DBFile, 'r');
        
        while (($buffer = fgets($handle_reader)) !== false) {
            
            $item = $this->parseWhere($buffer);
            
            if ($item !== null) {
                $new = array_merge($item, $update);
                fputs($handle_writer, json_encode($new) . PHP_EOL);
                $bo = true;
            } else fputs($handle_writer, $buffer);
        }
        
        fclose($handle_reader);
        fclose($handle_writer);
        
        if ($bo) rename($this->DBFile . 'w', $this->DBFile);
        else unlink($this->DBFile . 'w');
    }

    // 单条更新
    public function update($item)
    {
        if (!empty($this->where)) {
            
            if (isset($item['id'])) return false;
            
            $this->updateWhere($item);
            
            return true;
        }
        
        if (empty($item['id'])) return false;
        
        $bo = false;
        $id = $item['id'];
        $index = 0;
        $handle_writer = @fopen($this->DBFile . 'w', 'w');
        $handle_reader = @fopen($this->DBFile, 'r');
        
        while (($buffer = fgets($handle_reader)) !== false) {
            
            $index++;
            
            if ($index == $id) {
                
                $old = trim($buffer);
                $old = json_decode($old, true);
                
                if (!empty($old)) {
                    
                    $new = array_merge($old, $item);
                    fputs($handle_writer, json_encode($new) . PHP_EOL);
                    $bo = true;
                    
                } else fputs($handle_writer, $buffer);
            } else fputs($handle_writer, $buffer);
            
        }
        
        fclose($handle_reader);
        fclose($handle_writer);
        
        if ($bo) rename($this->DBFile . 'w', $this->DBFile);
        else unlink($this->DBFile . 'w');
        
        return $bo;
    }

    // 批量更新
    public function updateAll($list)
    {
        $arr = array_column($list, null, 'id');
        
        $count = 0;
        $index = 0;
        $handle_writer = @fopen($this->DBFile . 'w', 'w');
        $handle_reader = @fopen($this->DBFile, 'r');
        
        while (($buffer = fgets($handle_reader)) !== false) {
            
            $index++;
            
            if (isset($arr[$index])) {
                
                $old = trim($buffer);
                $old = json_decode($old, true);
                
                if (!empty($old)) {
                    $new = array_merge($old, $arr[$index]);
                    fputs($handle_writer, json_encode($new) . PHP_EOL);
                    $count++;
                } else fputs($handle_writer, $buffer);
                
            } else fputs($handle_writer, $buffer);
            
        }
        
        fclose($handle_reader);
        fclose($handle_writer);
        
        if ($count > 0) rename($this->DBFile . 'w', $this->DBFile);
        else unlink($this->DBFile . 'w');
        
        return $count;
    }

    // 条件删除
    private function deleteWhere()
    {
        $bo = false;
        
        $handle_writer = @fopen($this->DBFile . 'w', 'w');
        $handle_reader = @fopen($this->DBFile, 'r');
        
        while (($buffer = fgets($handle_reader)) !== false) {
            
            $item = $this->parseWhere($buffer);
            
            if ($item !== null) {
                
                fputs($handle_writer, PHP_EOL);
                $bo = true;
                $this->info['count']--;
                
            } else fputs($handle_writer, $buffer);
        }
        
        fclose($handle_reader);
        fclose($handle_writer);
        
        if ($bo) {
            rename($this->DBFile . 'w', $this->DBFile);
            $this->saveInfo();
        } else unlink($this->DBFile . 'w');
    }

    // 删除
    public function delete($id = null)
    {
        if (!empty($this->where)) {
            
            $this->deleteWhere();
            
            return true;
        }

        if (empty($id)) return false;

        $bo = false;
        $index = 0;
        $handle_writer = @fopen($this->DBFile . 'w', 'w');
        $handle_reader = @fopen($this->DBFile, 'r');
        
        while (($buffer = fgets($handle_reader)) !== false) {
            
            $index++;
            
            if ($index == $id) {
                
                $old = trim($buffer);
                $old = json_decode($old, true);
                
                if (!empty($old)) {
                    fputs($handle_writer, PHP_EOL);
                    $bo = true;
                } else fputs($handle_writer, $buffer);
            } else fputs($handle_writer, $buffer);
        }
        
        fclose($handle_reader);
        fclose($handle_writer);
        
        if ($bo) {
            
            rename($this->DBFile . 'w', $this->DBFile);
            $this->info['count']--;
            $this->saveInfo();
            
        } else unlink($this->DBFile . 'w');
        
        return $bo;
    }

    // 清空表
    public function deleteAll($where = null)
    {
        if ($where === null) $this->init();
        
        return true;
    }
}