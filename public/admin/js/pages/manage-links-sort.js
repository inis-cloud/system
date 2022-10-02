!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                links_sort: [],     // 全部分类数据
                edit: {},           // 编辑分类数据
                page_list: {},      // 分类页码列表
                page: 1,            // 当前分类页码  
                is_show: [],        // 是否显示数据
                is_load: true,      // 数据加载动画
                page_is_load: true, // 页码加载动画
                is_page_show: true, // 是否显示分页
                title: '新增分组',  // 标题
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
                
                this.title = utils.is.empty(id) ? '新增分组' : '修改分组'
                
                POST('/admin/ManageLinksSort', {
                    id, page, limit: 8
                }).then(res => {
                    if (res.code == 200) {
                        
                        // 更新数据
                        this.links_sort        = res.data
                        
                        this.links_sort.sort.data.forEach(item => {
                            if (item.is_show == 1) this.is_show.push(item.id)
                        })
                        
                        // 更新数据
                        if (utils.is.empty(res.data.edit)) this.edit = {name:'',description:'',slug:''}
                        else this.edit    = res.data.edit
                        
                        // 是否显示分页
                        if (utils.is.empty(this.links_sort.sort.data) || this.links_sort.sort.page == 1) this.is_page_show = false
                        else this.is_page_show = true
                        
                        // 更新页码
                        this.page              = page
                        
                        // 页码列表
                        this.page_list         = utils.create.paging(page, this.links_sort.sort.page, 5)
                        
                        // 数据加载动画
                        this.is_load           = false
                        // 页码加载动画
                        this.page_is_load      = false
                    }
                })
            },
            
            // 保存数据
            btnSave(id = ''){
                
                if (utils.is.empty(this.edit.name)) Tool.Notyf('请填写分组名称！', 'warning')
                else {
                    
                    Tool.Notyf('正在验证 ...')
                    
                    // 数据加载动画
                    this.is_load = true
                    
                    POST('/admin/method/SaveLinksSort', {
                        id, sort_name: this.edit.name, description: this.edit.description
                    }).then(res => {
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
                
                POST('/admin/method/DeleteLinksSort', { id }).then(res => {
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
                
                POST('/admin/handle/SetLinksSortShow', { id, status })
            },
        }
    }).mount('#manage-links-sort')

}(window.jQuery);