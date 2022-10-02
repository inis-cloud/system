!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                info: {                  // 信息
                    php:  {check:false}, // PHP
                    mysql:{check:false}, // 数据库
                    exten:{},            // 拓展
                },
                database: {},            // 数据库信息
                account:{},              // 帐号信息
                is_instal: false,        // 是否显示安装过程
                notes:[],                // 记录
                fulfill: {               // 安装完成
                    mysql: false,        // 数据库
                    sqlite: false,       // sqlite
                    account: false,      // 帐号
                },
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
                utils.set.storage('check',{database:false,finish:false})
                // 设置数据库信息缓存
                if (!utils.get.storage('database')) {
                    utils.set.storage('database',{
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
                
                this.database = utils.get.storage('database')
                
                POST('/install').then(res=>{
                    if (res.code == 200) this.info = res.data
                })
            },
            
            // 测试数据库连接
            testConn(){
                
                // 更新缓存
                utils.set.storage('database', this.database)
                
                POST('/install/handle/testConn', { ...this.database }).then(res=>{
                    if (res.code == 200) {
                        
                        utils.set.storage('check', { database: true })
                        this.initData()
                        this.next('setting')
                    }
                    else if (res.code == 400) Tool.Notyf(res.msg, 'warning')
                    else Tool.Notyf(res.msg, 'error')
                })
            },
            
            // 开始安装
            install(){
                
                utils.set.storage('check',{finish:false})
                
                if (utils.is.empty(this.account.nickname))      Tool.Notyf('昵称不得为空！', 'warning')
                else if (utils.is.empty(this.account.account))  Tool.Notyf('帐号不得为空！', 'warning')
                else if (utils.is.empty(this.account.password)) Tool.Notyf('密码不得为空！', 'warning')
                else if (utils.is.empty(this.account.email))    Tool.Notyf('邮箱不得为空！', 'warning')
                else {
                    
                    POST('/install/handle/setCache', { ...this.account }).then(res=>{
                        if (res.code == 200) {
                            this.is_instal = true
                            this.next('instal')
                            this.startInstall()
                        }
                    })
                }
            },

            // 开始安装
            async startInstall(){

                const check = await this.copyright()

                if (check) {
                    await this.createTables()
                    await this.createTables('sqlite')
                }
            },

            // 正版查询
            async copyright(){

                const result = await GET(inis.api + 'check')
                if (result.code == 200) {
                    if (result.data.status) return true
                    else {
                        const text = '您非正版用户，很抱歉我不能为您安装，如有能力，请点我支持正版！'
                        const notif= Tool.Notyf(text, 'error', { duration: 10 * 1000 })
                        const blank= () => window.open('https://inis.cc', '_blank')
                        notif.on('click',   () => blank())
                        notif.on('dismiss', () => blank())
                        return false
                    }
                } else Tool.Notyf('服务器正忙，请稍候重试！', 'warning')
            },

            // 创建表
            async createTables(db = 'mysql'){

                const id = `create-tables-${db}`

                this.notes.push({
                    id,
                    name : `创建 <span class='text-info'>${db == 'mysql' ? '主库' : '备库'}</span> 表`,
                    des  : '正在创建',
                    state: null,
                })
                POST('/install/handle/createTables', { db }).then(res=>{
                    if (res.code == 200) {
                        this.setNotes(id, { state: 'success', des: '创建完成' })
                        this.insertAll(db)
                    } else this.setNotes(id, { state: 'error', des: '创建失败' })
                })
            },

            // 插入数据
            async insertAll(db = 'mysql'){

                const id = `insert-all-${db}`
                this.notes.push({
                    id,
                    name : `导入 <span class='text-info'>${db == 'mysql' ? '主库' : '备库'}</span> 表默认数据`,
                    des  : '正在导入',
                    state: null,
                })
                POST('/install/handle/insertAll', { db }).then(async res=>{
                    if (res.code == 200 || res.code == 204) {
                        this.fulfill[db] = true
                        this.setNotes(id, { state: 'success', des: '导入完成' })
                        if (this.fulfill.mysql && this.fulfill.sqlite) await this.createAdmin()
                    }
                    else this.setNotes(id, { state: 'error', des: '导入失败' })
                })
            },

            // 设置记录
            setNotes(id, obj = {}){
                this.notes.forEach((item, index)=>{
                    // 合并新记录
                    if (item.id == id) this.notes[index] = {...item, ...obj}
                })
            },
            
            async createAdmin(){

                const id = `create-admin`
                
                this.notes.push({
                    id,
                    name : '创建帐号',
                    des  : '管理员帐号',
                    state: null,
                    reset: true,
                })
                        
                POST('/install/handle/createAdmin').then(res=>{
                    if (res.code == 200) {
                        this.fulfill.account = true
                        this.setNotes(id, { state: 'success' })
                        Tool.Notyf('安装完成！')
                        setTimeout(()=>{
                            Tool.Notyf('3秒 后自动为您跳转到登录页面！')
                        }, 1000)
                        setTimeout(()=>{
                            window.location.href = '/'
                        }, 3000)
                    }
                    else this.setNotes(id, { state: 'error' })
                }).catch(err=>this.setNotes(id, { state: 'error' }))
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
                        if (!utils.in.array('active', item.classList)) item.classList.add('active')
                    } else item.classList.remove('active')
                    
                })
                
                tab.forEach(item=>{
                    
                    if (item.getAttribute('id') == value) {
                        if (!utils.in.array('active', item.classList)) item.classList.add('active')
                    } else item.classList.remove('active')
                })
                
            },
            
            // 判断数据库校验是否通过
            isDatabase(){
                // 数据库校验未通过
                if (!utils.get.storage('check','database')) {
                    Tool.Notyf('请先完成数据库配置！', 'warning')
                } else this.next('setting')
            },
            
            // 判断环境是否通过
            isSetting(){
                if (!this.info.php.check) Tool.Notyf('PHP版本要求未通过！', 'warning')
                else if (!this.info.mysql.check) Tool.Notyf('数据库版本要求未通过！', 'warning')
                else this.next('account')
            },
            
            // 判断为空
            empty(value = null){
                return utils.is.empty(value) ? true : false;
            },
        },
        computed: {
            // 拓展信息
            extens(){
                
                let result = []
                // 需要校验的拓展
                const need = ['zip','PDO','curl','hash','json','mysqli','sqlite3','openssl','session','mysqlnd','cgi-fcgi','pdo_mysql','pdo_sqlite']
                
                if (!utils.is.empty(this.info.exten)) need.forEach(item=>{
                    (utils.in.array(item, this.info.exten)) ? result.push({name:item + ' 扩展',check:true}) : result.push({name:item,check:false})
                })
                
                return result
            }
        },
        watch: {
            notes: {
                
                handler(newValue,oldValue){
                    
                    const self = this
                },
                deep: true,
            },
        },
    }).mount('#install')
    
}(window.jQuery)