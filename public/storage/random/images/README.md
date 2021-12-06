详细文档说明可以看这里：https://docs.inis.cc/#/api/file?id=%e3%80%90%e9%9a%8f%e6%9c%ba%e5%9b%be%e3%80%91

备注：本系统的随机图有两种模式，分别是外链模式和本地图片模式

1、外链模式：
    备注：可以节省系统资源，在外链网速理想的情况下，加载更快
    准备工作：需要在当前文件夹下新建  任意名字的txt文件（如：img.txt）  ，txt文件内存放图片外链地址，一行一个
    调用方式：域名/api/file/random?file=自己起的txt文件名，举例：https://api.inis.cn/api/random?file=img
    
2、本地图片模式：
    备注：字面上的意思，就是随机图的来源从本地获取
    准备工作：在当前文件夹下 新建文件夹（如：images） ，文件夹内存放需要被随机的图片
    调用方式：域名/api/file/random?file=文件夹名，举例：https://api.inis.cn/api/random?file=images


