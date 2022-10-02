!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                article_sort: {sort:{}},   // 全部分类数据
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
            
        },
        mounted() {
            this.initData()
        },
        methods: {
            
            // 获取初始化数据
            initData(id = null, page = this.page, is_load = false){
                
                // 数据加载动画
                this.is_load = is_load
                
                POST('/admin/ManageArticleSort', {
                    id, page, limit: 8
                }).then(res => {
                    if (res.code == 200) {
                        
                        // 更新数据
                        this.article_sort      = res.data
                        
                        // 设置显示数据
                        this.article_sort.sort.data.forEach(item => {
                            if (item.is_show == 1) this.is_show.push(item.id)
                        })
                        
                        // 更新数据
                        if (utils.is.empty(res.data.edit_sort)) this.edit_sort = {name:'',description:'',head_img:''}
                        else {
                            this.edit_sort    = res.data.edit_sort
                            if (!utils.is.empty(this.edit_sort.opt)) {
                                if (!utils.is.empty(this.edit_sort.opt.head_img)) this.edit_sort.head_img = this.edit_sort.opt.head_img
                            }
                        }
                        
                        // 是否显示分页
                        if (utils.is.empty(this.article_sort.sort.data) || this.article_sort.sort.page == 1) this.is_page_show = false
                        else this.is_page_show = true
                        
                        // 更新页码
                        this.page              = page
                        
                        // 页码列表
                        this.page_list         = utils.create.paging(page, this.article_sort.sort.page, 5)
                        
                        // 数据加载动画
                        this.is_load           = false
                        // 页码加载动画
                        this.page_is_load      = false
                    }
                })
            },
            
            // 保存数据
            btnSave(id = ''){
                
                if (utils.is.empty(this.edit_sort.name)) Tool.Notyf('请填写分类名称！', 'warning')
                else {
                    
                    Tool.Notyf('正在验证 ...')
                    
                    // 数据加载动画
                    this.is_load = true
                    
                    POST('/admin/method/SaveArticleSort', {
                        id, sort_name: this.edit_sort.name,
                        description: this.edit_sort.description,
                        opt: {head_img: this.edit_sort.head_img}
                    }).then(res => {
                        if (res.code == 200) Tool.Notyf('保存成功！', 'success')
                        else Tool.Notyf(res.msg, 'error')
                        // 刷新数据
                        this.initData()
                    })
                }
            },
            
            // 删除数据
            btnDelete(id = null){
                
                // 数据加载动画
                this.is_load = true
                
                POST('/admin/method/DeleteArticleSort', { id }).then(res => {
                    if (res.code == 200) Tool.Notyf('删除成功！', 'success')
                    else Tool.Notyf(res.msg, 'error')
                    // 刷新数据
                    this.initData()
                })
            },
            
            // 是否显示
            isShow(id = ''){
                
                let [array, status] = [this.is_show, 0]
                
                // 状态取反
                status = utils.in.array(id, array) ? 0 : 1
                
                POST('/admin/handle/SetArticleSortShow', { id, status })
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
                
                if (file.size > 5 * 1024 * 1024)              Tool.Notyf('上传文件不得大于5MB！', 'warning')
                else if (utils.in.array(name.pop(), warning)) Tool.Notyf('请不要尝试提交可执行程序，因为你不会成功！', 'error')
                else {
                    
                    Tool.Notyf('正在上传 ...')
                    
                    let params = new FormData
                    params.append('file', file || '')
                    params.append('mode', 'file')
                    
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
                            this.edit_sort.head_img = res.data.data
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
        computed: {
            article_sort:{
                get:function(){
                    return this.article_sort
                },
                set:function(value){
                    value.sort.data.forEach(item=>{
                        if (utils.is.empty(item.opt)) item.opt = {'head_img':''}
                    })
                }
            }
        },
    }).mount('#manage-article-sort')

}(window.jQuery);