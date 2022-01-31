!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                api: 'https://inis.cc/api/',
                info: {                  // 信息
                    php:  {check:false}, // PHP
                    mysql:{check:false}, // 数据库
                    exten:{},            // 拓展
                },
                database: {},            // 数据库信息
                account:{},              // 帐号信息
                is_instal: false,        // 是否显示安装过程
                tables:{mysql:[],sqlite:[]},
                notes:[],                // 记录
                fulfill: false,          // 安装完成
            }
        },
        mounted() {
            this.initState()
            this.initData()
            this.next()
        },
        methods: {
            
            // 初始化数据
            initState(){
                // 设置禁止项缓存
                inisHelper.set.storage('check',{database:false,finish:false})
                // 设置数据库信息缓存
                if (!inisHelper.get.storage('database')) {
                    inisHelper.set.storage('database',{
                        HOSTNAME: 'localhost',
                        HOSTPORT: 3306,
                        DATABASE: null,
                        USERNAME: null,
                        PASSWORD: null,
                    })
                }
            },
            
            // 初始化
            initData() {
                
                this.database = inisHelper.get.storage('database')
                
                axios.post('/install').then(res=>{
                    if (res.data.code == 200) {
                        const result = res.data.data
                        this.info    = result
                    }
                })
            },
            
            // 测试数据库连接
            testConn(){
                
                // 更新缓存
                inisHelper.set.storage('database', this.database)
                
                const params = inisHelper.stringfy({
                    ...this.database
                })
                
                axios.post('/install/handle/testConn', params).then(res=>{
                    
                    if (res.data.code == 200) {
                        
                        const result = res.data.data
                        inisHelper.set.storage('check',{database:true})
                        this.initData()
                        this.next('setting')
                        
                    } else if (res.data.code == 400) t.NotificationApp.send("错误！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "warning");
                    else t.NotificationApp.send("错误！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "error");
                })
            },
            
            // 开始安装
            install(){
                
                inisHelper.set.storage('check',{finish:false})
                
                if (inisHelper.is.empty(this.account.nickname))      t.NotificationApp.send("提示！", "昵称不得为空！", "top-right", "rgba(0,0,0,0.2)", "warning");
                else if (inisHelper.is.empty(this.account.account))  t.NotificationApp.send("提示！", "帐号不得为空！", "top-right", "rgba(0,0,0,0.2)", "warning");
                else if (inisHelper.is.empty(this.account.password)) t.NotificationApp.send("提示！", "密码不得为空！", "top-right", "rgba(0,0,0,0.2)", "warning");
                else if (inisHelper.is.empty(this.account.email))    t.NotificationApp.send("提示！", "邮箱不得为空！", "top-right", "rgba(0,0,0,0.2)", "warning");
                else {
                    
                    const params = inisHelper.stringfy({
                        ...this.account
                    })
                    
                    axios.post('/install/handle/setCache', params).then(res=>{
                        if (res.data.code == 200) {
                            this.is_instal = true
                            this.next('instal')
                            this.getDbTables()
                            this.existSqlite()
                        }
                    })
                }
            },
            
            // 检查数据库文件是否存在
            existSqlite(){
                
                this.notes.push({
                    id   : 'existSqlite',
                    name : '检查sqlite数据库',
                    des  : '本地检查',
                    state: null,
                })
                
                axios.post('/install/handle/existSqlite').then(res=>{
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
                
                this.notes.push({
                    id   : 'downloadSqlite',
                    name : '下载sqlite数据库文件',
                    des  : '最近节点获取',
                    reset: true,
                    state: null,
                })
                
                axios.get(this.api + 'download/sqlite').then(res=>{
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
                axios.post('/install/handle/downloadFile', params).then(res=>{
                    if (res.data.code == 200) {
                        this.setNotes('downloadSqlite',{
                            state: 'success',
                        })
                        this.notes.push({
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
                const all = await axios.get(this.api + 'db/table', {
                    params: {'db':'sqlite'}
                }).then(res=>{
                    let result = []
                    if (res.data.code == 200) result = res.data.data
                    return result
                })
                // 获取sqlite本地表
                const self= await axios.post('/install/handle/sqliteTables').then(res=>{
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
                
                this.notes.push({
                    id   : 'createSqliteTable-' + item,
                    name : '创建 sqlite ' + item + ' 表',
                    des  : '最近节点获取',
                    state: null,
                })
                
                // 获取表信息
                const table  = await axios.get(this.api + 'db/table', {
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
                const create = await axios.post('/install/handle/createSqliteTable', inisHelper.stringfy({
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
            
            // 获取数据库表
            getDbTables(){
                
                this.notes.push({
                    id   : 'tables',
                    name : '获取数据库表',
                    des  : '最近节点获取',
                    state: null,
                })
                
                axios.get(this.api + 'db').then(res=>{
                    if (res.data.code == 200) {
                        
                        const result       = res.data.data
                        this.tables.mysql  = result.mysql
                        this.setNotes('tables',{
                            state: 'success',
                        })
                        
                        this.getTables()
                        
                    } else {
                        
                        this.setNotes('tables',{
                            state: 'error',
                        })
                    }
                })
            },
            
            // 设置记录
            setNotes(id, obj = {}){
                this.notes.forEach((item, index)=>{
                    // 合并新记录
                    if (item.id == id) this.notes[index] = {...item, ...obj}
                })
            },
            
            getTables(name = null){
                
                // 批量导入
                if (inisHelper.is.empty(name)) {
                    
                    this.tables.mysql.forEach(item=>{
                        
                        this.notes.push({
                            id   : item,
                            name : '创建 ' + item + ' 表',
                            des  : '最近节点获取',
                            state: null,
                            reset: true,
                        })
                        
                        this.getTable(item)
                    })
                    
                } else this.getTable(name)
            },
            
            getTable(name = null){
                
                if (name == 'createAdmin')           this.createAdmin()
                else if (name == 'importData')       this.getDbData()
                else if (name == 'downloadSqlite')   this.getSqlite()
                else {
                    
                    axios.get(this.api + 'db/table', {
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
            
            createTable(name, query){
                
                const params = inisHelper.stringfy({
                    table:name, query
                })
                
                axios.post('/install/handle/createTable', params).then(res=>{
                    if (res.data.code == 200) {
                        
                        this.setNotes(name,{
                            state: 'success',
                        })
                        // 开始创建帐号
                        if (name == 'inis_users') this.createAdmin()
                        
                    } else {
                        this.setNotes(name,{
                            state: 'error',
                        })
                        $.NotificationApp.send("表创建失败，请重试！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "warning");
                    }
                }).catch(err=>{
                    this.setNotes(name,{
                        state: 'error',
                    })
                })
            },
            
            getDbData(){
                this.notes.push({
                    id   : 'importData',
                    name : '初始化表数据',
                    des  : '最近节点获取',
                    state: null,
                    reset: true,
                })
                axios.get(this.api + 'db/data').then(res=>{
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
            
            importData(data){
                const params = inisHelper.stringfy({
                    ...data
                })
                axios.post('/install/handle/importData', params).then(res=>{
                    if (res.data.code == 200) {
                        this.setNotes('importData',{
                            state:'success'
                        })
                        this.fulfill = true
                        // 清除缓存
                        localStorage.removeItem('database')
                        t.NotificationApp.send("提示！", "系统安装完成，感谢您的支持！", "top-right", "rgba(0,0,0,0.2)", "info");
                    } else {
                        this.setNotes('importData',{
                            state:'error'
                        })
                        this.fulfill = false
                    }
                }).catch(err=>{
                    this.setNotes('importData',{
                        state:'error'
                    })
                })
            },
            
            createAdmin(){
                
                this.notes.push({
                    id   : 'createAdmin',
                    name : '创建帐号',
                    des  : '管理员帐号',
                    state: null,
                    reset: true,
                })
                        
                axios.post('/install/handle/createAdmin').then(res=>{
                    if (res.data.code == 200) {
                        this.setNotes('createAdmin',{
                            state: 'success',
                        })
                    } else {
                        this.setNotes('createAdmin',{
                            state: 'error',
                        })
                    }
                }).catch(err=>{
                    this.setNotes('createAdmin',{
                        state: 'error',
                    })
                })
            },
            
            // 下一步
            next(value  = 'database'){
                
                let nav = document.querySelector('.nav').querySelectorAll('.nav-item a')
                let tab = document.querySelectorAll('.tab-content .tab-pane')
                
                let progress = (1 / nav.length) * 100
                
                if (value == 'database')     progress = (1 / nav.length) * 100
                else if (value == 'setting') progress = (2 / nav.length) * 100
                else if (value == 'account') progress = (3 / nav.length) * 100
                else if (value == 'instal')  progress = (4 / nav.length) * 100
                
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
            
            // 判断数据库校验是否通过
            isDatabase(){
                // 数据库校验未通过
                if (!inisHelper.get.storage('check','database')) {
                    t.NotificationApp.send("提示！", "请先完成数据库配置！", "top-right", "rgba(0,0,0,0.2)", "warning");
                } else this.next('setting')
            },
            
            // 判断环境是否通过
            isSetting(){
                if (!this.info.php.check) t.NotificationApp.send("提示！", "PHP版本要求未通过！", "top-right", "rgba(0,0,0,0.2)", "warning");
                else if (!this.info.mysql.check) t.NotificationApp.send("提示！", "数据库版本要求未通过！", "top-right", "rgba(0,0,0,0.2)", "warning");
                else this.next('account')
            },
            
            // 判断为空
            empty(value = null){
                return inisHelper.is.empty(value) ? true : false;
            },
        },
        computed: {
            // 拓展信息
            extens(){
                
                let result = []
                // 需要校验的拓展
                const need = ['zip','PDO','curl','hash','json','mysqli','sqlite3','openssl','session','mysqlnd','cgi-fcgi','pdo_mysql','pdo_sqlite']
                
                if (!inisHelper.is.empty(this.info.exten)) need.forEach(item=>{
                    (inisHelper.in.array(item, this.info.exten)) ? result.push({name:item + ' 扩展',check:true}) : result.push({name:item,check:false})
                })
                
                return result
            }
        },
        watch: {
            notes: {
                
                handler(newValue,oldValue){
                    
                    const self = this
                    let array  = []
                    
                    self.notes.forEach(item=>{
                        if (inisHelper.in.array(item.id, self.tables.mysql)) {
                            array.push(item.state)
                        }
                    })
                    
                    
                    if (!inisHelper.get.storage('check','finish')) {
                        if (!inisHelper.is.empty(array)) if (array.every(item => item == 'success')) {
                            // 数据库表导入完成
                            inisHelper.set.storage('check',{finish:true})
                            self.getDbData()
                        }
                    }
                },
                deep: true,
            },
        },
    }).mount('#install')
    
}(window.jQuery)