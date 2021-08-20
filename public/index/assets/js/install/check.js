!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                php: [],       // PHP 依赖
                MySQL: [],     // MySQL 依赖
                sql: [],       // 数据库配置
                env_check: false,   // env通过
                libs_check: false,  // 校验通过
                deploy: false, // 配置
                ico:[],        // 图标
                inis_version:1,// inis版本号
            }
        },
        components: {
            
        },
        mounted() {
            this.initData()
            this.sql = {
                'HOSTNAME': 'localhost',
                'HOSTPORT': 3306,
                'DATABASE': 'inis',
                'USERNAME': 'root',
                'PASSWORD': ''
            }
        },
        methods: {
            // 初始化
            initData() {
                
                axios.post('/install').then(res=>{
                    if (res.data.code == 200) {
                        this.php   = res.data.data.php
                        this.MySQL = res.data.data.MySQL
                        this.inis_version = res.data.data.inis
                    }
                })
                
                const success = `<svg t="1625540637066" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2051" width="24" height="24"><path d="M514 912c-219.9 0-398.8-178.9-398.8-398.9 0-219.9 178.9-398.8 398.8-398.8s398.8 178.9 398.8 398.8c0 220-178.9 398.9-398.8 398.9z m0-701.5c-166.9 0-302.7 135.8-302.7 302.7S347.1 815.9 514 815.9c166.9 0 302.7-135.8 302.7-302.7S680.9 210.5 514 210.5z" fill="#BDD2EF" p-id="2052"></path><path d="M239.4 486.2l59.7-38.8c57.1-37.1 134.1-20.7 169.7 37.3 25.8 41.9 36.4 83.5 36.4 83.5s137.1-308.8 327.7-366.7c0 0-59.5 135.9 27.3 234 0 0-197.8 55.1-344.1 286.2-4.9 7.8-16.3 7.6-20.8-0.4-29.1-50.5-120.7-192.4-255.9-235.1z" fill="#0acf97" p-id="2053" data-spm-anchor-id="a313x.7781069.0.i6" class=""></path></svg>`
                const error   = `<svg t="1625540436425" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1855" width="24" height="24"><path d="M514 912c-219.9 0-398.8-178.9-398.8-398.8S294.1 114.4 514 114.4s398.8 178.9 398.8 398.8S733.9 912 514 912z m0-701.5c-166.9 0-302.7 135.8-302.7 302.7 0 166.9 135.8 302.7 302.7 302.7s302.7-135.8 302.7-302.7c0-166.9-135.8-302.7-302.7-302.7z" fill="#BDD2EF" p-id="1856"></path><path d="M593.3 513.2l89.3-89.3c21.8-21.8 21.8-57.5 0-79.3-21.8-21.8-57.5-21.8-79.3 0L514 433.9l-89.3-89.3c-21.8-21.8-57.5-21.8-79.3 0-21.8 21.8-21.8 57.5 0 79.3l89.3 89.3-89.3 89.3c-21.8 21.8-21.8 57.5 0 79.3 21.8 21.8 57.5 21.8 79.3 0l89.3-89.3 89.3 89.3c21.8 21.8 57.5 21.8 79.3 0 21.8-21.8 21.8-57.5 0-79.3l-89.3-89.3z" fill="#fa5c7c" p-id="1857" data-spm-anchor-id="a313x.7781069.0.i1" class=""></path></svg>`
                
                this.ico      = {success, error}
            },
            
            // 生成配置
            env() {
                
                if (inisHelper.is.empty(this.sql.DATABASE)) t.NotificationApp.send("提示！", "数据库名不能为空！", "top-right", "rgba(0,0,0,0.2)", "warning");
                else if (inisHelper.is.empty(this.sql.USERNAME)) t.NotificationApp.send("提示！", "数据库用户名不能为空！", "top-right", "rgba(0,0,0,0.2)", "warning");
                else if (inisHelper.is.empty(this.sql.PASSWORD)) t.NotificationApp.send("提示！", "数据库用户密码不能为空！", "top-right", "rgba(0,0,0,0.2)", "warning");
                else {
                    
                    this.deploy = true
                    
                    let params = new FormData
                    for (let item in this.sql) params.append(item, this.sql[item] || '')
                    
                    axios.post('/install/handle/createEnv', params).then(res=>{
                        if (res.data.code == 200) {
                            this.initData()
                            this.deploy    = false
                            this.env_check = true
                            this.check()
                        }
                    })
                    
                }
                
            },
            
            next(){
                if (!this.php.check) t.NotificationApp.send("提示！", "PHP版本不符合要求！", "top-right", "rgba(0,0,0,0.2)", "warning");
                else if (!this.MySQL.check) t.NotificationApp.send("提示！", "数据库版本不符合要求！", "top-right", "rgba(0,0,0,0.2)", "warning");
                else if (this.libs_check) {
                    window.location.href = '/install/next'
                }
            },
            
            check(){
                
                let array = []
                let result= true
                let check = (this.php.check && this.MySQL.check) ? true : false
                array.push(check)
                array.push(this.env_check)
                
                for (let item in this.sql) {
                    array.push(!inisHelper.is.empty(this.sql[item]))
                }
                
                for (let item in array) if(!array[item]) result = false;
                
                this.libs_check = result
            }
        },
        watch: {
            // 上传队列
            sql: {
                handler(newValue,oldValue){
                    this.check()
                },
                immediate: true,
                deep: true,
            },
        }
    }).mount('#check')

}(window.jQuery)