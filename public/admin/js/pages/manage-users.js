!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                users: {},          // 用户数据
                edit: {},           // 编辑用户
                title: '',          // 模态框标题
                speed: 0,           // 上传头像进度
                page: 1,            // 当前页码
                is_load: true,      // 数据加载动画
                page_list: [],      // 标签页码列表
                page_is_load: true, // 页码加载动画
                is_page_show: true, // 是否显示分页
                search_value: '',   // 搜索的内容
                is_enable: [],      // 是否启用
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
                
                let auth = [{'id':'user','text':'用户'},{'id':'admin','text':'管理员'}]
                
                POST('/admin/manage-users', {
                    id, page, search: this.search_value, limit: 8
                }).then(res => {
                    if (res.code == 200) {
                        
                        // 更新数据
                        this.users = res.data.users
                        
                        // 编辑的用户数据
                        if (!utils.is.empty(res.data.edit)) {
                            this.edit = res.data.edit
                            auth.forEach(item=>{
                                if (item.id == this.edit.level) item.selected = true
                            })
                            $('#sex-select2').empty()
                            $('#auth-select2').empty()
                            $('#enable-select2').empty()
                        }
                        
                        // 性别单选框
                        $('#sex-select2').select2({
                            minimumResultsForSearch: Infinity,
                            data: res.data.sex,
                        })
                        
                        // 权限单选框
                        $('#auth-select2').select2({
                            minimumResultsForSearch: Infinity,
                            data: auth
                        })
                        
                        // 启用单选框
                        $('#enable-select2').select2({
                            minimumResultsForSearch: Infinity,
                            data: res.data.enable,
                        })
                        
                        // 启用状态
                        this.is_enable = []
                        this.users.data.forEach((item) => {
                            if (item.status === 1) this.is_enable.push(item.id)
                            // 去重
                            this.is_enable = utils.array.unique(this.is_enable)
                        })
                        
                        // 是否显示分页
                        if (utils.is.empty(this.users.data) || this.users.page == 1) this.is_page_show = false
                        else this.is_page_show = true
                        
                        // 更新页码
                        this.page              = page
                        
                        // 页码列表
                        this.page_list         = utils.create.paging(page, this.users.page, 5)
                        
                        // 数据加载动画
                        this.is_load           = false
                        // 页码加载动画
                        this.page_is_load      = false
                    }
                })
            },
            
            // 保存
            save(){
                
                const factor1 = utils.is.empty(this.edit.nickname)
                const factor2 = utils.is.empty(this.edit.email)
                const factor3 = utils.is.email(this.edit.email)
                
                if (factor1)       Tool.Notyf('昵称不能为空！', 'warning')
                else if (factor2)  Tool.Notyf('邮箱不能为空！', 'warning')
                else if (!factor3) Tool.Notyf('邮箱格式错误！', 'warning')
                else {
                    
                    let params = {}

                    delete this.edit.create_time
                    delete this.edit.update_time

                    for (let item in this.edit) params[item] = this.edit[item]
                    
                    // 获取 select2 性别单选框数据
                    params.sex   = $('#sex-select2').select2('data')[0]['text']
                    // 获取 select2 启用状态单选框数据
                    params.status= $('#enable-select2').select2('data')[0]['id']
                    params.level = $('#auth-select2').select2('data')[0]['id']
                    
                    POST('/admin/method/SaveUsers', params).then(res => {
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
                const select    = document.querySelectorAll('.checkbox-item')
                if (selectAll.checked) for (let item of select) item.checked = true
                else for (let item of select) item.checked = false
            },
            
            // 启用状态
            isEnable(id){
                
                let [array, status] = [this.is_enable, 0]
                
                // 状态取反
                status = utils.in.array(id, array) ? 0 : 1
                
                POST('/admin/handle/SetUserEnable', { id, status})
            },
            
            // 批量删除用户
            deleteUsers(id = ''){
                
                const select  = document.querySelectorAll('.checkbox-item')
                let check_arr = [];
                
                for (let item of select) {
                    if (item.checked) check_arr.push(item.getAttribute('name'))
                }
                
                let params = {}
                params.id  = utils.is.empty(id) ? check_arr.join() : id
                
                POST('/admin/method/deleteUsers', params).then(res => {
                    if (res.code == 200) {
                        document.querySelector('#select-all').checked = false
                        Tool.Notyf('删除成功！', 'success')
                    } else Tool.Notyf(res.msg, 'error')
                    this.initData()
                })
            },
            
            // 触发上传事件
            clickUpload: () => document.querySelector('#input-file').click(),
            
            // 上传头像
            upload(event){
                
                const self = this
                
                /* 单图上传 */
                let file  = event.target.files[0]

                let name  = file.name
                name = name.split('.')
                const warning = ['php','js','htm','html','xml','json','bat','vb','exe']
                
                if (file.size > 5 * 1024 * 1024) Tool.Notyf('上传文件不得大于5MB！', 'warning')
                else if (utils.in.array(name.pop(), warning)) Tool.Notyf('请不要尝试提交可执行程序，因为你不会成功！', 'error')
                else {
                    
                    Tool.Notyf('正在上传 ...')
                    
                    let params = new FormData
                    params.append('file', file || '')
                    params.append('id', this.edit.id || 0)
                    
                    const config = {
                        headers: { 'Content-Type': 'multipart/form-data' },
                        onUploadProgress: (speed) => {
                            if (speed.lengthComputable) {
                                let ratio = speed.loaded / speed.total;
                                // 只是上传到后端，后端并未真正保存成功
                                if (ratio < 1) self.speed = ratio
                            }
                        }
                    }
                    
                    axios.post('/admin/handle/upload', params, config).then(res => {
                        if(res.data.code == 200){
                            self.speed = 1
                            this.edit.head_img = res.data.data
                            Tool.Notyf('上传成功！', 'success')
                        } else {
                            self.speed = 0
                            Tool.Notyf(res.data.msg, 'error')
                        }
                    })
                    
                    event.target.value = ''
                }
            },
            
            // 权限转换
            level: value => value == 'admin' ? '管理员' : '普通用户',
            
            // 时间戳转人性化时间
            natureTime: (time = '') => {
                
                let result = '从未登录'
                
                if (!utils.is.empty(time)) result = utils.time.nature(time)
                
                return result
            },
        },
        computed: {
            
        },
        watch: {
            edit: {
                handler(newValue,oldValue){
                    
                    const self = this
                    self.title = utils.is.empty(newValue.id) ? '添加用户' : '修改用户'
                },
                immediate: true,
                deep: true,
            }
        },
    }).mount('#manage-users')

}(window.jQuery)