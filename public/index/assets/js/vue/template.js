// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | 作用：组件文件
// +----------------------------------------------------------------------
class inisTemplate
{
    // 初始化方法
    constructor(){
        
        self = this
    };
    
    // 公共数据
    data(opt = ''){
        
        // 资源路径
        const path = {
            "assets" : "/index/assets/",
            "js"     : "/index/assets/js/",
            "css"    : "/index/assets/css/",
            "img"    : "/index/assets/images/",
            "libs"   : "/index/assets/libs/"
        }
        
        // 登录信息
        const login_account = JSON.parse(inisHelper.get.cookie('login_account'))
        
        const result = {path,login_account}
        
        return result[opt]
    }
    
    footer(opt = 'index'){
        
        // 页脚 - 登陆页面
        const login = {
            data() {
                return {
                    year: null  // 当前年份
                }
            },
            mounted() {
                this.year  = (new Date).getFullYear()
            },
            template: `<footer class="footer footer-alt">
                2020 - {{ year }} © INIS - <a href="//inis.cc" class="text-muted" target="_blank">inis.cc</a>
            </footer>
            `
        }
        
        // 页脚 - 后台首页
        const index = {
            data() {
                return {
                    year: null,       // 当前年份
                    version: '1.0',   // 版本号
                }
            },
            mounted() {
                this.getVersion()
                this.year  = (new Date).getFullYear()
            },
            methods: {
                getVersion(){
                    axios.post('/index/chart/version').then(res=>{
                        if (res.data.code == 200) this.version = res.data.data.version
                    })
                }
            },
            template: `<footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <span class="item left bg-dark">© {{ year }} Copyright</span>
                            <span class="item right bg-primary">inis - <a href="//inis.cc" target="_blank">inis.cc</a></span>
                        </div>
                        <div class="col-md-6">
                            <div class="text-md-right footer-links d-none d-md-block">
                                <span class="item left bg-dark"><a href="//inis.cc" target="_blank">inis</a></span>
                                <span class="item right bg-warning"><a href="javascript: void(0);">version {{ version || '1.0' }}</a></span>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            `
        }
        
        const result = {login,index}
        
        return result[opt]
    };
    
    // 顶部导航栏
    navbar(opt = 'top'){
        const top = {
            data() {
                return {
                    path: self.data('path'),                            // 资源路径
                    login_account: self.data('login_account'),          // 登录帐号信息
                    self_version: '1.0',                                // 版本号
                    official_version: [],                               // 官方版本
                    is_show_update: false,                              // 显示更新
                    genuine: true,                                      // 正版
                    official_api: 'https://inis.cc/api/',               // inis 官方API地址
                }
            },
            mounted() {
                this.initData()
            },
            methods: {
                // 初始化方法
                initData(){
                    this.getVersion()
                    this.checkDomain()
                },
                // 获取更新
                getUpdate() {
                    axios.get(this.official_api + 'version').then(res=>{
                        if (res.data.code == 200) {
                            this.official_version = res.data.data
                            this.checkUpdate()
                            this.official_version.update_time_nature = inisHelper.time.nature(this.official_version.update_time)
                            this.official_version.update_time = inisHelper.time.to.date(this.official_version.update_time)
                        }
                    })
                },
                // 正版校验
                checkDomain(){
                    // 获取当前域名
                    let domain = window.location.protocol+"//"+window.location.host;
                    axios.get(this.official_api + 'check', {
                        params: {domain}
                    }).then(res=>{
                        if (res.data.code == 200) {
                            if (!res.data.data.status) {
                                this.genuine = false
                                $.NotificationApp.send("警告！", "您非正版用户，无法获取更新，如有能力，请支持正版！<span class=\"badge badge-danger-lighten\"><a href=\"//inis.cc\" target=\"_blank\">inis 官网</span>", "top-right", "rgba(0,0,0,0.2)", "error");
                            }
                        }
                    })
                },
                // 获取本地版本号
                getVersion(){
                    axios.post('/index/chart/version').then(res=>{
                        if (res.data.code == 200) {
                            this.self_version = res.data.data.version
                            this.getUpdate()
                        }
                    })
                },
                // 校验更新
                checkUpdate(){
                    if (!inisHelper.is.empty(this.official_version)) {
                        let check = inisHelper.compare.version(this.official_version.version,this.self_version)
                        this.is_show_update = check
                    }
                    this.lottie()
                },
                // 更新方法
                update() {
                    this.updatePackage()
                },
                // 获取更新包地址
                updatePackage(){
                    
                    let params = {mode:'update'}
                    
                    axios.get(this.official_api + 'download', {params}).then(res=>{
                        if (res.data.code == 200) {
                            this.downloadUpdate(res.data.data.file)
                        }
                    })
                },
                // 下载更新包
                downloadUpdate(file_path){
                    
                    let params = new FormData
                    params.append("file_path", file_path || '')
                    
                    axios.post('/index/handle/downloadUpdate', params).then(res=>{
                        if (res.data.code == 200) {
                            this.unzipUpdate()
                        }
                    })
                },
                // 解压更新
                unzipUpdate(){
                    axios.post('/index/handle/unzipUpdate').then(res=>{
                        if (res.data.code == 200) {
                            $('#myModal').modal('update-info')
                            this.getVersion()
                            $.NotificationApp.send("提示！", "更新完成！", "top-right", "rgba(0,0,0,0.2)", "info");
                        }
                    })
                },
                // 动态图标
                lottie(){
                    axios.all([
                        axios.get('/index/assets/libs/lottie/json/beil.json').then(res=>res.data),
                    ]).then(axios.spread((beil)=>{
                        lottie.loadAnimation({container:document.getElementById("lottie-beil"),renderer:"svg",loop:this.is_show_update,autoplay:true,animationData:beil})
                    }))
                },
            },
            template: `<div class="navbar-custom">
                <ul class="list-unstyled topbar-right-menu float-right mb-0">
                
                    <li class="dropdown notification-list lottie">
                        <a data-toggle="modal" data-target="#update-info" class="nav-link dropdown-toggle arrow-none" href="javascript:;">
                            <!--  <i class="dripicons-bell noti-icon"></i>
                            <span v-if="is_show_update" class="noti-icon-badge"></span> -->
                            <!-- 图标 -->
                            <div id="lottie-beil"></div>
                            <!-- 点点 -->
                            <span v-show="is_show_update" class="bg-danger dots"></span>
                        </a>
                    </li>
                    
                    <li class="dropdown notification-list">
                        <a class="nav-link dropdown-toggle nav-user arrow-none mr-0" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <span class="account-user-avatar">
                                <img :src="login_account.head_img" :alt="login_account.description" class="rounded-circle">
                            </span>
                            <span>
                                <span class="account-user-name">{{login_account.nickname}}</span>
                                <span class="account-position">{{login_account.email}}</span>
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-animated topbar-dropdown-menu profile-dropdown">
                            
                            <div class=" dropdown-header noti-title">
                                <h6 class="text-overflow m-0">Welcome !</h6>
                            </div>
                            
                            <a href="/index/EditProfile" class="dropdown-item notify-item">
                                <i class="mdi mdi-account-circle mr-1"></i>
                                <span>个人资料</span>
                            </a>
                            
                            <a href="/index/WriteArticle" class="dropdown-item notify-item">
                                <i class="mdi mdi-account-edit mr-1"></i>
                                <span>撰写文章</span>
                            </a>
                            
                            <a href="/index/options" class="dropdown-item notify-item">
                                <i class="mdi mdi-lifebuoy mr-1"></i>
                                <span>站点设置</span>
                            </a>
                            
                            <a href="/index/comm/logout" class="dropdown-item notify-item">
                                <i class="mdi mdi-logout mr-1"></i>
                                <span>退出登录</span>
                            </a>
                            
                        </div>
                    </li>
                    
                </ul>
                <button class="button-menu-mobile open-left disable-btn">
                    <i class="mdi mdi-menu"></i>
                </button>
                <div class="app-search">
                    <form>
                        <div class="input-group" data-toggle="tooltip" data-original-title="搜索功能，暂时不可用">
                            <input type="text" class="form-control" placeholder="擅用搜索，事半功倍！">
                            <span class="mdi mdi-magnify"></span>
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit" disabled>搜索</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <teleport to="body">
            <div id="update-info" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel" style="display: none;" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header modal-colored-header bg-primary">
                            <h4 class="modal-title" id="primary-header-modalLabel">[ inis 系统 ] - 版本更新</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        </div>
                        <div v-if="is_show_update && genuine" class="modal-body">
                            <div class="card-body">
                                <div class="row flex-center font-20px">
                                    <svg t="1626366975999" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4254" width="40" height="40"><path d="M512 204.8c168.96 0 307.2 138.24 307.2 307.2s-138.24 307.2-307.2 307.2-307.2-138.24-307.2-307.2 138.24-307.2 307.2-307.2m0-51.2c-197.12 0-358.4 161.28-358.4 358.4s161.28 358.4 358.4 358.4 358.4-161.28 358.4-358.4-161.28-358.4-358.4-358.4z" p-id="4255" fill="#f39b12"></path><path d="M550.4 368.64m-38.4 0a38.4 38.4 0 1 0 76.8 0 38.4 38.4 0 1 0-76.8 0Z" p-id="4256" fill="#f39b12"></path><path d="M486.4 716.8c-7.68 0-15.36-2.56-20.48-7.68-5.12-5.12-7.68-15.36-5.12-23.04l38.4-179.2c-10.24 7.68-23.04 5.12-30.72-2.56-10.24-10.24-12.8-25.6-2.56-35.84 30.72-33.28 66.56-35.84 71.68-33.28 7.68 0 15.36 5.12 20.48 10.24 5.12 5.12 7.68 12.8 5.12 20.48l-38.4 179.2c7.68-5.12 20.48-5.12 28.16 0 12.8 7.68 15.36 23.04 7.68 35.84-25.6 30.72-66.56 35.84-74.24 35.84z" p-id="4257" fill="#f39b12"></path></svg>
                                    <span class="ml-1">有新的版本更新，是否更新？</span>
                                </div>
                                <div class="row">
                                    <div class="alert alert-light fade show w-100 ml-5 mr-5 mt-2" role="alert">
                                        <p class="pb-3">
                                            <span class="float-left">
                                                <strong>最新版本：</strong><span class="text-success">{{official_version.title}} - {{official_version.version}}</span>
                                            </span>
                                            <span class="float-right">
                                                <strong>更新时间：</strong><span class="text-success">{{official_version.update_time_nature}}</span>
                                            </span>
                                        </p>
                                        <p v-html="official_version.content" class="pre-line"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="!is_show_update && genuine" class="modal-body">
                            <div class="card-body">
                                <div class="row flex-center font-20px">
                                    <svg t="1626368367018" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2649" width="38" height="38"><path d="M512 85.333333c235.648 0 426.666667 191.018667 426.666667 426.666667s-191.018667 426.666667-426.666667 426.666667S85.333333 747.648 85.333333 512 276.352 85.333333 512 85.333333z m0 64C311.701333 149.333333 149.333333 311.701333 149.333333 512s162.368 362.666667 362.666667 362.666667 362.666667-162.368 362.666667-362.666667S712.298667 149.333333 512 149.333333z m225.173333 201.578667a32.853333 32.853333 0 0 1 0 46.186667l-272.64 275.989333a32.256 32.256 0 0 1-45.845333 0l-131.84-133.824a32.853333 32.853333 0 0 1 0-46.208 32.256 32.256 0 0 1 45.845333 0l93.738667 95.296a21.333333 21.333333 0 0 0 30.165333 0.256l0.213334-0.213333 234.496-237.482667a32.256 32.256 0 0 1 45.866666 0z" fill="#00ba9b" p-id="2650"></path></svg>
                                    <span class="ml-1">恭喜您，当前已经是最新版本</span>
                                </div>
                                <div class="row">
                                    <div class="alert alert-light fade show w-100 ml-5 mr-5 mt-2" role="alert">
                                        <p>
                                            <span class="float-left">
                                                <strong>当前版本：</strong><span class="text-success">{{official_version.title}} - {{self_version}}</span>
                                            </span>
                                            <span class="float-right">
                                                <strong>发布时间：</strong><span class="text-success">{{official_version.update_time}}</span>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="!genuine" class="modal-body">
                            <div class="card-body">
                                <div class="row flex-center font-20px">
                                    <svg t="1626407369812" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="3395" width="40" height="40"><path d="M513.2 212.8l372.3 598.1H147l366.2-598.1m0-61c-18.3 0-42.7 12.2-48.8 30.5L92.1 780.4c-24.4 42.7 6.1 91.5 48.8 91.5h738.5c48.8 0 79.3-54.9 48.8-91.5L562 182.3c-6.1-18.3-30.5-30.5-48.8-30.5z m0 0" fill="#FF262B" p-id="3396"></path><path d="M513.2 652.2c-18.3 0-30.5-12.2-30.5-30.5V438.6c0-18.3 12.2-30.5 30.5-30.5s30.5 12.2 30.5 30.5v183.1c0 18.3-12.2 30.5-30.5 30.5z m0 0M482.6 731.5c0 16.9 13.7 30.5 30.5 30.5 16.9 0 30.5-13.7 30.5-30.5 0-16.9-13.7-30.5-30.5-30.5s-30.5 13.7-30.5 30.5z m0 0" fill="#FF262B" p-id="3397"></path></svg>
                                    <span class="ml-1">警告，您非正版用户！</span>
                                </div>
                                <div class="row">
                                    <div class="alert alert-danger fade show w-100 ml-5 mr-5 mt-2" role="alert">
                                        <p>您非 inis 正版用户，无法获取更新，如有能力，请支持正版。</p>
                                        <p>
                                            <span class="float-left"><a href="//inis.cc" target="_blank">inis 官网</a></span>
                                            <span class="float-right"><a href="//racns.com/inis.html" target="_blank">正版购买途径</a></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">取消</button>
                            <button v-on:click="update()" v-if="is_show_update" type="button" class="btn btn-primary">立即更新</button>
                            <button v-else-if="!is_show_update" type="button" class="btn btn-primary" data-dismiss="modal">知道了</button>
                        </div>
                    </div>
                </div>
            </div>
            </teleport>
            `
        }
        // <h5 class="mt-0">
        //                         <span class="float-left">
        //                             最新版本：<span class="text-success">{{official_version.title}} - {{official_version.version}}</span>
        //                         </span>
        //                         <span class="float-right">
        //                             更新时间：<span class="text-success">{{official_version.update_time}}</span>
        //                         </span>
        //                     </h5>
                            
        //                     <p v-html="official_version.content"></p>
        const result = {top}
        
        return result[opt]
    };
    
    // 侧边栏 - 左边
    sidebar(opt = 'left'){
        
        const left   = {
            data() {
                return {
                    path: self.data('path'),    // 资源路径
                    year: null,                 // 当前年份
                    login_account: {},          // 登录账户信息
                }
            },
            mounted() {
                this.year  = (new Date).getFullYear()
                this.login_account = self.data('login_account')
            },
            methods:{
            },
            template: `<div class="left-side-menu">
                <div class="slimscroll-menu" id="left-side-menu-container">
                    
                    <!-- LOGO -->
                    <a href="/" class="logo text-center">
                        <span class="logo-lg">
                            <img :src="path.img + 'logo-1.png'" alt="" height="30">
                        </span>
                        <span class="logo-sm">
                            <img :src="path.img + 'logo-2.png'" alt="" height="30">
                        </span>
                    </a>
                    
                    <!--- Sidemenu -->
                    <ul class="metismenu side-nav">
                        
                        <li class="side-nav-title side-nav-item">导航</li>
                        
                        <li class="side-nav-item">
                            <a href="/" class="side-nav-link">
                                <i class="dripicons-meter"></i>
                                <span> 首页 </span>
                            </a>
                        </li>
                        
                        <li class="side-nav-item">
                            <a href="javascript:;" class="side-nav-link">
                                <i class="dripicons-copy"></i>
                                <span> 创作 </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="side-nav-second-level" aria-expanded="false">
                                <li><a href="/index/WriteArticle">撰写文章</a></li>
                                <li><a href="/index/WritePage">新建页面</a></li>
                            </ul>
                        </li>
                        
                        <li class="side-nav-item">
                            <a href="javascript:;" class="side-nav-link">
                                <i class="dripicons-browser"></i>
                                <span> 管理 </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="side-nav-second-level" aria-expanded="false">
                                <li><a href="/index/ManageArticle">管理文章</a></li>
                                <li><a href="/index/ManagePage">管理页面</a></li>
                                <li><a href="/index/ManageLinks">管理友链</a></li>
                                <li><a href="/index/ManageTag">管理标签</a></li>
                                <li><a href="/index/ManageComments">管理评论</a></li>
                                <li><a href="/index/ManageUsers">管理用户</a></li>
                            </ul>
                        </li>
                        
                        <li class="side-nav-item">
                            <a href="javascript:;" class="side-nav-link">
                                <i class="dripicons-view-apps"></i>
                                <span> 分类 </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="side-nav-second-level" aria-expanded="false">
                                <li><a href="/index/ManageArticleSort">文章分类</a></li>
                                <li><a href="/index/ManageLinksSort">友链分组</a></li>
                            </ul>
                        </li>
                        
                        <li class="side-nav-item">
                            <a href="javascript:;" class="side-nav-link">
                                <i class="dripicons-list"></i>
                                <span> 其他 </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="side-nav-second-level" aria-expanded="false">
                                <li><a href="/index/ManageBanner">管理轮播</a></li>
                                <li><a href="/index/music">管理音乐</a></li>
                            </ul>
                        </li>
                        
                        <li class="side-nav-title side-nav-item mt-1">控制台</li>
                        
                        <li class="side-nav-item">
                            <a href="/index/options" class="side-nav-link">
                                <i class="dripicons-briefcase"></i>
                                <span> 站点设置 </span>
                            </a>
                        </li>
                        
                        <li class="side-nav-item">
                            <a href="/index/EditProfile" class="side-nav-link">
                                <i class="dripicons-heart"></i>
                                <span class="badge badge-light float-right">me</span>
                                <span> 个人设置 </span>
                            </a>
                        </li>
                        
                        <li class="side-nav-item">
                            <a href="javascript:;" class="side-nav-link">
                                <i class="dripicons-document"></i>
                                <span> 服务设置 </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="side-nav-second-level" aria-expanded="false">
                                <li><a href="/index/configure">配置服务</a></li>
                                <li v-if="false"><a href="/index/AuthRule">权限规则</a></li>
                                <li v-if="false"><a href="/index/authority">权限配置</a></li>
                            </ul>
                        </li>
                        
                        <li class="side-nav-item">
                            <a href="/index/filesystem" class="side-nav-link">
                                <i class="dripicons-graph-pie"></i>
                                <span> 文件系统 </span>
                            </a>
                        </li>
                        
                        <li v-if="false" class="side-nav-item">
                            <a href="javascript:;" class="side-nav-link">
                                <i class="dripicons-location"></i>
                                <span> 开发者中心 </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="side-nav-second-level" aria-expanded="false">
                                <li><a href="javascript:;">小兔子很懒</a></li>
                                <li><a href="javascript:;">小兔子很懒</a></li>
                            </ul>
                        </li>
                    </ul>
                    
                    <div class="help-box text-white text-center">
                        <a href="javascript:;" class="float-right close-btn text-white">
                            <i class="mdi mdi-close"></i>
                        </a>
                        <img :src="path.assets + 'svg/help-icon.svg'" height="90" />
                        <h5 class="mt-3">{{ login_account.nickname }}，您好！</h5>
                        <p class="mb-3">请问有什么可以帮您！</p>
                        <a href="//docs.inis.cc" class="btn btn-outline-light btn-sm" target="_blank">帮助</a>
                    </div>
                    
                    <div class="clearfix"></div>
                    
                </div>
            </div>
            `
        }
        
        const right  = {
            data() {
                return {
                    
                }
            },
            mounted() {
                
            },
            template: `<div class="right-bar">
                <div class="rightbar-title">
                    <a href="javascript:void(0);" class="right-bar-toggle float-right">
                        <i class="dripicons-cross noti-icon"></i>
                    </a>
                    <h5 class="m-0">Settings</h5>
                </div>
                
                <div class="slimscroll-menu rightbar-content">
                
                    <!-- Settings -->
                    <hr class="mt-0" />
                    <h5 class="pl-3">Basic Settings</h5>
                    <hr class="mb-0" />
                    
                    <div class="p-3">
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="notifications-check" checked>
                            <label class="custom-control-label" for="notifications-check">Notifications</label>
                        </div>
                        
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="api-access-check">
                            <label class="custom-control-label" for="api-access-check">API Access</label>
                        </div>
                        
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="auto-updates-check" checked>
                            <label class="custom-control-label" for="auto-updates-check">Auto Updates</label>
                        </div>
                        
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="online-status-check" checked>
                            <label class="custom-control-label" for="online-status-check">Online Status</label>
                        </div>
                        
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="auto-payout-check">
                            <label class="custom-control-label" for="auto-payout-check">Auto Payout</label>
                        </div>
                    </div>
                    
                    <!-- Timeline -->
                    <hr class="mt-0" />
                    <h5 class="pl-3">Recent Activity</h5>
                    <hr class="mb-0" />
                    <div class="pl-2 pr-2">
                        <div class="timeline-alt">
                            <div class="timeline-item">
                                <i class="mdi mdi-upload bg-info-lighten text-info timeline-icon"></i>
                                <div class="timeline-item-info">
                                    <a href="#" class="text-info font-weight-bold mb-1 d-block">You sold an item</a>
                                    <small>Paul Burgess just purchased “Hyper - Admin Dashboard”!</small>
                                    <p class="mb-0 pb-2">
                                        <small class="text-muted">5 minutes ago</small>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <i class="mdi mdi-airplane bg-primary-lighten text-primary timeline-icon"></i>
                                <div class="timeline-item-info">
                                    <a href="#" class="text-primary font-weight-bold mb-1 d-block">Product on the Bootstrap Market</a>
                                    <small>Dave Gamache added
                                        <span class="font-weight-bold">Admin Dashboard</span>
                                    </small>
                                    <p class="mb-0 pb-2">
                                        <small class="text-muted">30 minutes ago</small>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <i class="mdi mdi-microphone bg-info-lighten text-info timeline-icon"></i>
                                <div class="timeline-item-info">
                                    <a href="#" class="text-info font-weight-bold mb-1 d-block">Robert Delaney</a>
                                    <small>Send you message
                                        <span class="font-weight-bold">"Are you there?"</span>
                                    </small>
                                    <p class="mb-0 pb-2">
                                        <small class="text-muted">2 hours ago</small>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <i class="mdi mdi-upload bg-primary-lighten text-primary timeline-icon"></i>
                                <div class="timeline-item-info">
                                    <a href="#" class="text-primary font-weight-bold mb-1 d-block">Audrey Tobey</a>
                                    <small>Uploaded a photo
                                        <span class="font-weight-bold">"Error.jpg"</span>
                                    </small>
                                    <p class="mb-0 pb-2">
                                        <small class="text-muted">14 hours ago</small>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <i class="mdi mdi-upload bg-info-lighten text-info timeline-icon"></i>
                                <div class="timeline-item-info">
                                    <a href="#" class="text-info font-weight-bold mb-1 d-block">You sold an item</a>
                                    <small>Paul Burgess just purchased “Hyper - Admin Dashboard”!</small>
                                    <p class="mb-0 pb-2">
                                        <small class="text-muted">1 day ago</small>
                                    </p>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="rightbar-overlay"></div>
            `
        }
        
        const result = {left,right}
        
        return result[opt]
    };
    
    // END
}

const inisTemp = new inisTemplate