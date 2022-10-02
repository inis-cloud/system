!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                links: [],          // 友链数据合集
                links_data: {},     // 全部友链数据
                edit: [],           // 编辑友链数据
                page_list: {},      // 友链页码列表
                page: 1,            // 当前友链页码
                is_show: [],        // 是否显示数组
                is_load: true,      // 数据加载动画
                page_is_load: true, // 页码加载动画
                is_page_show: true, // 是否显示分页
                title: '新增友链',  // 标题
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
                
                if(!utils.is.empty(id)) this.title = '修改友链'
                else this.title = '新增友链'
                
                POST('/admin/ManageLinks', {
                    id, page, limit: 8
                }).then(res => {
                    if (res.code == 200) {
                        
                        // 更新数据
                        this.links             = res.data
                        this.links_data        = res.data.links
                        
                        // 更新数据
                        if (utils.is.empty(res.data.edit)) {
                            this.edit = {name:'',description:'',url:'',head_img:'',sort_id:''}
                            // 格式化分组数据
                            this.links.sort.forEach((item)=>{
                                item.text = item.name
                            })
                        } else {
                            this.edit     = res.data.edit
                            // 格式化分组数据
                            this.links.sort.forEach((item)=>{
                                item.text = item.name
                                if (this.edit.sort_id.id == item.id) item.selected = true
                            })
                            $('#links-sort').empty()
                        }
                        
                        // 设置显示
                        this.links_data.data.forEach((item)=>{
                            if (item.is_show == 1) this.is_show.push(item.id)
                        })
                        
                        // 设置分组
                        $('#links-sort').select2({data:this.links.sort})
                        
                        // 是否显示分页
                        if(utils.is.empty(this.links_data.data) || this.links_data.page == 1) this.is_page_show = false
                        else this.is_page_show = true
                        
                        // 更新页码
                        this.page              = page
                        
                        // 页码列表
                        this.page_list         = utils.create.paging(page, this.links_data.page, 5)
                        
                        // 数据加载动画
                        this.is_load           = false
                        // 页码加载动画
                        this.page_is_load      = false
                    }
                })
            },
            
            // 保存数据
            btnSave(id = ''){
                
                let sort_id = $('#links-sort').select2('data')[0].id
                
                if (utils.is.empty(this.edit.name)) Tool.Notyf('请填写友链名称！', 'warning')
                else {
                    
                    Tool.Notyf('正在验证 ...')
                    
                    // 数据加载动画
                    this.is_load = true
                    
                    POST('/admin/method/SaveLinks', {
                        id, link_name: this.edit.name,
                        description: this.edit.description,
                        url: this.edit.url, sort_id,
                        head_img: this.edit.head_img
                    }).then(res => {
                        if (res.code == 200) Tool.Notyf('数据保存成功！', 'success')
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
                
                POST('/admin/method/DeleteLinks', { id }).then(res => {
                    if (res.code == 200) Tool.Notyf('数据删除成功！', 'success')
                    else Tool.Notyf(res.msg, 'error')
                    // 刷新数据
                    this.initData()
                }).catch(err => console.log(err))
            },
            
            // 是否显示
            isShow(id = ''){
                
                let [array, status] = [this.is_show, 0]
                
                // 状态取反
                status = utils.in.array(id, array) ? 0 : 1
                
                axios.post('/admin/handle/SetLinksShow', { id , status })
            },
            
            // 分类修改器
            modifySort(id = ''){
                
                let result = ''
                
                this.links.sort.forEach(item => {
                    if (id == item.id) result = item.name
                })
                
                return result
            }
        }
    }).mount('#manage-links')

}(window.jQuery);