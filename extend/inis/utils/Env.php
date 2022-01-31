<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: Env class - 读取.env文件
// +----------------------------------------------------------------------

namespace inis\utils;

/**
 * Class Env
 * @package inis\utils
 */
class Env
{
    protected $FILEPATH = '';

    /**
     * 加载配置文件
     * @access public
     * @param string $filePath 配置文件路径
     * @return array
     */
    public function load(string $filePath)
    {
        if (!file_exists($filePath)) throw new \Exception('配置文件' . $filePath . '不存在');
        // 返回二位数组
        $env = parse_ini_file($filePath, true);
        return $env;
    }
    
    public function __construct(string $filePath = null)
    {
        $this->FILEPATH = (empty($filePath)) ? '.env' : $filePath;
    }

    /**
     * 获取环境变量值
     * @access public
     * @param string $name 环境变量名（支持二级 . 号分割）
     * @param string $default 默认值
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        $result = $default;
        $env    = $this->load($this->FILEPATH);
        // 数组键名转大写
        $this->ArrayKeysToUpper($env, CASE_UPPER, true);
        
        $array  = explode('.', $name);
        // 数组键名转大写
        foreach ($array as $key => $value) $array[$key] = strtoupper($value);
        
        if (count($array) == 1) {
            
            if (in_array($array[0], array_keys($env))) $result = $env[$array[0]];
            
        } else {
            
            if (in_array($array[0], array_keys($env))) {
                if (in_array($array[1], array_keys($env[$array[0]]))) {
                    $result = $env[$array[0]][$array[1]];
                }
            }
        }
        
        return $result;
    }
    
    // 数组键名转大写
    public function ArrayKeysToUpper(&$array, $case = CASE_LOWER, $flag = false)
    {
        $array = array_change_key_case($array, $case);
        if ($flag) foreach ($array as $key => $value) if (is_array($value)) $this->ArrayKeysToUpper($array[$key], $case, true);
    }
}

// 用法
// $env  = new Env('../.env');
// $data = $env->get('database.hostname');
// ---------------------------------------------------------------------
// .env文件格式

// APP_DEBUG = true

// [APP]
// DEFAULT_TIMEZONE = Asia/Shanghai

// [DATABASE]
// TYPE     = mysql
// HOSTNAME = localhost
// DATABASE = db_name
// USERNAME = db_user
// PASSWORD = db_password
// HOSTPORT = 3306
// CHARSET  = utf8mb4
// DEBUG    = true
// PREFIX   = inis_

// [LANG]
// default_lang = zh-cn

// [WEB]
// APPCODE = inisblog
// KEY = aisfucasoiasdadwa_inis
// ISS = INIS
// AUD = YUEQING
// EXPIRE = 14400