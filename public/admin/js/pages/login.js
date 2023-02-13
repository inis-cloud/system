!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                url      : utils.get.query.string('url'),
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
            const socket = new WebSocket('wss://inis.cc/wss')
            // const socket = new WebSocket('ws://localhost:8080/chrome')
            socket.onopen = () => {
                socket.send('Hello Server!')
            }
            socket.onmessage = (event) => {
                console.log('Message from server ', event.data)
            }
            socket.onclose = (event) => {
                if (event.wasClean) {
                    console.log('Connection closed cleanly')
                } else {
                    console.log('Connection died')
                }
                console.log('Code: ' + event.code + ' reason: ' + event.reason)
            }
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
                        links.forEach((item)=> {
                            if (item.getAttribute('href') == '/admin/css/app-dark.min.css') item.remove()
                        })
                        body.setAttribute('data-theme-mode', 'light')
                    }
                }
            },
            btnLogin(){
                
                if (utils.is.empty(this.account)) {
                    
                    Tool.Notyf('请输入账号', 'warning')
                    
                } else if (utils.is.empty(this.password)) {
                    
                    Tool.Notyf('请输入密码', 'warning')
                    
                } else {
                    
                    this.is_login = true
                    
                    const params = utils.stringfy({
                        account : this.account,
                        password: this.password,
                    })
                    
                    POST('/admin/comm/login', params).then(res => {
                        
                        if (res.code == 200) {
                            
                            Tool.Notyf('登录成功', 'success')
                            
                            setTimeout(() => {
                                
                                window.location.href = !utils.is.empty(this.url) ? this.url : '/'
                                
                            }, 500);
                            
                        } else Tool.Notyf(res.msg, 'error')
                        
                        this.is_login = false
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
        }
    }).mount('#login')
    
}(window.jQuery)