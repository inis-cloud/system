!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                inis_version:1,     // inis版本号
                file_path: null,
                process: 66,        // 过程进度
            }
        },
        components: {
            
        },
        mounted() {
            this.initData()
        },
        methods: {
            // 初始化
            initData() {
                
                axios.post('/install/next').then(res=>{
                    if (res.data.code == 200) {
                        this.inis_version = res.data.data.inis
                    }
                })
                
                axios.get('https://inis.cc/api/download').then(res=>{
                    if (res.data.code == 200) {
                        this.file_path = res.data.data.file
                        this.download(res.data.data.file)
                        this.process = 77
                    } else {
                        t.NotificationApp.send("提示！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "error");
                    }
                }).catch(err=>{
                    t.NotificationApp.send("提示！", "请确认您的域名时候在<a href='//inis.cc/admin/index' target='_blank'>官网</a>的域名授权名单中", "top-right", "rgba(0,0,0,0.2)", "error");
                    setTimeout(()=>{
                        t.NotificationApp.send("提示！", "如果您非正版授权用户，<a href='//inis.cc' target='_blank'>请先购买</a>", "top-right", "rgba(0,0,0,0.2)", "error");
                    }, 3000);
                })
            },
            
            download(file_path = this.file_path){
                
                let params = new FormData
                params.append("file_path", file_path || '')
                
                axios.post('/install/handle/downloadDb', params).then(res=>{
                    if (res.data.code == 200) {
                        this.importDb()
                        this.process = 88
                    }
                })
            },
            
            importDb(){
                axios.post('/install/handle/importDb').then(res=>{
                    if (res.data.code == 200) {
                        document.querySelector(".home").disabled = false
                        t.NotificationApp.send("提示！", "安装完成！", "top-right", "rgba(0,0,0,0.2)", "info");
                        this.process = 100
                    } else t.NotificationApp.send("提示！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "error");
                })
            },
            
        }
    }).mount('#next')

}(window.jQuery)