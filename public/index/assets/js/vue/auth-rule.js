!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                title: '',      // 模态框标题
                edit:  '',      // 编辑
                attri: [],      // 归属
                auth_rule: [],  // 规则数据
            }
        },
        components: {
            'i-footer'    : inisTemp.footer(),
            'i-top-nav'   : inisTemp.navbar(),
            'i-left-side' : inisTemp.sidebar(),
            'i-right-side': inisTemp.sidebar('right'),
        },
        mounted() {
            this.title = '添加规则'
            this.watch()
            this.getData()
            this.getMethods()
        },
        methods: {
            getData(){
                
                const self = this
                let $table = $('#table');
                
                let params = new FormData
                params.append('id', this.edit.id || '')
                axios.post('/index/AuthRule', params).then(res => {
                    if (res.data.code == 200) {
                        this.attri = res.data.data.attri
                        this.auth_rule = res.data.data.auth_rule
                        
                        this.attri.forEach((item)=>{
                            item.text = item.title
                            delete item.title
                        })
                        
                        // 归属选择框
                        $("#attri-select2").select2({
                            minimumResultsForSearch: Infinity,
                            data:this.attri
                        })
                        // 控制器选择框
                        $("#controller-select2").select2({
                            minimumResultsForSearch: Infinity,
                            data: [{"id":"Index","text":"访问页面"},{"id":"Method","text":"新增修改或删除"},{"id":"Handle","text":"其他"},{"id":"FileSystem","text":"文件系统"}],
                        })
                    }
                    console.log(self.auth_rule)
                })
             
                $(function(){
                    //控制台输出一下数据
                    $table.bootstrapTable({
                        data: self.auth_rule,
                        idField: 'id',
                        dataType:'jsonp',
                        columns: [
                            { field: 'check',  checkbox: true, formatter: function (value, row, index) {
                                    if (row.check == true) {
                                        // console.log(row.serverName);
                                        // 设置选中
                                        return {  checked: true };
                                    }
                                }
                            },
                            { field: 'title' ,  title: '名称' },
                            { field: 'route', title: '路由'  },
                            { field: 'create_time',  title: '时间', sortable: true,  align: 'center'},
                            { field: 'operate', title: '操作', align: 'center', events : operateEvents, formatter: operateFormatter() },
                        ],
                        
                        // bootstrap-table-treegrid.js 插件配置 -- start
                        
                        // 在哪一列展开树形
                        treeShowField: 'title',
                        // 指定父id列
                        parentIdField: 'pid',
                        
                        onResetView: (data) => {
                            //console.log('load');
                            $table.treegrid({
                                // 所有节点都折叠
                                initialState: 'collapsed',
                                // 所有节点都展开，默认展开
                                // initialState: 'expanded',
                                treeColumn: 1,
                                // 展开图标样式
                                // expanderExpandedClass: 'glyphicon glyphicon-minus',
                                // 折叠图标样式
                                // expanderCollapsedClass: 'glyphicon glyphicon-plus',
                                onChange: () => {
                                    $table.bootstrapTable('resetWidth');
                                }
                            });
                            
                            // 只展开树形的第一级节点
                            // $table.treegrid('getRootNodes').treegrid('expand');
                        },
                        onCheck: (row) => {
                            let datas = $table.bootstrapTable('getData');
                            // 勾选子类
                            self.selectChilds(datas,row,"id","pid",true);
                            // 勾选父类
                            self.selectParentChecked(datas,row,"id","pid")
                            // 刷新数据
                            $table.bootstrapTable('load', datas);
                        },
                        onUncheck: (row) => {
                            let datas = $table.bootstrapTable('getData');
                            self.selectChilds(datas,row,"id","pid",false);
                            $table.bootstrapTable('load', datas);
                        },
                        // bootstrap-table-treetreegrid.js 插件配置 -- end
                    });
                });
             
                // 格式化按钮
                function operateFormatter(value, row, index) {
                    return [
                        '<button type="button" class="update btn btn-outline-info mr-2">修改</button>',
                        '<button type="button" class="delete btn btn-outline-danger mr-2">删除</button>'
                    ].join('');
                    
                }
                
                // 初始化操作按钮的方法
                let operateEvents = {
                    'click .update': function (e, value, row, index) {
                        console.log(row.id);
                    },
                    'click .delete': function (e, value, row, index) {
                        console.log(row.id);
                    },
                };
            },
            
            save(){
                const attri = $("#attri-select2").select2('data')[0]['id']
                const methods = $("#methods-select2").select2('data')[0]['id']
                const controller = $("#controller-select2").select2('data')[0]['id']
                const route = '/' + controller + '/' + methods
                
                if (inisHelper.is.empty(this.edit.title)) {
                    $.NotificationApp.send("提示！", "请填写规则名称！", "top-right", "rgba(0,0,0,0.2)", "warning");
                } else {
                    
                    let params = new FormData
                    params.append('id', this.edit.id || '')
                    params.append('title', this.edit.title)
                    params.append('pid', attri || 0)
                    params.append('route', route)
                    
                    axios.post('/index/method/SaveAuthRule', params).then(res=>{
                        console.log(res.data)
                    })
                }
            },
            
            getMethods(className = 'Index'){
                let params = new FormData
                params.append('class', className || 'Index')
                axios.post('/index/handle/GetMethods',params).then(res=>{
                    if (res.data.code == 200) {
                        let methods = res.data.data
                        $("#methods-select2").select2({
                            minimumResultsForSearch: Infinity,
                            data: methods,
                        })
                    }
                })
            },
            
            watch(){
                let self = this
                $(()=>{
                    $("#controller-select2").on("select2:select", (e)=>{
                        let data = e.params.data;
                        $('#methods-select2').empty()
                        self.getMethods(data.id)
                    });
                })
            },
            
            // 选中子类
            selectChilds(datas,row,id,pid,checked) {
                for(var i in datas){
                    if(datas[i][pid] == row[id]){
                        datas[i].check=checked;
                        this.selectChilds(datas,datas[i],id,pid,checked);
                    };
                }
            },
            
            // 选中父类
            selectParentChecked(datas,row,id,pid){
                for(var i in datas){
                    if(datas[i][id] == row[pid]){
                        datas[i].check=true;
                        this.selectParentChecked(datas,datas[i],id,pid);
                    };
                }
            }
        }
    }).mount('#auth-rule')

}(window.jQuery);