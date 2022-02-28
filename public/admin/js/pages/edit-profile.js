!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                login_account: [],
                password1: '',
                password2: '',
                wechat_speed: 0,        // 上传图片进度
                ali_speed: 0,           // 上传图片进度
                qq_speed: 0,            // 上传图片进度
            }
        },
        components: {
            
        },
        mounted() {
            this.initData()
        },
        methods: {
            
            initData(){
                
                inisHelper.fetch.get('/admin/api/user').then(res=>{
                    if (res.code == 200) {
                        
                        this.login_account            = res.data
                        // 支付宝收款码
                        this.login_account.qq_pay     = (inisHelper.is.empty(this.login_account.opt.qq_pay))     ? '' : this.login_account.opt.qq_pay
                        // 支付宝收款码
                        this.login_account.alipay     = (inisHelper.is.empty(this.login_account.opt.alipay))     ? '' : this.login_account.opt.alipay
                        // 微信收款码
                        this.login_account.wechat_pay = (inisHelper.is.empty(this.login_account.opt.wechat_pay)) ? '' : this.login_account.opt.wechat_pay
                        
                        let sex = [{"id":0,"text":"女"},{"id":1,"text":"男"},{"id":3,"text":"保密"}]
                        sex.forEach((item)=>{
                            if (this.login_account.sex == item.text) item.selected = true
                        })
                        
                        // 重置数据
                        $("#sex-select2").select2().empty()
                        
                        // 状态单选框
                        $("#sex-select2").select2({
                            minimumResultsForSearch: Infinity,
                            data: sex
                        })
                    }
                })
            },
            
            /* 保存数据 */
            btnSave(){
                
                // 去除空字符
                this.password1 = inisHelper.trim.string(this.password1)
                this.password2 = inisHelper.trim.string(this.password2)
                this.login_account.email = inisHelper.trim.string(this.login_account.email)
                this.login_account.account = inisHelper.trim.string(this.login_account.account)
                
                if (inisHelper.is.empty(this.login_account.account)) {
                    $.NotificationApp.send(null, "帐号不得为空!", "top-right", "rgba(0,0,0,0.2)", "warning");
                } else if(inisHelper.is.empty(this.login_account.email)) {
                    $.NotificationApp.send(null, "邮箱不得为空!", "top-right", "rgba(0,0,0,0.2)", "warning");
                } else if(inisHelper.is.email(this.login_account.email)) {
                    
                    // 删除多余字段
                    delete this.login_account.opt
                    delete this.login_account.level
                    delete this.login_account.status
                    delete this.login_account.update_time
                    delete this.login_account.create_time
                    
                    let params = new FormData
                    
                    for (let item in this.login_account) params.append(item,this.login_account[item] || '')
                    params.append('password1',this.password1 || '')
                    params.append('password2',this.password2 || '')
                    
                    const sex = $('#sex-select2').select2('data')[0]['text'];
                    params.append("sex", sex || '')
                    
                    axios.post('/admin/method/EditProfile', params).then((res) => {
                        if (res.data.code == 200) {
                            
                            $.NotificationApp.send(null, res.data.msg, "top-right", "rgba(0,0,0,0.2)", "success");
                            
                            if (!inisHelper.is.empty(res.data.url)) {
                                if (res.data.url.indexOf("login") != -1) setTimeout(() => {window.location.href = res.data.url},1000);
                            }
                            
                        } else {
                            
                            this.password1 = ''
                            this.password2 = ''
                            
                            $.NotificationApp.send(null, res.data.msg, "top-right", "rgba(0,0,0,0.2)", "error");
                        }
                    })
                    
                } else {
                    $.NotificationApp.send(null, "邮箱格式不正确!", "top-right", "rgba(0,0,0,0.2)", "error");
                }
            },
            
            /* 触发上传事件 */
            clickUpload(){
                document.querySelector("#btn_file").click()
            },
            
            /* 上传头像 */
            upload(e){
                
                /* 单图上传 */
                let file  = e.target.files[0];
                
                if (file.size > 5*1024*1024) $.NotificationApp.send(null, "上传文件不得大于5MB！", "top-right", "rgba(0,0,0,0.2)", "warning");
                else {
                    
                    $.NotificationApp.send(null, "正在上传 ...", "top-right", "rgba(0,0,0,0.2)", "info");
                    
                    let params = new FormData();
                    params.append("image", file || '');
                    
                    axios.post("/admin/handle/uploadHead", params, {
                        headers: {
                            "Content-Type": "multipart/form-data"
                        }
                    }).then((res) => {
                        if (res.data.code == 200) {
                            e.target.value = ''
                            $.NotificationApp.send(null, "头像已更新！", "top-right", "rgba(0,0,0,0.2)", "success");
                        } else $.NotificationApp.send(null, res.data.msg, "top-right", "rgba(0,0,0,0.2)", "error");
                    })
                }
            },
            
            // 触发上传事件
            qqClick: () => {
                document.querySelector("#qq-pay").click()
            },
            
            // 触发上传事件
            aliClick: () => {
                document.querySelector("#ali-pay").click()
            },
            
            // 触发上传事件
            wechatClick: () => {
                document.querySelector("#wechat-pay").click()
            },
            
            // 上传图片
            uploadPay(event, opt = 'wechat'){
                
                let self = this
                
                /* 单图上传 */
                let file  = event.target.files[0]
                
                let name  = file.name
                name = name.split('.')
                const warning = ['php','js','htm','html','xml','json','bat','vb','exe']
                
                if (file.size > 5 * 1024 * 1024) $.NotificationApp.send(null, "上传文件不得大于5MB！", "top-right", "rgba(0,0,0,0.2)", "warning");
                else if (inisHelper.in.array(name.pop(), warning)){
                    $.NotificationApp.send(null, "请不要尝试提交可执行程序，因为你不会成功！", "top-right", "rgba(0,0,0,0.2)", "error");
                } else {
                    
                    $.NotificationApp.send(null, "正在上传 ...", "top-right", "rgba(0,0,0,0.2)", "info");
                    
                    let params = new FormData
                    params.append("mode", 'pay')
                    params.append("file", file || '')
                    params.append("id", this.login_account.id || 0)
                    
                    const config = {
                        headers: { "Content-Type": "multipart/form-data" },
                        onUploadProgress: (speed) => {
                            if (speed.lengthComputable) {
                                let ratio = speed.loaded / speed.total;
                                // 只是上传到后端，后端并未真正保存成功
                                if (ratio < 1) {
                                    if (opt == 'wechat') self.wechat_speed = ratio
                                    else if (opt == 'qq_pay') self.qq_speed = ratio
                                    else self.ali_speed = ratio
                                }
                            }
                        }
                    }
                    
                    axios.post("/admin/handle/upload", params, config).then((res) => {
                        if(res.data.code == 200){
                            if (opt == 'wechat') {
                                self.wechat_speed = 1
                                self.login_account.wechat_pay = res.data.data
                            } else if (opt == 'qq_pay') {
                                self.qq_speed = 1
                                self.login_account.qq_pay = res.data.data
                            } else {
                                self.ali_speed = 1
                                self.login_account.alipay = res.data.data
                            }
                            $.NotificationApp.send(null, "上传成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                        } else {
                            if (opt == 'wechat') self.wechat_speed  = 0
                            else if (opt == 'qq_pay') self.qq_speed = 0
                            else self.ali_speed = 0
                            $.NotificationApp.send(null, res.data.msg, "top-right", "rgba(0,0,0,0.2)", "error");
                        }
                    })
                    
                    event.target.value = ''
                }
            },
        }
    }).mount('#edit-profile')

}(window.jQuery);