!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                placard: {},        // 公告数据
                edit: {             // 编辑用户
                    opt: {
                        url: '',
                        article_id: '',
                        jump: 'outside'
                    }
                },
                title: '',          // 模态框标题
                page: 1,            // 当前页码
                is_load: true,      // 数据加载动画
                page_list: [],      // 标签页码列表
                page_is_load: true, // 页码加载动画
                is_page_show: true, // 是否显示分页
                search_value: '',   // 搜索的内容
                sort: [],           // 公告分类
                jump: 'outside',    // 跳转方式
            }
        },
        components: {
            
        },
        mounted() {
            this.initData()
        },
        methods: {
            
            // 初始化数据
            initState(){
                
                this.edit = {opt:{jump:'outside',article_id:'',url:''}}
            },
            
            // 获取初始化数据
            initData(id = '', page = this.page, is_load = false){
                
                // 数据加载动画
                this.is_load = is_load
                
                // 判断分页变化 - 清除全选
                if (page != this.page) document.querySelector('#select-all').checked = false
                
                POST('/admin/ManagePlacard', {
                    id, page, search: this.search_value, limit: 8
                }).then(res => {
                    if (res.code == 200) {
                        
                        // 更新数据
                        this.placard  = res.data.placard
                        
                        let type      = []
                        let sort      = res.data.sort
                        this.sort     = sort
                        for (let item in sort) type.push({id:item, text:sort[item]})
                        
                        // 编辑数据
                        if (utils.is.empty(res.data.edit)) this.initState()
                        else {
                            this.edit = res.data.edit
                            // 重置分类数据
                            $('#type-select2').empty()
                            $('#inside-select2').empty()
                            type = []
                            for (let item in sort) {
                                if (this.edit.type == item) type.push({id:item,text:sort[item],selected:true})
                                else type.push({id:item,text:sort[item]})
                            }
                        }
                        
                        // 分类单选框
                        $('#type-select2').select2({
                            minimumResultsForSearch: Infinity,
                            data: type,
                        })
                        
                        let article = res.data.article
                        article.forEach(item=>{
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
                            // minimumResultsForSearch: Infinity,
                            data: article,
                        })
                        
                        let edit = this.edit
                        if (!utils.is.empty(edit.opt)) {
                            if (!utils.is.empty(edit.opt.jump)) this.jump = edit.opt.jump
                        }
                        
                        // 是否显示分页
                        if (utils.is.empty(this.placard.data) || this.placard.page == 1) this.is_page_show = false
                        else this.is_page_show = true
                        
                        // 更新页码
                        this.page              = page
                        
                        // 页码列表
                        this.page_list         = utils.create.paging(page, this.placard.page, 5)
                        
                        // 数据加载动画
                        this.is_load           = false
                        // 页码加载动画
                        this.page_is_load      = false
                    }
                })
            },
            
            // 保存
            save(){
                
                const factor1 = utils.is.empty(this.edit.title)
                const factor2 = utils.is.empty(this.edit.content)
                
                if (factor1) Tool.Notyf('公告标题不能为空！', 'warning')
                else if (factor2) Tool.Notyf('公告内容不能为空！', 'warning')
                else {
                    
                    let params = { opt: {} }
                    
                    delete this.edit.create_time
                    delete this.edit.update_time
                    
                    for (let item in this.edit) params[item] = this.edit[item]

                    // 获取 select2 分类单选框数据
                    params.type   = $('#type-select2').select2('data')[0]['id']
                    
                    // 获取 select2 站内跳转单选框数据
                    const inside = $('#inside-select2').select2('data')[0].id
                    params.opt = {jump:this.jump, article_id:inside, url:this.edit.opt.url}
                    
                    POST('/admin/method/SavePlacard', params).then(res => {
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
                            Tool.Notyf(res.msg, 'success')
                        }
                    })
                }
                
            },
            
            // 全选或全不选
            selectAll(){
                const selectAll = document.querySelector('#select-all')
                const select = document.querySelectorAll('.checkbox-item')
                if (selectAll.checked) for (let item of select) item.checked = true
                else for (let item of select) item.checked = false
            },
            
            // 批量删除
            deletePlacard(id = ''){
                
                const select  = document.querySelectorAll('.checkbox-item')
                let check_arr = []
                
                for (let item of select) {
                    if (item.checked) check_arr.push(item.getAttribute('name'))
                }
                
                let params = {}
                params.id = utils.is.empty(id) ? check_arr.join() : id
                
                POST('/admin/method/deletePlacard', params).then(res => {
                    if (res.code == 200) Tool.Notyf('删除成功！', 'success')
                    else Tool.Notyf(res.msg, 'error')
                    this.initData()
                })
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
        watch: {
            edit: {
                handler(newValue,oldValue){
                    
                    const self = this
                    self.title = utils.is.empty(newValue.id) ? '添加公告' : '修改公告'
                },
                immediate: true,
                deep: true,
            }
        },
    }).mount('#manage-placard')

}(window.jQuery)