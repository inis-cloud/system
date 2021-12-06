!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                email_serve: {
                    opt: {},
                    value: {},
                },      // 配置邮件服务
                preview_email_temp:'',// 预览邮箱模板
                other: {              // 其他配置
                    article: {
                        comments: {
                            show : true,
                            allow: true,
                        }
                    },
                    optimize: {
                        cdn: ''
                    }
                },
            }
        },
        components: {
            'i-footer'    : inisTemp.footer(),
            'i-top-nav'   : inisTemp.navbar(),
            'i-left-side' : inisTemp.sidebar(),
            'i-right-side': inisTemp.sidebar('right'),
        },
        mounted() {
            this.initData()
        },
        methods: {
            
            // 获取页面初始化数据
            initData(){
                axios.post('/index/configure').then((res)=>{
                    if(res.data.code == 200){
                        
                        const result = res.data.data
                        
                        // 配置邮箱服务
                        this.setEmail(result)
                        // 设置其他配置
                        this.setOther(result)
                    }
                })
            },
            
            // 保存数据
            btnSave(){
                
                this.email_serve.opt.port    = $('#port-select2').select2('data')[0]['text'];
                this.email_serve.opt.encry   = $('#encry-select2').select2('data')[0]['text'];
                this.email_serve.opt.encoded = $('#encoded-select2').select2('data')[0]['text'];
                
                let params = inisHelper.stringfy({
                    'key':'config:email-serve',
                    opt:this.email_serve.opt,
                    value:JSON.stringify(this.email_serve.value)
                })
                
                axios.post('/index/method/SaveOptions', params).then((res)=>{
                    if(res.data.code == 200){
                        this.initData()
                        $.NotificationApp.send("", "数据保存成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                    }else{
                        $.NotificationApp.send("错误！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "warning");
                    }
                })
            },
            
            // 切换按钮
            iSwitch(){
                
                let params = inisHelper.stringfy({
                    opt: this.other,
                    key: 'config:system'
                })
                
                axios.post('/index/method/SaveOptions', params).then((res) => {
                    if(res.data.code == 200){
                        $.NotificationApp.send("提示！", "保存成功！", "top-right", "rgba(0,0,0,0.2)", "info");
                    } else $.NotificationApp.send("错误！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "warning");
                    // 刷新数据
                    this.initData()
                })
            },
            
            // 配置邮箱服务
            setEmail(result){
                
                this.email_serve = result.email_serve
                
                // select2 端口号 初始化
                let port_data = [{id: 0,text: 25},{id: 1,text: 465},{id: 2,text: 587},{id: 3,text: 2525}]
                port_data.forEach((item)=>{
                    // 设置预选中
                    if(this.email_serve.opt.port == item.text) item.selected = true
                });
                $("#port-select2").select2({
                    data: port_data,
                    minimumResultsForSearch: Infinity
                })
                
                // select2 加密方式 初始化
                let encry_data = [{id: 0,text: 'ssl'},{id: 1,text: 'tls'}]
                encry_data.forEach((item)=>{
                    // 设置预选中
                    if(this.email_serve.opt.encry == item.text) item.selected = true
                });
                $("#encry-select2").select2({
                    data: encry_data,
                    minimumResultsForSearch: Infinity
                })
                
                // select2 邮件编码 初始化
                let encoded_data = [{id: 0,text: 'UTF-8'},{id: 1,text: 'GB2312'}]
                encoded_data.forEach((item)=>{
                    // 设置预选中
                    if(this.email_serve.opt.encoded == item.text) item.selected = true
                });
                $("#encoded-select2").select2({
                    data: encoded_data,
                    minimumResultsForSearch: Infinity
                })
                
                // 设置默认的预览邮箱模板
                this.preview_email_temp = this.email_serve.value.template_1
            },
            
            // 设置其他配置
            setOther(result){
                
                this.other = result.system_config.opt
            },
            
            // 保存邮箱抄送
            SaveEmailCC(){
                
                let email_cc = this.email_serve.opt.email_cc
                
                email_cc = email_cc.split(/[(\r\n)\r\n]+/).join(',');
                
                let params = new FormData()
                params.append('email_cc', email_cc || '')
                
                axios.post('/index/handle/SaveEmailCC', params)
            },
            
            // 设置预览邮箱模板
            setPreview(id){
                if(id == 1) this.preview_email_temp = this.email_serve.value.template_1
                else if(id == 2) this.preview_email_temp = this.email_serve.value.template_2
                else if(id == 3) this.preview_email_temp = this.email_serve.value.template_3
            },
            
            // 测试邮件
            testEmail(){
                
                this.email_serve.opt.port    = $('#port-select2').select2('data')[0]['text'];
                this.email_serve.opt.encry   = $('#encry-select2').select2('data')[0]['text'];
                this.email_serve.opt.encoded = $('#encoded-select2').select2('data')[0]['text'];
                this.email_serve.opt.to_email= this.email_serve.opt.email
                this.email_serve.opt.title   = '测试邮件服务'
                this.email_serve.opt.content = '当您看到这条邮件信息时，表示您的邮件服务配置成功'
                
                let params = inisHelper.stringfy({
                    ...this.email_serve.opt
                })
                
                axios.post('/index/handle/testEmail', params).then(res=>{
                    if (res.data.code == 200) {
                        $.NotificationApp.send("提示", `测试邮件已发送至您的邮箱${this.email_serve.opt.email}`, "top-right", "rgba(0,0,0,0.2)", "info");
                    } else {
                        $.NotificationApp.send("提示", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "warning");
                    }
                })
                
            },
        }
    }).mount('#configure')

}(window.jQuery);