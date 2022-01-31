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
                    copy: '',    // 备案号
                    year: null,  // 当前年份
                    text: {      // 内置一言
                        is_show: false,
                        data: '',
                    },
                }
            },
            mounted() {
                this.getText()
                this.initData()
                this.year  = (new Date).getFullYear()
            },
            methods: {
                // 初始化数据
                initData(){
                    axios.put('/index/comm/login').then(res=>{
                        if (res.data.code == 200) this.copy = res.data.data.copy
                    })
                },
                // 获取内置一言
                getText(){
                    axios.get('/api/file/words').then(res=>{
                        if (res.data.code == 200) {
                            this.text = {
                                is_show: true,
                                data: res.data.data
                            }
                        } else if (res.data.code == 204) this.text.is_show = false
                    })
                }
            },
            template: `<footer class="footer footer-alt">
                <div v-show="text.is_show">「{{ text.data }}」</div>
                2020 - {{ year }} © INIS - <a href="//inis.cc" class="text-muted" target="_blank">inis.cc</a> | <a href="https://beian.miit.gov.cn" class="text-muted" target="_blank">{{ copy }}</a>
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
                this.localInfo()
                this.year  = (new Date).getFullYear()
            },
            methods: {
                localInfo(){
                    axios.post('/index/update/info').then(res=>{
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
                    info: {                                             // 本地信息
                        official: {
                            api: "https://inis.cc/api/",
                        },
                        version: '1.0'
                    },
                    update: {
                        is_show: false,     // 版本比较 - 是否显示更新
                        genuine: false,     // 正版查询
                        fulfill: false,     // 更新完成
                        info: {},           // 最新版信息
                        notes: [],          // 更新记录
                        content: [],        // 处理后的更新内容
                        tables:{news:[],olds:[],adds:[]}, // 表信息
                    },
                }
            },
            mounted() {
                this.lottie()
                this.initData()
            },
            methods: {
                // 初始化方法
                initData(){
                    // 设置禁止项缓存
                    inisHelper.set.storage('update',{finish:false})
                    this.localInfo()
                },
                // 获取本地信息
                localInfo(){
                    axios.post('/index/update/info').then(res=>{
                        if (res.data.code == 200) {
                            const result = res.data.data
                            this.info    = result
                        }
                        this.genuine()
                    })
                },
                // 正版查询
                genuine(){
                    axios.get(this.info.official.api + 'check').then(res=>{
                        if (res.data.code == 200) {
                            const result  = res.data.data
                            if (!result.status) {
                                this.update.genuine = false
                                $.NotificationApp.send("警告！", "您非正版用户，无法获取更新，如有能力，请支持正版！<span class=\"badge badge-danger-lighten\"><a href=\"//inis.cc\" target=\"_blank\">inis 官网</span>", "top-right", "rgba(0,0,0,0.2)", "error");
                            } else {
                                this.update.genuine = true
                                this.getUpdate()
                            }
                        }
                    })
                },
                // 检查更新
                getUpdate(){
                    axios.get(this.info.official.api + 'version').then(res=>{
                        if (res.data.code == 200) {
                            const result = res.data.data
                            this.update.info = result
                            const content = (result.content.split(/[(\r\n)\r\n]+/)).filter((s)=>{
                                return s && s.trim();
                            });
                            this.update.content = content
                            this.compare()
                        }
                    })
                },
                // 版本比较
                compare(){
                    if (!inisHelper.is.empty(this.update.info.version)) {
                        let check = inisHelper.compare.version(this.update.info.version, this.info.version)
                        this.update.is_show = check
                    }
                    this.lottie()
                },
                // 获取最新的数据库表信息
                getNewTables(){
                    
                    this.existSqlite()
                    
                    this.next('update-notes')
                    
                    this.update.notes.push({
                        id   : 'new-tables',
                        name : '获取最新数据库表信息',
                        des  : '最近节点获取',
                        state: null,
                    })
                    
                    axios.get(this.info.official.api + 'db').then(res=>{
                        if (res.data.code == 200) {
                            
                            const result           = res.data.data
                            this.update.tables.news = result.mysql
                            this.setNotes('new-tables',{
                                state: 'success',
                            })
                            
                            this.getOldTables()
                            
                        } else {
                            
                            this.setNotes('new-tables',{
                                state: 'error',
                            })
                        }
                    }).catch(err=>{
                        this.setNotes('new-tables',{
                            state: 'error',
                        })
                    })
                },
                
                // 检查数据库文件是否存在
                existSqlite(){
                    
                    this.update.notes.push({
                        id   : 'existSqlite',
                        name : '检查sqlite数据库',
                        des  : '本地检查',
                        state: null,
                    })
                    
                    axios.post('/index/update/existSqlite').then(res=>{
                        if (res.data.code == 200) {
                            this.setNotes('existSqlite',{
                                state: 'success',
                            })
                            this.diffSqliteTables()
                        } else if (res.data.code == 204) {
                            this.setNotes('existSqlite',{
                                state: 'success',
                            })
                            this.getSqlite()
                        } else {
                            this.setNotes('existSqlite',{
                                state: 'error',
                            })
                        }
                    }).catch(err=>{
                         this.setNotes('existSqlite',{
                            state: 'error',
                        })
                    })
                },
                
                // 获取sqlite文件地址
                getSqlite(){
                    
                    this.update.notes.push({
                        id   : 'downloadSqlite',
                        name : '下载sqlite数据库文件',
                        des  : '最近节点获取',
                        reset: true,
                        state: null,
                    })
                    
                    axios.get(this.info.official.api + 'download/sqlite').then(res=>{
                        if (res.data.code == 200) {
                            
                            const result = res.data.data
                            
                            this.setNotes('downloadSqlite',{
                                state: 'cache',
                            })
                            
                            this.downloadSqlite(result.file)
                            
                        } else {
                            
                            this.setNotes('downloadSqlite',{
                                state: 'error',
                            })
                        }
                        
                    }).catch(err=>{
                        this.setNotes('downloadSqlite',{
                            state: 'error',
                        })
                    })
                },
                
                // 下载sqlite数据库文件
                downloadSqlite(path){
                    const params = inisHelper.stringfy({
                        path
                    })
                    axios.post('/index/update/downloadSqlite', params).then(res=>{
                        if (res.data.code == 200) {
                            this.setNotes('downloadSqlite',{
                                state: 'success',
                            })
                            this.update.notes.push({
                                id   : 'diffSqliteTables',
                                name : '比较sqlite表差异',
                                des  : '本地比较',
                                state: null,
                            })
                            this.diffSqliteTables()
                        } else {
                            this.setNotes('downloadSqlite',{
                                state: 'error',
                            })
                        }
                    }).catch(err=>{
                        this.setNotes('downloadSqlite',{
                            state: 'error',
                        })
                    })
                },
                
                // 比较sqlite表差异
                async diffSqliteTables(){
                    
                    // 获取sqlite最新表
                    const all = await axios.get(this.info.official.api + 'db/table', {
                        params: {'db':'sqlite'}
                    }).then(res=>{
                        let result = []
                        if (res.data.code == 200) result = res.data.data
                        return result
                    })
                    // 获取sqlite本地表
                    const self= await axios.post('/index/update/sqliteTables').then(res=>{
                        let result = []
                        if (res.data.code == 200) result = res.data.data
                        return result
                    })
                    
                    // 两个数组求差
                    Array.prototype.diff = function(a) {
                        return this.filter((i)=>{return a.indexOf(i) < 0;});
                    };
                    let diff = all.diff(self);
                    
                    this.setNotes('diffSqliteTables',{
                        state: 'success',
                    })
                    
                    // 数据不为空
                    if (!inisHelper.is.empty(diff)) diff.forEach(item=>{
                        this.createSqliteTable(item)
                    })
                },
                
                // 创建sqlite表
                async createSqliteTable(item = null){
                    
                    this.update.notes.push({
                        id   : 'createSqliteTable-' + item,
                        name : '创建 sqlite ' + item + ' 表',
                        des  : '最近节点获取',
                        state: null,
                    })
                    
                    // 获取表信息
                    const table  = await axios.get(this.info.official.api + 'db/table', {
                        params:{'db':'sqlite','name':item}
                    }).then(res=>{
                        
                        let result = []
                        
                        if (res.data.code == 200) {
                            
                            const item = res.data.data
                            result     = item.sql
                            this.setNotes('createSqliteTable-' + item,{
                                state: 'success',
                            })
                            
                        } else {
                            
                            this.setNotes('createSqliteTable-' + item,{
                                state: 'error',
                            })
                        }
                        
                        return result
                    })
                    
                    // 创建表
                    const create = await axios.post('/index/update/createSqliteTable', inisHelper.stringfy({
                        table:item, query: table
                    })).then(res=>{
                        let result = false
                        if (res.data.code == 200) {
                            
                            this.setNotes('createSqliteTable-' + item,{
                                state: 'success',
                            })
                            
                            result = true
                            
                        } else {
                            this.setNotes('createSqliteTable-' + item,{
                                state: 'error',
                            })
                            $.NotificationApp.send("表创建失败，请重试！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "warning");
                        }
                        return result
                    }).catch(err=>{
                        this.setNotes('createSqliteTable-' + item,{
                            state: 'error',
                        })
                    })
                    
                    if (inisHelper.is.true(create)) this.importSqliteData()
                },
                
                // 导入sqlite数据
                importSqliteData(){
                    
                },
                
                // 获取最新的数据库表信息
                getOldTables(){
                    
                    this.update.notes.push({
                        id   : 'old-tables',
                        name : '获取本地数据库表信息',
                        des  : '从本地数据库获取',
                        state: null,
                    })
                    
                    axios.post('/index/update/tables').then(res=>{
                        if (res.data.code == 200) {
                            
                            const result            = res.data.data
                            this.update.tables.olds = result
                            this.setNotes('old-tables',{
                                state: 'success',
                            })
                            
                            this.diffTables()
                            
                        } else {
                            
                            this.setNotes('old-tables',{
                                state: 'error',
                            })
                        }
                    }).catch(err=>{
                        this.setNotes('old-tables',{
                            state: 'error',
                        })
                    })
                },
                // 计算数据库表差值
                diffTables(){
                    
                    // 数组求差
                    Array.prototype.diff = function(a) {
                        return this.filter(function(i) {return a.indexOf(i) < 0;});
                    };
                    const diff = this.update.tables.news.diff(this.update.tables.olds);
                    
                    this.update.notes.push({
                        id   : 'diff-tables',
                        name : '计算数据库表差异',
                        des  : '本地计算',
                        state: 'success',
                    })
                    
                    // 结果不为空 - 表示可能有新表 - 但不一定是官方的
                    if (!inisHelper.is.empty(diff)) {
                        
                        diff.forEach(item=>{
                            // 得到需要更新的官方数据库表
                            if (inisHelper.in.array(item, this.update.tables.news)) this.update.tables.adds.push(item)
                        })
                        this.update.notes.push({
                            id   : 'diff-tables-ok',
                            name : '数据库表差异计算完成',
                            des  : '需更新 ' + this.update.tables.adds.length + '个表',
                            state: 'success',
                        })
                        this.getTables()
                        
                    } else {
                        
                        this.update.notes.push({
                            id   : 'diff-tables-ok',
                            name : '数据库表差异计算完成',
                            des  : '表无需更新',
                            state: 'success',
                        })
                        
                        this.getDbData()
                    }
                },
                // 批量获取表信息
                getTables(name = null){
                    
                    // 批量导入
                    if (inisHelper.is.empty(name)) {
                        
                        this.update.tables.adds.forEach(item=>{
                            
                            this.update.notes.push({
                                id   : item,
                                name : '创建 ' + item + ' 表',
                                des  : '最近节点获取',
                                state: null,
                                reset: true,
                            })
                            
                            this.tableInfo(item)
                        })
                        
                    } else this.tableInfo(name)
                },
                // 获取表信息
                tableInfo(name = null){
                    
                    if (name == 'new-tables')      this.getNewTables()
                    else if (name == 'old-tables') this.getOldTables()
                    else if (name == 'importData') this.getDbData()
                    else if (name == 'getUpdatePackage') this.getUpdatePackage()
                    else if (name == 'downloadFile')     this.getUpdatePackage()
                    else if (name == 'unzipFile')        this.unzipFile()
                    else {
                        
                        axios.get(this.info.official.api + 'db/table', {
                            params: {name}
                        }).then(res=>{
                            
                            if (res.data.code == 200) {
                                
                                const result = res.data.data
                                
                                this.setNotes(name,{
                                    state: 'cache',
                                })
                                
                                this.createTable(name, result['Create Table'])
                                
                            } else {
                                
                                this.setNotes(name,{
                                    state: 'error',
                                })
                            }
                            
                        }).catch(err=>{
                            this.setNotes(name,{
                                state: 'error',
                            })
                        })
                    }
                },
                // 创建数据库表
                createTable(name, query){
                    
                    const params = inisHelper.stringfy({
                        table:name, query
                    })
                    
                    axios.post('/index/update/createTable', params).then(res=>{
                        if (res.data.code == 200) {
                            
                            this.setNotes(name,{
                                state: 'success',
                            })
                            
                        } else {
                            this.setNotes(name,{
                                state: 'error',
                            })
                            t.NotificationApp.send("表创建失败，请重试！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "warning");
                        }
                        
                    }).catch(err=>{
                        this.setNotes(name,{
                            state: 'error',
                        })
                    })
                },
                // 获取数据库表数据
                getDbData(){
                    this.update.notes.push({
                        id   : 'importData',
                        name : '表数据差异化导入',
                        des  : '最近节点获取',
                        state: null,
                        reset: true,
                    })
                    axios.get(this.info.official.api + 'db/data').then(res=>{
                        if (res.data.code == 200) {
                            const result = res.data.data
                            this.setNotes('importData',{
                                state:'cache'
                            })
                            this.importData(result.mysql)
                        } else {
                            this.setNotes('importData',{
                                state:'error'
                            })
                        }
                    }).catch(err=>{
                        this.setNotes('importData',{
                            state:'error'
                        })
                    })  
                },
                // 往数据库导入数据
                importData(data){
                    const params = inisHelper.stringfy({
                        data, tables: this.update.tables.adds
                    })
                    axios.post('/index/update/importData', params).then(res=>{
                        if (res.data.code == 200) {
                            this.setNotes('importData',{
                                state:'success'
                            })
                            this.getUpdatePackage()
                        } else {
                            this.setNotes('importData',{
                                state:'error'
                            })
                        }
                    }).catch(err=>{
                        this.setNotes('importData',{
                            state:'error'
                        })
                    })
                },
                // 获取更新包地址
                getUpdatePackage(){
                    this.update.notes.push({
                        id   : 'getUpdatePackage',
                        name : '获取更新包',
                        des  : '最近节点获取',
                        state: null,
                        reset: true,
                    })
                    axios.get(this.info.official.api + 'download', {
                        params:{mode:'update'}
                    }).then(res=>{
                        if (res.data.code == 200) {
                            this.setNotes('getUpdatePackage',{
                                state:'success'
                            })
                            this.downloadFile(res.data.data.file)
                        } else {
                            this.setNotes('getUpdatePackage',{
                                state:'error'
                            })
                        }
                    }).catch(err=>{
                        this.setNotes('getUpdatePackage',{
                            state:'error'
                        })
                    })
                },
                // 下载更新包
                downloadFile(path){
                    
                    this.update.notes.push({
                        id   : 'downloadFile',
                        name : '下载更新包',
                        des  : '最近节点获取',
                        state: null,
                        reset: true,
                    })
                    
                    const params = inisHelper.stringfy({
                        path
                    })
                    
                    axios.post('/index/update/downloadFile', params).then(res=>{
                        if (res.data.code == 200) {
                            this.setNotes('downloadFile',{
                                state:'success'
                            })
                            this.unzipFile()
                        } else {
                            this.setNotes('downloadFile',{
                                state:'error'
                            })
                        }
                    }).catch(err=>{
                        this.setNotes('downloadFile',{
                            state:'error'
                        })
                    })
                },
                // 解压更新
                unzipFile(){
                    
                    this.update.notes.push({
                        id   : 'unzipFile',
                        name : '解压更新包',
                        des  : '服务器内解压',
                        state: null,
                        reset: true,
                    })
                    
                    axios.post('/index/update/unzipFile').then(res=>{
                        if (res.data.code == 200) {
                            this.setNotes('unzipFile',{
                                state:'success'
                            })
                            this.update.fulfill = true
                            this.update.notes.push({
                                id   : 'update-ok',
                                name : '最终更新结果',
                                des  : '更新完成',
                                state: 'success',
                            })
                            setTimeout(()=>{
                                // 重新比对版本
                                this.compare()
                            }, 3000);
                            $.NotificationApp.send("提示！", "更新完成！", "top-right", "rgba(0,0,0,0.2)", "info");
                        } else {
                            this.setNotes('unzipFile',{
                                state:'error'
                            })
                        }
                    })
                },
                // 设置记录
                setNotes(id, obj = {}){
                    this.update.notes.forEach((item, index)=>{
                        // 合并新记录
                        if (item.id == id) this.update.notes[index] = {...item, ...obj}
                    })
                },
                // 系统修复
                restore(){
                    this.update.is_show = true
                    this.next('update-notes')
                    this.getNewTables()
                },
                // 下一步
                next(value  = 'update-content'){
                    
                    let nav = document.querySelector('#update-info .nav').querySelectorAll('.nav-item a')
                    let tab = document.querySelectorAll('#update-info .tab-content .tab-pane')
                    
                    let progress = (1 / nav.length) * 100
                    
                    if (value == 'update-content')     progress = (1 / nav.length) * 100
                    else if (value == 'update-notes')  progress = (2 / nav.length) * 100
                    
                    let bar = document.querySelector('#bar .progress-bar')
                    bar.style.setProperty('width', progress + '%')
                    
                    nav.forEach(item=>{
                        
                        if (item.getAttribute('href') == '#' + value) {
                            if (!inisHelper.in.array('active', item.classList)) item.classList.add('active')
                        } else item.classList.remove('active')
                        
                    })
                    
                    tab.forEach(item=>{
                        
                        if (item.getAttribute('id') == value) {
                            if (!inisHelper.in.array('active', item.classList)) item.classList.add('active')
                        } else item.classList.remove('active')
                    })
                },
                // 判断为空
                empty(value = null){
                    return inisHelper.is.empty(value) ? true : false;
                },
                // 时间戳转人性化时间
                natureTime: (time = '') => {
                    return (!inisHelper.is.empty(time)) ? inisHelper.time.nature(time) : time
                },
                // 动态图标
                lottie(){
                    axios.all([
                        axios.get('/index/assets/libs/lottie/json/beil.json').then(res=>res.data),
                    ]).then(axios.spread((beil)=>{
                        // 先删除动态图标
                        document.querySelector('#lottie-beil').innerHTML = ''
                        // 更新动态图标
                        lottie.loadAnimation({container:document.getElementById("lottie-beil"),renderer:"svg",loop:this.update.is_show,autoplay:true,animationData:beil})
                    }))
                },
            },
            watch: {
                update: {
                    handler(newValue,oldValue){
                        
                        const self = this
                        let array  = []
                        
                        self.update.notes.forEach(item=>{
                            if (inisHelper.in.array(item.id, self.update.tables.adds)) {
                                array.push(item.state)
                            }
                        })
                        
                        if (!inisHelper.get.storage('update','finish')) {
                            if (!inisHelper.is.empty(array)) if (array.every(item => item == 'success')) {
                                // 数据库表导入完成
                                inisHelper.set.storage('update',{finish:true})
                                self.getDbData()
                            }
                        }
                    },
                    deep: true,
                },
            },
            template: `<div class="navbar-custom">
                <ul class="list-unstyled topbar-right-menu float-right mb-0">
                
                    <li class="dropdown notification-list lottie">
                        <a data-toggle="modal" data-target="#update-info" class="nav-link dropdown-toggle arrow-none" href="javascript:;">
                            <!-- 图标 -->
                            <div id="lottie-beil"></div>
                            <!-- 点点 -->
                            <span v-show="update.is_show" class="bg-danger dots"></span>
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
            <div v-show="update.genuine" id="update-info" class="modal fade customize-progress" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel" style="display: none;" aria-hidden="true">
                <div v-show="!update.is_show" class="modal-dialog">
                    <div class="modal-content modal-filled bg-success">
                        <div class="modal-body p-4">
                            <div class="text-center">
                                <i class="dripicons-checkmark h1"></i>
                                <h4 class="mt-2">已经是最新版！</h4>
                                <p class="mt-3">
                                    当前系统暂无更新！更多资讯您可以前往
                                    <a href="//inis.cc" target="_blank" class="text-white">inis 社区</a>
                                    获取
                                </p>
                                <a href="//inis.cc" target="_blank" class="btn btn-light my-2 mr-2">inis 社区</a>
                                <button v-on:click="restore()" type="button" class="btn btn-light my-2" data-toggle="tooltip" data-original-title="进行系统修复，不会删除原有数据">系统修复</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-show="update.is_show" class="modal-dialog modal-lg">
                    <div class="modal-content">
                        
                        <div class="modal-body p-1 pt-3 pb-3 p-md-4">
                            <div class="text-center">
                                <i class="dripicons-information h1 text-info"></i>
                                <h4 class="mt-2">{{update.info.title}}</h4>
                            </div>
                            
                            <div id="progressbarwizard">
                            
                                <ul class="nav nav-tabs nav-justified nav-bordered mb-0">
                                    <li class="nav-item">
                                        <a href="#update-content" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2 active">
                                            <i class="mdi mdi-checkbox-marked-circle-outline mr-1"></i>
                                            <span class="d-none d-sm-inline">更新内容</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#update-notes" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                                            <i class="mdi mdi-spin mdi-star mr-1"></i>
                                            <span class="d-none d-sm-inline">更新过程</span>
                                        </a>
                                    </li>
                                </ul>
                            
                                <div class="tab-content b-0 mb-0">
                            
                                    <div id="bar" class="progress mb-3" style="height: 7px;">
                                        <div class="bar progress-bar progress-bar-striped progress-bar-animated bg-success"></div>
                                    </div>
                            
                                    <div class="tab-pane active" id="update-content">
                                        <div class="alert alert-light bg-light text-dark border-0" role="alert">
                                            <span>版本号：{{update.info.version}}</span>
                                            <span class="float-right">更新时间：{{natureTime(update.info.update_time)}}</span>
                                        </div>
                                        <div class="customize-scroll" style="max-height:450px">
                                            <div class="table-responsive-sm p-2">
                                                <table class="table customize-table table-centered mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>序号</th>
                                                            <th>说明</th>
                                                            <th>-</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(item, index) in update.content" :key="index">
                                                            <td>{{index + 1}}</td>
                                                            <td>{{item}}</td>
                                                            <td>
                                                                <svg t="1642523210900" class="icon" viewBox="0 0 1025 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1760" width="12" height="12"><path d="M483.84768 867.808C466.37568 885.792 441.73568 896 415.87968 896 390.05568 896 365.41568 885.792 347.94368 867.808L27.46368 547.552C-9.17632 508.864-9.17632 450.336 27.46368 411.648 44.26368 394.944 67.30368 385.088 91.68768 384.256 118.72768 383.008 144.93568 393.024 163.46368 411.648L415.87968 664 860.61568 219.552C878.31168 201.952 902.88768 192 928.58368 192 954.24768 192 978.82368 201.952 996.51968 219.552 1033.15968 258.208 1033.15968 316.704 996.51968 355.36L483.84768 867.808Z" p-id="1761" fill="#707070"></path></svg>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div v-if="!update.fulfill && update.is_show" class="flex-center mt-2">
                                            <ul class="list-inline mb-0 wizard">
                                                <li class="next list-inline-item float-right">
                                                    <button v-on:click="getNewTables()" type="button" class="btn btn-info">开始更新</button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="tab-pane" id="update-notes">
                                        <div v-if="update.fulfill" class="alert alert-success" role="alert">
                                            <i class="dripicons-checkmark mr-2"></i> 更新 <strong>已完成</strong> ！
                                        </div>
                                        <div v-else class="alert alert-info" role="alert">
                                            <i class="dripicons-information mr-2"></i> 更新完成之前，请 <strong>不要刷新</strong> 或 <strong>关闭</strong> 当前窗口
                                        </div>
                                        <div class="customize-scroll" style="max-height:450px">
                                            <div class="table-responsive-sm p-2">
                                                <table class="table customize-table table-centered mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>名称</th>
                                                            <th>-</th>
                                                            <th>说明</th>
                                                            <th>状态</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(item, index) in update.notes" :key="index">
                                                            <td>{{item.name || ''}}</td>
                                                            <td>
                                                                <svg v-if="item.state == 'success'" t="1642523210900" class="icon" viewBox="0 0 1025 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1760" width="12" height="12"><path d="M483.84768 867.808C466.37568 885.792 441.73568 896 415.87968 896 390.05568 896 365.41568 885.792 347.94368 867.808L27.46368 547.552C-9.17632 508.864-9.17632 450.336 27.46368 411.648 44.26368 394.944 67.30368 385.088 91.68768 384.256 118.72768 383.008 144.93568 393.024 163.46368 411.648L415.87968 664 860.61568 219.552C878.31168 201.952 902.88768 192 928.58368 192 954.24768 192 978.82368 201.952 996.51968 219.552 1033.15968 258.208 1033.15968 316.704 996.51968 355.36L483.84768 867.808Z" p-id="1761" fill="#707070"></path></svg>
                                                                <span v-else-if="empty(item.state)">-</span>
                                                                <svg v-else-if="item.state == 'cache'" t="1642599159698" class="icon" style="margin-left: -2px;" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="11220" width="16" height="16"><path d="M512.66280359 912.83100045c-227.88660496 0-413.26540601-185.37880105-413.26540601-413.265406s185.37880105-413.29486395 413.26540601-413.29486393c75.79527335 0 149.94090218 20.7089301 214.40959852 59.87325807 19.97248167 12.15139925 26.30593824 38.17748707 14.18399693 58.13523979-12.15139925 19.94302372-38.14802914 26.2764803-58.13523977 14.18399694-51.19789547-31.15176897-110.17268647-47.58929812-170.45835568-47.58929814-181.22523186 0-328.66220933 147.46643541-328.66220933 328.69166727s147.43697747 328.66220933 328.66220933 328.66220934 328.69166727-147.43697747 328.69166728-328.66220934c0-56.79490361-14.71423981-112.750256-42.52253285-161.84190894-11.53278256-20.32597692-4.37450373-46.16058815 15.92201524-57.67864175 20.3848928-11.53278256 46.13113022-4.37450373 57.67864174 15.92201525 35.04021673 61.80275299 53.5398015 132.20722375 53.5398015 203.58380647-0.01472898 227.90133391-185.42298797 413.28013498-413.30959291 413.28013497z" fill="#707070" p-id="11221"></path><path d="M679.54201986 596.51166697h-160.54575968c-51.75759629 0-93.83826009-42.0806638-93.83826011-93.8382601v-160.54575969c0-23.36014448 18.95618282-42.30159835 42.30159834-42.30159833s42.30159835 18.95618282 42.30159834 42.30159833v160.54575969c0 5.0962232 4.12411126 9.22033445 9.22033445 9.22033446h160.5457597c23.36014448 0 42.30159835 18.95618282 42.30159833 42.30159832 0.01472898 23.37487345-18.9267249 42.31632729-42.28686937 42.31632732z" fill="#707070" p-id="11222"></path></svg>
                                                                <span v-else-if="item.state == 'error' && item.reset" v-on:click="tableInfo(item.id)" class="pointer">
                                                                    <svg t="1642599497768" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="13439" width="16" height="16"><path d="M684.032 403.456q-17.408-8.192-15.872-22.016t11.776-22.016q3.072-2.048 19.968-15.872t41.472-33.28q-43.008-49.152-102.4-77.312t-129.024-28.16q-64.512 0-120.832 24.064t-98.304 66.048-66.048 98.304-24.064 120.832q0 63.488 24.064 119.808t66.048 98.304 98.304 66.048 120.832 24.064q53.248 0 100.864-16.896t87.04-47.616 67.584-72.192 41.472-90.624q7.168-23.552 26.624-38.912t46.08-15.36q31.744 0 53.76 22.528t22.016 53.248q0 14.336-5.12 27.648-21.504 71.68-63.488 132.096t-99.84 103.936-128.512 68.096-148.48 24.576q-95.232 0-179.2-35.84t-145.92-98.304-98.304-145.92-36.352-178.688 36.352-179.2 98.304-145.92 145.92-98.304 179.2-36.352q105.472 0 195.584 43.52t153.6 118.272q23.552-17.408 39.424-30.208t19.968-15.872q6.144-5.12 13.312-7.68t13.312 0 10.752 10.752 6.656 24.576q1.024 9.216 2.048 31.232t2.048 51.2 1.024 60.416-1.024 58.88q-1.024 34.816-16.384 50.176-8.192 8.192-24.576 9.216t-34.816-3.072q-27.648-6.144-60.928-13.312t-63.488-14.848-53.248-14.336-29.184-9.728z" p-id="13440" fill="#707070"></path></svg>
                                                                    刷新
                                                                </span>
                                                            </td>
                                                            <td>{{item.des || ''}}</td>
                                                            <td>
                                                                <span v-if="item.state == 'success'"><i class="mdi mdi-circle text-success"></i> 完成</span>
                                                                <span v-if="item.state == 'cache'"><i class="mdi mdi-circle text-info"></i> 等待中</span>
                                                                <span v-else-if="empty(item.state)"><i class="mdi mdi-circle text-warning"></i> 获取中</span>
                                                                <span v-else-if="item.state == 'error'"><i class="mdi mdi-circle text-danger"></i> 失败</span>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div> 
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
            <div v-if="!update.genuine" id="update-info" class="modal fade" tabindex="-1" role="dialog" aria-modal="true" style="display: none;">
                <div class="modal-dialog">
                    <div class="modal-content modal-filled bg-danger">
                        <div class="modal-body p-4">
                            <div class="text-center">
                                <i class="dripicons-wrong h1"></i>
                                <p class="mt-3">您的系统未添加授权，无法获取更新信息，请前往<a href="//inis.cc" target="_blank" class="text-white">inis 社区</a>添加授权！</p>
                                <a href="//inis.cc/admin/comm/login?url=/admin/warrant" target="_blank" class="btn btn-light my-2">inis 社区</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </teleport>
            `
        }
        
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
                                <li><a href="/index/placard">管理公告</a></li>
                            </ul>
                        </li>
                        
                        <li class="side-nav-title side-nav-item mt-1">控制台</li>
                        
                        <li class="side-nav-item">
                            <a href="/index/apiStore" class="side-nav-link">
                                <i class="mdi mdi-yin-yang"></i>
                                <span> API 商城 </span>
                            </a>
                        </li>
                        
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
                                <li><a href="/index/applets">小程序</a></li>
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