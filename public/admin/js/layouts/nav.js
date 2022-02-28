!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
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
                search: {
                    data: [
                        {name:'首页',url:'/',key:'首页 控制台'},
                        {name:'撰写文章',url:'/admin/write-article.html',key:'写文章 撰写文章'},
                        {name:'新建页面',url:'/admin/write-page.html',key:'新建页面 创建页面'},
                        {name:'管理文章',url:'/admin/manage-article.html',key:'管理文章 文章管理'},
                        {name:'管理页面',url:'/admin/manage-page.html',key:'管理页面 页面管理'},
                        {name:'管理友链',url:'/admin/manage-links.html',key:'管理友链 友链管理'},
                        {name:'管理标签',url:'/admin/manage-tag.html',key:'管理标签 标签管理'},
                        {name:'管理评论',url:'/admin/manage-comments.html',key:'管理评论 评论管理'},
                        {name:'管理用户',url:'/admin/manage-users.html',key:'管理用户 用户管理'},
                        {name:'文章分类',url:'/admin/manage-article-sort.html',key:'文章分类'},
                        {name:'友链分组',url:'/admin/manage-links-sort.html',key:'友链分组 友链分类'},
                        {name:'轮播',url:'/admin/manage-banner.html',key:'管理轮播 轮播管理'},
                        {name:'音乐',url:'/admin/manage-music.html',key:'管理音乐 音乐管理'},
                        {name:'公告',url:'/admin/manage-placard.html',key:'管理公告 公告管理'},
                        {name:'API 商城',url:'/admin/api-store.html',key:'API商城 api商城'},
                        {name:'个人资料',url:'/admin/edit-profile.html',key:'个人资料 我的资料 我的信息 个人信息'},
                        {name:'系统配置',url:'/admin/system.html',key:'系统配置 站点信息 冗余资源 垃圾清理 其他配置 小程序 文章配置 速度优化 CDN cdn 加速'},
                        {name:'文件系统',url:'/admin/filesystem.html',key:'文件系统 硬盘信息 磁盘信息'}
                    ],
                    result: []
                },
                search_key:''
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
                axios.post('/admin/update/info').then(res=>{
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
                
                axios.post('/admin/update/existSqlite').then(res=>{
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
                axios.post('/admin/update/downloadSqlite', params).then(res=>{
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
                const self= await axios.post('/admin/update/sqliteTables').then(res=>{
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
                const create = await axios.post('/admin/update/createSqliteTable', inisHelper.stringfy({
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
                
                axios.post('/admin/update/tables').then(res=>{
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
                
                axios.post('/admin/update/createTable', params).then(res=>{
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
                axios.post('/admin/update/importData', params).then(res=>{
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
                
                axios.post('/admin/update/downloadFile', params).then(res=>{
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
                
                axios.post('/admin/update/unzipFile').then(res=>{
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
                    axios.get('/admin/libs/lottie/json/beil.json').then(res=>res.data),
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
            search_key: {
                handler(newValue,oldValue){
                    
                    const self  = this
                    self.search.result = inisHelper.array.search(self.search.data, 'key', self.search_key)
                    console.log(self.search.result)
                },
                // deep: true,
            }
        },
    }).mount('#nav')
    
}()