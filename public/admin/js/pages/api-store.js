!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                apis:{},            // 官方的API
                internal: {},       // 内置的API
                plugin: {           // 已安装API
                    installed: {
                        version: []
                    }
                },
                page: {             // 分页
                    apis: {         // 官方的API
                        code: 1,
                        list: [],
                        is_show: false,
                        is_load: true,
                    },
                    plugin:{        // 已安装API
                        code: 1,
                        list: [],
                        is_show: false,
                        is_load: true,
                    },
                    internal:{      // 内置的API
                        code: 1,
                        list: [],
                        is_show: false,
                        is_load: true,
                    },
                },
                notes: [],          // 记录
                fulfill: false,     // 操作完成
                installed: [],      // 从官网获取已安装的API信息，用于判断是否有升级
            }
        },
        components: {
            
        },
        mounted() {
            this.getPlugin()
            this.getInternal()
            this.initData()
        },
        methods: {
            
            // 刷新
            refresh(){
                this.getInternal()
                this.getPlugin()
            },
            
            initData(){

            },
            
            // 获取内置的API
            getInternal(page = this.page.internal.code){
                
                // 数据加载动画
                this.page.internal.is_load = true
                
                POST('/admin/apiStore', {
                    page, limit: 12
                }).then(res => {
                    if (res.code == 200) {
                        
                        const result = res.data
                        this.internal= result
                        
                        // 是否显示分页
                        if (utils.is.empty(result.data) || result.page == 1) this.page.internal.is_show = false
                        else this.page.internal.is_show = true
                        
                        // 更新页码
                        this.page.internal.code    = page
                        
                        // 页码列表
                        this.page.internal.list    = utils.create.paging(page, result.page, 5)
                        
                        // 数据加载动画
                        this.page.internal.is_load = false
                    }
                })
            },
            
            // 获取已安装的API
            getPlugin(page = this.page.plugin.code){
                
                // 数据加载动画
                this.page.plugin.is_load = true
                
                POST('/admin/installedApi', {
                    page, limit: 12
                }).then(res => {
                    if (res.code == 200) {
                        
                        const result = res.data
                        this.plugin  = result
                        
                        // 是否显示分页
                        if(utils.is.empty(result.data) || result.page == 1) this.page.plugin.is_show = false
                        else this.page.plugin.is_show = true
                        
                        // 更新页码
                        this.page.plugin.code    = page
                        
                        // 页码列表
                        this.page.plugin.list    = utils.create.paging(page, result.page, 5)
                        
                        // 数据加载动画
                        this.page.plugin.is_load = false
                        
                        this.installedApi()
                        this.getApis()
                    }
                })
            },
            
            // 获取全部API
            getApis(page = this.page.apis.code){
                
                // 数据加载动画
                this.page.apis.is_load = true
                
                GET(inis.api + 'plugin', {
                    page, limit: 12
                }).then(res => {
                    if (res.code == 200) {
                        
                        const result = res.data
                        
                        result.data.forEach((item, index)=>{
                            // 判断是否已安装
                            item.installed = (utils.in.array(item.id, this.plugin.installed.id)) ? true : false
                            // 先拿到本地这个API的版本信息
                            const version  = this.getArrayId(this.plugin.installed.version, item.id)
                            item.update    = utils.compare.version(this.getArrayId(this.installed, item.id), version)
                        })
                        
                        this.apis    = result
                        
                        // 是否显示分页
                        if (utils.is.empty(result.data) || result.page == 1) this.page.apis.is_show = false
                        else this.page.apis.is_show = true
                        // 更新页码
                        this.page.apis.code    = page
                        
                        // 页码列表
                        this.page.apis.list    = utils.create.paging(page, result.page, 5)
                        
                        // 数据加载动画
                        this.page.apis.is_load = false
                    } else Tool.Notyf(res.msg, 'error')
                })
            },
            
            // 从官方获取已安装API的最新数据
            installedApi(){
                GET(inis.api + 'plugin', {
                    id: this.plugin.installed.id,
                    withoutField: ['uid','title','expand','content','url','docsify','status','size','opt','longtext','create_time','update_time']
                }).then(res => {
                    if (res.code == 200) {
                        
                        const result   = res.data
                        this.installed = result
                        
                        this.plugin.data.forEach((item, index)=>{
                            // 比对两个版本，是否有更新
                            const version = this.getArrayId(result, item.id)
                            const compare = utils.compare.version(version, item.version)
                            item.update   = compare
                            if (compare) item.new_version = version
                        })
                    }
                })
            },
            
            getArrayId(data = [], id = null){
                let result = ''
                if (typeof data == 'object') data = Object.keys(data).map(function(i){return data[i]})
                data.forEach(item => {
                    if (item.id == id) result = item.version
                })
                return result
            },
            
            // 安装API
            installApi(obj = {}){
                
                // 清空记录
                this.notes   = [];
                this.fulfill = false;
                
                $('#install-notes').modal('show')
                
                this.notes.push({
                    id   : 'startInstall',
                    name : '开始安装 ' + obj.title,
                    des  : '安装第三方API，名称：' + obj.title,
                    state: 'cache',
                })
                
                this.notes.push({
                    id   : 'downloadApiFile',
                    name : '下载API文件',
                    des  : '最近节点获取',
                    state: null,
                })
                
                POST('/admin/method/installApi', {
                    ...obj
                }).then(res => {
                    
                    if (res.code == 200) {
                        const result = res.data
                        this.setNotes('downloadApiFile', {state:'success'})
                        this.unzipApi(result)
                    } else this.setNotes('downloadApiFile', {state:'error'})
                    
                }).catch(() => this.setNotes('downloadApiFile', {state:'error'}))
            },
            
            // 解压API
            unzipApi(obj = {}){
                
                this.notes.push({
                    id   : 'unzipApiFile',
                    name : '解压API文件',
                    des  : '本地解压',
                    state: null,
                })
                
                POST('/admin/method/unzipApi', {
                    ...obj
                }).then(res => {
                    
                    if (res.code == 200) {
                        
                        this.fulfill = true;
                        const result = res.data
                        this.setNotes('unzipApiFile', {state:'success'})
                        this.setNotes('startInstall', {state:'success'})
                        this.getPlugin()
                        
                    } else this.setNotes('unzipApiFile', {state:'error'})
                })
            },
            
            // 卸载API
            uninstall(id = null){
                
                POST('/admin/method/uninstallApi', { id }).then(res => {
                    if (res.code == 200) this.getPlugin()
                    else Tool.Notyf('卸载失败！', 'error')
                })
            },
            
            // 设置记录
            setNotes(id, obj = {}){
                this.notes.forEach((item, index)=>{
                    // 合并新记录
                    if (item.id == id) this.notes[index] = {...item, ...obj}
                })
            },
            
            // 判断为空
            empty: value => utils.is.empty(value),
            
            // 是否为True
            isTrue: data => utils.is.true(data),
        },
        watch: {
            
        }
        
    }).mount('#api-store')

}(window.jQuery);