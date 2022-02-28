!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                banner: [],         // 友链数据合集
                edit: [],           // 编辑友链数据
                page_list: {},      // 友链页码列表
                page: 1,            // 当前友链页码     
                is_load: true,      // 数据加载动画
                page_is_load: true, // 页码加载动画
                is_page_show: true, // 是否显示分页
                title: '新增轮播',  // 标题
                speed: 0,           // 上传图片进度
                jump: 'outside',    // 跳转方式
            }
        },
        components: {
            
        },
        mounted() {
            this.initData()
        },
        methods: {
            
            // 获取初始化数据
            initData(id = '', page = this.page, is_load = false){
                
                
                // 数据加载动画
                this.is_load = is_load
                
                if(!inisHelper.is.empty(id)) this.title = '修改轮播'
                else this.title = '新增轮播'
                
                const params = inisHelper.stringfy({
                    id, page, limit: 8
                })
                
                axios.post('/admin/ManageBanner', params).then((res) => {
                    if(res.data.code == 200){
                        
                        // 更新数据
                        this.banner             = res.data.data.banner
                        
                        // 更新数据
                        if (inisHelper.is.empty(res.data.data.edit)) this.edit = {title:'',description:'',url:'',img:'',opt:{jump:'outside',article_id:''}}
                        else {
                            
                            this.edit   = res.data.data.edit
                            $("#inside-select2").empty()
                            
                        }
                        
                        // 是否显示分页
                        if (inisHelper.is.empty(this.banner.data) || this.banner.page == 1) this.is_page_show = false
                        else this.is_page_show = true
                        
                        let article = res.data.data.article
                        article.forEach(item=>{
                            item.text = item.title
                            delete item.title
                            if (!inisHelper.is.empty(res.data.data.edit)) {
                                if (!inisHelper.is.empty(this.edit.opt)) {
                                    if (!inisHelper.is.empty(this.edit.opt.jump)) {
                                        if (item.id == this.edit.opt.article_id) item.selected = true
                                    }
                                } 
                            }
                        })
                        
                        // 站内跳转单选框
                        $("#inside-select2").select2({
                            data: article,
                        })
                        
                        // 更新页码
                        this.page              = page
                        
                        // 页码列表
                        this.page_list         = inisHelper.create.paging(page, this.banner.page, 5)
                        
                        // 数据加载动画
                        this.is_load           = false
                        // 页码加载动画
                        this.page_is_load      = false
                    }
                })
            },
            
            // 保存数据
            btnSave(id = ''){
                
                if (inisHelper.is.empty(this.edit.img)) {
                    $.NotificationApp.send(null, "请上传轮播图片！", "top-right", "rgba(0,0,0,0.2)", "warning");
                } else {
                    
                    $.NotificationApp.send(null, "正在验证 ... ...", "top-right", "rgba(0,0,0,0.2)", "info");
                    
                    // 数据加载动画
                    this.is_load = true
                    
                    let params = new FormData
                    params.append('id',id || '')
                    
                    for (let item in this.edit) {
                        params.append(item, this.edit[item] || '')
                    }
                    
                    // 获取 select2 站内跳转单选框数据
                    const inside = $('#inside-select2').select2('data')[0].id;
                    params.append("opt[jump]", this.jump || '')
                    params.append("opt[article_id]", inside || '')
                    
                    axios.post('/admin/method/SaveBanner', params).then((res) => {
                        if (res.data.code == 200) {
                            $.NotificationApp.send(null, "数据保存成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                        } else $.NotificationApp.send(null, res.data.msg, "top-right", "rgba(0,0,0,0.2)", "error");
                        // 刷新数据
                        this.initData()
                    })
                }
            },
            
            // 删除数据
            btnDelete(id = ''){
                
                // 数据加载动画
                this.is_load = true
                
                let params = new FormData
                params.append('id',id || '')
                
                axios.post('/admin/method/DeleteBanner', params).then((res) => {
                    if (res.data.code == 200) {
                        $.NotificationApp.send(null, "删除成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                    } else {
                        $.NotificationApp.send(null, res.data.msg, "top-right", "rgba(0,0,0,0.2)", "error");
                    }
                    // 刷新数据
                    this.initData()
                })
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
                
                if (file.size > 20 * 1024 * 1024) $.NotificationApp.send(null, "上传文件不得大于20MB！", "top-right", "rgba(0,0,0,0.2)", "warning");
                else if (inisHelper.in.array(name.pop(), warning)){
                    $.NotificationApp.send(null, "请不要尝试提交可执行程序，因为你不会成功！", "top-right", "rgba(0,0,0,0.2)", "error");
                } else {
                    
                    $.NotificationApp.send(null, "正在上传 ...", "top-right", "rgba(0,0,0,0.2)", "info");
                    
                    let params = new FormData
                    params.append("file", file || '')
                    params.append("id", this.edit.id || 0)
                    params.append("mode", 'banner')
                    
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
                    
                    axios.post("/admin/handle/upload", params, config).then((res) => {
                        if (res.data.code == 200) {
                            self.speed = 1
                            this.edit.img = res.data.data
                            $.NotificationApp.send(null, "上传成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                        } else {
                            self.speed = 0
                            $.NotificationApp.send(null, res.data.msg, "top-right", "rgba(0,0,0,0.2)", "error");
                        }
                    })
                    
                    event.target.value = ''
                }
            },
        },
        watch: {
            edit: {
                handler(newValue,oldValue){
                    
                    let edit = this.edit
                    if (!inisHelper.is.empty(edit.opt)) {
                        if (!inisHelper.is.empty(edit.opt.jump)) this.jump = edit.opt.jump
                    }
                },
            }
        }
        
    }).mount('#manage-banner')

}(window.jQuery);