!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                options: {},                // 站点信息
                token: {},                  // token信息
                token_switch: false,        // token验证开关
                token_open_switch: false,   // token API开关
                domain: {},                 // 白名单信息
                domain_switch: false,       // 白名单开关
                master: [],                 // 站长信息
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
            
            initData(){
                this.getOptions()
                this.webmaster()
            },
            
            // 获取站点数据
            getOptions(){
                
                axios.post('/index/options').then((res) => {
                    if(res.data.code == 200){
                        this.options = res.data.data
                        this.token   = res.data.data.token
                        this.domain  = res.data.data.domain
                        // Token 开关
                        if(res.data.data.token.status == 1) this.token_switch = true
                        else this.token_switch = false
                        // Token API 开关
                        if(res.data.data.token.open == 1) this.token_open_switch = true
                        else this.token_open_switch = false
                        // domain 开关
                        if(res.data.data.domain.status.status == 1) this.domain_switch = true
                        else this.domain_switch = false
                    }
                })
                
            },
            
            webmaster(){
                
                axios.post('/index/webmaster').then(res=>{
                    if (res.data.code == 200) {
                        
                        $("#web-master").empty()
                        
                        let master = []
                        this.master= res.data.data
                        
                        this.master.users.forEach(item=>{
                            let push = {id:item.id,text:item.nickname}
                            if (item.id == this.master.info.opt.users_id) push.selected = true
                            master.push(push)
                        })
                        
                        for (let item in this.master.info.opt) this.master[item] = this.master.info.opt[item]
                        
                        // 性别单选框
                        $("#web-master").select2({
                            minimumResultsForSearch: Infinity,
                            data: master
                        })
                    }
                })
            },
            
            saveMaster(){
                
                let master = this.master
                
                delete master.info
                delete master.users
                delete master.value
                
                let users_id = $('#web-master').select2('data')[0]['id']
                
                let params = new FormData
                master.users_id = users_id
                for (let item in master) params.append(item, master[item] || '')
                
                axios.post('/index/method/saveMaster', params).then(res=>{
                    if (res.data.code == 200) {
                        // 关闭 model 窗口
                        $('#fill-master-modal').modal('toggle')
                    }
                })
                
            },
            
            // 保存配置信息
            btnSave(){
                // 删除多余字段
                delete this.options.token
                delete this.options.domain
                
                let params = new FormData()
                    
                for(let item in this.options){
                    params.append(item,this.options[item] || '')
                }
                
                axios.post('/index/method/EditOptions', params).then((res) => {
                    if(res.data.code == 200){
                        $.NotificationApp.send("", "保存成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                    }else{
                        $.NotificationApp.send("修改错误！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "warning");
                    }
                })
            },
            
            // 触发上传事件
            clickUpload(){
                document.querySelector("#btn_file").click()
            },
            
            // 上传头像
            upload(e){
                
                let file  = e.target.files[0];
                
                if(file.size > 5*1024*1024) $.NotificationApp.send("验证错误！", "上传文件不得大于5MB！", "top-right", "rgba(0,0,0,0.2)", "warning");
                else{
                    
                    $.NotificationApp.send("", "正在上传 ...", "top-right", "rgba(0,0,0,0.2)", "info");
                    
                    let params = new FormData();
                    params.append("image", file || '');
                    
                    axios.post("/index/handle/uploadSiteHead", params, {
                        headers: {
                            "Content-Type": "multipart/form-data"
                        }
                    }).then((res) => {
                        if(res.data.code == 200){
                            this.getOptions()
                            e.target.value = ''
                            $.NotificationApp.send("上传成功！", "头像已更新！", "top-right", "rgba(0,0,0,0.2)", "success");
                        }else $.NotificationApp.send("上传失败！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "warning");
                    })
                }
            },
            
            // 保存Token开关信息
            btnSaveToken(){
                
                // 获取当前Token开关状态
                let status = this.token.status
                // Token开关状态取反
                if(status == 1) status = 0;
                else if(status == 0) status = 1;
                
                let params = new FormData()
                params.append('status',status || '')
                
                axios.post('/index/handle/SetToken', params).then((res) => {
                    if(res.data.code == 200){
                        // 更新当前开关状状态
                        if(res.data.data.status == 1){
                            this.token.status = 1
                            this.token_switch = true
                        }else if(res.data.data.status == 0){
                            this.token.status = 0
                            this.token_switch = false
                        }
                        // 更新Token信息
                        this.token.value  = res.data.data.token
                    }
                })
            },
            
            // 允许通过API方式获取Token
            btnSaveTokenOpen(){
                
                // 获取当前Token API开关状态
                let status = this.token.open
                // Token API开关状态取反
                if(status == 1) status = 0;
                else if(status == 0) status = 1;
                
                let params = new FormData()
                params.append('status',status || '')
                
                axios.post('/index/handle/TokenIsOpen', params).then((res) => {
                    if(res.data.code == 200){
                        // 更新当前开关状状态
                        if(res.data.data.status == 1){
                            this.token.open = 1
                            this.token_open_switch = true
                        }else if(res.data.data.status == 0){
                            this.token.open = 0
                            this.token_open_switch = false
                        }
                    }
                })
            },
            
            // 刷新Token
            resetToken(){
                
                let params = new FormData()
                params.append('code',1 || '')
                
                axios.post('/index/handle/ResetToken', params).then((res) => {
                    if(res.data.code == 200){
                        // 更新Token信息
                        this.token.value  = res.data.data.token
                    }else{
                        $.NotificationApp.send(res.data.msg, "请检查网络是否正常！", "top-right", "rgba(0,0,0,0.2)", "warning");
                    }
                })
            },
            
            // 白名单开关
            btnSaveDomain(){
                // 获取当前Token开关状态
                let status = this.domain.status.status
                // Token开关状态取反
                if(status == 1) status = 0;
                else if(status == 0) status = 1;
                
                let params = new FormData()
                params.append('status',status || '')
                
                axios.post('/index/handle/SetDomain', params).then((res) => {
                    if(res.data.code == 200){
                        // 更新当前开关状状态
                        if(res.data.data.status == 1){
                            this.domain.status.status = 1
                            this.domain_switch = true
                        }else if(res.data.data.status == 0){
                            this.domain.status.status = 0
                            this.domain_switch = false
                        }
                    }
                })
            },
            
            // 保存域名白名单
            btnSaveDomainValue(){
                
                let domain = this.domain.value;
                
                domain = domain.split(/[(\r\n)\r\n]+/).join(',');
                
                let params = new FormData()
                params.append('domain',domain || '')
                
                axios.post('/index/method/EditOptions', params).then((res) => {
                    if(res.data.code == 200){
                        $.NotificationApp.send("操作信息！", "保存成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                    }else{
                        $.NotificationApp.send("保存失败！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "warning");
                    }
                })
            }
        }
    }).mount('#options')

}(window.jQuery);