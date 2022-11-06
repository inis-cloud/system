<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: Image class - 图片压缩类
// +----------------------------------------------------------------------

namespace inis\utils;

/**
 * Class Image
 * @package inis\utils
 */
class Image{
    
    private $src;
    private $info;
    private $image;
    private $percent = 0.5;
    
    /**
     * 图片压缩
     * @param $src 源图
     * @param float $percent  压缩比例
     */
    public function __construct($src, $percent = 1)
    {
        $this->src     = $src;
        $this->percent = $percent;
    }
    

    /** 高清压缩图片
     * @param string $saveName  提供图片名（可不带扩展名，用源图扩展名）用于保存。或不提供文件名直接显示
     */
    public function compress($saveName = '')
    {
        $this->open();
        
        // 保存
        !empty($saveName) ? $this->save($saveName) : $this->show();
    }

    /**
    * 内部：打开图片
    */
    private function open()
    {
        list($width, $height, $type, $attr) = getimagesize($this->src);
        
        $this->info = [
            'width' => $width,
            'height'=> $height,
            'type'  => image_type_to_extension($type,false),
            'attr'  => $attr
        ];
        
        $function    = "imagecreatefrom" . $this->info['type'];
        $this->image = $function($this->src);
        $this->set();
    }
    
    /**
     * 内部：设置图片
     */
    private function set()
    {
        $new_width   = (int)($this->info['width']  * ($this->percent <= 0 ? 0.5 : $this->percent));
        $new_height  = (int)($this->info['height'] * ($this->percent <= 0 ? 0.5 : $this->percent));
        $image_thump = imagecreatetruecolor($new_width, $new_height);
        
        // 将原图复制带图片载体上面，并且按照一定比例压缩,极大的保持了清晰度
        imagecopyresampled($image_thump, $this->image, 0, 0, 0, 0, $new_width, $new_height, $this->info['width'], $this->info['height']);
        imagedestroy($this->image);
        
        $this->image = $image_thump;
    }

    /**
     * 输出图片:保存图片则用save()
     */
    private function show()
    {
        header('Content-Type: image/' . $this->info['type']);
        $function = "image" . $this->info['type'];
        $function($this->image);
    }
    
    /**
     * 保存图片到硬盘：
     * @param  string $dstImgName  1、可指定字符串不带后缀的名称，使用源图扩展名 。2、直接指定目标图片名带扩展名。
     */
    private function save($dstImgName)
    {
        if (empty($dstImgName)) return false;
        
        // 如果目标图片名有后缀就用目标图片扩展名 后缀，如果没有，则用源图的扩展名
        $allowImgs = ['.jpg', '.jpeg', '.png', '.bmp', '.wbmp','.gif'];
        $dstExt    = strrchr($dstImgName, ".");
        $sourseExt = strrchr($this->src, ".");
        
        if (!empty($dstExt))    $dstExt    = strtolower($dstExt);
        if (!empty($sourseExt)) $sourseExt = strtolower($sourseExt);
        
        // 有指定目标名扩展名
        if (!empty($dstExt) && in_array($dstExt,$allowImgs)){
            $dstName = $dstImgName;
        } else if (!empty($sourseExt) && in_array($sourseExt,$allowImgs)){
            $dstName = $dstImgName.$sourseExt;
        } else {
            $dstName = $dstImgName.$this->info['type'];
        }
        
        $function = "image" . $this->info['type'];
        $function($this->image, $dstName);
    }
    
    /**
     * 销毁图片
     */
    public function destroy(){
        imagedestroy($this->image);
    }
}
 
// $source  = 'storage/1.jpg';
// // 可加存放路径
// $dst_img = 'storage/1-2.jpg';
// // 原图压缩，不缩放
// $percent = 0.5;
// $image   = (new Image($source,$percent))->compress($dst_img);