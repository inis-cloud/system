!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                articles: {all:{}, my:{}, del:{}},          // 文章数据
                articles_page: {'all':1,'my':1,'del':1},    // 当前页面
                articles_limit: {'all':8,'my':8,'del':8},   // 文章分页
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
            
        },
        mounted() {
            this.initData()
        },
        methods: {
            
            /* 获取文章数据 */
            initData(all_page = this.articles_page.all, my_page = this.articles_page.my, del_page = this.articles_page.del){
                
                this.is_load = true
                
                POST('/admin/ManageArticle', {
                    article_limit: this.articles_limit.all || '',
                    my_limit     : this.articles_limit.my  || '',
                    del_limit    : this.articles_limit.del || '',
                    article_page : all_page || '',
                    my_page      : my_page  || '',
                    del_page     : del_page || '',
                    all_search   : this.all_search_value || '',
                    my_search    : this.my_search_value  || '',
                }).then(res => {
                    if(res.code == 200){
                        
                        // 文章数据
                        this.articles['all']= res.data.article.data
                        this.articles['my'] = res.data.my_article.data
                        this.articles['del']= res.data.del_article
                        // 更新文章页码
                        this.articles_page['all']  = all_page
                        this.articles_page['my']   = my_page
                        this.articles_page['del']  = del_page
                        // 页码列表
                        this.page_list['all']  = utils.create.paging(this.articles_page.all, this.articles.all.page, 5)
                        this.page_list['my']   = utils.create.paging(this.articles_page.my , this.articles.my.page , 5)
                        this.page_list['del']  = utils.create.paging(this.articles_page.del, this.articles.del.page, 5)
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
                        if(utils.is.empty(this.articles.all.data)) this.is_empty.all = true
                        else this.is_empty.all = false
                        if(utils.is.empty(this.articles.my.data))  this.is_empty.my  = true
                        else this.is_empty.my  = false
                        if(utils.is.empty(this.articles.del.data)) this.is_empty.del = true
                        else this.is_empty.del = false
                        // 是否显示分页
                        if(utils.isEmpty(this.articles.all.data)  || this.articles.all.page == 1) this.is_page_show['all'] = false
                        else this.is_page_show['all'] = true
                        
                        if(utils.is.empty(this.articles.my.data)  || this.articles.my.page  == 1) this.is_page_show['my']  = false
                        else this.is_page_show['my']  = true
                        
                        if(utils.is.empty(this.articles.del.data) || this.articles.del.page == 1) this.is_page_show['del'] = false
                        else this.is_page_show['del'] = true
                        
                        // 数据加载动画
                        this.is_load        = false
                        // 页码加载动画
                        this.page_is_load   = false
                    }
                })
            },
            
            /* 删除文章 */
            btnRemove(id, model = ''){
                
                POST('/admin/method/DeleteArticle', { id, model }).then(res => {
                    
                    if(res.code == 200){
                        
                        this.initData()
                        if (model == true) Tool.Notyf('真 · 删除成功！', 'success')
                        else Tool.Notyf('删除成功！', 'success')
                        
                    } else Tool.Notyf('删除失败！', 'error')
                    
                })
            },
            
            /* 恢复文章 */
            btnRecover(id){
                
                POST('/admin/method/RecoverArticle', { id }).then(res => {
                    
                    if(res.code == 200){
                        
                        this.initData()
                        Tool.Notyf('恢复成功！', 'success')
                        
                    } else Tool.Notyf('恢复失败！', 'error')
                    
                })
            },
            
            /* 是否置顶 */
            isTop(opt = 'all', id){
                
                let [array, status] = ['', 0]
                
                opt = opt == 'all' ? this.is_top_all : this.is_top_my
                
                // 状态取反
                status = utils.in.array(id, array) ? 0 : 1
                
                POST('/admin/handle/SetArticleTop', { id, status })
            },
            
            /* 是否显示 */
            isShow(opt = 'all', id){
                
                let [array, status] = ['', 0]
                
                array = opt == 'all' ? this.is_show_all : this.is_show_my
                
                // 状态取反
                status = utils.in.array(id, array) ? 0 : 1
                
                POST('/admin/handle/SetArticleShow', { id, status })
            },
            
            // 触发上传事件
            clickUpload: () => {
                document.querySelector('#input-files').click()
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
                
                Tool.Notyf('正在上传 ...')
                
                let params = new FormData
                params.append('file', file || '')
                
                const config = {
                    headers: { 'Content-Type': 'multipart/form-data' },
                    onUploadProgress: (speed) => {
                        if (speed.lengthComputable) {
                            let ratio = speed.loaded / speed.total;
                        }
                    }
                }
                
                axios.post('/admin/handle/importArticle', params, config).then(res => {
                    
                    if (res.data.code == 200) {
                        
                        this.initData()
                        Tool.Notyf('上传成功！', 'success')
                        
                    } else Tool.Notyf(res.data.msg, 'error')
                    
                    this.file.item++
                    
                    // 全部上传完成，清空input数据
                    if (this.file.item == this.file.length) document.querySelector('#input-files').value = ''
                })
                
            },
            
            /* 人性化时间戳 */
            NatureTime: time => utils.time.nature(time)
        },
        computed: {
            
        }
    }).mount('#manage-article')

}(window.jQuery);