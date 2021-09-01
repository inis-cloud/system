!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                article_sort: [],   // 全部分类数据
                edit_sort: {},      // 编辑分类数据
                page_list: {},      // 分类页码列表
                page: 1,            // 当前分类页码  
                is_show: [],        // 是否显示数据
                is_load: true,      // 数据加载动画
                page_is_load: true, // 页码加载动画
                is_page_show: true, // 是否显示分页
                speed: 0,           // 上传头像进度
            }
        },
        components: {
            'i-footer'    : inisTemp.footer(),
            'i-top-nav'   : inisTemp.navbar(),
            'i-left-side' : inisTemp.sidebar(),
            'i-right-side': inisTemp.sidebar('right'),
        },
        mounted() {
            this.initData()
        },
        methods: {
            
            // 获取初始化数据
            initData(id = null, page = this.page, is_load = false){
                
                // 数据加载动画
                this.is_load = is_load
                
                let params = new FormData
                params.append('id', id || '')
                params.append('page',page || '')
                
                axios.post('/index/ManageArticleSort', params).then((res) => {
                    if(res.data.code == 200){
                        
                        // 更新数据
                        this.article_sort      = res.data.data
                        
                        // 设置显示数据
                        this.article_sort.sort.data.forEach((item)=>{
                            if (item.is_show == 1) this.is_show.push(item.id)
                        })
                        
                        // 更新数据
                        if(inisHelper.is.empty(res.data.data.edit_sort)) this.edit_sort = {name:'',description:'',head_img:''}
                        else {
                            this.edit_sort    = res.data.data.edit_sort
                            if (!inisHelper.is.empty(this.edit_sort.opt)) {
                                if (!inisHelper.is.empty(this.edit_sort.opt.head_img)) this.edit_sort.head_img = this.edit_sort.opt.head_img
                            }
                        }
                        
                        // 是否显示分页
                        if(inisHelper.is.empty(this.article_sort.sort.data) || this.article_sort.sort.page == 1) this.is_page_show = false
                        else this.is_page_show = true
                        
                        // 更新页码
                        this.page              = page
                        
                        // 页码列表
                        this.page_list         = inisHelper.create.paging(page, this.article_sort.sort.page, 7)
                        
                        // 数据加载动画
                        this.is_load           = false
                        // 页码加载动画
                        this.page_is_load      = false
                    }
                })
            },
            
            // 保存数据
            btnSave(id = ''){
                
                if(inisHelper.is.empty(this.edit_sort.name)){
                    $.NotificationApp.send("错误！", "请填写分类名称！", "top-right", "rgba(0,0,0,0.2)", "warning");
                }else{
                    
                    $.NotificationApp.send("执行验证！", "正在验证 ... ...", "top-right", "rgba(0,0,0,0.2)", "info");
                    
                    // 数据加载动画
                    this.is_load = true
                    
                    let params = new FormData
                    params.append('id', id || '')
                    params.append('sort_name', this.edit_sort.name || '')
                    params.append('description', this.edit_sort.description || '')
                    params.append('opt[head_img]', this.edit_sort.head_img || '')
                    
                    axios.post('/index/method/SaveArticleSort', params).then((res) => {
                        if(res.data.code == 200){
                            $.NotificationApp.send("验证成功！", "数据保存成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                        }else{
                            $.NotificationApp.send("验证错误！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "warning");
                        }
                        // 刷新数据
                        this.initData()
                    })
                }
            },
            
            // 删除数据
            btnDelete(id){
                
                id = id || null
                
                // 数据加载动画
                this.is_load = true
                
                let params = new FormData()
                params.append('id', id || '')
                
                axios.post('/index/method/DeleteArticleSort', params).then((res) => {
                    if(res.data.code == 200){
                        $.NotificationApp.send("", "删除成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                    }else{
                        $.NotificationApp.send("删除失败！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "warning");
                    }
                    // 刷新数据
                    this.initData()
                })
            },
            
            // 是否显示
            isShow(id = ''){
                
                let [arr, status] = [this.is_show, 0]
                
                // 状态取反
                if(inisHelper.in.array(id,arr)) status = 0
                else if(!inisHelper.in.array(id,arr)) status = 1
                
                let params = new FormData
                params.append('id',id || '')
                params.append('status',status || '')
                
                axios.post('/index/handle/SetArticleSortShow', params)
            },
            
            // 触发上传事件
            clickUpload: () => {
                document.querySelector("#input-file").click()
            },
            
            // 上传头像
            upload(event){
                
                const self = this
                
                /* 单图上传 */
                let file  = event.target.files[0]
                
                let name  = file.name
                name = name.split('.')
                const warning = ['php','js','htm','html','xml','json','bat','vb','exe']
                
                if (file.size > 5 * 1024 * 1024) $.NotificationApp.send("错误！", "上传文件不得大于5MB！", "top-right", "rgba(0,0,0,0.2)", "warning");
                else if (inisHelper.in.array(name.pop(), warning)){
                    $.NotificationApp.send("提示！", "请不要尝试提交可执行程序，因为你不会成功！", "top-right", "rgba(0,0,0,0.2)", "error");
                } else {
                    
                    $.NotificationApp.send("提示！", "正在上传 ...", "top-right", "rgba(0,0,0,0.2)", "info");
                    
                    let params = new FormData
                    params.append("file", file || '')
                    params.append("mode", 'file')
                    
                    const config = {
                        headers: { "Content-Type": "multipart/form-data" },
                        onUploadProgress: (speed) => {
                            if (speed.lengthComputable) {
                                let ratio = speed.loaded / speed.total;
                                // 只是上传到后端，后端并未真正保存成功
                                if (ratio < 1) self.speed = ratio
                            }
                        }
                    }
                    
                    axios.post("/index/handle/upload", params, config).then((res) => {
                        if(res.data.code == 200){
                            self.speed = 1
                            this.edit_sort.head_img = res.data.data
                            $.NotificationApp.send("提示！", "<span style='color:var(--blue)'>上传成功！</span>", "top-right", "rgba(0,0,0,0.2)", "info");
                        } else {
                            self.speed = 0
                            $.NotificationApp.send("错误！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "info");
                        }
                    })
                    
                    event.target.value = ''
                }
            },
        },
        computed: {
            article_sort:{
                get:function(){
                    return this.article_sort
                },
                set:function(value){
                    value.sort.data.forEach(item=>{
                        if (inisHelper.is.empty(item.opt)) item.opt = {"head_img":""}
                    })
                }
            }
        },
    }).mount('#manage-article-sort')

}(window.jQuery);