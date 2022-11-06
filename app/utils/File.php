<?php
declare (strict_types = 1);

namespace app\utils;

use think\Exception;
use inis\utils\{Image};

class File
{
    // 文件上传
    private $file = 'file';
    private $disk = 'uploads';
    private $ext  = 'jpg,jpeg,png,gif,webp,svg,ico,zip,gz,mp3,mp4,avi';
    private $size = 1024 * 1024 * 2;
    private $name = '';
    // 压缩文件
    private $compress = false;
    private $percent  = 0.5;

    // 设置文件
    public function file($file = 'file')
    {
        $this->file = $file;
        return $this;
    }

    // 保存的文件目录
    public function disk($disk = 'uploads')
    {
        $this->disk = $disk;
        return $this;
    }

    // 限制文件后缀
    public function ext($ext = 'jpg,jpeg,png,gif,webp,svg,ico,zip,gz,mp3,mp4,avi')
    {
        $this->ext = is_array($ext) ? implode(',', $ext) : $ext;
        return $this;
    }

    // 限制文件大小，单位字节
    public function size($size = 1024 * 1024 * 2)
    {
        $this->size = $size;
        return $this;
    }

    // 保存的文件名
    public function name($name = '')
    {
        $this->name = $name;
        return $this;
    }

    // 压缩文件
    public function compress($percent = 0.5, $compress = true)
    {
        $this->compress = $compress;
        $this->percent  = $percent;
        return $this;
    }

    // 上传文件
    public function upload($name = null)
    {
        $this->name = !empty($name) ? $name : $this->name;
        if (empty($this->file)) throw new Exception('上传的文件不能为空！',    400);
        if (empty($this->disk)) throw new Exception('保存的文件目录不能为空！', 400);

        $item = request()->file($this->file);
        if (empty($item)) throw new Exception('上传的文件不能为空！', 400);

        if (!empty($this->ext)) {
            if (!in_array($item->getOriginalExtension(), explode(',', $this->ext))) {
                throw new Exception('上传的文件格式不正确！', 400);
            }
        }

        if (!empty($this->size)) if ($item->getSize() > $this->size) {
            throw new Exception('上传的文件大小超出限制！', 400);
        }

        $ext = $item->getOriginalExtension();
        $name = !empty($this->name) ? $this->name : $item->getOriginalName();
        // 判断 $name 是否包含文件后缀
        if (strpos((string)$name, '.') === false) {
            $name = $name . '.' . $ext;
        }

        $result = $item->move($this->disk, $name);
        $url    = $result->getPathname();

        // 允许的压缩格式
        $allow  = ['jpg', 'jpeg', 'png', 'bmp', 'wbmp','gif'];

        // 压缩图片
        if ($this->compress and in_array($ext, $allow)) (new Image($url, $this->percent))->compress($url);

        // 返回保存的文件路径
        return $url;
    }
}