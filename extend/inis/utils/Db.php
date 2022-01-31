<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: Db class - 数据库类
// +----------------------------------------------------------------------

namespace inis\utils;

class Db {
    
	// 数据库信息
	private $HOSTNAME;
	private $USERNAME;
	private $PASSWORD;
	private $HOSTPORT;
	private $DATABASE;
	private $CHARSET;
	private $link;
	private $tablepre;
	
	public function __construct(array $array)
	{
		$this->HOSTNAME = isset($array['HOSTNAME']) ? $array['HOSTNAME'] : '';
		$this->USERNAME = isset($array['USERNAME']) ? $array['USERNAME'] : '';
		$this->PASSWORD = isset($array['PASSWORD']) ? $array['PASSWORD'] : '';
		$this->HOSTPORT = isset($array['HOSTPORT']) ? $array['HOSTPORT'] : 3306;
		$this->DATABASE = isset($array['DATABASE']) ? $array['DATABASE'] : '';
		$this->CHARSET  = isset($array['CHARSET'])  ? $array['CHARSET']  : 'utf8';
		$this->tablepre = isset($array['tablepre']) ? $array['tablepre'] : '';
		
		$conn = $this->conn();
		
		if (!$conn['status']) {
			return $conn;
		}
	}
	
	// 链接设置数据库
	protected function conn()
	{
	    $result = ['status'=>true, 'info'=>'ok'];
	    
	    $link = mysqli_connect($this->HOSTNAME, $this->USERNAME, $this->PASSWORD, null, $this->HOSTPORT);
	    	
	    if (!$link) $result = ['status'=>false,'info'=>'数据库连接失败'];
	    else $this->link = $link;
	    
	    $version = mysqli_get_server_info($this->link);
	
	    // 设置字符集
	    if($version >= '5.7' && $this->CHARSET) {
	        mysqli_query($link, "SET NAMES {$this->CHARSET}");
	    }
	
	    // 选择数据库
	    mysqli_select_db($this->link, $this->DATABASE);
	    
	    return $result;
	}
	
	/**
     * @param $dbfile  要导入的sql数据文件
     * @param string $prefix  导入的sql文件的表前缀
     * @return array
     */
	public function importData($dbfile, $prefix = 'inis_')
	{
	    $result = [];
	    
	    if (!file_exists($dbfile)) $result = ['status'=>false,'info'=>'数据库文件不存在'];
	
	    $sql    = file_get_contents($dbfile);
	    $status = $this->execute($this->link, $sql, $prefix);
	    
	    if ($status) $result = ['status'=>true,'info'=>'导入数据库成功'];
	    else $result = ['status'=>true,'info'=>'导入数据库失败'];
	    
	    return $result;
	}
	
	/**
     * @param $link  数据库链接
     * @param $sql   要导入的sql语句
     * @param $prefix 导入的sql文件的表前缀
     * @return bool
     */
	protected function execute($link, $sql, $prefix)
	{
		$sqls = $this->sqlSplit($link, $sql, $prefix);
		if (is_array($sqls)) foreach($sqls as $sql) if(!empty(trim($sql))) mysqli_query($link, $sql);
		else mysqli_query($link,$sqls);
		
		return true;
	}
	
	/**
     * @param $link  表链接对象
     * @param $sql   导入的sql
     * @param $prefix  sql文件中的sql表前缀
     * @return array
     */
	protected function sqlSplit($link, $sql, $prefix)
	{
		if(mysqli_get_server_info($link) >= '5.7' && $this->CHARSET) {
		    $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=".$this->CHARSET,$sql);
		}
		
		// 如果有表前缀就替换现有的前缀
		if($this->tablepre) $sql=str_replace($prefix, $this->tablepre, $sql);
		
		$sql = str_replace("\r", "\n", $sql);
		$ret = [];
		$num = 0;
		
		$queriesarray = explode(";\n", trim($sql));
		
		unset($sql);
		
		foreach($queriesarray as $query) {
		    
		    $result[$num] = '';
		    $queries = explode("\n", trim($query));
		    $queries = array_filter($queries);
		    
		    foreach($queries as $query) {
		        $str1 = substr($query, 0, 1);
		        if ($str1 != '#' && $str1 != '-') $result[$num] .= $query;
		    }
		    
		    $num++;
		}
		return $result;
	}
}

// 用法
// $array = [
//     'HOSTNAME'  => 'localhost',
//     'USERNAME'  => 'api_test',
//     'PASSWORD'  => '123456',
//     'DATABASE'  => 'api_test',
//     'HOSTPORT'  => 3306,
//     'CHARSET'   => 'utf8mb4'
// ];

// $DB = new Db($array);
// $result = $DB->importData('storage/runtime/db.sql');

// return json($result);