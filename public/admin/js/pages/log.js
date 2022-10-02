(()=>{
    const app = Vue.createApp({
        data() {
            return {
                items: {},          // 数据
                edit: {},           // 编辑
                title: '',          // 模态框标题
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
            initData(page = this.page, is_load = false){
                
                // 数据加载动画
                this.is_load = is_load
                
                // 判断分页变化 - 清除全选
                if (page != this.page) document.querySelector('#select-all').checked = false
                
                POST('/admin/Log', {
                    page, search: this.search_value, limit: 8
                }).then(res => {
                    if (res.code == 200) {
                        
                        // 更新数据
                        this.items = res.data.items
                        
                        // 是否显示分页
                        if (utils.is.empty(this.items.data) || this.items.page == 1) this.is_page_show = false
                        else this.is_page_show = true
                        
                        // 更新页码
                        this.page              = page
                        
                        // 页码列表
                        this.page_list         = utils.create.paging(page, this.items.page, 5)
                        
                        // 数据加载动画
                        this.is_load           = false
                        // 页码加载动画
                        this.page_is_load      = false
                    }
                })
            },
            
            // 保存
            save(){
                
                const factor1 = utils.is.empty(this.edit.name)
                
                if (factor1) Tool.Notyf('搜索内容不能为空！', 'warning')
                else {
                    
                    POST('/admin/method/SaveSearch', {
                        ...this.edit, named: this.edit.name
                    }).then(res => {
                        if (res.code == 200) {
                            // 刷新数据
                            this.initData()
                            // 关闭 model 窗口
                            $('#fill-edit-modal').modal('toggle')
                            Tool.Notyf('保存成功！', 'success')
                        } else if (res.code == 201){
                            // 刷新数据
                            this.initData()
                            // 关闭 model 窗口
                            $('#fill-edit-modal').modal('toggle')
                            Tool.Notyf(res.msg, 'error')
                        }
                    })
                }
                
            },

            runSearch(value = ''){
                this.search_value = value
                this.initData()
            },
            
            // 全选或全不选
            selectAll(){
                const selectAll = document.querySelector('#select-all')
                const select    = document.querySelectorAll('.checkbox-item')
                if (selectAll.checked) for (let item of select) item.checked = true
                else for (let item of select) item.checked = false
            },
            
            // 批量删除
            deleteMusic(id = ''){
                
                const select  = document.querySelectorAll('.checkbox-item')
                let check_arr = [];
                
                for (let item of select) {
                    if (item.checked) check_arr.push(item.getAttribute('name'))
                }
                
                let params = {}
                params.id = utils.is.empty(id) ? check_arr.join() : id
                
                POST('/admin/method/deleteSearch', params).then(res => {
                    if (res.code == 200) Tool.Notyf('删除成功！', 'success')
                    else Tool.Notyf(res.msg, 'error')
                    this.initData()
                })
            },

            // 过滤字符串中?后面的数据
            filterQuery(url = ''){
                if (url.indexOf('?') != -1) {
                    url = url.split('?')[0]
                }
                return url
            },

            // 时间戳转人性化时间
            natureTime: (time = '') => {
                
                let result = ''
                
                if (!utils.is.empty(time)) {
                    result = utils.time.nature(utils.date.to.time(time))
                }
                
                return result
            },
        },
        computed: {
            
        },
    }).mount('#log')
})()