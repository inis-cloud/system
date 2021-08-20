!function(){
    
    const app = Vue.createApp({
        data() {
            return {
                count: [],
                populars: {},
                system_cache: [],       // 系统缓存数据
                system_cache_total: 0,  // 缓存总大小
                selectedCache: 0,       // 选中的缓存大小
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
            
            // 初始化
            initData(){
                axios.post('/index/index').then((res) => {
                    if (res.data.code == 200) {
                        this.count    = res.data.data.count
                        this.populars = res.data.data.popular
                    }
                })
                this.getRuntime()
            },
            
            // 获取运行缓存
            getRuntime(){
                
                const select = document.querySelectorAll(".checkbox-cache")
                for (let item of select) if (item.checked) item.checked = false
                
                axios.post('/index/chart/SystemCache').then(res=>{
                    if (res.data.code == 200) {
                        
                        let result = res.data.data
                        this.system_cache = []
                        this.system_cache_total = 0
                        this.selectedCache = 0
                        
                        for (let item in result) {
                            
                            let [name,value,size,file_count,description] = [null,null,null,null,null]
                            
                            if (item == 'api') {
                                name = 'api日志'
                                description = '将清理此前的所有api访问日志'
                            } else if (item == 'index') {
                                name = '后台日志'
                                description = '将清理此前的所有后台访问日志'
                            } else if (item == 'cache') {
                                name = '缓存'
                                description = '清理之后，将更新API缓存数据'
                            } else if (item == 'log') {
                                name = '其他'
                                description = '将清理此前的所有其他日志'
                            } else if (item == 'session') {
                                name = 'PHP会话'
                                description = '清理后，目前已登录站点的用户将需要重新登录'
                            } else if (item == 'install') {
                                name = '安装引导'
                                description = '将清理此前的所有安装引导访问日志'
                            } else name = item
                            
                            size  = result[item].size
                            value = Math.round(size / 1024)
                            file_count = result[item].file_count
                            
                            this.system_cache_total += size
                            this.system_cache.push({item,name,value,size,file_count,description})
                        }
                        this.systemCache()
                    }
                })
            },
            
            // 绘制系统缓存图
            systemCache(){
                
                // 基于准备好的dom，初始化echarts实例
                let chart  = echarts.init(document.getElementById('system-cache'));
                
                // 指定图表的配置项和数据
                let option = {
                        tooltip: {
                            trigger: 'item',
                            formatter: '{b}：\n{c} KB',
                        },
                        legend: {
                            orient: 'vertical',
                            left: 'left',
                        },
                        series: [
                            {
                                name: '占用',
                                type: 'pie',
                                radius: ['40%', '70%'],
                                avoidLabelOverlap: false,
                                itemStyle: {
                                    borderRadius: 10,
                                    borderColor: '#fff',
                                    borderWidth: 2
                                },
                                label: {
                                    show: false,
                                    position: 'center'
                                },
                                emphasis: {
                                    label: {
                                        show: true,
                                        fontSize: '40',
                                        fontWeight: 'bold'
                                    },
                                },
                                labelLine: {
                                    show: false
                                },
                                data: this.system_cache
                            }
                        ]
                    };
                
                // 使用刚指定的配置项和数据显示图表。
                chart.setOption(option);
            },
            
            // 计算大小
            computedBytes(value = 0){
                return inisHelper.format.bytes(value)
            },
            
            // 计算已选中的日志大小
            computedSelected(){
                
                let [total,check] = [0,[]]
                const select = document.querySelectorAll(".checkbox-cache")
                
                for (let item of select) {
                    if (item.checked) check.push(parseInt(item.getAttribute("sizes")))
                }
                
                check.forEach(item=>total+=item)
                this.selectedCache = this.computedBytes(total)
            },
            
            // 清理缓存
            clearCache() {
                
                let check    = []
                const select = document.querySelectorAll(".checkbox-cache")
                
                for (let item of select) {
                    if (item.checked) check.push(item.getAttribute("name"))
                }
                
                if (inisHelper.is.empty(check)) $.NotificationApp.send("提示！", "未选择清理选项", "top-right", "rgba(0,0,0,0.2)", "warning");
                else {
                    
                    let params = new FormData
                    params.append("file_name", check.join() || '')
                    
                    axios.post('/index/handle/clearCache', params).then(res=>{
                        if (res.data.code == 200) {
                            this.getRuntime()
                        }
                    })
                }
            },
        },
    }).mount('#index')

}()