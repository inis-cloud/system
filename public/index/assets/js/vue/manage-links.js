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
                
                if(!inisHelper.is.empty(id)) this.title = '修改友链'
                else this.title = '新增友链'
                
                let params = new FormData()
                params.append('id',id     || '')
                params.append('page',page || '')
                
                axios.post('/index/ManageLinks', params).then((res) => {
                    if(res.data.code == 200){
                        
                        // 更新数据
                        this.links             = res.data.data
                        this.links_data        = res.data.data.links
                        
                        // 更新数据
                        if(inisHelper.is.empty(res.data.data.edit)) {
                            this.edit = {name:'',description:'',url:'',head_img:'',sort_id:''}
                            // 格式化分组数据
                            this.links.sort.forEach((item)=>{
                                item.text = item.name
                            })
                        } else {
                            this.edit     = res.data.data.edit
                            // 格式化分组数据
                            this.links.sort.forEach((item)=>{
                                item.text = item.name
                                if (this.edit.sort_id.id == item.id) item.selected = true
                            })
                            $("#links-sort").empty()
                        }
                        
                        // 设置显示
                        this.links_data.data.forEach((item)=>{
                            if (item.is_show == 1) this.is_show.push(item.id)
                        })
                        
                        // 设置分组
                        $("#links-sort").select2({data:this.links.sort})
                        
                        // 是否显示分页
                        if(inisHelper.is.empty(this.links_data.data) || this.links_data.page == 1) this.is_page_show = false
                        else this.is_page_show = true
                        
                        // 更新页码
                        this.page              = page
                        
                        // 页码列表
                        this.page_list         = inisHelper.create.paging(page, this.links_data.page, 7)
                        
                        // 数据加载动画
                        this.is_load           = false
                        // 页码加载动画
                        this.page_is_load      = false
                    }
                })
            },
            
            // 保存数据
            btnSave(id = ''){
                
                let sort_id = $("#links-sort").select2("data")[0].id
                
                if(inisHelper.is.empty(this.edit.name)){
                    $.NotificationApp.send("错误！", "请填写友链名称！", "top-right", "rgba(0,0,0,0.2)", "warning");
                }else{
                    
                    $.NotificationApp.send("执行验证！", "正在验证 ... ...", "top-right", "rgba(0,0,0,0.2)", "info");
                    
                    // 数据加载动画
                    this.is_load = true
                    
                    let params = new FormData
                    params.append('id',id || '')
                    params.append('link_name',this.edit.name || '')
                    params.append('description',this.edit.description || '')
                    params.append('url',this.edit.url || '')
                    params.append('head_img',this.edit.head_img || '')
                    params.append('sort_id',sort_id || '')
                    
                    axios.post('/index/method/SaveLinks', params).then((res) => {
                        if(res.data.code == 200){
                            $.NotificationApp.send("验证成功！", "数据保存成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                        } else $.NotificationApp.send("验证错误！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "warning");
                        // 刷新数据
                        this.initData()
                    })
                }
            },
            
            // 删除数据
            btnDelete(id = ''){
                
                // 数据加载动画
                this.is_load = true
                
                let params = new FormData()
                params.append('id',id || '')
                
                axios.post('/index/method/DeleteLinks', params).then((res) => {
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
                
                axios.post('/index/handle/SetLinksShow', params)
            },
            
            // 分类修改器
            modifySort(id = ''){
                
                let result = ''
                
                this.links.sort.forEach((item)=>{
                    if (id == item.id) result = item.name
                })
                
                return result
            }
        }
    }).mount('#manage-links')

}(window.jQuery);