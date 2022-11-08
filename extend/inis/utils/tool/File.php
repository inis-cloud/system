<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: File class - 数组类
// +----------------------------------------------------------------------

namespace inis\utils\tool;

/**
 * Class File
 * @package inis\utils\tool
 */
class File
{
    // 操作的文件
    private $path  = '';
    // 文件名
    private $name  = '';
    // 加载文件
    private $load  = '';
    // 默认权限
    private $chmod = 0755;
    // 文件后缀
    private $ext   = 'htm,html,js,css';

    // 设置路径
    public function path($path = null)
    {
        if (!empty($path)) $this->path = $path;
        return $this;
    }

    // 设置文件名
    public function name($name = null)
    {
        if (!empty($name)) $this->name = $name;
        return $this;
    }

    // 加载文件 - 带路径 + 文件名 = $path + $name
    public function load($load = null)
    {
        if (!empty($load)) $this->load = $load;
        return $this;
    }

    // 设置权限
    public function chmod($chmod = null)
    {
        if (!empty($chmod)) $this->chmod = $chmod;
        return $this;
    }

    // 设置后缀
    public function ext($ext = null)
    {
        if (!empty($ext)) {

            if (is_string($ext)) $ext = explode(',', $ext);

            // 去重 去空 重排 去空格
            $ext = array_map('trim', $ext);
            $ext = array_unique($ext);
            $ext = array_filter($ext);
            $ext = array_values($ext);
            $this->ext = implode(',', $ext);
        }
        return $this;
    }

    // 读取文件
    // 用法：(new utils)->file->load($path . $name)->read(true);
    public function read($line = false)
    {
        $path = !empty($this->load) ? $this->load : $this->path . $this->name;

        if (!file_exists($path)) return false;

        if (file_exists($path)) {
            $file = fopen($path, 'r');
            $data = fread($file, filesize($path));
            fclose($file);
            // 按行读取
            if ($line) $data = explode(PHP_EOL, $data);
            // 去掉 /r
            if (is_array($data)) foreach ($data as $key => $val) $data[$key] = str_replace("\r", '', $val);
            return $data;
        }
    }

    // 写入文件
    // 用法：(new utils)->file->load($path . $name)->write($text);
    public function write($text = null, $append = true)
    {
        $path = !empty($this->load) ? $this->load : $this->path . $this->name;

        // 文件不存在，创建文件
        if (!file_exists($path)) {
            $file = fopen($path, 'w');
            fwrite($file, $text);
            fclose($file);
            chmod($path, $this->chmod);
        }
        // 文件存在，修改文件
        else {
            // 追加内容
            if ($append) {
                $file = fopen($path, 'a');
                fwrite($file, $text);
                fclose($file);
            }
            // 覆盖内容
            else {
                $file = fopen($path, 'w');
                fwrite($file, $text);
                fclose($file);
            }
        }
    }

    // 查询文件
    // 用法：(new utils)->file->load($path . $name)->select($path);
    public function select($path = null)
    {
        $path = !empty($path) ? $path : (!empty($this->load) ? $this->load : $this->path . $this->name);

        // 判断是否为目录
        if (!is_dir($path)) return false;

        // 判断末尾是否有 /
        if (substr($path, -1) != '/') $path .= '/';

        // 打开目录
        $dir = opendir($path);

        $result = [];

        // 获取当前目录下的文件和文件夹
        while (($file = readdir($dir)) !== false) {
            // 过滤掉当前目录和上一级目录
            if ($file == '.' || $file == '..') continue;
            // 判断是否为目录
            if (is_dir($path . $file)) {
                $result['directory'][] = $file;
            }
            // 判断是否为文件
            if (is_file($path . $file)) {
                $result['file'][] = $file;
            }
        }

        return $result;
    }

    // 创建文件或目录
    // 用法：(new utils)->file->load($path . $name)->create('内容');
    public function create($text = null, $cover = true)
    {
        $path = !empty($this->load) ? $this->load : $this->path . $this->name;

        // 判断是否以 / 结尾
        $isDir = substr($path, -1) == '/' ? true : false;

        // 创建文件夹
        if ($isDir and !file_exists($path)) {
            mkdir($path, $this->chmod, true);
            return true;
        }
        // 创建文件夹并创建文件
        else {
            // 取出文件夹路径
            $dir = substr($path, 0, strrpos($path, '/'));
            // 创建文件夹
            if (!file_exists($dir)) mkdir($dir, $this->chmod, true);
            // 判断文件是否存在
            if (!$cover and file_exists($path)) return false;
            // 创建文件
            $file = fopen($path, 'w');
            fwrite($file, $text);
            fclose($file);
            return true;
        }
    }

    // 删除文件或目录
    // 用法：(new utils)->file->load($path . $name)->delete($path);
    public function delete($path = false)
    {
        $path = !empty($path) ? $path : (!empty($this->load) ? $this->load : $this->path . $this->name);

        // 不存在
        if (!file_exists($path)) return false;

        // 是否为目录
        if (is_dir($path)) {
            $dir = opendir($path);
            while ($file = readdir($dir)) {
                // 过滤掉 . 和 ..
                if ($file != '.' and $file != '..') {
                    $this->delete($path . '/' . $file);
                }
            }
            // 关闭目录
            closedir($dir);
            rmdir($path);
        }
        // 删除文件
        else unlink($path);

        return true;
    }

    // 清除目录下的文件
    // 用法：(new utils)->file->load($path . $name)->clear($path);
    public function clear($path = null)
    {
        $path = !empty($path) ? $path : (!empty($this->load) ? $this->load : $this->path . $this->name);

        // 判断是否为目录
        if (!is_dir($path)) return false;

        // 打开目录
        $dir = opendir($path);
        // 循环读取目录
        while (($file = readdir($dir)) !== false) {
            // 过滤掉当前目录和上一级目录
            if ($file == '.' or $file == '..') continue;
            // 判断是否为目录
            if (is_dir($path . '/' . $file)) {
                // 递归调用
                $this->clear($path . '/' . $file);
                // 删除目录
                rmdir($path . '/' . $file);
            }
            // 删除文件
            else unlink($path . '/' . $file);
        }
        // 关闭目录
        closedir($dir);
        return true;
    }

    // 下载远程文件
    // 用法：(new utils)->file->path($path)->name($name)->downloadFile($path);
    public function download($url = null)
    {
        $savePath = !empty($this->path) ? $this->path : './';
        if (empty($url)) return false;

        // 判断保存路径是否存在
        if (!file_exists($savePath)) mkdir($savePath, $this->chmod, true);

        // 判断保存目录末尾是否有 /
        if (substr($savePath, -1) != '/') $savePath .= '/';
        // 保存的文件名
        $savePath .= !empty($this->name) ? $this->name . strstr(basename($url), '.') : basename($url);

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
        if (curl_exec($ch) === false) return false;
        curl_close($ch);

        $resource = fopen($savePath, 'a');
        fwrite($resource, $file);
        fclose($resource);

        return true;
    }

    // 文件或目录是否为空
    // 用法：(new utils)->file->load($path . $name)->isEmpty();
    public function isEmpty()
    {
        $path = !empty($this->load) ? $this->load : $this->path . $this->name;
        if (!file_exists($path)) return false;

        if (is_dir($path)) {
            $dir = opendir($path);
            while ($file = readdir($dir)) {
                if ($file != '.' and $file != '..') return false;
            }
            return true;
        } else if (is_file($path)) return filesize($path) == 0 ? true : false;

        return false;
    }

    // 文件或目录信息
    // 用法：(new utils)->file->load($path . $name)->info();
    public function info()
    {
        $path = !empty($this->load) ? $this->load : $this->path . $this->name;
        if (!file_exists($path)) return false;

        $result = [];
        $result['name']       = basename($path);
        $result['path']       = dirname($path);
        $result['size']       = filesize($path);
        $result['type']       = filetype($path);
        $result['ext']        = pathinfo($path, PATHINFO_EXTENSION);
        $result['ctime']      = filectime($path);
        $result['mtime']      = filemtime($path);
        $result['atime']      = fileatime($path);
        $result['owner']      = fileowner($path);
        $result['perms']      = substr(sprintf('%o', fileperms($path)), -4);
        $result['isDir']      = is_dir($path);
        $result['isFile']     = is_file($path);
        $result['isLink']     = is_link($path);
        $result['isReadable'] = is_readable($path);
        $result['isWritable'] = is_writable($path);
        $result['isUpload']   = is_uploaded_file($path);
        // 上传大小限制
        $result['maxSize']    = ini_get('upload_max_filesize');

        return $result;
    }

    // 调用不存在的方法时触发
    public function __call($name, $args)
    {
        // 获取当前 class 存在的方法
        $methods = get_class_methods($this);
        // 过滤掉魔术方法
        $methods = array_filter($methods, function ($item) {
            return !preg_match('/^__/', $item);
        });
        // 获取当前的 class 名称
        $class = get_class($this);
        // 返回异常
        throw new \Exception("当前 {$class} 类没有 {$name} 方法, 存在的方法有: " . implode('、', $methods));
    }
}