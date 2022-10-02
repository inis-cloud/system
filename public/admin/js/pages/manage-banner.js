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
                title: '新增轮播',   // 标题
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

                this.title = utils.is.empty(id) ? '新增轮播' : '修改轮播'
                
                POST('/admin/ManageBanner', {
                    id, page, limit: 8
                }).then(res => {
                    if (res.code == 200) {
                        
                        // 更新数据
                        this.banner             = res.data.banner
                        
                        // 更新数据
                        if (utils.is.empty(res.data.edit)) this.edit = {title:'',description:'',url:'',img:'',opt:{jump:'outside',article_id:''}}
                        else {
                            
                            this.edit   = res.data.edit
                            $('#inside-select2').empty()
                        }
                        
                        // 是否显示分页
                        if (utils.is.empty(this.banner.data) || this.banner.page == 1) this.is_page_show = false
                        else this.is_page_show = true
                        
                        let article = res.data.article
                        article.forEach(item => {
                            item.text = item.title
                            delete item.title
                            if (!utils.is.empty(res.data.edit)) {
                                if (!utils.is.empty(this.edit.opt)) {
                                    if (!utils.is.empty(this.edit.opt.jump)) {
                                        if (item.id == this.edit.opt.article_id) item.selected = true
                                    }
                                } 
                            }
                        })
                        
                        // 站内跳转单选框
                        $('#inside-select2').select2({
                            data: article,
                        })
                        
                        // 更新页码
                        this.page              = page
                        
                        // 页码列表
                        this.page_list         = utils.create.paging(page, this.banner.page, 5)
                        
                        // 数据加载动画
                        this.is_load           = false
                        // 页码加载动画
                        this.page_is_load      = false
                    }
                })
            },
            
            // 保存数据
            btnSave(id = ''){
                
                if (utils.is.empty(this.edit.img)) Tool.Notyf('请上传轮播图片！', 'warning')
                else {
                    
                    Tool.Notyf('正在验证 ...')
                    
                    // 数据加载动画
                    this.is_load = true

                    let params = { id, opt: {} }
                    for (let item in this.edit) params[item] = this.edit[item] || ''
                    // 获取 select2 站内跳转单选框数据
                    params.opt.jump = this.jump || ''
                    params.opt.article_id = $('#inside-select2').select2('data')[0].id
                    
                    POST('/admin/method/SaveBanner', params).then(res => {
                        if (res.code == 200) Tool.Notyf('保存成功！', 'success')
                        else Tool.Notyf(res.msg, 'error')
                        // 刷新数据
                        this.initData()
                    })
                }
            },
            
            // 删除数据
            btnDelete(id = ''){
                
                // 数据加载动画
                this.is_load = true
                
                POST('/admin/method/DeleteBanner', { id }).then(res => {
                    if (res.code == 200) Tool.Notyf('删除成功！', 'success')
                    else Tool.Notyf(res.msg, 'error')
                    // 刷新数据
                    this.initData()
                })
            },
            
            // 触发上传事件
            clickUpload: () => document.querySelector('#input-file').click(),
            
            // 上传头像
            upload(event){
                
                const self = this
                
                /* 单图上传 */
                let file  = event.target.files[0]
                
                let name  = file.name
                name = name.split('.')
                const warning = ['php','js','htm','html','xml','json','bat','vb','exe']
                
                if (file.size > 20 * 1024 * 1024)             Tool.Notyf('上传文件不得大于20MB！', 'warning')
                else if (utils.in.array(name.pop(), warning)) Tool.Notyf('请不要尝试提交可执行程序，因为你不会成功！', 'error')
                else {
                    
                    Tool.Notyf('正在上传 ...')
                    
                    let params = new FormData
                    params.append('file', file || '')
                    params.append('id', this.edit.id || 0)
                    params.append('mode', 'banner')
                    
                    const config = {
                        headers: { 'Content-Type': 'multipart/form-data' },
                        onUploadProgress: (speed) => {
                            if (speed.lengthComputable) {
                                let ratio = speed.loaded / speed.total;
                                // 只是上传到后端，后端并未真正保存成功
                                if (ratio < 1) self.speed = ratio
                            }
                        }
                    }
                    
                    axios.post('/admin/handle/upload', params, config).then(res => {
                        if (res.data.code == 200) {
                            self.speed = 1
                            this.edit.img = res.data.data
                            Tool.Notyf('上传成功！', 'success')
                        } else {
                            self.speed = 0
                            Tool.Notyf(res.data.msg, 'error')
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
                    if (!utils.is.empty(edit.opt)) {
                        if (!utils.is.empty(edit.opt.jump)) this.jump = edit.opt.jump
                    }
                },
            }
        }
        
    }).mount('#manage-banner')

}(window.jQuery);