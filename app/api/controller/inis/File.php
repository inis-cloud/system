<?php
declare (strict_types = 1);

namespace app\api\controller\inis;

use think\Request;
use think\facade\{Cache};

class File extends Base
{
    /**
     * 显示资源列表
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function index(Request $request)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['all'];
        
        $mode   = (empty($param['mode'])) ? 'all' : $param['mode'];
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->create($data, $msg, $code);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = 'ok';
        $result = [];
        
        $mode   = !empty($param['mode']) ? $param['mode'] : null;
        
        if ($mode == 'upload') {
            $result = $this->upload($param);
            foreach ($result as $key => $val) $$key = $val;
        }
        
        return $this->create($data, $msg, $code);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $IID
     * @return \think\Response
     */
    public function read(Request $request, $IID)
    {
        // 获取请求参数
        $param  = $request->param();
        
        if ($IID == 'read') $IID = 'reads';
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['random','words','info','reads','find','inWords'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->create($data, $msg, $code);
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $IID
     * @return \think\Response
     */
    public function update(Request $request, $IID)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $IID
     * @return \think\Response
     */
    public function delete(Request $request, $IID)
    {
        //
    }
    
    // 上传文件
    public function upload($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        $name = explode('.', $_FILES['file']['name']);
        array_pop($name);
        $name = implode('.',$name);
        
        $upload = $this->tool->upload(
            'file', 
            ['storage','users/files/uid-'.$this->user['data']->id.'/'.date("Y-m"), [$name]], 
            'one', 
            'file|fileExt:jpg,jpeg,png,gif,webp,svg,ico,zip,gz,mp3,mp4,avi|fileSize:20971520'
        );
        
        foreach ($upload as $key => $val) $$key = $val;
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 随机图
    public function random($param)
    {
        $data = [];
        $code = 200;
        $msg  = 'ok';
        
        $file = !empty($param['file']) ? $param['file'] : null;
        $json = (empty($param['json']) or $param['json'] == 'false') ? false : true;
        
        $path = $this->config['random']['path'] . 'images/';
        $list = $this->File->dirInfo($path);
        
        $filter = ['.','..','使用说明.txt','README.md'];
        // 过滤特殊文件
        foreach ($list as $key => $val) if (in_array($val, $filter)) unset($list[$key]);
        
        // 重新组合存在的文件或文件夹列表
        $list = array_merge($list);
        
        // 指定随机
        if (!empty($file)) {
            
            // 存在这个目录
            if (in_array($file, $list)) {
                
                $data = $this->File->listDirInfo($path.$file);
                $data = str_replace(PHP_EOL,'', $this->helper->RandArray($data));
                $data = $this->helper->domain().'/'.$data;
                
                if (!$json) header("Location: " . $data);
                
            } else if (in_array($file.'.txt', $list)) {  // 存在这个txt文件
            
                $data = file($path.$file.'.txt');
                // 去除换行符号
                foreach ($data as $key => $val) $data[$key] = str_replace(PHP_EOL,'',$val);
                $data = $this->helper->RandArray(array_filter($data));
                
                if (!$json) header("Location: " . $data);
                
            } else {
                $code = 204;
                $msg  = '不存在该文件或文件夹';
            }
            
        } else {
            
            $item = $this->helper->RandArray($list);
            $info = $this->File->listInfo($path.$item);
            
            if ($info['type'] == 'dir') {
                
                $data = $this->File->listDirInfo($path.$item);
                $data = str_replace(PHP_EOL,'', $this->helper->RandArray($data));
                $data = $this->helper->domain().'/'.$data;
                
            } else {
                
                $data = file($path.$item);
                
                // 去除换行符号
                foreach ($data as $key => $val) $data[$key] = str_replace(PHP_EOL,'',$val);
                $data = $this->helper->RandArray(array_filter($data));
            }
            
            if (!$json) header("Location: " . $data);
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 随机文本
    public function words($param)
    {
        $data = [];
        $code = 200;
        $msg  = 'ok';
        
        $file = !empty($param['file']) ? $param['file'] : null;
        
        $path = $this->config['random']['path'] . 'words/';
        $list = $this->File->dirInfo($path);
        
        $filter = ['.','..','使用说明.txt','README.md'];
        // 过滤特殊文件
        foreach ($list as $key => $val) if (in_array($val, $filter)) unset($list[$key]);
        
        // 重新组合存在的文件或文件夹列表
        $list = array_merge($list);
        
        // 指定随机
        if (!empty($file)) {
            
            // 存在这个文件
            if (in_array($file.'.txt', $list)) {  // 存在这个txt文件
                
                $data = file($path.$file.'.txt');
                $data = str_replace(PHP_EOL,'', $this->helper->RandArray($data));
                
            } else {
                
                $code = 204;
                $msg  = '不存在该文件';
            }
            
        } else if (!empty($list)) {
            
            $item = $this->helper->RandArray($list);
            $info = $this->File->listInfo($path.$item);
            
            $data = file($path.$item);
            $data = str_replace(PHP_EOL,'', $this->helper->RandArray($data));
            
        } else $code = 204;
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取目录信息
    public function all($param)
    {
        // 空路径 - 根路径
        if (empty($param['path']) or $param['path'] == '/') $param['path'] = './';
        // 路径末尾加上 / 
        if (substr($param['path'],-1) !== '/') $param['path'] .= '/';
        // 去除路径首位 /
        if (substr($param['path'],0,1) == '/') $param['path'] = substr($param['path'],1);
        
        // 被获取的路径
        $path = $param['path'];
        
        // 避免提权，强制定义当前目录 ./ - 防跨站
        $path = explode('/', $path);
        // 过滤返回上一级操作
        foreach ($path as $key => $val) if ($val == '..') unset($path[$key]);
        // 过滤空数组并合并数组
        $path = implode('/', array_merge(array_filter($path)));
        $path = (empty($path)) ? './' : $path . '/';
        
        // 文件图片路径
        $ico_path = $this->tool->domain() . '/admin/svg/filesystem/';
        
        // 返回指定路径的文件夹信息，其中包含指定路径中的文件和目录
        $dir_info = $this->File->dirInfo($path);
        
        // 去除 . ..
        unset($dir_info[0]);
        unset($dir_info[1]);
        
        $file_info = [];
        
        // 文件类型
        $obtain = ['svg','jpg','jpeg','png','gif','ttf','woff','woff2','php','js','css','json','html','doc','docx','txt'];
        
        // 封装路径文件和文件夹数据
        foreach ($dir_info as $val) {
            
            $arr['name'] = $val;
            $arr['info'] = $this->File->listInfo($path.$val);
            
            if ($arr['info']['type'] == 'dir') $arr['info']['ico'] = $ico_path.'yellow-folder.svg';
            else if (in_array($arr['info']['ext'],$obtain)) foreach ($obtain as $val) {
                
                if ($arr['info']['ext'] == $val) $arr['info']['ico'] = $ico_path.$val.'.svg';
                
            } else $arr['info']['ico'] = $ico_path.'other.svg';
            
            array_push($file_info,$arr);
        }
        
        // 重新排序
        if (!empty($file_info)) {
            
            $dir_data  = [];
            $file_data = [];
            
            foreach ($file_info as $key => $val) {
                if ($val['info']['type'] == 'dir') array_push($dir_data,$val);
                else array_push($file_data,$val);
            }
            
            $file_info = array_merge($dir_data,$file_data);
        }
        
        $data = ['path'=>$path,'info'=>$file_info];
        
        $result = ['data'=>$data,'code'=>200,'msg'=>'ok'];
        
        return $result;
    }
    
    // 文件或目录信息
    public function info($param)
    {
        // 空路径 - 根路径
        if (empty($param['path']) or $param['path'] == '/') $param['path'] = './';
        // 路径末尾加上 / 
        if (substr($param['path'],-1) !== '/') $param['path'] .= '/';
        // 去除路径首位 /
        if (substr($param['path'],0,1) == '/') $param['path'] = substr($param['path'],1);
        
        $data = [];
        $code = 200;
        $msg  = 'ok';
        
        if (!empty($param['file'])) {
            
            $type = 'file';
            
            // 文件路径 + 文件名
            $path_file = $param['path'].$param['file'];
            
        } else {
            
            $type = 'dir';
            $path_file = $param['path'];
        }
        
        try {
            
            $data['other'] = $this->File->listInfo($path_file);
            
            if ($type == 'dir') $data['size'] = $this->File->getDirInfo($path_file);
            else $data['size'] = $this->File->openInfo($path_file);
            
        } catch (\Exception $e) {
            
            $code = 400;
            $msg  = $e->getMessage();
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 读取文件内容
    public function reads($param)
    {
        $data  = [];
        $code  = 400;
        $msg   = 'ok';
        
        $file_path = (empty($param['path']) or $param['path'] == '/') ? './' : $param['path'];
        
        // 重新定义路径，防止提权
        $path_array = array_filter(explode('/', $file_path));
        $path_file  = implode('/', $path_array);
        
        try {
            
            $code = 200;
            $data['content'] = $this->File->readFile($path_file);
            $data['info']    = $this->File->listInfo($path_file);
            
        } catch (\Exception $e) {
            
            $msg  = $e->getMessage();
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 查找文件
    public function find($param)
    {
        $data  = [];
        $code  = 400;
        $msg   = 'ok';
        
        // 本地域名
        $local_domains = $this->tool->domain();
        
        // 空路径 - 根路径
        if (empty($param['path']) or $param['path'] == '/') $param['path'] = './';
        // 路径末尾加上 / 
        if (substr($param['path'],-1) !== '/') $param['path'] .= '/';
        // 去除路径首位 /
        if (substr($param['path'],0,1) == '/') $param['path'] = substr($param['path'],1);
        // 是否获取全部
        $all = (empty($param['all']) or $param == 'false') ? false : (($param['all'] == 'true') ? true : false);
        // 文件后缀
        $suffix = !empty($param['suffix']) ? array_filter(explode(',', $param['suffix'])) : '';
        // 排序方式 asc desc nat
        $order  = !empty($param['order']) ? $param['order'] : 'asc';
        // 合并搜索结果
        $merge  = (empty($param['merge']) or $param == 'false') ? false : (($param['merge'] == 'true') ? true : false);
        // 是否处理域名
        $domain = (empty($param['domain']) or $param['domain'] == 'true') ? true : (($param['domain'] == 'false') ? false : true);     
        
        try {
            
            $code = 200;
            
            if (empty($suffix)) {
                
                $data = $this->File->listDirInfo($param['path'], $all, '', $order);
                
                // 在路径前面拼接域名
                if ($domain) {
                    foreach ($data as $k => $v) {
                        if (substr($v, 0, 2) == './') $data[$k] = $local_domains . substr($v,1);
                        else $data[$k] = $local_domains . '/' . $v;
                    }
                }
                
            } else {
                
                foreach ($suffix as $val) {
                    
                    // 过滤后缀前面的 .
                    if (substr($val, 0, 1) == '.') $val = substr($val, 1);
                    
                    $item =  $this->File->listDirInfo($param['path'], $all, $val, $order);
                    
                    // 在路径前面拼接域名
                    if ($domain) {
                        foreach ($item as $k => $v) {
                            if (substr($v, 0, 2) == './') $item[$k] = $local_domains . substr($v,1);
                            else $item[$k] = $local_domains . '/' . $v;
                        }
                    }
                    
                    $data[$val] = $item;
                }
                
                // 合并搜索结果
                if ($merge) {
                    
                    foreach ($data as $val) foreach ($val as $v) $items[] = $v;
                    $data = $items;
                }
            }
            
        } catch (\Exception $e) {
            
            $msg  = $e->getMessage();
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 违禁词API
    public function inWords($param)
    {
        $data = [];
        $code = 200;
        $msg  = 'ok';
        
        // 词库路径
        $path = 'storage/random/inWords/';
        // 选择词库
        $file = !empty($param['txt']) ? $param['txt'] : 'default';
        
        // 打开词库
        $text = $this->File->readFile($path . $file . '.txt');
        
        if (!$text) {
            $code = 500;
            $msg  = '缺失文件，请在目录' . $path . '下新建 ' . $file . '.txt文件，并填充内容，每个一行';
        }
        else if (empty($param['value'])) $data = explode(PHP_EOL, $text);
        else {
            
            foreach (explode(PHP_EOL, $text) as $key => $val) {
                if (strpos($param['value'], $val) !== false) $data[] = $val;
            }
            // 去重去空处理
            $data = array_filter(array_unique($data));
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}
