// !function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                comments: [],       // 全部评论数据
                edit: [],           // 编辑评论数据
                page_list: [],      // 评论页码列表
                page: 1,            // 当前评论页码  
                is_load: true,      // 数据加载动画
                page_is_load: true, // 页码加载动画
                is_page_show: true, // 是否显示分页
                is_show: [],        // 是否显示
                search_value: '',   // 搜索内容
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
                
                const params = inisHelper.stringfy({
                    id, page, search: this.search_value, limit: 8
                })
                
                axios.post('/admin/ManageComments', params).then((res) => {
                    if(res.data.code == 200){
                        
                        // 更新数据
                        this.edit              = (!inisHelper.is.empty(res.data.data.edit)) ? res.data.data.edit : {'nickname':'','email':'','url':'','content':''}
                        this.comments          = res.data.data.comments
                        
                        // 是否显示分页
                        if(inisHelper.is.empty(this.comments.data) || this.comments.page == 1) this.is_page_show = false
                        else this.is_page_show = true
                        
                        // 更新页码
                        this.page              = page
                        
                        // 页码列表
                        this.page_list         = inisHelper.create.paging(page, this.comments.page, 5)
                        
                        // 数据加载动画
                        this.is_load           = false
                        // 页码加载动画
                        this.page_is_load      = false
                    }
                })
            },
            
            // 保存评论
            save(){
                
                if (inisHelper.is.empty(this.edit.nickname)) $.NotificationApp.send(null, "昵称不能为空！", "top-right", "rgba(0,0,0,0.2)", "warning");
                else if (inisHelper.is.empty(this.edit.content)) $.NotificationApp.send(null, "评论内容不能为空！", "top-right", "rgba(0,0,0,0.2)", "warning");
                else {
                    let params = new FormData
                    const allow = ['id','nickname','email','url','content']
                    for (let item in this.edit) if (inisHelper.in.array(item,allow)) params.append(item, this.edit[item] || '')
                    axios.post('/admin/method/SaveComments', params).then(res=>{
                        if (res.data.code == 200) {
                            // 更新数据
                            this.initData()
                            // 关闭 model 窗口
                            $('#fill-edit-modal').modal('toggle')
                        }
                    })
                }
            },
            
            deleteComments(id = ''){
                const select  = document.querySelectorAll(".checkbox-item")
                let check_arr = [];
                
                for (let item of select) {
                    if (item.checked) check_arr.push(item.getAttribute("name"))
                }
                
                let params = new FormData
                
                if (inisHelper.is.empty(id)) params.append("id", check_arr.join() || '')
                else params.append("id", id || '')
                
                axios.post('/admin/method/DeleteComments', params).then(res=>{
                    if (res.data.code == 200) {
                        document.querySelector("#select-all").checked = false
                        $.NotificationApp.send(null, "删除成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                    } else {
                        $.NotificationApp.send(null, res.data.msg, "top-right", "rgba(0,0,0,0.2)", "error");
                    }
                    this.initData()
                })
            },
            
            // 全选或全不选
            selectAll(){
                const selectAll = document.querySelector("#select-all")
                const select = document.querySelectorAll(".checkbox-item")
                if (selectAll.checked) {
                    for (let item of select) {
                        item.checked = true
                    }
                } else {
                    for (let item of select) {
                        item.checked = false
                    }
                }
            },
            
            // 时间戳转人性化时间
            natureTime(date){
                let time = inisHelper.date.to.time(date)
                return inisHelper.time.nature(time)
            },
        }
    }).mount('#manage-comments')

// }(window.jQuery);