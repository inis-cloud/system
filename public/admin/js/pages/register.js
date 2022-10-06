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
                if (utils.has.storage('config')) {
                    const config = utils.get.storage('config')
                    const body   = document.querySelector('body')
                    // 夜间模式
                    if (config.mode.theme == 'dark') {
                        body.setAttribute('data-theme-mode', 'dark')
                        utils.set.links('/admin/css/app-dark.min.css', 'link')
                    } else {
                        const links = document.querySelectorAll('link[rel=stylesheet]')
                        links.forEach(item => {
                            if (item.getAttribute('href') == '/admin/css/app-dark.min.css') item.remove()
                        })
                        body.setAttribute('data-theme-mode', 'light')
                    }
                }
            },
            // 提交数据
            save(){
                
                if (this.check()) {
                    
                    POST('/api/comm/register', {
                        email: this.user.email,
                        code : this.code.value,
                        nickname: this.user.nickname,
                        password: this.user.password1
                    }).then(res => {
                        if (res.code == 200) {
                            this.result = res.data
                            Tool.Notyf('注册成功', 'success')
                            t('#fill-signup-modal').modal('show');
                        } else {
                            const code = document.querySelector('#code')
                            code.classList.add('btn-danger')
                            code.classList.remove('btn-dark')
                            code.classList.remove('btn-info')
                            Tool.Notyf(res.msg, 'error')
                        }
                    })
                }
            },
            
            // 校验数据
            check(){
                
                let result = false
                const signup = document.querySelector('#checkbox-signup').checked
                
                if (utils.is.empty(this.user.nickname))       Tool.Notyf('必须填写用户名！', 'warning')
                else if (utils.is.empty(this.user.password1)) Tool.Notyf('请填写密码！', 'warning')
                else if (utils.is.empty(this.user.password2)) Tool.Notyf('请再次填写密码！', 'warning')
                else if (this.user.password1 != this.user.password2) Tool.Notyf('两次密码不一致！', 'warning')
                else if (utils.is.empty(this.user.email))  Tool.Notyf('邮箱不得为空！', 'warning')
                else if (!utils.is.email(this.user.email)) Tool.Notyf('邮箱格式不正确！', 'warning')
                else if (utils.is.empty(this.code.value))  Tool.Notyf('请填写验证码！', 'warning')
                else if (!signup) Tool.Notyf('请先同意用户协议！', 'warning')
                else result = true
                
                return result
            },
            
            // 验证码
            verify(){
                
                if (utils.is.empty(this.user.email))       Tool.Notyf('请先填写邮箱！', 'warning')
                else if (!utils.is.email(this.user.email)) Tool.Notyf('邮箱格式不正确！', 'warning')
                else {
                    
                    let time   = 60;
                    const code = document.querySelector('#code')
                    
                    let timeStop = setInterval(() => {
                        
                        time--;
                        
                        if (time > 0) {
                            this.code.title = '获取' + time + 's'
                            code.disabled = true
                        } else {
                            // 当减到0时赋值为60
                            time = 60
                            this.code.title = '获取'
                            // 清除定时器
                            clearInterval(timeStop)
                            code.disabled = false
                        }
                        
                    }, 1000)
                    
                    POST('/api/verify-code/create', {
                        email: this.user.email,
                    }).then(res=>{
                        if (res.code == 200) Tool.Notyf(res.msg, 'success')
                        else if (res.code == 412) {
                            clearInterval(timeStop)
                            code.disabled = false
                            Tool.Notyf(`此邮箱已注册本站帐号，<a href='reset.html' style='color:red'>点击此处可找回密码</a>`)
                        } else {
                            clearInterval(timeStop)
                            code.disabled = false
                            Tool.Notyf(res.msg, 'error')
                        }
                    })
                }
            },
            
            oneWord(){
                GET('/api/file/words').then(res => {
                    if (res.code == 200) {
                        const result = res.data
                        document.querySelector('.footer .one-word').append(result)
                    }
                })
            }
        },
        watch: {
            code: {
                handler(newValue,oldValue){
                    
                    const self = this
                    
                    const code = document.querySelector('#code')
                    
                    if (!utils.is.empty(newValue)) {
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