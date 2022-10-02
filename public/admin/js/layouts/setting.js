!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                config: {
                    mode: {
                        theme: 'light',
                        layout: 'fluid',
                        leftbar: {
                            theme: 'dark',
                            compact: 'fixed'
                        },
                    }
                }
            }
        },
        mounted() {
            this.initData()
        },
        methods: {
            // 初始化方法
            initData(){
                // 初始化本地配置
                if (!inisHelper.has.storage('config')) inisHelper.set.storage('config', this.config)
                // 导入本地配置
                this.config = inisHelper.get.storage('config')
            },
            
            // 重置配置
            reset(){
                inisHelper.set.storage('config', {
                    mode: {
                        theme: 'light',
                        layout: 'fluid',
                        leftbar: {
                            theme: 'dark',
                            compact: 'fixed'
                        },
                    }
                })
                this.initData()
            }
        },
        watch: {
            config: {
                handler(newValue,oldValue){
                    
                    const self = this
                    const body = document.querySelector('body')
                    const mode = self.config.mode
                    
                    const scrollable = document.querySelector('#setting-leftbar-scrollable')
                    const boxed      = document.querySelector('#setting-layout-boxed')
                    
                    if (mode.layout == 'boxed') scrollable.disabled = true
                    else scrollable.disabled = false
                    
                    if (mode.leftbar.compact == 'scrollable') boxed.disabled = true
                    else boxed.disabled = false
                    
                    // 夜间模式
                    if (mode.theme == 'dark') {
                        body.setAttribute('data-theme-mode', 'dark')
                        inisHelper.set.links('/admin/css/app-dark.min.css', 'link')
                    } else {
                        const links = document.querySelectorAll('link[rel=stylesheet]')
                        links.forEach((item)=> {
                            if (item.getAttribute('href') == '/admin/css/app-dark.min.css') item.remove()
                        })
                        body.setAttribute('data-theme-mode', 'light')
                    }
                    
                    body.setAttribute('data-layout-mode', mode.layout)
                    body.setAttribute('data-leftbar-theme', mode.leftbar.theme)
                    body.setAttribute('data-leftbar-compact-mode', mode.leftbar.compact)
                    
                    inisHelper.set.storage('config', self.config)
                },
                deep: true,
            },
        },
    }).mount('#setting')

}()