!function (t) {
    
    const app = Vue.createApp({
        data(){
            return{
                contentEditor:[],       // 编辑器配置
                article: [],            // 页面初始数据
                edit: [],               // 编辑文章数据
                id: '',                 // 文章ID
                tags: [],               // 标签数据
                sorts: [],              // 分类数据
                destroy_model: true,    // 关闭窗口
                emoji: [],              // 表情包数据
                speed: 0,               // 上传进度
                
                insert_color_text: [],         // 插入彩色文字数据
                insert_bg_color_text: [],      // 插入带背景颜色的文字数据
                insert_highlight: [],          // 插入高亮引用
                insert_btn: [],                // 插入按钮
                insert_collapse: [],           // 插入收缩框
                insert_tabs: [],               // 插入tabs
                insert_files: [],              // 插入图片或文件
                
                opt: {                         // opt字段
                    password :  '',
                    comments : {
                        show : true,
                        allow: true
                    }
                },
                pwd_input_show: false,         // 显示设置访问密码框
            }
        },
        components: {
            
        },
        mounted(){
            
            // 获取URL id
            this.id = inisHelper.get.query.string('id')
            
            // 窗口关闭前事件
            this.destroy()
            
            // 获取表情包数据
            axios.get('/admin/json/emoji.json').then((res)=>{
                this.emoji = res.data
                this.initVditor()
            }).catch((err)=>{
                this.emoji = {
                    "+1": "👍",
                    "-1": "👎",
                    "confused": "😕",
                    "eyes": "👀️",
                    "heart": "❤️",
                    "rocket": "🚀️",
                    "smile": "😄",
                    "tada": "🎉️",
                }
                this.initVditor()
            })
            
            // 初始化插入数据
            this.initInsertData()
        },
        methods:{
            
            // 初始化编辑器
            initVditor(){
                
                /* vditor 编辑器配置 */
                this.contentEditor = new Vditor("vditor",{
                    height: 360,
                    minHeight: 500,
                    cdn: '/admin/libs/vditor',
                    placeholder: '写点什么吧！',
                    icon: 'material',           // 图标风格
                    toolbarConfig: {
                        pin: true,              // 固定工具栏
                    },
                    cache: {
                        enable: false,          // 关闭缓存
                    },
                    counter: {
                        enable: true,           // 启用计数器
                    },
                    resize: {
                        enable: true,           // 支持主窗口大小拖拽
                    },
                    preview: {
                        hljs: {
                            enable: true,       // 启用代码高亮
                            lineNumber: true,   // 启用行号
                            // style: 'monokai',   // 样式
                        },
                        markdown:{
                            autoSpace: true,    // 自动空格
                            fixTermTypo: true,  // 自动矫正术语
                            toc: true,          // 插入目录
                            paragraphBeginningSpace: true,  // 首行缩进二字符
                            sanitize: true,     // 启用过滤 XSS
                            // mark: true,         // 	启用 mark 标记
                        }
                    },
                    // 编辑器异步渲染完成后的回调方法
                    after: () => {
                        this.initData(this.id)
                    },
                    ctrlEnter: () => {
                        this.btnSave(this.id)
                    },
                    hint: {
                        emoji: this.emoji,
                    },
                    // 大纲
                    outline: {
                        // enable: true,
                    },
                    upload: {
                        // accept: 'image/jpg, image/jpeg, image/png, image/gif, image/webp, image/gif, audio/*',
                        accept: 'image/*, video/*',
                        multiple: false,
                        // 上传失败自定义方法
                        handler: (files) => {
                            
                            this.contentEditor.tip('上传中...', 2000)
                            
                            let params = new FormData
                            params.append('file', ...files)
                            params.append('mode', 'file')
                            
                            axios.post('/admin/handle/upload', params, {
                                headers: {
                                    "Content-Type": "multipart/form-data"
                                }
                            }).then((res) => {
                                if (res.data.code == 200) {
                                    
                                    let result = res.data.data
                                    if (this.checkFile(result) == 'image') {
                                        this.contentEditor.insertValue(`![](${result})`)
                                    } else if (this.checkFile(result) == 'video') {
                                        this.contentEditor.insertValue(`<video src="${result}" controls>Not Support</video>`)
                                    } else {
                                        this.contentEditor.insertValue(`${result}`)
                                    }
                                    
                                    this.contentEditor.tip('上传完成！', 2000)
                                    
                                } else {
                                    this.contentEditor.tip(res.data.msg, 2000)
                                }
                            })
                        },
                        filename: (name) => {
                            return name.replace(/[^(a-zA-Z0-9\u4e00-\u9fa5\.)]/g, "")
                            .replace(/[\?\\/:|<>\*\[\]\(\)\$%\{\}@~]/g, "")
                            .replace("/\\s/g", "");
                        },
                    },
                    toolbar: [
                      "emoji","headings","bold","italic","strike","link",
                      "|",
                      "list","ordered-list","check","outdent","indent",
                      "|",
                      "quote","line","code","inline-code","insert-before","insert-after",
                      "|",
                      "upload","table",
                      "|",
                      "undo","redo",
                      "|",
                      "export","fullscreen","preview","edit-mode",
                      "|",
                      {
                            hotkey: "",
                            name: "album",
                            tipPosition: "s",
                            tip: "插入相册",
                            className: "right",
                            icon: `<img style="margin: -4px 0 0 -6px;" src='/admin/svg/album.svg' height="16" />`,
                            click: () => {
                                this.contentEditor.insertValue('[album]\n支持Markdown格式和HTML格式的图片\n[/album]')
                            }
                      },
                      {
                            hotkey: "",
                            name: "album",
                            tipPosition: "s",
                            tip: "插入评论可见",
                            className: "right",
                            icon: `<img style="margin: -4px 0 0 -6px;" src='/admin/svg/comments.svg' height="16" />`,
                            click: () => {
                                this.contentEditor.insertValue('[hide]\n此处为评论可见内容\n[/hide]')
                            }
                      },
                      {
                          hotkey: "",
                          name: "doubt",
                          tipPosition: "s",
                          tip: "帮助文档",
                          className: "right",
                          icon: `<img style="height: 14px;margin: -4px 0 0 -6px;" src='/admin/svg/doubt.svg'/>`,
                          click: () => {
                              window.open("https://ld246.com/guide/markdown",'top')
                          }
                      },
                      {
                          hotkey: "⌘S",
                          name: "save",
                          tipPosition: "s",
                          tip: "保存",
                          className: "right",
                          icon: `<img style="height: 22px;margin: -4px 0 0 -6px;" src='/admin/svg/save.svg'/>`,
                          click: () => {
                              this.btnSave(this.id)
                          }
                      },
                      "|",
                      {
                          name: "more",
                          toolbar: [
                              {
                                  name: "insert_tabs",
                                  tipPosition: "s",
                                  tip: "插入tab",
                                  className: "right",
                                  icon: `插入tab`,
                                  click: () => {
                                      this.openModal("insert_tabs")
                                  }
                              },
                              {
                                  name: "insert_btn",
                                  tipPosition: "s",
                                  tip: "插入按钮",
                                  className: "right",
                                  icon: `插入按钮`,
                                  click: () => {
                                      this.openModal("insert_btn")
                                  }
                              },
                              {
                                  name: "insert_collapse",
                                  tipPosition: "s",
                                  tip: "插入收缩框",
                                  className: "right",
                                  icon: `插入收缩框`,
                                  click: () => {
                                      this.openModal("insert_collapse")
                                  }
                              },
                              {
                                  name: "insert_highlight",
                                  tipPosition: "s",
                                  tip: "插入高亮引用",
                                  className: "right",
                                  icon: `插入高亮引用`,
                                  click: () => {
                                      this.openModal("insert_highlight")
                                  }
                              },
                              {
                                  name: "insert_color_text",
                                  tipPosition: "s",
                                  tip: "插入彩色文字",
                                  className: "right",
                                  icon: `插入彩色文字`,
                                  click: () => {
                                      this.openModal("insert_color_text")
                                  }
                              },
                              {
                                  name: "insert_files",
                                  tipPosition: "s",
                                  tip: "插入图片或文件",
                                  className: "right",
                                  icon: `插入图片或文件`,
                                  click: () => {
                                      this.openModal("insert_files")
                                  }
                              },
                              {
                                  name: "import_article",
                                  tipPosition: "s",
                                  tip: "导入Markdown文件",
                                  className: "right",
                                  icon: `导入Markdown文件`,
                                  click: () => {
                                      this.clickUploadMD()
                                  }
                              },
                              {
                                  name: "insert_bg_color_text",
                                  tipPosition: "s",
                                  tip: "插入带背景颜色的文字",
                                  className: "right",
                                  icon: `插入带背景颜色的文字`,
                                  click: () => {
                                      this.openModal("insert_bg_color_text")
                                  }
                              },
                              
                              
                              
                              "both",
                              // "code-theme",
                              // "content-theme",
                              "outline",
                              // "devtools", // 开发者工具
                              "info",
                              "help",
                          ]
                      },
                  ],
                })
            },
            
            // 获取初始化数据
            initData(id = this.id, is_load = false){
                
                let params = new FormData
                params.append('id',id || '')
                
                axios.post('/admin/WriteArticle', params).then((res) => {
                    if(res.data.code == 200){
                        
                        const result = res.data.data
                        
                        this.article = result
                        
                        let auth = [{id:"anyone",text:"公开"},{id:"private",text:"自己可见"},{id:"login",text:"登录可见"},{id:"password",text:"密码可见"}]
                        let show_comments  = [{id:0,text:"显示"},{id:1,text:"不显示"}]
                        let allow_comments = [{id:0,text:"允许"},{id:1,text:"不允许"}]
                        
                        if (!inisHelper.is.empty(id)) {
                            
                            this.edit = result.article
                            // 设置编辑器初始值
                            this.contentEditor.setValue(this.edit.content)
                            
                            // 标签和分类数据转数组
                            if (inisHelper.is.empty(this.article.article.tag_id)) this.tags   = ''
                            else {
                                this.tags  = this.article.article.tag_id.split("|");
                                // 过滤空字段
                                this.tags  = this.tags.filter((s)=>{ return s && s.trim() })
                            }
                            if (inisHelper.is.empty(this.article.article.sort_id)) this.sorts = ''
                            else {
                                this.sorts = this.article.article.sort_id.split("|");
                                // 过滤空字段
                                this.sorts = this.sorts.filter((s)=>{ return s && s.trim() })
                            }
                            
                            $("#article-auth").empty()
                            $("#show-comments").empty()
                            $("#allow-comments").empty()
                            
                            let opt = this.article.article.opt
                            
                            // select2 文章权限 初始化
                            if (!inisHelper.is.empty(opt)) {
                                if (!inisHelper.is.empty(opt.auth)) {
                                    auth.forEach(item=>{
                                        if (item.id == opt.auth) {
                                            item.selected = true
                                            this.pwd_input_show = (item.id === "password") ? true : false
                                        }
                                    })
                                }
                                if (!inisHelper.is.empty(opt.comments)) {
                                    
                                    if (opt.comments.show  == 'true') show_comments[0].selected = true
                                    else show_comments[1].selected  = true
                                    
                                    if (opt.comments.allow == 'true') allow_comments[0].selected = true
                                    else allow_comments[1].selected = true
                                }
                                this.opt = opt
                            }
                        }
                        
                        // 处理分类数据
                        this.article.sort.forEach((item)=>{
                            item.text = item.name
                            delete item.name
                            // 设置预选中
                            if(!inisHelper.is.empty(id) && inisHelper.in.array(item.id,this.sorts)) item.selected = true
                        });
                        // 处理标签数据
                        this.article.tag.forEach((item)=>{
                            item.text = item.name
                            delete item.name
                            // 设置预选中
                            if(!inisHelper.is.empty(id) && inisHelper.in.array(item.id,this.tags)) item.selected = true
                        });
                        // select2 分类 初始化
                        $("#sort-select2").select2({
                            data: this.article.sort
                        })
                        // select2 标签 初始化
                        $("#tag-select2").select2({
                            tags: true,  // 有输入法冲突BUG
                            data: this.article.tag,
                        })
                        
                        // select2 文章权限 初始化
                        $("#article-auth").select2({
                            minimumResultsForSearch: Infinity,
                            data: auth
                        })
                        
                        // select2 显示评论 初始化
                        $("#show-comments").select2({
                            minimumResultsForSearch: Infinity,
                            data: show_comments
                        })
                        // select2 允许评论 初始化
                        $("#allow-comments").select2({
                            minimumResultsForSearch: Infinity,
                            data: allow_comments
                        })
                        
                        $('#article-auth').on('select2:select', (e) => {
                            
                            const data = e.params.data;
                            this.pwd_input_show = (data.id == 'password') ? true : false
                            
                        });
                        
                        if (is_load) $.NotificationApp.send(null, "数据已刷新！", "top-right", "rgba(0,0,0,0.2)", "success");
                    }
                })
            },
            
            // 保存文章数据
            btnSave(id = this.id, jump = false){
                
                // 分类ID
                let sort_id = [];
                
                // 获取分类数据
                for (i=0; i<$("#sort-select2").select2("data").length; i++){
                    let sort_value = $("#sort-select2").select2("data")[i]['id'];
                    sort_id[i]     = sort_value;
                }
                
                // 已有的标签 - 新增的标签
                let [tag_id,tag_name]   = [[],[]];
                
                // 获取标签数据
                for (i=0; i<$("#tag-select2").select2("data").length; i++){
                    
                    let this_id   = $("#tag-select2").select2("data")[i]['id'];
                    let this_text = $("#tag-select2").select2("data")[i]['text'];
                    
                    if (this_id != this_text) tag_id[i]   = this_id;
                    if (this_id == this_text) tag_name[i] = this_text;
                }
                
                // 过滤空字段
                tag_name = tag_name.filter((s)=>{ return s && s.trim() })
                
                let font_count = document.querySelector('span.vditor-counter.vditor-tooltipped.vditor-tooltipped__nw').textContent
                
                this.opt.auth = $("#article-auth").select2("data")[0]['id']
                this.opt.comments = {
                    show  : ($("#show-comments").select2("data")[0]['id']  == 0) ? true : false,
                    allow : ($("#allow-comments").select2("data")[0]['id'] == 0) ? true : false
                }
                
                let params = inisHelper.stringfy({
                    id, tag_id, sort_id,
                    tag_name, font_count,
                    opt         :   this.opt,
                    title       :   this.edit.title,
                    img_src     :   this.edit.img_src,
                    description :   this.edit.description,
                    content     :   this.contentEditor.getValue(),
                })
                
                // 提交数据
                axios.post('/admin/method/SaveArticle', params).then((res) => {
                    if (res.data.code == 200) {
                        
                        $.NotificationApp.send(null, "保存成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                        
                        window.onbeforeunload = () => null;
                        
                        if (jump) setTimeout(()=>{window.location.href = '/admin/ManageArticle'}, 500);
                        
                    } else $.NotificationApp.send(null, "保存失败！", "top-right", "rgba(0,0,0,0.2)", "error");
                })
            },
            
            // 窗口关闭前事件
            destroy(){
                
                window.onbeforeunload = (e) => {
                    
                    e = e || window.event;
                 
                    if (e) {
                        e.returnValue = 'Any string';
                    }
                    return '您正在编辑的数据尚未保存，确定要离开此页吗？';
                }
            },
            
            // 触发上传事件
            clickUpload: () => {
                document.querySelector("#input-file").click()
            },
            
            // 上传图片
            upload(event){
                
                const self = this
                
                /* 单图上传 */
                let file  = event.target.files[0]
                
                let name  = file.name
                name = name.split('.')
                const warning = ['php','js','htm','html','xml','json','bat','vb','exe']
                
                if (file.size > 20 * 1024 * 1024) $.NotificationApp.send(null, "上传文件不得大于20MB！", "top-right", "rgba(0,0,0,0.2)", "warning");
                else if (inisHelper.in.array(name.pop(), warning)){
                    $.NotificationApp.send(null, "请不要尝试提交可执行程序，因为你不会成功！", "top-right", "rgba(0,0,0,0.2)", "error");
                } else {
                    
                    $.NotificationApp.send(null, "正在上传 ...", "top-right", "rgba(0,0,0,0.2)", "info");
                    
                    let params = new FormData
                    params.append("file", file || '')
                    params.append("mode", "article")
                    
                    const config = {
                        headers: { "Content-Type": "multipart/form-data" },
                        onUploadProgress: (speed) => {
                            if (speed.lengthComputable) {
                                let ratio = speed.loaded / speed.total;
                                // 只是上传到后端，后端并未真正保存成功
                                if (ratio < 1) self.speed = ratio
                            }
                        }
                    }
                    
                    axios.post("/admin/handle/upload", params, config).then((res) => {
                        if (res.data.code == 200) {
                            self.speed = 1
                            this.edit.img_src = res.data.data
                            $.NotificationApp.send(null, "上传成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                        } else {
                            self.speed = 0
                            $.NotificationApp.send(null, res.data.msg, "top-right", "rgba(0,0,0,0.2)", "error");
                        }
                    })
                    
                    event.target.value = ''
                }
            },
            
            // 打开模态框
            openModal(value = null){
                $(`#${value}`).modal('show')
            },
            
            // 设置插入数据
            set_insert_data(opt = null, obj = {}) {
                
                // 设置插入彩色文本
                if (opt == "insert_color_text") {
                    let bg_color = {
                        "text-muted"     :  "灰色",
                        "text-dark"      :  "黑色",
                        "text-white"     :  "白色",
                        "text-primary"   :  "紫色",
                        "text-success"   :  "绿色",
                        "text-info"      :  "蓝色",
                        "text-warning"   :  "黄色",
                        "text-danger"    :  "红色",
                    }
                    for (let item in obj) if (item == "class") {
                        this.insert_color_text.class = obj[item]
                        this.insert_color_text.title = bg_color[obj[item]]
                    }
                } else if (opt == "insert_bg_color_text") {  // 插入带背景颜色的文字
                    let bg_color = {
                        "badge-secondary" :  "灰色",
                        "badge-dark"      :  "黑色",
                        "badge-light"     :  "白色",
                        "badge-primary"   :  "紫色",
                        "badge-success"   :  "绿色",
                        "badge-info"      :  "蓝色",
                        "badge-warning"   :  "黄色",
                        "badge-danger"    :  "红色",
                    }
                    for (let item in obj) if (item == "class") {
                        this.insert_bg_color_text.class = obj[item]
                        this.insert_bg_color_text.title = bg_color[obj[item]]
                    }
                } else if (opt == "insert_highlight") {  // 插入高亮引用
                    let text_color = {
                        "text-muted"     :  "灰色",
                        "text-dark"      :  "黑色",
                        "text-white"     :  "白色",
                        "text-primary"   :  "紫色",
                        "text-success"   :  "绿色",
                        "text-info"      :  "蓝色",
                        "text-warning"   :  "黄色",
                        "text-danger"    :  "红色",
                    }
                    let bg_color = {
                        "bg-secondary"   :  "灰色",
                        "alert-secondary":  "淡灰",
                        "alert-light"    :  "白色",
                        "bg-light"       :  "深白",
                        "bg-white"       :  "纯白",
                        "bg-dark"        :  "黑色",
                        "alert-dark"     :  "淡黑",
                        "bg-danger"      :  "红色",
                        "alert-danger"   :  "淡红",
                        "bg-warning"     :  "黄色",
                        "alert-warning"  :  "淡黄",
                        "bg-info"        :  "蓝色",
                        "alert-info"     :  "淡蓝",
                        "bg-success"     :  "绿色",
                        "alert-success"  :  "淡色",
                        "bg-primary"     :  "紫色",
                        "alert-primary"  :  "淡紫",
                    }
                    for (let item in obj) {
                        if (item == "text_color") {
                            this.insert_highlight.text_color = obj[item]
                            this.insert_highlight.text_color_title = text_color[obj[item]]
                        } else if (item == "bg_color") {
                            this.insert_highlight.bg_color = obj[item]
                            this.insert_highlight.bg_color_title = bg_color[obj[item]]
                        }
                    }
                } else if (opt == "insert_btn") {  // 插入按钮
                    let color = {
                        "secondary" :  "灰色",
                        "dark"      :  "黑色",
                        "light"     :  "白色",
                        "primary"   :  "紫色",
                        "success"   :  "绿色",
                        "info"      :  "蓝色",
                        "warning"   :  "黄色",
                        "danger"    :  "红色",
                    }
                    for (let item in obj) if (item == "class") {
                        this.insert_btn.color = obj[item]
                        this.insert_btn.color_title = color[obj[item]]
                    }
                } else if (opt == "insert_tabs") {
                    let text_color = {
                        "text-muted"     :  "灰色",
                        "text-dark"      :  "黑色",
                        "text-white"     :  "白色",
                        "text-primary"   :  "紫色",
                        "text-success"   :  "绿色",
                        "text-info"      :  "蓝色",
                        "text-warning"   :  "黄色",
                        "text-danger"    :  "红色",
                    }
                    for (let item in obj) {
                        if (item == "text_color") {
                            this.insert_tabs.text_color = obj[item]
                            this.insert_tabs.text_color_title = text_color[obj[item]]
                        }
                    }
                }
                
            },
            
            // 插入标签
            insertTag(opt = null) {
                
                let content = null
                
                // 插入彩色文本
                if (opt == "insert_color_text") {
                    
                    // [text class]内容[/text]
                    content = `[text class="${this.insert_color_text.class}"]${this.insert_color_text.text}[/text]\n`
                    
                } else if (opt == "insert_bg_color_text") {  // 插入带背景颜色的文字
                    
                    let bg_mode = $('#insert_bg_color_text-mode-select2').select2('data')[0]['id'];
                    let round   = $('#insert_round_color_text-mode-select2').select2('data')[0]['id'];
                    let lighten = (bg_mode == 1) ? '-lighten' : ''
                    let pill    = (round == 1) ? 'badge-pill' : ''
                    // [tag class]内容[/tag]
                    content = `[tag class="${this.insert_bg_color_text.class}${lighten} ${pill}"]${this.insert_bg_color_text.text}[/tag]\n`
                    
                } else if (opt == "insert_highlight") {  // 插入高亮引用
                
                    // [info class="alert-success"][/info]
                    content = `[info class="${this.insert_highlight.bg_color} ${this.insert_highlight.text_color}"]${this.insert_highlight.text}[/info]\n`
                    
                } else if (opt == "insert_btn") {  // 插入按钮
                
                    let line = $('#insert_outline_btn-select2').select2('data')[0]['id'];
                    let round   = $('#insert_round_btn-select2').select2('data')[0]['id'];
                    let outline = (line == 1) ? '-outline' : ''
                    let pill    = (round == 1) ? ' btn-rounded' : ''
                    let url     = (inisHelper.is.empty(this.insert_btn.url)) ? '' : ` url="${this.insert_btn.url}"`
                    
                    // [btn class url][/btn]
                    content = `[btn class="btn${outline}-${this.insert_btn.color}${pill}"${url}]${this.insert_btn.text}[/btn]`
                    
                } else if (opt == "insert_collapse") {
                    
                    // [collapse]
                    //     [item name="首页" active="true"]这是内容[/item]
                    //     [item name="其他"]这是内容[/item]
                    // [/collapse]
                    
                    let is_active = $('#insert_collapse_active-select2').select2('data')[0]['id'];
                    let active    = (is_active == 1) ? ' active="true"' : ''
                    
                    content = `[collapse]\n\t[item name="${this.insert_collapse.text}"${active}]内容[/item]\n\t[item name="第二收缩框"]内容[/item]\n[/collapse]`
                    
                } else if (opt == "insert_tabs") {
                    
                    // [tabs title class="nav-bordered nav-justified"]
                    //     [item name="tab-name-1" active="true"]
                    //         内容二
                    //     [/item]
                    //     [item name="tab-name-2" class]
                    //         内容二
                    //     [/item]
                    // [/tabs]
                    
                    let id = $('#insert_tabs_mode-select2').select2('data')[0]['id'];
                    let mode = ""
                    
                    if (id == 1) mode = ` class="nav-bordered"`
                    else if (id == 2) mode = ` class="nav-bordered nav-justified"`
                    else if (id == 3) mode = ` class="nav-pills bg-nav-pills nav-justified"`
                    else if (id == 4) mode = ` type="right"`
                    else if (id == 5) mode = ` type="left"`
                    content = `[tabs title="${this.insert_tabs.title || ''}"${mode}]\n\t[item name="${this.insert_tabs.item_title}" class="${this.insert_tabs.text_color}" active="true"]\n\t在这里撰写内容1\n\t[/item]\n\t[item name="${this.insert_tabs.item_title}" class="${this.insert_tabs.text_color}"]\n\t在这里撰写内容2\n\t[/item]\n[/tabs]`
                }
                
                // 在焦点处插入标签
                this.contentEditor.insertValue(content, false)
                // 关闭模态框
                $(`#${opt}`).modal('hide')
            },
            
            // 初始化插入数据
            initInsertData(){
                
                this.insert_color_text = {title: "灰色",class:"text-muted", text:""}
                this.insert_bg_color_text = {title: "紫色",class:"badge-primary", text:""}
                this.insert_highlight = {text_color_title: "黑色",text_color:"text-dark", bg_color_title:"紫色",bg_color:"bg-primary", text:""}
                this.insert_btn = {color_title:"紫色",color:"primary", text:"", url:null}
                this.insert_collapse = {text:"收缩框内容"}
                this.insert_tabs = {text_color_title: "黑色",text_color:"text-dark"}
                
                // 借用方法 - 兼容 bootstrap - jQuery 语法
                axios.get('/admin/json/emoji.json').then(res=>{
                    
                    $("#insert_bg_color_text-mode-select2").select2({
                        minimumResultsForSearch: Infinity,  // 取消搜索
                        data: [{"id":0,"text":"深色"},{"id":1,"text":"浅色"}],
                    })
                    $("#insert_round_color_text-mode-select2").select2({
                        minimumResultsForSearch: Infinity,  // 取消搜索
                        data: [{"id":0,"text":"否"},{"id":1,"text":"是"}],
                    })
                    
                    $("#insert_outline_btn-select2").select2({
                        minimumResultsForSearch: Infinity,  // 取消搜索
                        data: [{"id":0,"text":"实心"},{"id":1,"text":"线条"}],
                    })
                    $("#insert_round_btn-select2").select2({
                        minimumResultsForSearch: Infinity,  // 取消搜索
                        data: [{"id":0,"text":"否"},{"id":1,"text":"是"}],
                    })
                    
                    $("#insert_collapse_active-select2").select2({
                        minimumResultsForSearch: Infinity,  // 取消搜索
                        data: [{"id":0,"text":"否"},{"id":1,"text":"是","selected":true}],
                    })
                    
                    $("#insert_tabs_mode-select2").select2({
                        minimumResultsForSearch: Infinity,  // 取消搜索
                        data: [
                            {"id":0,"text":"默认"},
                            {"id":1,"text":"类型一","selected":true},
                            {"id":2,"text":"类型二"},
                            {"id":3,"text":"类型三"},
                            {"id":4,"text":"类型四"},
                            {"id":5,"text":"类型五"},
                        ]
                    })
                    
                })
            },
            
            // 插入图片或文件
            insertFile(url = this.insert_files.url){
                
                if (!inisHelper.is.empty(url)) {
                    if (this.checkFile(url) == 'image') {
                        this.contentEditor.insertValue(`![](${url})`)
                    } else if (this.checkFile(url) == 'video') {
                        this.contentEditor.insertValue(`<video src="${url}" controls>Not Support</video>`)
                    } else {
                        this.contentEditor.insertValue(`${url}`)
                    }
                }
            },
            
            // 校验文件格式
            checkFile(url = null){
                
                let result  = 'other'
                const image = ['png','jpg','jpeg','gif','webp','svg','ico']
                const video = ['avi','mp4']
                const array = url.split('.')
                const pop   = array.pop()
                
                if (inisHelper.in.array(pop, image)) result = 'image'
                else if (inisHelper.in.array(pop, video)) result = 'video'
                
                return result
            },
            
            // 触发上传事件
            clickUploadMD: () => {
                document.querySelector("#input-md").click()
            },
            
            // 单个文件上传
            uploadMD(event){
                
                const self = this
                const file = event.target.files[0]
                
                $.NotificationApp.send(null, "正在上传 ...", "top-right", "rgba(0,0,0,0.2)", "info");
                
                let params = new FormData
                params.append("file", file || '')
                
                const config = {
                    headers: { "Content-Type": "multipart/form-data" },
                    onUploadProgress: (speed) => {
                        if (speed.lengthComputable) {
                            let ratio = speed.loaded / speed.total;
                        }
                    }
                }
                
                axios.post("/admin/handle/readFile", params, config).then((res) => {
                    
                    if (res.data.code == 200) {
                        
                        const result    = res.data.data
                        // 文章标题
                        this.edit.title = result.name
                        // 文章内容
                        this.contentEditor.setValue(result.content)
                        
                        $.NotificationApp.send(null, "导入成功！", "top-right", "rgba(0,0,0,0.2)", "success");
                        
                    } else $.NotificationApp.send(null, res.data.msg, "top-right", "rgba(0,0,0,0.2)", "error");
                    
                    event.target.value = ''
                })
            },
            
            // 触发上传
            runUpload(){
                document.querySelector("#upload-files").click()
            },
            
            // 上传文件
            uoloadFiles(event){
                
                const files = event.target.files
                
                $('#insert_files').modal('hide')
                
                for (let item of files) {
                    
                    const params = new FormData
                    params.append('file', item)
                    params.append('mode', 'file')
                    
                    axios.post('/admin/handle/upload', params, {
                        headers: {
                            "Content-Type": "multipart/form-data"
                        }
                    }).then((res) => {
                        if (res.data.code == 200) {
                            
                            let result = res.data.data
                            
                            if (this.checkFile(result) == 'image') {
                                this.contentEditor.insertValue(`![](${result})`)
                            } else if (this.checkFile(result) == 'video') {
                                this.contentEditor.insertValue(`<video src="${result}" controls>Not Support</video>`)
                            } else {
                                this.contentEditor.insertValue(`${result}`)
                            }
                            
                            this.contentEditor.tip('上传完成！', 2000)
                            
                        } else {
                            this.contentEditor.tip(res.data.msg, 2000)
                        }
                    })
                }
                event.target.value = ''
            }
        },
        
    }).mount('#write-article')
    
}(window.jQuery)