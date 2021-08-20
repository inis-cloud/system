!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                user: [],           // 注册的信息
                code_title: '获取', // 验证码标题
                code_data: null,    // 验证码值
                result: {},         // 注册成功返回的结果
            }
        },
        components: {
            'i-footer': inisTemp.footer('login')
        },
        mounted() {
            
        },
        methods: {
            // 提交数据
            save(){
                
                if (this.check()) {
                    
                    let params = new FormData
                    params.append('email'   , this.user.email      || '')
                    params.append('code'    , this.code_data       || '')
                    params.append('nickname', this.user.nickname   || '')
                    params.append('password', this.user.password1  || '')
                    
                    // t.NotificationApp.send("提示！", "开发期间，禁止注册！"  , "top-right", "rgba(0,0,0,0.2)", "info");
                    
                    axios.post('/api/comm/register', params).then((res) => {
                        if (res.data.code == 200) {
                            this.result = res.data.data
                            t.NotificationApp.send("提示！", "注册成功！"  , "top-right", "rgba(0,0,0,0.2)", "info");
                            t("#fill-signup-modal").modal('show');
                        } else {
                            const code = document.querySelector("#code")
                            code.classList.add('btn-danger')
                            code.classList.remove('btn-dark')
                            code.classList.remove('btn-info')
                            t.NotificationApp.send("提示！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "warning");
                        }
                    })
                }
            },
            
            // 校验数据
            check(){
                
                let result = false
                const signup = document.querySelector("#checkbox-signup").checked
                
                if (inisHelper.is.empty(this.user.nickname)) {
                    t.NotificationApp.send("提示！", "必须填写用户名！", "top-right", "rgba(0,0,0,0.2)", "warning");
                } else if (inisHelper.is.empty(this.user.password1)) {
                    t.NotificationApp.send("提示！", "请填写密码！"    , "top-right", "rgba(0,0,0,0.2)", "warning");
                } else if (inisHelper.is.empty(this.user.password2)) {
                    t.NotificationApp.send("提示！", "请再次填写密码！", "top-right", "rgba(0,0,0,0.2)", "warning");
                } else if (this.user.password1 != this.user.password2) {
                    t.NotificationApp.send("提示！", "两次密码不一致！", "top-right", "rgba(0,0,0,0.2)", "warning");
                } else if (inisHelper.is.empty(this.user.email)) {
                    t.NotificationApp.send("提示！", "邮箱不得为空！"  , "top-right", "rgba(0,0,0,0.2)", "warning");
                } else if (!inisHelper.is.email(this.user.email)) {
                    t.NotificationApp.send("提示！", "邮箱格式不正确！", "top-right", "rgba(0,0,0,0.2)", "warning");
                } else if (inisHelper.is.empty(this.code_data)) {
                    t.NotificationApp.send("提示！", "请填写验证码！"  , "top-right", "rgba(0,0,0,0.2)", "warning");
                } else if (!signup) {
                    t.NotificationApp.send("提示！", "请先同意用户协议！"  , "top-right", "rgba(0,0,0,0.2)", "warning");
                } else result = true
                
                return result
            },
            
            // 验证码
            code(){
                
                if (inisHelper.is.empty(this.user.email)) {
                    t.NotificationApp.send("提示！", "请先填写邮箱！"  , "top-right", "rgba(0,0,0,0.2)", "warning");
                } else if (!inisHelper.is.email(this.user.email)) {
                    t.NotificationApp.send("提示！", "邮箱格式不正确！", "top-right", "rgba(0,0,0,0.2)", "warning");
                } else {
                    
                    let time   = 60;
                    const code = document.querySelector("#code");
                    
                    let timeStop = setInterval(()=>{
                    
                        time--;
                        
                        if (time > 0) {
                            this.code_title = '获取' + time + 's';
                            code.disabled = true
                        } else {
                            // 当减到0时赋值为60
                            timeo = 60;
                            this.code_title = '获取';
                            // 清除定时器
                            clearInterval(timeStop);
                            code.disabled = false
                        }
                        
                    },1000)
                    
                    let params = new FormData
                    params.append('mode'  , 'create'  || '')
                    params.append('email' , this.user.email || '')
                    
                    // t.NotificationApp.send("提示！", "开发期间，禁止注册！"  , "top-right", "rgba(0,0,0,0.2)", "info");
                    
                    axios.post('/api/verify-code', params).then(res=>{
                        if (res.data.code == 200) {
                            t.NotificationApp.send("提示！", res.data.msg  , "top-right", "rgba(0,0,0,0.2)", "info");
                        } else if (res.data.code == 412) {
                            clearInterval(timeStop);
                            code.disabled = false
                            t.NotificationApp.send("提示！", "此邮箱已注册本站帐号，<a href='reset.html' style='color:red'>点击此处可找回密码</a>" , "top-right", "rgba(0,0,0,0.2)", "info");
                        } else {
                            clearInterval(timeStop);
                            code.disabled = false
                            t.NotificationApp.send("提示！", res.data.msg  , "top-right", "rgba(0,0,0,0.2)", "warning");
                        }
                    })
                }
                
            },
        },
        watch: {
            code_data: {
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
                // deep: true,
            },
        },
    }).mount('#register')

}(window.jQuery)