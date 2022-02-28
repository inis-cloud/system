!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                account  : null,
                password : null,
                is_login : false,   // 是否登录中
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
            btnLogin(){
                
                if (inisHelper.is.empty(this.account)) {
                    
                    t.NotificationApp.send(null, "请填写帐号！", "top-right", "rgba(0,0,0,0.2)", "warning");
                    
                } else if (inisHelper.is.empty(this.password)) {
                    
                    t.NotificationApp.send(null, "请填写密码！", "top-right", "rgba(0,0,0,0.2)", "warning");
                    
                } else {
                    
                    this.is_login = true
                    
                    const params = inisHelper.stringfy({
                        account : this.account,
                        password: this.password,
                    })
                    
                    axios.post('/admin/comm/login', params).then((res) => {
                        
                        if (res.data.code == 200) {
                            
                            t.NotificationApp.send(null, "登录成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                            
                            setTimeout(() => {
                                
                                window.location.href = '/';
                                
                            }, 100);
                            
                        } else if (res.data.code == 403) {
                            
                            t.NotificationApp.send(null, res.data.msg, "top-right", "rgba(0,0,0,0.2)", "error");
                            
                        } else t.NotificationApp.send(null, res.data.msg, "top-right", "rgba(0,0,0,0.2)", "error");
                        
                        this.is_login = false
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
        }
    }).mount('#login')
    
}(window.jQuery)