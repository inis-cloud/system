!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                articles: {},           // 全部文章数据
                articles_page: {'all':1,'my':1,'del':1},       // 全部文章页码
                articles_limit: {'all':10,'my':10,'del':10},   // 全部文章数量
                page_list: {},          // 全部页码列表
                is_load: true,          // 数据加载动画
                is_empty: {},           // 数据是否为空
                page_is_load: true,     // 页码加载动画
                is_top_all: [],         // 全部文章置顶开关
                is_show_all: [],        // 全部文章显示开关
                is_top_my: [],          // 我的置顶开关
                is_show_my: [],         // 我的显示开关
                is_page_show: {},       // 是否显示分页
                all_search_value:'',    // 搜索的内容
                my_search_value: '',    // 搜索的内容
                file: {item:0},         // 文件
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
            
            /* 获取文章数据 */
            initData(all_page = this.articles_page.all, my_page = this.articles_page.my, del_page = this.articles_page.del){
                
                this.is_load = true;
                
                let params = new FormData
                params.append('article_limit', this.articles_limit.all || '')
                params.append('my_limit', this.articles_limit.my || '')
                params.append('del_limit', this.articles_limit.del || '')
                params.append('article_page', all_page || '')
                params.append('my_page', my_page || '')
                params.append('del_page', del_page || '')
                params.append('all_search',this.all_search_value || '')
                params.append('my_search', this.my_search_value || '')
                
                axios.post('/index/ManageArticle', params).then((res) => {
                    if(res.data.code == 200){
                        
                        // 文章数据
                        this.articles['all']= res.data.data.article.data
                        this.articles['my'] = res.data.data.my_article.data
                        this.articles['del']= res.data.data.del_article
                        // 更新文章页码
                        this.articles_page['all']  = all_page
                        this.articles_page['my']   = my_page
                        this.articles_page['del']  = del_page
                        // 页码列表
                        this.page_list['all']  = inisHelper.create.paging(this.articles_page.all, this.articles.all.page, 7)
                        this.page_list['my']   = inisHelper.create.paging(this.articles_page.my , this.articles.my.page , 7)
                        this.page_list['del']  = inisHelper.create.paging(this.articles_page.del, this.articles.del.page, 7)
                        // 文章置顶开关 和 文章显示开关
                        this.articles.all.data.forEach((item) => {
                            if(item.is_top  === 1) this.is_top_all.push(item.id)
                            if(item.is_show === 1) this.is_show_all.push(item.id)
                        })
                        // 文章置顶开关 和 文章显示开关
                        this.articles.my.data.forEach((item) => {
                            if(item.is_top  === 1) this.is_top_my.push(item.id)
                            if(item.is_show === 1) this.is_show_my.push(item.id)
                        })
                        // 数据是否为空
                        if(inisHelper.is.empty(this.articles.all.data)) this.is_empty.all = true
                        else this.is_empty.all = false
                        if(inisHelper.is.empty(this.articles.my.data))  this.is_empty.my  = true
                        else this.is_empty.my  = false
                        if(inisHelper.is.empty(this.articles.del.data)) this.is_empty.del = true
                        else this.is_empty.del = false
                        // 是否显示分页
                        if(inisHelper.isEmpty(this.articles.all.data)  || this.articles.all.page == 1) this.is_page_show['all'] = false
                        else this.is_page_show['all'] = true
                        
                        if(inisHelper.is.empty(this.articles.my.data)  || this.articles.my.page  == 1) this.is_page_show['my']  = false
                        else this.is_page_show['my']  = true
                        
                        if(inisHelper.is.empty(this.articles.del.data) || this.articles.del.page == 1) this.is_page_show['del'] = false
                        else this.is_page_show['del'] = true
                        
                        // 数据加载动画
                        this.is_load        = false
                        // 页码加载动画
                        this.page_is_load   = false
                    }
                })
            },
            
            /* 删除文章 */
            btnRemove(id,model = ""){
                
                let params = new FormData()
                params.append('id',id || '')
                params.append('model',model || '')
                
                axios.post('/index/method/DeleteArticle', params).then((res) => {
                    
                    if(res.data.code == 200){
                        
                        this.initData()
                        if(model == true) $.NotificationApp.send("", "真 · 删除成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                        else $.NotificationApp.send("", "删除成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                        
                    }else $.NotificationApp.send("", "删除失败！", "top-right", "rgba(0,0,0,0.2)", "warning");
                    
                })
            },
            
            /* 恢复文章 */
            btnRecover(id){
                
                let params = new FormData()
                params.append('id',id || '')
                
                axios.post('/index/method/RecoverArticle', params).then((res) => {
                    
                    if(res.data.code == 200){
                        
                        this.initData()
                        $.NotificationApp.send("",  "文章恢复成功！" , "top-right", "rgba(0,0,0,0.2)", "success");
                        
                    }else $.NotificationApp.send("", "文章恢复失败！", "top-right", "rgba(0,0,0,0.2)", "warning");
                    
                })
            },
            
            /* 是否置顶 */
            isTop(opt='all',id){
                
                let [arr, status] = ['', 0]
                
                if (opt == 'all') arr = this.is_top_all
                else if (opt == 'my') arr = this.is_top_my
                
                // 状态取反
                if(inisHelper.in.array(id,arr)) status = 0
                else if(!inisHelper.in.array(id,arr)) status = 1
                
                let params = new FormData
                params.append('id',id || '')
                params.append('status',status || '')
                
                axios.post('/index/handle/SetArticleTop', params)
            },
            
            /* 是否显示 */
            isShow(opt='all',id){
                
                let [arr, status] = ['', 0]
                
                if (opt == 'all') arr = this.is_show_all
                else if (opt == 'my') arr = this.is_show_my
                
                // 状态取反
                if(inisHelper.in.array(id,arr)) status = 0
                else if(!inisHelper.in.array(id,arr)) status = 1
                
                let params = new FormData()
                params.append('id',id || '')
                params.append('status',status || '')
                
                axios.post('/index/handle/SetArticleShow', params)
            },
            
            // 触发上传事件
            clickUpload: () => {
                document.querySelector("#input-files").click()
            },
            
            // 多文件
            files(event){
                
                const files = event.target.files
                for (let item of files) this.upload(item)
                
                this.file.length = files.length
            },
            
            // 单个文件上传
            upload(file){
                
                const self  = this
                
                $.NotificationApp.send("", "正在上传 ...", "top-right", "rgba(0,0,0,0.2)", "info");
                
                let params = new FormData
                params.append("file", file || '')
                
                const config = {
                    headers: { "Content-Type": "multipart/form-data" },
                    onUploadProgress: (speed) => {
                        if (speed.lengthComputable) {
                            let ratio = speed.loaded / speed.total;
                        }
                    }
                }
                
                axios.post("/index/handle/importArticle", params, config).then((res) => {
                    
                    if (res.data.code == 200) {
                        
                        this.initData()
                        $.NotificationApp.send("提示！", "<span style='color:var(--blue)'>上传成功！</span>", "top-right", "rgba(0,0,0,0.2)", "info");
                        
                    } else $.NotificationApp.send("错误！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "info");
                    
                    this.file.item++
                    
                    // 全部上传完成，清空input数据
                    if (this.file.item == this.file.length) document.querySelector("#input-files").value = ''
                })
                
            },
            
            /* 人性化时间戳 */
            NatureTime(time){
                return inisHelper.time.nature(time)
            },
        },
        computed: {
            
        }
    }).mount('#manage-article')

}(window.jQuery);