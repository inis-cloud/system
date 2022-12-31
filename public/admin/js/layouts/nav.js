!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                update: {
                    show: false,     // 版本比较 - 是否显示更新
                    // genuine: false,     // 正版查询
                    fulfill: false,     // 更新完成
                    info: {},           // 最新版信息
                    notes: [],          // 更新记录
                    content: [],        // 处理后的更新内容
                    // 表信息
                    mysql: {news:[],olds:[],adds:[]},
                    sqlite: {news:[],olds:[],adds:[]},
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
                this.checkUpdate()
            },
            // 检查更新 - 获取版本更新信息
            async checkUpdate(){

                const { code, data, msg } = await GET(inis.api + 'update/version')
                if (code != 200) return

                this.update.info    = data
                this.update.content = (data.content.split(/[(\r\n)\r\n]+/)).filter(value=>value && value.trim())
                // 版本比较
                let check = utils.compare.version(data.version, inis.version)
                // 显示更新
                this.update.show = check
                this.lottie()

                // 自动更新
                if (inis.autoupdate && check) {
                    Tool.Notyf('检测到新版本，正在为您更新...', 'default', { duration: 3 * 1000 })
                    this.startUpdate()
                }
            },
            // 开始更新
            async startUpdate(){

                this.next('update-notes')

                await this.getNewTables()
                await this.getOldTables()
                await this.diffTables()

                await this.getNewTables('sqlite')
                await this.getOldTables('sqlite')
                await this.diffTables('sqlite')

                await this.downloadFile()
            },

            // 获取新表
            async getNewTables(db = 'mysql'){

                const id = `new-tables-${db}`
                let name = `获取远程 <span class='text-info'>${db == 'mysql' ? '主库' : '备库'}</span> 信息`

                this.update.notes.push({
                    id, name,
                    des  : '最近节点获取',
                    state: null,
                })

                const { code, data, msg } = await GET(inis.api + db + '/table')

                if (code == 200) {
                    this.update[db].news = data
                    this.setNotes(id, { state: 'success' })
                } else this.setNotes(id, { state: 'error' })
            },

            // 获取旧表
            async getOldTables(db = 'mysql'){

                const id = `old-tables-${db}`

                this.update.notes.push({
                    id,
                    name : `获取本地 <span class='text-info'>${db == 'mysql' ? '主库' : '备库'}</span> 信息`,
                    des  : '从本地服务器获取',
                    state: null,
                })

                const result = await POST('/admin/update/tables', { db })

                if (result.code == 200) {
                    this.update[db].olds = result.data
                    this.setNotes(id, { state: 'success' })
                } else this.setNotes(id, { state: 'error' })
            },

            // 比对新旧表差异
            async diffTables(db = 'mysql'){

                // 数组求差
                const arrayDiff = function(maxArray, minArray) {

                    let result= []
                    maxArray  = new Set(maxArray)
                    minArray  = new Set(minArray)
                    for (let item of maxArray) if (!minArray.has(item)) result.push(item)
                
                    return result
                }
                
                // 表差异
                const diff = arrayDiff(this.update[db].news, this.update[db].olds)

                const id = `diff-tables-${db}-ok`
                
                this.update.notes.push({
                    id,
                    name : `<span class='text-info'>${db == 'mysql' ? '主库' : '备库'}</span> 差异计算完成`,
                    des  : `<span class='text-info'>${db == 'mysql' ? '主库' : '备库'}</span> 需更新 <span class='text-info'>${diff.length}</span> 个表`,
                    state: 'success',
                })

                this.update[db].adds = diff

                // 更新表
                if (!utils.is.empty(diff)) await this.createTables(db)
            },

            // 创建表
            async createTables(db = 'mysql'){

                const id = `create-tables-${db}`

                this.update.notes.push({
                    id,
                    name : `创建 <span class='text-info'>${db == 'mysql' ? '主库' : '备库'}</span> 表`,
                    des  : '正在创建',
                    state: null,
                })
                POST('/admin/update/createTables', {
                    db, tables: this.update[db].adds,
                }).then(res=>{
                    if (res.code == 200) {
                        this.setNotes(id, { state: 'success', des: '创建完成' })
                        this.insertAll(db, this.update[db].adds)
                    } else this.setNotes(id, { state: 'error', des: '创建失败' })
                })
            },

            // 插入数据
            async insertAll(db = 'mysql', tables = []){

                if (!utils.is.empty(tables)) {
                    const id = `insert-all-${db}`
                    this.update.notes.push({
                        id,
                        name : `导入 <span class='text-info'>${db == 'mysql' ? '主库' : '备库'}</span> 表默认数据`,
                        des  : '正在导入',
                        state: null,
                    })
                    POST('/admin/update/insertAll', {
                        db, tables,
                    }).then(res=>{
                        if (res.code == 200)      this.setNotes(id, { state: 'success', des: '导入完成' })
                        else if (res.code == 204) this.setNotes(id, { state: 'success', des: '导入完成' })
                        else this.setNotes(id, { state: 'error', des: '导入失败' })
                    })
                }
            },

            // 下载文件
            async downloadFile(){

                const id = 'download-file'
                this.update.notes.push({
                    id,
                    name : '下载更新包',
                    des  : '获取下载地址',
                    state: null,
                    reset: true,
                })

                const { package: path } = this.update.info

                this.setNotes(id, { state: 'cache', des: '正在下载' })

                const { code, data, msg } = await POST('/admin/update/downloadFile', { path })
                if (code != 200) return this.setNotes(id, { state: 'error', des: '下载失败' })

                this.setNotes(id, { state: 'success', des: '下载完成' })
                await this.unzipFile()
            },

            // 解压更新
            async unzipFile(){

                const id = 'unzip-file'
                
                this.update.notes.push({
                    id,
                    name : '解压更新包',
                    des  : '服务器内解压',
                    state: null,
                    reset: true,
                })

                const { code, data, msg } = await POST('/admin/update/unzipFile')
                if (code != 200) return this.setNotes(id, { state: 'error', des: '解压失败' })

                this.setNotes(id, { state: 'success', des: '解压完成' })
                this.update.fulfill = true
                this.update.notes.push({
                    id   : 'update-ok',
                    name : '最终更新结果',
                    des  : '更新完成',
                    state: 'success',
                })
                // 热更新 - 临时更改版本号
                inis.version = this.update.info.version
                setTimeout(()=>{
                    // 重新比对版本
                    this.checkUpdate()
                }, 1000);
                Tool.Notyf('更新完成！')
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
                this.update.show = true
                this.next('update-notes')
                this.startUpdate()
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
                        if (!utils.in.array('active', item.classList)) item.classList.add('active')
                    } else item.classList.remove('active')
                    
                })
                
                tab.forEach(item=>{
                    
                    if (item.getAttribute('id') == value) {
                        if (!utils.in.array('active', item.classList)) item.classList.add('active')
                    } else item.classList.remove('active')
                })
            },
            // 判断为空
            empty(value = null){
                return utils.is.empty(value) ? true : false;
            },
            // 时间戳转人性化时间
            natureTime: (time = '') => {
                return (!utils.is.empty(time)) ? utils.time.nature(time) : time
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
                },
                deep: true,
            },
            search_key: {
                handler(newValue,oldValue){
                    
                    const self  = this
                    self.search.result = utils.array.search(self.search.data, 'key', self.search_key)
                },
            }
        },
    }).mount('#nav')
    
}()