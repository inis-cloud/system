!function(){
    
    const app = Vue.createApp({
        data() {
            return {
                count: [],
                populars: {},
                system: {
                    cache: {
                        data:  [],
                        total: 0,
                        name:  [],
                        size:  [],
                        color: ["#727cf5", "#0acf97", "#fa5c7c", "#ffbc00", "#2c8ef8", "#eef2f7", "#6c757d", "#313a46"],
                    }
                },
                selectedCache: 0,       // 选中的缓存大小
                load: {
                    cache: false
                },
                comments: {},
            }
        },
        components: {
            
        },
        mounted() {
            this.initData()
        },
        methods: {
            
            // 初始化
            initData(){
                axios.post('/admin/home').then((res) => {
                    if (res.data.code == 200) {
                        this.count    = res.data.data.count
                        this.populars = res.data.data.popular
                    }
                })
                this.visitChart()
                this.getRuntime()
                this.getComments()
            },
            
            getComments(){
                axios.get('/admin/api/comments', {
                    params: {limit:10}
                }).then(res=>{
                    if (res.data.code == 200) {
                        const result = res.data.data
                        this.comments= result
                    }
                })
            },
            
            // 获取运行缓存
            getRuntime(){
                
                const select = document.querySelectorAll(".checkbox-cache")
                for (let item of select) if (item.checked) item.checked = false
                
                axios.post('/admin/chart/SystemCache').then(res=>{
                    if (res.data.code == 200) {
                        
                        let result = res.data.data
                        this.system.cache = {
                            data:  [],
                            total: 0,
                            name:  [],
                            size:  [],
                            color: ["#727cf5", "#0acf97", "#fa5c7c", "#ffbc00", "#2c8ef8", "#eef2f7", "#6c757d", "#313a46"],
                        }
                        this.selectedCache = 0
                        
                        for (let item in result) {
                            
                            let [name,value,size,file_count,description] = [null,null,null,null,null]
                            
                            if (item == 'api') {
                                name = 'api日志'
                                description = '将清理此前的所有api访问日志'
                            } else if (item == 'index') {
                                name = '前台日志'
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
                            } else if (item == 'admin') {
                                name = '后台日志'
                                description = '将清理此前的所有后台访问日志'
                            } else name = item
                            
                            size  = result[item].size
                            value = Math.round(size / 1024)
                            file_count = result[item].file_count
                            
                            this.system.cache.total += size
                            this.system.cache.data.push({item,name,value,size,file_count,description})
                            this.system.cache.name.push(name)
                            this.system.cache.size.push(size)
                        }
                        this.systemCache()
                    }
                })
            },
            
            // 绘制系统缓存图
            systemCache(){
                
                const self = this
                
                const option = {
                    chart: {
                        height: 208,
                        type: "donut"
                    },
                    legend: {
                        show: !1
                    },
                    stroke: {
                        colors: ["transparent"]
                    },
                    tooltip: {
                        y: {
                            formatter: (e) => self.computedBytes(e)
                        }
                    },
                    series: this.system.cache.size,
                    labels: this.system.cache.name,
                    colors: this.system.cache.color,
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 200
                            },
                            legend: {
                                position: "bottom"
                            }
                        }
                    }]
                };
                
                new ApexCharts(document.querySelector("#system-cache"), option).render()
            },
            
            // 绘制访问图表
            visitChart(){
                const option = {
                    chart: {
                        height: 257,
                        type: "bar",
                        stacked: !0
                    },
                    plotOptions: {
                        bar: {
                            horizontal: !1,
                            columnWidth: "20%"
                        }
                    },
                    dataLabels: {
                        enabled: !1
                    },
                    stroke: {
                        show: !0,
                        width: 2,
                        colors: ["transparent"]
                    },
                    series: [{
                        name: "Actual",
                        data: [65, 59, 80, 81, 56, 89, 40, 32, 65, 59, 80, 81]
                    },
                    {
                        name: "Projection",
                        data: [89, 40, 32, 65, 59, 80, 81, 56, 89, 40, 65, 59]
                    }],
                    zoom: {
                        enabled: !1
                    },
                    legend: {
                        show: !1
                    },
                    colors: ["#727cf5", "#e3eaef"],
                    xaxis: {
                        categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                        axisBorder: {
                            show: !1
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: function(item) {
                                return item + "k"
                            },
                            offsetX: -15
                        }
                    },
                    fill: {
                        opacity: 1
                    },
                    tooltip: {
                        y: {
                            formatter: function(item) {
                                return "$" + item + "k"
                            }
                        }
                    }
                }
                new ApexCharts(document.querySelector("#high-performing-product"), option).render();
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
                
                if (inisHelper.is.empty(check)) $.NotificationApp.send(null, "未选择清理选项", "top-right", "rgba(0,0,0,0.2)", "warning");
                else {
                    
                    this.load.cache = true
                    
                    let params = new FormData
                    params.append("file_name", check.join() || '')
                    
                    axios.post('/admin/handle/clearCache', params).then(res=>{
                        if (res.data.code == 200) {
                            this.getRuntime()
                        }
                        this.load.cache = false
                    })
                }
            },
            
            // 根据日期获取当前星期
            getWeek(string){
                
                let date = null
                
                if (inisHelper.is.empty(string)) date = new Date;
                else {
                    let array = string.split("-");
                    date = new Date(array[0], parseInt(array[1] - 1), array[2]);
                }
                
                return ["周日", "周一", "周二", "周三", "周四", "周五", "周六"][date.getDay()];
            },
            
            // 时间戳转人性化时间
            natureTime: (value, bool = false) => {
                
                return (bool) ? inisHelper.time.nature(value) : inisHelper.time.nature(inisHelper.date.to.time(value))
            },
        },
    }).mount('#home')

}()