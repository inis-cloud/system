<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: File class - 文件系统
// +----------------------------------------------------------------------

namespace inis\utils;

/**
 * Class File
 * @package inis\utils
 */
class File
{
    /**
     * 创建多级目录
     * @param string $dir
     * @param int $chmod
     * @return boolean
     */
    public function createDir(string $dir = '', int $chmod = 0777)
    {
        return is_dir($dir) or ($this->createDir(dirname($dir)) and mkdir($dir, $chmod));
    }
    
    /**
     * 创建指定路径下的指定文件
     * @param string $path(需要包含文件名和后缀)
     * @param boolean $cover 是否覆盖文件
     * @param int $time 设置时间。默认是当前系统时间
     * @param int $visit_time 设置访问时间。默认是当前系统时间
     * @return boolean
     */
    public function createFile(string $path, bool $cover = false, int $time = null, int $visit_time = null)
    {
        $path  = $this->dirReplace($path);
        $time  = empty($time) ? time() : $time;
        $visit_time = empty($visit_time) ? time() : $visit_time;
        
        if(file_exists($path) && $cover) $this->unlinkFile($path);
        
        $catalog = dirname($path);
        $this->createDir($catalog);
        
        return touch($path, $time, $visit_time);
    }
    
    /**
     * 文件操作(复制/移动)
     * @param string $old_path 指定要操作文件路径(需要含有文件名和后缀名)
     * @param string $new_path 指定新文件路径（需要新的文件名和后缀名）
     * @param string $mode 文件操作类型
     * @param boolean $over 是否覆盖已存在文件
     * @return boolean
     */
    public function handleFile(string $old_path, string $new_path, string $mode = 'copy', bool $over = false)
    {
        $old_path = $this->dirReplace($old_path);
        $new_path = $this->dirReplace($new_path);
        
        if(file_exists($new_path) && $over = false) return false;
        else if(file_exists($new_path) && $over = true) $this->unlinkFile($new_path);
        
        $catalog = dirname($new_path);
        $this->createDir($catalog);
        
        switch ($mode){
            case 'copy':
                $result = copy($old_path, $new_path);
                break;
            case 'move':
                $result = rename($old_path, $new_path);
                break;
        }
        
        return $result;
    }
    
    /**
     * 文件夹操作(复制/移动)
     * @param string $old_path 指定要操作文件夹路径
     * @param string $new_path 指定新文件夹路径
     * @param string $mode 操作类型
     * @param boolean $over 是否覆盖文件和文件夹
     * @return boolean
     */
    public function handleDir(string $old_path, string $new_path, string $mode = 'copy', bool $over = false)
    {
        $new_path = $this->checkPath($new_path);
        $old_path = $this->checkPath($old_path);
        
        if (!is_dir($old_path)) $result = false;
        if (!file_exists($new_path)) $this->createDir($new_path);
        
        $dir_handle = opendir($old_path);
        if (!$dir_handle) $result = false;
        
        $boolean = true;
        
        while(false !== ($file = readdir($dir_handle))){
            
            if ($file == '.' || $file == '..') continue;
            
            if (!is_dir($old_path.$file)) $boolean = $this->handleFile($old_path.$file, $new_path.$file, $mode, $over);
            else $this->handleDir($old_path.$file, $new_path.$file, $mode, $over);
        }
        
        switch ($mode){
            
            case 'copy':
                closedir($dir_handle);
                $result = $boolean;
                break;
            case 'move':
                closedir($dir_handle);
                $result = rmdir($old_path);
                break;
        }
        
        return $result;
    }
    
    /**
     * 文件重命名
     * @param string $old_name
     * @param string $new_name
     */
    public function rename(string $old_name, string $new_name)
    {
        if(($new_name != $old_name) && is_writable($old_name)) return rename($old_name, $new_name);
    }
    
    /**
     * 获取指定路径下的信息
     * @param string $dir 路径
     * @return ArrayObject
     */
    public function getDirInfo(string $dir)
    {
        // 打开指定目录
        $handle = @opendir($dir);
        
        $file_cout       = 0;
        $total_size      = 0;
        $directory_count = 0;
        
        while (false !== ($file_path = readdir($handle))){
            
            if($file_path != "." && $file_path != ".."){
                
                //is_dir("$dir/$file_path") ? $sizeResult += $this->get_dir_size("$dir/$file_path") : $sizeResult += filesize("$dir/$file_path");
                $next_path = $dir.'/'.$file_path;
                
                if (is_dir($next_path)){
                    
                    $directory_count++;
                    $result = self::getDirInfo($next_path);
                    $total_size += $result['size'];
                    $file_cout += $result['file_count'];
                    $directory_count += $result['dir_count'];
                    
                }elseif (is_file($next_path)){
                    $total_size += filesize($next_path);
                    $file_cout++;
                }
            }
        }
        
        // 关闭指定目录
        closedir($handle);
        
        $result = [
            'size'      =>   $total_size,
            'file_count'=>   $file_cout,
            'dir_count' =>   $directory_count
        ];
        
        return $result;
    }
    
    /**
     * 列出指定目录下符合条件的文件和文件夹
     * @param string $dir 路径
     * @param boolean $is_all 是否列出子目录中的文件
     * @param string $exts 需要列出的后缀名文件
     * @param string $sort 数组排序
     * @return ArrayObject
     */
    public function listDirInfo(string $dir = './', bool $is_all = false, string $exts = '', string $sort = 'asc')
    {
        // 处理多于的/号
        $new = strrev($dir);
        if(strpos($new,'/') == 0) $new = substr($new, 1);
        
        $dir = strrev($new);
        // 将字符转换成小写
        $sort = strtolower($sort);
        
        $files = [];
        $sub_files = [];
        
        if(is_dir($dir)){
            
            $fh = opendir($dir);
            
            while (($file = readdir($fh)) !== false){
                
                if (strcmp($file, '.') == 0 || strcmp($file, '..') == 0) continue;
                
                $file_path = $dir.'/'.$file;
                
                switch ($exts){
                    
                    case '*':
                        if (is_dir($file_path) && $is_all == true) $files = array_merge($files, self::listDirInfo($file_path, $is_all, $exts, $sort));
                        array_push($files,$file_path);
                        break;
                    case 'folder':
                        if (is_dir($file_path) && $is_all == true){
                            $files = array_merge($files, self::listDirInfo($file_path, $is_all, $exts, $sort));
                            array_push($files, $file_path);
                        }elseif (is_dir($file_path)) array_push($files, $file_path);
                        break;
                    case 'file':
                        if (is_dir($file_path) && $is_all == true) $files = array_merge($files, self::listDirInfo($file_path, $is_all, $exts, $sort));
                        elseif (is_file($file_path)) array_push($files, $file_path);
                        break;
                    default:
                        if (is_dir($file_path) && $is_all == true) $files = array_merge($files, self::listDirInfo($file_path, $is_all, $exts, $sort));
                        elseif(preg_match("/\.($exts)/i",$file_path) && is_file($file_path)) array_push($files, $file_path);
                        break;
                }
                
                switch ($sort){
                    case 'asc':
                        sort($files);
                        break;
                    case 'desc':
                        rsort($files);
                        break;
                    case 'nat':
                        natcasesort($files);
                        break;
                }
            }
            
            closedir($fh);
            $result = $files;
            
        }else $result = false;
        
        return $result;
    }
    
    /**
     * 返回指定路径的文件夹信息，其中包含指定路径中的文件和目录
     * @param string $dir
     * @return ArrayObject
     */
    public function dirInfo(string $dir)
    {
        return scandir($dir);
    }
    
    /**
     * 判断目录是否为空
     * @param string $dir
     * @return boolean
     */
    public function isEmpty(string $dir)
    {
        return (($files = @scandir($dir)) && count($files) <= 2);
        
        // return $result;
    }
    
    /**
     * 返回指定文件和目录的信息
     * @param string $file
     * @return ArrayObject
     */
    public function listInfo(string $file)
    {
        $dir = [];
        $dir['file_name']  = basename($file);   // 返回路径中的文件名部分。
        $dir['path_name']  = realpath($file);   // 返回绝对路径名。
        $dir['owner']      = fileowner($file);  // 文件的 user ID （所有者）。
        $dir['perms']      = fileperms($file);  // 返回文件的 inode 编号。
        $dir['inode']      = fileinode($file);  // 返回文件的 inode 编号。
        $dir['group']      = filegroup($file);  // 返回文件的组 ID。
        $dir['path']       = dirname($file);    // 返回路径中的目录名称部分。
        $dir['atime']      = fileatime($file);  // 返回文件的上次访问时间。
        $dir['ctime']      = filectime($file);  // 返回文件的上次改变时间。
        $dir['perms']      = fileperms($file);  // 返回文件的权限。 
        $dir['size']       = filesize($file);   // 返回文件大小。
        $dir['type']       = filetype($file);   // 返回文件类型。
        $dir['ext']        = is_file($file) ? pathinfo($file,PATHINFO_EXTENSION) : '';  // 返回文件后缀名
        $dir['mtime']      = filemtime($file);  // 返回文件的上次修改时间。
        $dir['isDir']      = is_dir($file);     // 判断指定的文件名是否是一个目录。
        $dir['isFile']     = is_file($file);    // 判断指定文件是否为常规的文件。
        $dir['isLink']     = is_link($file);    // 判断指定的文件是否是连接。
        $dir['isReadable'] = is_readable($file);// 判断文件是否可读。
        $dir['isWritable'] = is_writable($file);// 判断文件是否可写。
        $dir['isUpload']   = is_uploaded_file($file);   // 判断文件是否是通过 HTTP POST 上传的。
        return $dir;
    }
    
    /**
     * 返回关于打开文件的信息
     * @param $file
     * @return ArrayObject
     * 数字下标     关联键名（自 PHP 4.0.6）     说明
     * 0      dev       设备名
     * 1      ino       号码
     * 2      mode      node 保护模式
     * 3      nlink     被连接数目
     * 4      uid       所有者的用户 id
     * 5      gid       所有者的组 id
     * 6      rdev      设备类型，如果是 inode 设备的话
     * 7      size      文件大小的字节数
     * 8      atime     上次访问时间（Unix 时间戳）
     * 9      mtime     上次修改时间（Unix 时间戳）
     * 10     ctime     上次改变时间（Unix 时间戳）
     * 11     blksize   文件系统 IO 的块大小
     * 12     blocks    所占据块的数目
     */
    public function openInfo(string $file)
    {
        $file = fopen($file,"r");
        $result = fstat($file);
        fclose($file);
        
        return $result;
    }
    
    /**
     * 改变文件和目录的相关属性
     * @param string $file 文件或路径
     * @param string $mode 操作类型
     * @param string $ch_info 操作信息
     * @return boolean
     */
    public function changeFile(string $file, string  $mode, $ch_info)
    {
        switch ($mode){
            case 'group' : $is_ok = chgrp($file, $ch_info);  // 改变文件组。
                break;
            case 'mode'  : $is_ok = chmod($file, $ch_info);  // 改变文件模式。
                break;
            case 'ower'  : $is_ok = chown($file, $ch_info);  // 改变文件所有者。
                break;
        }
    }
    
    /**
     * 取得文件路径信息
     * @param $file 路径或文件
     * @return ArrayObject
     */
    public function getFileType(string $file)
    {
        return pathinfo($file);
    }
    
    /**
     * 关闭文件操作
     * @param string $path
     * @return boolean
     */
    public function close(string $path)
    {
        return fclose($path);
    }
    
    /*
     * 读取文件内容
     */
    public function readFile($file_name, $array = false)
	{
		$result = '';
		if ($array and function_exists('file')) @$result = file($file_name);
		else if (!$array and function_exists('file_get_contents')) @$result = file_get_contents($file_name);
		else {
		    if (@$fp = fopen($file_name, 'r')) {
				@$result = fread($fp, filesize($file_name));
				@fclose($fp);
			}
		}
		return $result;
	}
	
    // 读取文件	
	public function read($file_path)
	{
	    $result = [];
	    
	    if (is_array($file_path))       $path_array   = $file_path;
	    else if (is_string($file_path)) $path_array[] = $file_path;
	    
	    foreach ($path_array as $val) $result = array_merge($result,file($val));
	    
	    return $result;
	}
	
	public function writeFile($file_name, $text, $open_mod = 'w')
	{
		if (@$fp = fopen($file_name, $open_mod)) {
			flock($fp, 2);
			fwrite($fp, $text);
			fclose($fp);
			$result = true;
		} else $result = false;
		
		return $result;
	}
	
	// 列出目录
	public function getDir($dir) 
	{
		$dir = rtrim($dir,'/').'/';
		$dirArray [][] = NULL;
		if (false != ($handle = opendir ( $dir )))
		{
			$i = 0;
			$j = 0;
			while ( false !== ($file = readdir ( $handle )) ) 
			{
				if (is_dir ( $dir . $file )) 
				{ //判断是否文件夹
					$dirArray ['dir'] [$i] = $file;
					$i ++;
				} 
				else 
				{
					$dirArray ['file'] [$j] = $file;
					$j ++;
				}
			}
			closedir ($handle);
		}
		return $dirArray;
	}
	
    // 统计文件夹大小	
	public function getSize($dir)
	{ 
		$dirlist = opendir($dir);
		$dirsize = 0;
		while (false !==  ($folderorfile = readdir($dirlist)))
		{ 
			if($folderorfile != "." && $folderorfile != "..")
			{ 
				if (is_dir("$dir/$folderorfile"))
				{ 
					$dirsize += self::getSize("$dir/$folderorfile"); 
				}
				else
				{ 
					$dirsize += filesize("$dir/$folderorfile"); 
				}
			}    
		}
		closedir($dirlist);
		return $dirsize;
	}
	
	// 检测是否为空文件夹
	public function emptyDir($dir)
	{
		return (($files = @scandir($dir)) && count($files) <= 2); 
	}
    
    // /**
    //  * 读取文件操作
    //  * @param string $file
    //  * @return boolean
    //  */
    // public function readFile(string $file)
    // {
    //     return @file_get_contents($file);
    // }
    
    /**
     * 确定服务器的最大上传限制（字节数）
     * @return int 服务器允许的最大上传字节数
     */
    public function allowUploadSize()
    {
        $val = trim(ini_get('upload_max_filesize'));
        return $val;
    }
    
    /**
     * 字节格式化 把字节数格式为 B K M G T P E Z Y 描述的大小
     * @param int $size 大小
     * @param int $dec 显示类型
     * @return int
     */
    public function byteFormat($size, $dec = 2)
    {
        $a = array("B", "KB", "MB", "GB", "TB", "PB","EB","ZB","YB");
        $pos = 0;
        while ($size >= 1024){
            
             $size /= 1024;
             $pos++;
        }
        
        return round($size,$dec)." ".$a[$pos];
    }
    
    /**
     * 获取完整文件名
     * @param string $fn 路径
     * @return string
     */
    public function getBaseName($file_path)
    {
        $file_path = $this->dirReplace($file_path);
        return basename(str_replace('\\', '/', $file_path));
    }
    
    /**
     * 获取文件后缀名
     * @param string $file 文件路径
     * @return string
     */
    public function getExt($file)
    {
        $file = $this->dirReplace($file);
        return pathinfo($file,PATHINFO_EXTENSION);
    }
    
    /**
     * 取得指定目录名称
     * @param string $path 文件路径
     * @param int $num 需要返回以上级目录的数
     * @return string
     */
    public function fatherDir($path, $num = 1)
    {
        $path = $this->dirReplace($path);
        $arr = explode('/',$path);
        if ($num == 0 || count($arr)<$num) $result = pathinfo($path,PATHINFO_BASENAME);
        $result = substr(strrev($path),0,1) == '/' ? $arr[(count($arr)-(1+$num))] : $arr[(count($arr)-$num)];
        
        return $result;
    }
    
    /**
     * 替换相应的字符
     * @param string $path 路径
     * @return string
     */
    public function dirReplace($path)
    {
        return str_replace('//','/',str_replace('\\','/',$path));
    }
    
    /**
     * 删除文件
     * @param string $path
     * @return boolean
     */
    public function unlinkFile(string $path) 
    {
        $path = $this->dirReplace($path);
        if (file_exists($path)) return unlink($path);
    }
    
    /**
     * 删除非空目录
     * 说明:只能删除非系统和特定权限的文件,否则会出现错误
     * @param string $dir_path 目录路径
     * @param boolean $is_all 是否删除所有
     * @return boolean
     */
    public function removeDir(string $dir_path, bool $is_all = false)
    {
        $dir_name = $this->dirReplace($dir_path);
        $handle = @opendir($dir_name);
        while (($file = @readdir($handle)) !== false){
            
            if($file != '.' && $file != '..'){
                
                $dir = $dir_name . '/' . $file;
                if($is_all) is_dir($dir) ? $this->removeDir($dir) : $this->unlinkFile($dir);
                else if(is_file($dir)) $this->unlinkFile($dir);
            }
        }
        closedir($handle);
        
        return @rmdir($dir_name);
    }
    
    /**
     * 文件保存路径处理
     * @return string
     */
    public function checkPath($path)
    {
        return (preg_match('/\/$/',$path)) ? $path : $path . '/';
    }
    
    /**
    * 下载文件到服务器 - 不太好用 - 个别服务器会有不兼容的问题
    */
    public function download($url, $save_dir = '', $file_name = '', $type = 0)
    {
    	if (empty(trim($url))) $result = false;
    	if (empty(trim($save_dir))) $save_dir = './';
    	if (0 !== strrpos($save_dir, '/')) $save_dir.= '/';
    	
    	// 创建保存目录
    	if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) $result = false;
    	
    	// 获取远程文件所采用的方法
    	if ($type) {
    		$ch = curl_init();
    		$timeout = 5;
    		curl_setopt($ch, CURLOPT_URL, $url);
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    		$content = curl_exec($ch);
    		curl_close($ch);
    	} else {
    		ob_start();
    		readfile($url);
    		$content = ob_get_contents();
    		ob_end_clean();
    	}
    	
    	// 文件大小
    	$size = strlen($content);
    	
    	$fp2  = @fopen($save_dir . $file_name, 'a');
    	fwrite($fp2, $content);
    	fclose($fp2);
    	unset($content, $url);
    	
    	$result['code'] = 200;
    	$result['fild_name'] = $file_name;
    	
    	return $result;
    }
    
    /*
     * 保存文件方法
     */
    public function saveFile($url, $file, $path = './', $filename = '', $cover = true)
    {
        // 是否自定义文件名
        if (empty($filename)) $filename = pathinfo($url, PATHINFO_BASENAME);
        // 是否覆盖原有内容
        if ($cover) if (file_exists($path . $filename)) $this->unlinkFile($path . $filename);
        // 打开文件
        $resource = fopen($path . $filename, 'a');
        // 写入文件
        fwrite($resource, $file);
        // 关闭文件
        fclose($resource);
    }
    
    /*
     * 下载文件方法 - 比上面的下载方法好用
     */
    public function downloadFile($url, $path = './', $filename = '', $cover = true)
    {
        if (empty(trim($path))) $path = './';
        if (0 !== strrpos($path, '/')) $path .= '/';
        
        // 创建保存目录
        if (!file_exists($path) && !mkdir($path, 0777, true)) $result = false;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        // 避免https 的ssl验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSLVERSION, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $file = curl_exec($ch);
        if (curl_exec($ch) === false) echo 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        // 保存文件
        $this->saveFile($url, $file, $path, $filename, $cover);
    }
}