(()=>{
    const app = Vue.createApp({
        data() {
            return {
                items: {},          // 音乐数据
                edit: {},           // 编辑用户
                title: '',          // 模态框标题
                speed: 0,           // 上传头像进度
                page: 1,            // 当前页码
                is_load: true,      // 数据加载动画
                page_list: [],      // 标签页码列表
                page_is_load: true, // 页码加载动画
                is_page_show: true, // 是否显示分页
                search_value: '',   // 搜索的内容
                is_show: [],        // 是否启用
                music_is_show:true, // 打开编辑后的是否显示
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
                
                const params = inisHelper.stringfy({
                    id, page, search: this.search_value, limit: 8
                })
                
                // 数据加载动画
                this.is_load = is_load
                
                // 判断分页变化 - 清除全选
                if (page != this.page) document.querySelector("#select-all").checked = false
                
                axios.post('/admin/ManageSearch', params).then((res) => {
                    if (res.data.code == 200) {
                        
                        // 更新数据
                        this.items = res.data.data.items
                        
                        // 编辑数据
                        if (!inisHelper.is.empty(res.data.data.edit)) {
                            this.edit = res.data.data.edit
                            if (this.edit.is_show == 1) this.music_is_show = true
                            else this.music_is_show = false
                        }
                        
                        // 是否显示分页
                        if (inisHelper.is.empty(this.items.data) || this.items.page == 1) this.is_page_show = false
                        else this.is_page_show = true
                        
                        // 更新页码
                        this.page              = page
                        
                        // 页码列表
                        this.page_list         = inisHelper.create.paging(page, this.items.page, 5)
                        
                        // 数据加载动画
                        this.is_load           = false
                        // 页码加载动画
                        this.page_is_load      = false
                    }
                })
            },
            
            // 保存
            save(){
                
                const factor1 = inisHelper.is.empty(this.edit.name)
                
                if (factor1) {
                    $.NotificationApp.send(null, "搜索内容不能为空！", "top-right", "rgba(0,0,0,0.2)", "warning");
                } else {
                    
                    axios.post('/admin/method/SaveSearch', inisHelper.stringfy({
                        ...this.edit, named: this.edit.name
                    })).then((res) => {
                        if (res.data.code == 200) {
                            // 刷新数据
                            this.initData()
                            // 关闭 model 窗口
                            $('#fill-edit-modal').modal('toggle')
                            $.NotificationApp.send(null, "保存成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                        } else if (res.data.code == 201){
                            // 刷新数据
                            this.initData()
                            // 关闭 model 窗口
                            $('#fill-edit-modal').modal('toggle')
                            $.NotificationApp.send(null, res.data.msg, "top-right", "rgba(0,0,0,0.2)", "error");
                        }
                    })
                }
                
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
            
            // 批量删除
            deleteMusic(id = ''){
                
                const select  = document.querySelectorAll(".checkbox-item")
                let check_arr = [];
                
                for (let item of select) {
                    if (item.checked) check_arr.push(item.getAttribute("name"))
                }
                
                let params = new FormData
                
                if (inisHelper.is.empty(id)) params.append("id", check_arr.join() || '')
                else params.append("id", id || '')
                
                axios.post('/admin/method/deleteSearch', params).then(res=>{
                    if (res.data.code == 200) {
                        $.NotificationApp.send(null, "删除成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                    } else {
                        $.NotificationApp.send(null, res.data.msg, "top-right", "rgba(0,0,0,0.2)", "error");
                    }
                    this.initData()
                })
            },
            
            // 时间戳转人性化时间
            natureTime: (time = '') => {
                
                let result = ''
                
                if (!inisHelper.is.empty(time)) {
                    result = inisHelper.date.to.time(time)
                    result = inisHelper.time.nature(result)
                }
                
                return result
            },
        },
        computed: {
            
        },
        watch: {
            edit: {
                handler(newValue,oldValue){
                    
                    const self = this
                    
                    if (inisHelper.is.empty(newValue.id)) self.title = '添加搜索'
                    else self.title = '修改搜索'
                },
                immediate: true,
                deep: true,
            }
        },
    }).mount('#manage-search')
})()