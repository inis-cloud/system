!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                site: {},                   // 站点信息
                token: {},                  // token信息
                token_switch: false,        // token验证开关
                token_open_switch: false,   // token API开关
                domain: {},                 // 白名单信息
                domain_switch: false,       // 白名单开关
                master: [],                 // 站长信息
                redundancy: {},             // 冗余资源
                serve: {
                    email: {
                        opt: {email_cc:''},
                        value: {}
                    },
                    other: {
                        article: {
                            comments: {
                                show : true,
                                allow: true,
                            }
                        },
                        optimize: {
                            cdn: '',
                            image: {
                                open: true,
                                ratio: 50
                            }
                        },
                        system: {
                            autoUpdate: false,
                        }
                    },
                    applets: {
                        qq    : {show:{comments:true}},
                        wechat: {show:{comments:true}},
                        other : {show:{comments:true}}
                    },
                },
                load: {
                    test_email: false
                },
                // test:`<input type='text' data-plugin='range-slider' data-min='0' data-max='100' data-from='50'>`
            }
        },
        components: {
            
        },
        mounted() {
            
            this.initData()
        },
        methods: {
            
            initData(){
                this.getSystem()
                this.webmaster()
                this.getRedundancy()
                this.getServer()
                this.getApplets()
            },
            
            // 获取站点数据
            getSystem(){
                
                POST('/admin/system').then(res => {
                    if (res.code == 200){
                        
                        let result   = res.data
                        this.site    = result.site
                        this.token   = result.token
                        this.domain  = result.domain
                        // Token 开关
                        if (res.data.token.status == 1) this.token_switch = true
                        else this.token_switch = false
                        // Token API 开关
                        if (res.data.token.open == 1) this.token_open_switch = true
                        else this.token_open_switch = false
                        // domain 开关
                        if (res.data.domain.status.status == 1) this.domain_switch = true
                        else this.domain_switch = false
                    }
                })
                
            },
            
            // 站长信息
            webmaster(){
                
                POST('/admin/webmaster').then(res => {
                    if (res.code == 200) {
                        
                        $('#web-master').empty()
                        
                        let master = []
                        this.master= res.data
                        
                        this.master.users.forEach(item=>{
                            let push = {id:item.id,text:item.nickname}
                            if (item.id == this.master.info.opt.users_id) push.selected = true
                            master.push(push)
                        })
                        
                        for (let item in this.master.info.opt) this.master[item] = this.master.info.opt[item]
                        
                        // 性别单选框
                        $('#web-master').select2({
                            minimumResultsForSearch: Infinity,
                            data: master
                        })
                    }
                })
            },
            
            // 保存站长信息
            saveMaster(){
                
                let master = this.master
                
                delete master.info
                delete master.users
                delete master.value
                
                let users_id = $('#web-master').select2('data')[0]['id']
                
                POST('/admin/method/saveMaster', {
                    ...master, users_id
                }).then(res => {
                    if (res.code == 200) {
                        // 关闭 model 窗口
                        $('#fill-master-modal').modal('toggle')
                    }
                })
                
            },
            
            // 获取冗余资源
            getRedundancy(){
                POST('/admin/redundancy').then(res => {
                    if (res.code == 200) {
                        const result    = res.data
                        this.redundancy = result.image
                    }
                })
            },
            
            // 清理冗余资源
            clearTrash(){
                POST('/admin/redundancy', { clear: 'true' }).then(res=>{
                    if (res.code == 200) this.getRedundancy()
                })
            },
            
            // 保存配置信息
            btnSave(){
                
                POST('/admin/method/SaveOptions', {
                    key: 'site',
                    opt: this.site
                }).then(res => {
                    if (res.code == 200) Tool.Notyf('保存成功！', 'success')
                    else Tool.Notyf(res.msg, 'error')
                })
            },
            
            // 触发上传事件
            clickUpload: () => document.querySelector('#btn_file').click(),
            
            // 上传头像
            upload(event){
                
                let file  = event.target.files[0];
                
                if (file.size > 5 * 1024 * 1024) Tool.Notyf('上传文件不得大于5MB！', 'warning')
                else {
                    
                    Tool.Notyf('正在上传 ...')
                    
                    let params = new FormData
                    params.append('image', file || '')
                    
                    axios.post('/admin/handle/uploadSiteHead', params, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }).then(res => {
                        if (res.data.code == 200) {
                            this.getSystem()
                            event.target.value = ''
                            Tool.Notyf('头像已更新！', 'success')
                        } else Tool.Notyf(res.data.msg, 'error')
                    })
                }
            },
            
            // 保存Token开关信息
            btnSaveToken(){
                
                // 获取当前Token开关状态
                let status = this.token.status
                // Token开关状态取反
                status = status == 1 ? 0 : 1
                
                let params = new FormData
                params.append('status',status || '')
                
                POST('/admin/handle/SetToken', { status }).then(res => {
                    if(res.code == 200){
                        // 更新当前开关状状态
                        if (res.data.status == 1) {
                            this.token.status = 1
                            this.token_switch = true
                        } else if(res.data.status == 0) {
                            this.token.status = 0
                            this.token_switch = false
                        }
                        // 更新Token信息
                        this.token.value  = res.data.token
                    }
                })
            },
            
            // 保存Token
            saveToken(){

                POST('/admin/handle/SaveToken', {
                    token: this.token.value
                }).then(res => {
                    if (res.code == 200) {
                        // 关闭 model 窗口
                        $('#fill-token-modal').modal('toggle')
                    } else Tool.Notyf('请检查网络是否正常！', 'error')
                })
                
            },
            
            // 允许通过API方式获取Token
            btnSaveTokenOpen(){
                
                // 获取当前Token API开关状态
                let status = this.token.open
                // Token API开关状态取反
                status = status == 1 ? 0 : 1
                
                POST('/admin/handle/TokenIsOpen', { status }).then(res => {
                    if(res.code == 200){
                        // 更新当前开关状状态
                        if (res.data.status == 1) {
                            this.token.open = 1
                            this.token_open_switch = true
                        } else if(res.data.status == 0) {
                            this.token.open = 0
                            this.token_open_switch = false
                        }
                    }
                })
            },
            
            // 刷新Token
            resetToken(){
                
                POST('/admin/handle/ResetToken', { code: 1 }).then(res => {
                    if (res.code == 200) {
                        // 更新Token信息
                        this.token.value  = res.data.token
                    } else Tool.Notyf('请检查网络是否正常！', 'error')
                })
            },
            
            // 白名单开关
            btnSaveDomain(){
                // 获取当前Token开关状态
                let status = this.domain.status.status
                // Token开关状态取反
                status = status == 1 ? 0 : 1

                POST('/admin/handle/SetDomain', { status }).then(res => {
                    if(res.code == 200){
                        // 更新当前开关状状态
                        if (res.data.status == 1) {
                            this.domain.status.status = 1
                            this.domain_switch = true
                        } else if(res.data.status == 0) {
                            this.domain.status.status = 0
                            this.domain_switch = false
                        }
                    }
                })
            },
            
            // 保存域名白名单
            btnSaveDomainValue(){
                
                let domain = this.domain.value
                
                domain = domain.split(/[(\r\n)\r\n]+/).join(',')
                
                POST('/admin/method/SaveOptions', {
                    key: 'config:security',
                    value: domain
                }).then(res => {
                    if (res.code == 200) Tool.Notyf('保存成功！', 'success')
                    else Tool.Notyf(res.msg, 'error')
                })
            },
            
            // 解析时间
            parseTime(url = null){
                
                const array = url.split('/')
                const pop   = parseInt(array.pop())
                
                return (!utils.is.empty(pop)) ? utils.natureTime(pop) : '未知'
            },
            
            // 获取服务配置
            getServer(){

                POST('/admin/serve').then(res=>{
                    if (res.code == 200) {
                        
                        const result = res.data
                        
                        // 配置邮箱服务
                        this.setEmail(result)
                        // 设置其他配置
                        this.setOther(result)
                    }
                })
            },
            
            // 设置邮件服务信息
            setEmail(result){
                
                this.serve.email = result.email_serve
                
                // select2 加密方式 初始化
                let encry_data = [{id: 0,text: 'ssl'},{id: 1,text: 'tls'}]
                encry_data.forEach((item)=>{
                    // 设置预选中
                    if(this.serve.email.opt.encrypt == item.text) item.selected = true
                });
                $('#encrypt-select').select2({
                    data: encry_data,
                    minimumResultsForSearch: Infinity
                })
                
                // select2 编码方式 初始化
                let encoded_data = [{id: 0,text: 'UTF-8'},{id: 1,text: 'GB2312'}]
                encoded_data.forEach((item)=>{
                    // 设置预选中
                    if(this.serve.email.opt.encoded == item.text) item.selected = true
                });
                $('#coding-select').select2({
                    data: encoded_data,
                    minimumResultsForSearch: Infinity
                })
            },
            
            // 设置其他服务信息
            setOther(result){
                this.serve.other = utils.object.deep.merge(this.serve.other, result.system_config.opt)
            },
            
            // 测试邮件
            testEmail(){
                
                let config = this.serve.email
                
                if (utils.is.empty(config.opt.email))         Tool.Notyf('邮箱帐号不能为空！', 'warning')
                else if (utils.is.empty(config.opt.password)) Tool.Notyf('邮箱密码不能为空！', 'warning')
                else if (utils.is.empty(config.opt.smtp))     Tool.Notyf('服务地址不能为空！', 'warning')
                else if (utils.is.empty(config.opt.nickname)) Tool.Notyf('发件人昵称不能为空！', 'warning')
                else if (utils.is.empty(config.opt.email_cc)) Tool.Notyf('抄送不能为空！', 'warning')
                else if (utils.is.empty(config.opt.port))     Tool.Notyf('端口号不能为空！', 'warning')
                else {
                    
                    this.load.test_email = true
                    
                    config.opt.encrypt = $('#encrypt-select').select2('data')[0]['text']
                    config.opt.coding  = $('#coding-select').select2('data')[0]['text']
                    
                    POST('/admin/handle/testEmail', {
                        ...config.opt,
                        port: parseInt(config.opt.port),
                        email_cc: config.opt.email_cc.split(','),
                        title:'测试邮件服务！',
                        content:'当您看到这条邮件信息时，表示您的邮件服务配置成功！'
                    }).then(res => {
                        if (res.code == 200) {
                            Tool.Notyf(`测试邮件已发送至以下邮箱<br>${config.opt.email_cc.replace(',','<br>')}`, 'success')
                            setTimeout(()=>{
                                this.saveEmail(true)
                            }, 1500);
                        } else Tool.Notyf(res.msg, 'error')
                        this.load.test_email = false
                    })
                }
            },
            
            // 保存邮件服务
            saveEmail(auto_save = false){
                
                let config = this.serve.email
                
                if (utils.is.empty(config.opt.email))         Tool.Notyf('邮箱帐号不能为空！', 'warning')
                else if (utils.is.empty(config.opt.password)) Tool.Notyf('邮箱密码不能为空！', 'warning')
                else if (utils.is.empty(config.opt.smtp))     Tool.Notyf('服务地址不能为空！', 'warning')
                else if (utils.is.empty(config.opt.nickname)) Tool.Notyf('发件人昵称不能为空！', 'warning')
                else if (utils.is.empty(config.opt.email_cc)) Tool.Notyf('抄送不能为空！', 'warning')
                else if (utils.is.empty(config.opt.port))     Tool.Notyf('端口号不能为空！', 'warning')
                else {
                    
                    config.opt.port    = parseInt(config.opt.port),
                    config.opt.encrypt = $('#encrypt-select').select2('data')[0]['text']
                    config.opt.coding  = $('#coding-select').select2('data')[0]['text']
                    
                    POST('/admin/method/saveOptObj', {
                        ...config,
                        keys: 'config:email-serve'
                    }).then(res => {
                        if (res.code == 200) {
                            this.getServer()
                            if (auto_save) Tool.Notyf('已为您自动保存配置！', 'success')
                            else Tool.Notyf('保存成功！', 'success')
                        } else Tool.Notyf(res.data, 'error')
                    })
                }
            },
            
            // 切换按钮
            saveOther(notice = false){
                
                Promise.all([
                    POST('/admin/method/SaveOptions', {
                        opt: this.serve.other,
                        key: 'config:system'
                    }),
                    POST('/admin/method/SaveOptions', {
                        opt: this.serve.applets,
                        key: 'config:applets'
                    })
                ]).then(res => {
                    if (res[0].code == 200 && res[1].code == 200) {
                        if (notice) Tool.Notyf('保存成功！', 'success')
                    } else Tool.Notyf(res.msg, 'error')
                    // 刷新数据
                    this.getServer()
                    this.getApplets()
                })
            },
            
            // 获取小程序配置
            getApplets(){
                POST('/admin/applets').then(res => {
                    if (res.code == 200) {
                        const result       = res.data
                        this.serve.applets = result.opt
                    }
                })
            },
        },
        computed: {
            // 关键词
            keywords: function(){
                return !utils.is.empty(this.site.keywords) ? (this.site.keywords.split(',')).filter((s)=>{ return s && s.trim() }) : this.site.keywords
            },
            // 当前域名
            self_domain: function(){
                return window.location.protocol + '//' + window.location.host
            }
        },
        watch: {
            serve: {
                handler(newValue,oldValue){
                    
                    const self  = this
                    const email = self.serve.email
                    const other = self.serve.other
                    
                    // 解决 , 变 \n 的问题
                    try {
                        self.serve.email.opt.email_cc = email.opt.email_cc.replaceAll('\n',',')
                    } catch (e) {
                        
                    }
                    // 防止图片压缩比例超出范围
                    let ratio = parseInt(other.optimize.image.ratio)
                    if (utils.is.empty(ratio) || ratio < 0) ratio = 0
                    else if (ratio > 100) ratio = 100
                    
                    other.optimize.image.ratio = ratio
                },
                // immediate: true,
                deep: true,
            }
        }
    }).mount('#system')

}(window.jQuery);