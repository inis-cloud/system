!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                user: [],           // 注册的信息
                code: {
                    title: '获取',
                    value: null
                },
                result: {},         // 注册成功返回的结果
            }
        },
        components: {
            
        },
        mounted() {
            this.oneWord()
            this.initData()
        },
        methods: {
            // 初始化本地配置
            initData(){
                // 初始化本地配置
                if (inisHelper.has.storage('config')) {
                    const config = inisHelper.get.storage('config')
                    const body   = document.querySelector('body')
                    // 夜间模式
                    if (config.mode.theme == 'dark') {
                        body.setAttribute('data-theme-mode', 'dark')
                        inisHelper.set.links('/admin/css/app-dark.min.css', 'link')
                    } else {
                        const links = document.querySelectorAll('link[rel=stylesheet]')
                        links.forEach((item)=> {
                            if (item.getAttribute('href') == '/admin/css/app-dark.min.css') item.remove()
                        })
                        body.setAttribute('data-theme-mode', 'light')
                    }
                }
            },
            // 提交数据
            save(){
                
                if (this.check()) {
                    
                    const params = inisHelper.stringfy({
                        email: this.user.email,
                        code : this.code.value,
                        nickname: this.user.nickname,
                        password: this.user.password1
                    })
                    
                    axios.post('/api/comm/register', params).then((res) => {
                        if (res.data.code == 200) {
                            this.result = res.data.data
                            t.NotificationApp.send(null, "注册成功！"  , "top-right", "rgba(0,0,0,0.2)", "success");
                            t("#fill-signup-modal").modal('show');
                        } else {
                            const code = document.querySelector("#code")
                            code.classList.add('btn-danger')
                            code.classList.remove('btn-dark')
                            code.classList.remove('btn-info')
                            t.NotificationApp.send(null, res.data.msg, "top-right", "rgba(0,0,0,0.2)", "error");
                        }
                    })
                }
            },
            
            // 校验数据
            check(){
                
                let result = false
                const signup = document.querySelector("#checkbox-signup").checked
                
                if (inisHelper.is.empty(this.user.nickname)) {
                    t.NotificationApp.send(null, "必须填写用户名！", "top-right", "rgba(0,0,0,0.2)", "warning");
                } else if (inisHelper.is.empty(this.user.password1)) {
                    t.NotificationApp.send(null, "请填写密码！"    , "top-right", "rgba(0,0,0,0.2)", "warning");
                } else if (inisHelper.is.empty(this.user.password2)) {
                    t.NotificationApp.send(null, "请再次填写密码！", "top-right", "rgba(0,0,0,0.2)", "warning");
                } else if (this.user.password1 != this.user.password2) {
                    t.NotificationApp.send(null, "两次密码不一致！", "top-right", "rgba(0,0,0,0.2)", "warning");
                } else if (inisHelper.is.empty(this.user.email)) {
                    t.NotificationApp.send(null, "邮箱不得为空！"  , "top-right", "rgba(0,0,0,0.2)", "warning");
                } else if (!inisHelper.is.email(this.user.email)) {
                    t.NotificationApp.send(null, "邮箱格式不正确！", "top-right", "rgba(0,0,0,0.2)", "warning");
                } else if (inisHelper.is.empty(this.code.value)) {
                    t.NotificationApp.send(null, "请填写验证码！"  , "top-right", "rgba(0,0,0,0.2)", "warning");
                } else if (!signup) {
                    t.NotificationApp.send(null, "请先同意用户协议！"  , "top-right", "rgba(0,0,0,0.2)", "warning");
                } else result = true
                
                return result
            },
            
            // 验证码
            verify(){
                
                if (inisHelper.is.empty(this.user.email)) {
                    t.NotificationApp.send(null, "请先填写邮箱！"  , "top-right", "rgba(0,0,0,0.2)", "warning");
                } else if (!inisHelper.is.email(this.user.email)) {
                    t.NotificationApp.send(null, "邮箱格式不正确！", "top-right", "rgba(0,0,0,0.2)", "warning");
                } else {
                    
                    let time   = 60;
                    const code = document.querySelector("#code");
                    
                    let timeStop = setInterval(()=>{
                        
                        time--;
                        
                        if (time > 0) {
                            this.code.title = '获取' + time + 's';
                            code.disabled = true
                        } else {
                            // 当减到0时赋值为60
                            timeo = 60;
                            this.code.title = '获取';
                            // 清除定时器
                            clearInterval(timeStop);
                            code.disabled = false
                        }
                        
                    },1000)
                    
                    const params = inisHelper.stringfy({
                        mode : 'create',
                        email: this.user.email,
                    })
                    
                    axios.post('/api/verify-code', params).then(res=>{
                        if (res.data.code == 200) {
                            t.NotificationApp.send(null, res.data.msg  , "top-right", "rgba(0,0,0,0.2)", "success");
                        } else if (res.data.code == 412) {
                            clearInterval(timeStop);
                            code.disabled = false
                            t.NotificationApp.send(null, "此邮箱已注册本站帐号，<a href='reset.html' style='color:red'>点击此处可找回密码</a>" , "top-right", "rgba(0,0,0,0.2)", "success");
                        } else {
                            clearInterval(timeStop);
                            code.disabled = false
                            t.NotificationApp.send(null, res.data.msg  , "top-right", "rgba(0,0,0,0.2)", "error");
                        }
                    })
                }
            },
            
            oneWord(){
                axios.get('/api/file/words').then(res=>{
                    if (res.data.code == 200) {
                        const result = res.data.data
                        document.querySelector('.footer .one-word').append(result)
                    }
                })
            }
        },
        watch: {
            code: {
                handler(newValue,oldValue){
                    
                    const self = this
                    
                    const code = document.querySelector("#code")
                    
                    if (!inisHelper.is.empty(newValue)) {
                        code.classList.add('btn-info')
                        code.classList.remove('btn-dark')
                    } else {
                        code.classList.add('btn-dark')
                        code.classList.remove('btn-info')
                    }
                    
                },
                // immediate: true,
                deep: true,
            },
        },
    }).mount('#register')

}(window.jQuery)