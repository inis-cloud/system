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
            'i-footer'    : inisTemp.footer(),
            'i-top-nav'   : inisTemp.navbar(),
            'i-left-side' : inisTemp.sidebar(),
            'i-right-side': inisTemp.sidebar('right'),
        },
        mounted() {
            
            this.initData()
            
            // window.onscroll = () => {
                
            //     // 滚动条滚动时，距离顶部的距离
            //     let scrollTop    = document.documentElement.scrollTop || document.body.scrollTop;
            //     // 可视区的高度
            //     let windowHeight = document.documentElement.clientHeight || document.body.clientHeight;
            //     // 滚动条的总高度
            //     let scrollHeight = document.documentElement.scrollHeight || document.body.scrollHeight;
        
            //     // 滚动条到底部的条件
            //     if ((scrollTop + windowHeight) == scrollHeight) this.lazyData()
            // }
        },
        methods: {
            
            // 获取初始化数据
            initData(id = '', page = this.page, is_load = false){
                
                // 数据加载动画
                this.is_load = is_load
                
                let params = new FormData
                params.append('id',  id   || '')
                params.append('page',page || '')
                params.append('search',this.search_value || '')
                
                axios.post('/index/ManageComments', params).then((res) => {
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
                        this.page_list         = inisHelper.create.paging(page, this.comments.page, 7)
                        
                        // 数据加载动画
                        this.is_load           = false
                        // 页码加载动画
                        this.page_is_load      = false
                    }
                })
            },
            
            // 保存评论
            save(){
                
                if (inisHelper.is.empty(this.edit.nickname)) $.NotificationApp.send("提示！", "昵称不能为空！", "top-right", "rgba(0,0,0,0.2)", "info");
                else if (inisHelper.is.empty(this.edit.content)) $.NotificationApp.send("提示！", "评论内容不能为空！", "top-right", "rgba(0,0,0,0.2)", "info");
                else {
                    let params = new FormData
                    const allow = ['id','nickname','email','url','content']
                    for (let item in this.edit) if (inisHelper.in.array(item,allow)) params.append(item, this.edit[item] || '')
                    axios.post('/index/method/SaveComments', params).then(res=>{
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
                
                axios.post('/index/method/DeleteComments', params).then(res=>{
                    if (res.data.code == 200) {
                        document.querySelector("#select-all").checked = false
                        $.NotificationApp.send("提示！", "删除成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                    } else {
                        $.NotificationApp.send("提示！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "info");
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