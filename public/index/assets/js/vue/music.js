!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                music: {},          // 音乐数据
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
                
                axios.post('/index/Music', params).then((res) => {
                    if (res.data.code == 200) {
                        
                        // 更新数据
                        this.music = res.data.data.music
                        
                        // 编辑数据
                        if (!inisHelper.is.empty(res.data.data.edit)) {
                            this.edit = res.data.data.edit
                            if (this.edit.is_show == 1) this.music_is_show = true
                            else this.music_is_show = false
                        }
                        
                        // 显示状态
                        this.is_show = []
                        this.music.data.forEach((item) => {
                            if(item.is_show === 1) this.is_show.push(item.id)
                            // 去重
                            this.is_show = inisHelper.array.unique(this.is_show)
                        })
                        
                        // 是否显示分页
                        if(inisHelper.is.empty(this.music.data) || this.music.page == 1) this.is_page_show = false
                        else this.is_page_show = true
                        
                        // 更新页码
                        this.page              = page
                        
                        // 页码列表
                        this.page_list         = inisHelper.create.paging(page, this.music.page, 7)
                        
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
                const factor2 = inisHelper.is.empty(this.edit.url)
                
                if (factor1) {
                    $.NotificationApp.send("提示！", "歌单名称不能为空！", "top-right", "rgba(0,0,0,0.2)", "warning");
                } else if (factor2) {
                    $.NotificationApp.send("提示！", "歌单地址不能为空！", "top-right", "rgba(0,0,0,0.2)", "warning");
                } else {
                    
                    let params = new FormData
                    
                    delete this.edit.create_time
                    delete this.edit.update_time
                    
                    for (let item in this.edit) {
                        params.append(item, this.edit[item] || '')
                    }
                    
                    axios.post('/index/method/SaveMusic', params).then((res) => {
                        if (res.data.code == 200) {
                            // 刷新数据
                            this.initData()
                            // 关闭 model 窗口
                            $('#fill-edit-modal').modal('toggle')
                            $.NotificationApp.send("提示！", "保存成功！", "top-right", "rgba(0,0,0,0.2)", "info");
                        } else if (res.data.code == 201){
                            // 刷新数据
                            this.initData()
                            // 关闭 model 窗口
                            $('#fill-edit-modal').modal('toggle')
                            $.NotificationApp.send("提示！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "info");
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
            
            /* 启用状态 */
            isEnable(id, is_load = false){
                
                let [arr, status] = [this.is_show, 0]
                
                // 状态取反
                if (inisHelper.in.array(id,arr)) status = 0
                else if (!inisHelper.in.array(id,arr)) status = 1
                
                let params = new FormData
                
                params.append('id',id || '')
                params.append('status',status || '')
                
                axios.post('/index/handle/MusicIsShow', params).then(res=>{
                    if (res.data.code == 200 && is_load) {
                        this.initData('','',true)
                    }
                })
            },
            
            // 触发上传事件
            clickUpload: () => {
                document.querySelector("#input-file").click()
            },
            
            // 上传头像
            upload(event){
                
                const self = this
                
                /* 单图上传 */
                let file  = event.target.files[0]
                
                let name  = file.name
                name = name.split('.')
                const warning = ['php','js','htm','html','xml','json','bat','vb','exe']
                
                if (file.size > 5 * 1024 * 1024) $.NotificationApp.send("错误！", "上传文件不得大于5MB！", "top-right", "rgba(0,0,0,0.2)", "warning");
                else if (inisHelper.in.array(name.pop(), warning)){
                    $.NotificationApp.send("提示！", "请不要尝试提交可执行程序，因为你不会成功！", "top-right", "rgba(0,0,0,0.2)", "error");
                } else {
                    
                    $.NotificationApp.send("", "正在上传 ...", "top-right", "rgba(0,0,0,0.2)", "info");
                    
                    let params = new FormData
                    params.append("file", file || '')
                    params.append("id", this.edit.id || 0)
                    
                    const config = {
                        headers: { "Content-Type": "multipart/form-data" },
                        onUploadProgress: (speed) => {
                            if (speed.lengthComputable) {
                                let ratio = speed.loaded / speed.total;
                                // 只是上传到后端，后端并未真正保存成功
                                if (ratio < 1) self.speed = ratio
                            }
                        }
                    }
                    
                    axios.post("/index/handle/upload", params, config).then((res) => {
                        if(res.data.code == 200){
                            self.speed = 1
                            this.edit.head_img = res.data.data
                            $.NotificationApp.send("提示！", "<span style='color:var(--blue)'>上传成功！</span>", "top-right", "rgba(0,0,0,0.2)", "info");
                        } else {
                            self.speed = 0
                            $.NotificationApp.send("错误！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "info");
                        }
                    })
                    
                    event.target.value = ''
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
                
                axios.post('/index/method/deleteMusic', params).then(res=>{
                    if (res.data.code == 200) {
                        $.NotificationApp.send("提示！", "删除成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                    } else {
                        $.NotificationApp.send("提示！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "info");
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
                    
                    if (inisHelper.is.empty(newValue.id)) self.title = '添加歌单'
                    else self.title = '修改歌单'
                },
                immediate: true,
                deep: true,
            }
        },
    }).mount('#music')

}(window.jQuery)