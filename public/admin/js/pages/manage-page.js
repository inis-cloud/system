!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                pages: {},          // 数据
                page: 1,            // 当前页码
                is_load: true,      // 数据加载动画
                page_list: [],      // 标签页码列表
                page_is_load: true, // 页码加载动画
                is_page_show: true, // 是否显示分页
                search_value: '',   // 搜索的内容
                is_show: [],        // 是否显示
                file: {item:0},     // 文件
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
                
                // 判断分页变化 - 清除全选
                if (page != this.page) document.querySelector('#select-all').checked = false
                
                POST('/admin/ManagePage', {
                    id, page, limit: 8,
                    search: this.search_value
                }).then(res => {
                    if (res.code == 200) {
                        
                        // 更新数据
                        this.pages = res.data.page.data
                        
                        // 是否显示
                        this.is_show = []
                        this.pages.data.forEach((item) => {
                            if(item.is_show === 1) this.is_show.push(item.id)
                            // 去重
                            this.is_show = utils.array.unique(this.is_show)
                        })
                        
                        // 是否显示分页
                        if(utils.is.empty(this.pages.data) || this.pages.page == 1) this.is_page_show = false
                        else this.is_page_show = true
                        
                        // 更新页码
                        this.page              = page
                        
                        // 页码列表
                        this.page_list         = utils.create.paging(page, this.pages.page, 5)
                        
                        // 数据加载动画
                        this.is_load           = false
                        // 页码加载动画
                        this.page_is_load      = false
                    }
                })
            },
            
            // 全选或全不选
            selectAll(){
                const selectAll = document.querySelector('#select-all')
                const select = document.querySelectorAll('.checkbox-item')
                if (selectAll.checked) for (let item of select) item.checked = true
                else for (let item of select) item.checked = false
            },
            
            /* 启用状态 */
            isShow(id){
                
                let [arr, status] = [this.is_show, 0]
                
                // 状态取反
                if (utils.in.array(id,arr)) status = 0
                else if (!utils.in.array(id,arr)) status = 1
                
                axios.post('/admin/handle/SetPageShow', { id, status })
            },
            
            // 批量删除
            deletePage(id = ''){
                
                const select  = document.querySelectorAll('.checkbox-item')
                let check_arr = [];
                
                for (let item of select) {
                    if (item.checked) check_arr.push(item.getAttribute('name'))
                }
                
                let params = new FormData
                
                if (utils.is.empty(id)) params.append('id', check_arr.join() || '')
                else params.append('id', id || '')
                
                POST('/admin/method/deletePage', params).then(res => {
                    if (res.code == 200) Tool.Notyf('删除成功！', 'success')
                    else Tool.Notyf('删除失败！', 'error')
                    this.initData()
                })
            },
            
            // 触发上传事件
            clickUpload: () => document.querySelector('#input-files').click(),
            
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
                
                axios.post('/admin/handle/importPage', params, config).then(res => {
                    
                    if (res.data.code == 200) {
                        
                        this.initData()
                        Tool.Notyf('上传成功！', 'success')
                        
                    } else Tool.Notyf(res.data.msg, 'error')
                    
                    this.file.item++
                    
                    // 全部上传完成，清空input数据
                    if (this.file.item == this.file.length) document.querySelector('#input-files').value = ''
                })
                
            },
            
            // 时间戳转人性化时间
            natureTime: date => utils.time.nature(utils.date.to.time(date))
        },
        computed: {
            
        },
        watch: {
            
        },
    }).mount('#manage-page')

}(window.jQuery)