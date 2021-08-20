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
            initData(id = '', page = this.page, is_load = false){
                
                // 数据加载动画
                this.is_load = is_load
                
                if(!(new helper).isEmpty(id)) this.title = '修改分组'
                else this.title = '新增分组'
                
                let params = new FormData()
                params.append('id',id || '')
                params.append('page',page || '')
                
                axios.post('/index/ManageLinksSort', params).then((res) => {
                    if(res.data.code == 200){
                        
                        // 更新数据
                        this.links_sort        = res.data.data
                        
                        this.links_sort.sort.data.forEach((item)=>{
                            if (item.is_show == 1) this.is_show.push(item.id)
                        })
                        
                        // 更新数据
                        if(inisHelper.is.empty(res.data.data.edit)) this.edit = {name:'',description:'',slug:''}
                        else this.edit    = res.data.data.edit
                        
                        // 是否显示分页
                        if(inisHelper.is.empty(this.links_sort.sort.data) || this.links_sort.sort.page == 1) this.is_page_show = false
                        else this.is_page_show = true
                        
                        // 更新页码
                        this.page              = page
                        
                        // 页码列表
                        this.page_list         = inisHelper.create.paging(page, this.links_sort.sort.page, 7)
                        
                        // 数据加载动画
                        this.is_load           = false
                        // 页码加载动画
                        this.page_is_load      = false
                    }
                })
            },
            
            // 保存数据
            btnSave(id){
                
                id = id || ''
                
                if(inisHelper.is.empty(this.edit.name)){
                    $.NotificationApp.send("错误！", "请填写分组名称！", "top-right", "rgba(0,0,0,0.2)", "warning");
                }else{
                    
                    $.NotificationApp.send("执行验证！", "正在验证 ... ...", "top-right", "rgba(0,0,0,0.2)", "info");
                    
                    // 数据加载动画
                    this.is_load = true
                    
                    let params = new FormData()
                    params.append('id',id || '')
                    params.append('sort_name',this.edit.name || '')
                    params.append('description',this.edit.description || '')
                    
                    axios.post('/index/method/SaveLinksSort', params).then((res) => {
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
                
                id = id || ''
                
                // 数据加载动画
                this.is_load = true
                
                let params = new FormData()
                params.append('id',id || '')
                
                axios.post('/index/method/DeleteLinksSort', params).then((res) => {
                    if(res.data.code == 200){
                        $.NotificationApp.send("", "删除成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                    }else{
                        $.NotificationApp.send("删除失败！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "warning");
                    }
                    // 刷新数据
                    this.initData()
                }).catch((err) => {
                    console.log(err)
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
                
                axios.post('/index/handle/SetLinksSortShow', params)
            },
        }
    }).mount('#manage-links-sort')

}(window.jQuery);