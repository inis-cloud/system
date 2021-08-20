<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>inis 系统 安装引导</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
        
        <!-- App favicon -->
        <link rel="shortcut icon" href="public/index/assets/images/logo-2.png">
        
        <!-- App css -->
        <link href="public/index/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="public/index/assets/css/app.min.css" rel="stylesheet" type="text/css" />
        
    </head>

    <body>

        <div class="mt-5 mb-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12">
                        
                        <div class="text-center">
                            <img src="public/index/assets/svg/maintenance.svg" height="140">
                            <h3 class="mt-4">安装前准备工作</h3>
                            <p class="text-muted">在安装之前，您需要按照以下要求进行准备工作</p>
                            
                            <div class="row mt-5">
                                <div class="col-md-4">
                                    <div class="text-center mt-3 pl-1 pr-1">
                                        <i class="dripicons-jewel bg-primary maintenance-icon text-white mb-2"></i>
                                        <h5>将 <span class="badge badge-primary-lighten badge-pill">public</span> 目录设置为 <span class="badge badge-primary-lighten badge-pill">运行目录</span></h5>
                                        <p class="text-muted">在源码根目录中，有一个名为 public 的目录，将目录设置为运行目录即可。<a href="//docs.inis.cc/#/start/install-inis-api?id=_3、设置运行目录" target="_blank">举例教程</a></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center mt-3 pl-1 pr-1">
                                        <i class="dripicons-clock bg-primary maintenance-icon text-white mb-2"></i>
                                        <h5>为网站配置 <span class="badge badge-primary-lighten badge-pill">伪静态</span></h5>
                                        <p class="text-muted">为网站设置伪静态，不同环境的配置各不相同，所以这里请根据系统环境的实际情况配置。<a href="https://docs.inis.cc/#/start/install-inis-api?id=_4、配置伪静态" target="_blank">举例教程</a></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center mt-3 pl-1 pr-1">
                                        <i class="dripicons-question bg-primary maintenance-icon text-white mb-2"></i>
                                        <h5><span class="badge badge-primary-lighten badge-pill">刷新</span> 当前页面</h5>
                                        <p class="text-muted">前两步完成之后，您只需要刷新当前页面，或者访问您的网站域名即可进行下一步操作</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        
        <footer class="footer footer-alt">
            <?= date('Y'); ?> © inis - <a href="//inis.cc" class="text-muted">inis.cc</a>
        </footer>
        
        <script src="public/index/assets/js/app.min.js"></script>
    </body>
</html>
