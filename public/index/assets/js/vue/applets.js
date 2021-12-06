!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                config: {   // 小程序配置
                    "qq"    :   {"show":{"comments":true}},
                    'wechat':   {"show":{"comments":true}},
                    'other' :   {"show":{"comments":true}}
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
            
            // 初始化默认数据
            initState(){
                
            },
            
            // 获取页面初始化数据
            initData(){
                axios.post('/index/applets').then((res)=>{
                    if(res.data.code == 200){
                        this.config = res.data.data.opt
                    }
                })
            },
            
            // 切换按钮
            iSwitch(){
                
                let params = inisHelper.stringfy({
                    opt: this.config,
                    key: 'config:applets'
                })
                
                axios.post('/index/method/SaveOptions', params).then((res) => {
                    if(res.data.code == 200){
                        $.NotificationApp.send("提示！", "保存成功！", "top-right", "rgba(0,0,0,0.2)", "info");
                    } else $.NotificationApp.send("错误！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "warning");
                    // 刷新数据
                    this.initData()
                })
            },
            
            // 保存数据
            btnSave(){
                
            },
        },
        watch: {
            
        }
        
    }).mount('#applets')

}(window.jQuery);