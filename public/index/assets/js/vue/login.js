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
            'i-footer': inisTemp.footer('login')
        },
        mounted() {
            
        },
        methods: {
            btnLogin(){
                
                if (inisHelper.is.empty(this.account)) {
                    
                    t.NotificationApp.send("提示！", "请填写帐号！", "top-right", "rgba(0,0,0,0.2)", "warning");
                    
                } else if (inisHelper.is.empty(this.password)) {
                    
                    t.NotificationApp.send("提示！", "请填写密码！", "top-right", "rgba(0,0,0,0.2)", "warning");
                    
                } else {
                    
                    this.is_login = true
                    
                    let params = new FormData
                    params.append('account', this.account  || '')
                    params.append('password',this.password || '')
                    
                    axios.post('/index/comm/login', params).then((res) => {
                        
                        if(res.data.code == 200){
                            
                            t.NotificationApp.send("提示！", "<span style='color:red'>登录成功！</span>", "top-right", "rgba(0,0,0,0.2)", "info");
                            
                            setTimeout(() => {
                                
                                window.location.href = '/';
                                
                            }, 100);
                            
                        } else if (res.data.code == 403) {
                            
                            t.NotificationApp.send("提示！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "error");
                            
                        } else t.NotificationApp.send("提示！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "warning");
                        
                        this.is_login = false
                    })
                }
            },
        }
    }).mount('#login')
    
}(window.jQuery)