!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                pages: {},           // 用户数据
                page: 1,            // 当前页码
                is_load: true,      // 数据加载动画
                page_list: [],      // 标签页码列表
                page_is_load: true, // 页码加载动画
                is_page_show: true, // 是否显示分页
                search_value: '',   // 搜索的内容
                is_show: [],        // 是否显示
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
                
                let params = new FormData
                params.append('id',id || '')
                params.append('page',page || '')
                params.append('search',this.search_value || '')
                
                // 数据加载动画
                this.is_load = is_load
                
                // 判断分页变化 - 清除全选
                if (page != this.page) document.querySelector("#select-all").checked = false
                
                axios.post('/index/ManagePage', params).then((res) => {
                    if (res.data.code == 200) {
                        
                        // 更新数据
                        this.pages = res.data.data.page.data
                        
                        // 是否显示
                        this.is_show = []
                        this.pages.data.forEach((item) => {
                            if(item.is_show === 1) this.is_show.push(item.id)
                            // 去重
                            this.is_show = inisHelper.array.unique(this.is_show)
                        })
                        
                        // 是否显示分页
                        if(inisHelper.is.empty(this.pages.data) || this.pages.page == 1) this.is_page_show = false
                        else this.is_page_show = true
                        
                        // 更新页码
                        this.page              = page
                        
                        // 页码列表
                        this.page_list         = inisHelper.create.paging(page, this.pages.page, 7)
                        
                        // 数据加载动画
                        this.is_load           = false
                        // 页码加载动画
                        this.page_is_load      = false
                    }
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
            
            /* 启用状态 */
            isShow(id){
                
                let [arr, status] = [this.is_show, 0]
                
                // 状态取反
                if (inisHelper.in.array(id,arr)) status = 0
                else if (!inisHelper.in.array(id,arr)) status = 1
                
                let params = new FormData
                
                params.append('id',id || '')
                params.append('status',status || '')
                
                axios.post('/index/handle/SetPageShow', params)
            },
            
            // 批量删除
            deletePage(id = ''){
                
                const select  = document.querySelectorAll(".checkbox-item")
                let check_arr = [];
                
                for (let item of select) {
                    if (item.checked) check_arr.push(item.getAttribute("name"))
                }
                
                let params = new FormData
                
                if (inisHelper.is.empty(id)) params.append("id", check_arr.join() || '')
                else params.append("id", id || '')
                
                axios.post('/index/method/deletePage', params).then(res=>{
                    if (res.data.code == 200) {
                        $.NotificationApp.send("提示！", "删除成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                    } else {
                        $.NotificationApp.send("提示！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "info");
                    }
                    this.initData()
                })
            },
            
            // 时间戳转人性化时间
            natureTime: (date = '') => {
                let time = inisHelper.date.to.time(date)
                return inisHelper.time.nature(time)
            },
        },
        computed: {
            
        },
        watch: {
            
        },
    }).mount('#manage-page')

}(window.jQuery)