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
                
                const params = inisHelper.stringfy({
                    id, page, search: this.search_value, limit: 8
                })
                
                // 数据加载动画
                this.is_load = is_load
                
                // 判断分页变化 - 清除全选
                if (page != this.page) document.querySelector("#select-all").checked = false
                
                axios.post('/admin/ManagePlacard', params).then((res) => {
                    if (res.data.code == 200) {
                        
                        // 更新数据
                        this.placard  = res.data.data.placard
                        
                        let type      = []
                        let sort      = res.data.data.sort
                        this.sort     = sort
                        for (let item in sort) {
                            type.push({'id':item,text:sort[item]})
                        }
                        
                        // 编辑数据
                        if (inisHelper.is.empty(res.data.data.edit)) this.initState()
                        else {
                            this.edit = res.data.data.edit
                            // 重置分类数据
                            $("#type-select2").empty()
                            $("#inside-select2").empty()
                            type = []
                            for (let item in sort) {
                                if (this.edit.type == item) type.push({'id':item,text:sort[item],selected:true})
                                else type.push({'id':item,text:sort[item]})
                            }
                        }
                        
                        // 分类单选框
                        $("#type-select2").select2({
                            minimumResultsForSearch: Infinity,
                            data: type,
                        })
                        
                        let article = res.data.data.article
                        article.forEach(item=>{
                            item.text = item.title
                            delete item.title
                            if (!inisHelper.is.empty(res.data.data.edit)) {
                                if (!inisHelper.is.empty(this.edit.opt)) {
                                    if (!inisHelper.is.empty(this.edit.opt.jump)) {
                                        if (item.id == this.edit.opt.article_id) item.selected = true
                                    }
                                } 
                            }
                        })
                        
                        // 站内跳转单选框
                        $("#inside-select2").select2({
                            // minimumResultsForSearch: Infinity,
                            data: article,
                        })
                        
                        let edit = this.edit
                        if (!inisHelper.is.empty(edit.opt)) {
                            if (!inisHelper.is.empty(edit.opt.jump)) this.jump = edit.opt.jump
                        }
                        
                        // 是否显示分页
                        if(inisHelper.is.empty(this.placard.data) || this.placard.page == 1) this.is_page_show = false
                        else this.is_page_show = true
                        
                        // 更新页码
                        this.page              = page
                        
                        // 页码列表
                        this.page_list         = inisHelper.create.paging(page, this.placard.page, 5)
                        
                        // 数据加载动画
                        this.is_load           = false
                        // 页码加载动画
                        this.page_is_load      = false
                    }
                })
            },
            
            // 保存
            save(){
                
                const factor1 = inisHelper.is.empty(this.edit.title)
                const factor2 = inisHelper.is.empty(this.edit.content)
                
                if (factor1) {
                    $.NotificationApp.send(null, "公告标题不能为空！", "top-right", "rgba(0,0,0,0.2)", "warning");
                } else if (factor2) {
                    $.NotificationApp.send(null, "公告内容不能为空！", "top-right", "rgba(0,0,0,0.2)", "warning");
                } else {
                    
                    let params = new FormData
                    
                    delete this.edit.create_time
                    delete this.edit.update_time
                    
                    for (let item in this.edit) {
                        params.append(item, this.edit[item] || '')
                    }
                    // 获取 select2 分类单选框数据
                    const type    = $('#type-select2').select2('data')[0]['id'];
                    params.append("type", type || '')
                    
                    // 获取 select2 站内跳转单选框数据
                    const inside = $('#inside-select2').select2('data')[0].id;
                    params.append("opt[jump]", this.jump || '')
                    params.append("opt[article_id]", inside || '')
                    params.append("opt[url]", this.edit.opt.url || '')
                    
                    axios.post('/admin/method/SavePlacard', params).then((res) => {
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
                            $.NotificationApp.send(null, res.data.msg, "top-right", "rgba(0,0,0,0.2)", "success");
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
            deletePlacard(id = ''){
                
                const select  = document.querySelectorAll(".checkbox-item")
                let check_arr = [];
                
                for (let item of select) {
                    if (item.checked) check_arr.push(item.getAttribute("name"))
                }
                
                let params = new FormData
                
                if (inisHelper.is.empty(id)) params.append("id", check_arr.join() || '')
                else params.append("id", id || '')
                
                axios.post('/admin/method/deletePlacard', params).then(res=>{
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
                    
                    if (inisHelper.is.empty(newValue.id)) self.title = '添加公告'
                    else self.title = '修改公告'
                    
                },
                immediate: true,
                deep: true,
            }
        },
    }).mount('#manage-placard')

}(window.jQuery)