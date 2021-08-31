!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                path: './',             // 当前路径
                dir_info:[],            // 文件数据
                mouse_menu: [],         // 右键菜单数据
                mouse_menu_type: false, // 鼠标菜单模式
                edit_data: [],          // 编辑数据
                file_info: [],          // 文件详情信息
                file_other: [],         // 文件其他信息
                add_file_name: '',      // 新建文件或文件夹名称
                default_ico: '',        // 默认的文件图标
                add_file_title: '',     // 新建文件或文件夹标题
                add_file_type: true,    // true = 文件; false = 文件夹
                show_path_array: true,  // 显示数组模式的路径
                
                preview: [],            // 上传预览队列
                preview_length: 0,      // 剩余文件数量
                upload_is_show: false,  // 是否显示上传
                uploads_status: false,  // 上传状态
                await_uploads: [],      // 等待上传的队列
                queue_uploads: [],      // 正在上传的队列
                await_count: 3,         // 允许同步上传数量
                uploads_id : 0,         // 用于辨别上传文件的唯一性
                uplpading_id: [],       // 记录已经开始或完成上传的文件ID，避免重复上传
                
                aceEditor: [],          // 编辑器
                file_data: "",          // 文件数据
                loading: {
                    read_file: false,   // 读取文件动画
                    save_file: false,   // 保存编辑文件动画
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
            // 页面初始化加载事件
            window.onload = this.load()
            // 获取初始化数据
            this.getData()
            this.dialogbox()
            // 初始化默认的数据
            this.add_file_title = '新建文件'
            this.default_ico    = '/index/assets/svg/filesystem/other.svg'
        },
        methods: {
            
            // 获取数据
            getData(name = '', type = false, load = false){
                
                let path = ''
                
                if (!type) {
                    // 获取 cookie 中的文件系统路径
                    path = inisHelper.get.cookie('filesystem-path')
                    path = !inisHelper.is.empty(path) ? path : this.path
                    path = inisHelper.is.empty(name)  ? path : path + name + '/'
                } else {
                    path = (name.substr(name.length - 1, name.length) != '/') ? name + '/' : name
                }
                
                let params = new FormData
                params.append('path', path)
                
                axios.post('/index/file/getDir', params).then(res=>{
                    if (res.data.code == 200) {
                        this.dir_info = res.data.data
                        inisHelper.set.cookie('filesystem-path',this.dir_info.path,5)
                        if (load == true) $.NotificationApp.send("", "刷新成功！", "top-right", "rgba(0,0,0,0.2)", "info");
                        let [path, array, list] = ['', [], this.dir_info.path.split('/')]
                        list.forEach((item, index)=>{
                            if (!inisHelper.is.empty(item)) {
                                path += item + '/'
                                array.push({item, path})
                            }
                        })
                        this.dir_info.array = array
                        this.show_path_array = true
                    }
                })
            },
            
            // 单击事件
            clickFile(name,type) {
                // 目录
                if (type == 'dir') {
                    this.getData(name)
                    // 去除框选
                    this.clearBoxSelect()
                } else {  // 文件
                    // console.log(name,type)
                }
            },
            
            // 双击事件
            dblclick(name = this.mouse_menu.file_name, type = this.mouse_menu.type){
                // 文件
                if (type == 'file') {
                    
                    this.loading.read_file = true
                    let params = new FormData
                    let path   = this.dir_info.path
                    
                    params.append("path", path + name)
                    
                    axios.post("/index/file/read", params).then(res=>{
                        if (res.data.code == 200) {
                            this.file_data = res.data.data
                            this.openDialog()
                        } else $.NotificationApp.send("错误！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "warning");
                        this.loading.read_file = false
                    })
                }
            },
            
            // 路径跳转
            toPath() {
                // 获取 cookie 中的文件系统路径
                let path = inisHelper.get.cookie('filesystem-path')
                // 数组去空
                let arr_path = inisHelper.trim.array(path.split('/'))
                // 去除最后一个数组元素
                if (arr_path.length >= 2) arr_path.pop()
                // 数组转字符串 得到上一级路径
                path = arr_path.join('/') + '/'
                this.getData(path,true)
                // 去除框选
                this.clearBoxSelect()
            },
            
            // 编辑文件名称
            getEditName() {
                this.edit_data = this.mouse_menu
                this.edit_data.old_file_name = this.mouse_menu.file_name
            },
            
            // 重命名
            editName(){
                
                let params = new FormData
                params.append('old_name', this.edit_data.old_file_name)
                params.append('new_name', this.edit_data.file_name)
                params.append('path',this.dir_info.path)
                
                // $.NotificationApp.send("提示！", "开发期间，禁止此操作！", "top-right", "rgba(0,0,0,0.2)", "info");
                
                axios.post('/index/file/editName', params).then(res=>{
                    if (res.data.code == 200) this.getData()
                })
            },
            
            // 文件详情信息
            viewDetails(){
                
                this.edit_data = this.mouse_menu
                
                if (inisHelper.is.empty(this.edit_data)) this.edit_data = {'ico':'/index/assets/svg/filesystem/system.svg','file_name':'当前目录'}
                
                let params = new FormData
                params.append('type',this.mouse_menu.type || '')
                params.append('path',this.dir_info.path   || '')
                params.append('file',this.mouse_menu.file_name || '')
                
                axios.post('/index/file/fileInfo', params).then(res=>{
                    if (res.data.code == 200) {
                        
                        this.file_info  = res.data.data.size
                        this.file_other = res.data.data.other
                        
                        this.file_info.size   = inisHelper.format.bytes(this.file_info.size)
                        this.file_other.atime = inisHelper.time.to.date(this.file_other.atime)
                        this.file_other.ctime = inisHelper.time.to.date(this.file_other.ctime)
                        this.file_other.mtime = inisHelper.time.to.date(this.file_other.mtime)
                        this.file_other.perms = this.file_other.perms.toString(8).slice(2)
                        this.file_other.perms = this.file_other.perms.substring(this.file_other.perms.length-3)
                    }
                })
            },
            
            // 设置新建文件或文件夹标题
            setAddFileTitle(title,ico,type){
                
                title    = title || '新建文件'
                ico      = ico   || 'other.svg'
                type     = type  || true
                let path = '/index/assets/svg/filesystem/'
                
                this.add_file_type  = (type == true) ? true : false
                this.default_ico    = path + ico
                this.add_file_title = title
            },
            
            // 新增文件或文件夹
            addFile(){
                
                let file = (this.add_file_type == true) ? '新建文件' : '新建文件夹'
                if (inisHelper.is.empty(this.add_file_name)) this.add_file_name = (this.add_file_type == true) ? '新建文件' : '新建文件夹'
                
                let params = new FormData
                params.append('file', this.add_file_name || file)
                params.append('path', this.dir_info.path || './')
                params.append('type', this.add_file_type || false)
                
                axios.post('/index/file/addFile', params).then(res=>{
                    if (res.data.code == 200) {
                        this.getData()
                        this.add_file_name = ''
                        $.NotificationApp.send("", "创建成功！", "top-right", "rgba(0,0,0,0.2)", "info");
                    }
                })
            },
            
            // 删除文件或文件夹
            delFile(){
                
                /* ============ 确认删除的机制还没做 ============ */
                
                let params = new FormData
                params.append('file', this.mouse_menu.file_name || '')
                params.append('type', this.mouse_menu.type      || '')
                params.append('path', this.dir_info.path        || '')
                
                // $.NotificationApp.send("提示！", "开发期间，禁止此操作！", "top-right", "rgba(0,0,0,0.2)", "info");
                
                axios.post('/index/file/delFile', params).then(res=>{
                    if (res.data.code == 200) {
                        this.getData()
                        $.NotificationApp.send("", "删除成功！", "top-right", "rgba(0,0,0,0.2)", "info");
                    }
                })
            },
            
            // 阻止冒泡事件
            bubbling(val){
                // 阻止冒泡事件
                window.event ? window.event.cancelBubble = true : event.stopPropagation();
            },
            
            // 过滤子文件夹名称
            filterFolder(item, length, parent_dir) {
                // 没有子文件夹
                if (item.fullPath.lastIndexOf('/') === 0) {
                    if (length === 0) {
                        // console.log('请勿上传空文件夹')
                    } else {
                        let temp = {
                            // id: this.folders.length,
                            name: item.name,
                            process: 0,
                            total: length
                        }
                        // item.attr = this.folders.length
                        // if (this.folders.length < 30) {
                        //     // this.folders.push(temp)
                        //     console.log(temp)
                        // } else {
                        //     console.log('一次只能拖拽30个文件夹')
                        // }
                        return temp
                    }
                }
                if (parent_dir) {
                    parent_dir.total += length - 1
                }
            },
            
            // 过滤调Mac默认的.DS_Store文件
            filterStore(arr) {
                let entries = arr.filter(item => {
                    return item.name !== '.DS_Store'
                })
                return entries
            },
            
            // 设置文件图标
            setIco(file){
                
                let result = ''
                
                // 文件图片路径
                const path = '/index/assets/svg/filesystem/'
                
                if (file.type.indexOf("image/gif")  != -1) result = path + 'gif.svg'
                else if (file.type.indexOf("image") != -1) result = path + 'img.svg'
                else if (file.type.indexOf("x-gzip")!= -1) result = path + 'gz.svg'
                else if (file.type.indexOf("zip")   != -1) result = path + 'zip.svg'
                else if (file.type.indexOf("audio") != -1) result = path + 'mp3.svg'
                else if (file.type.indexOf("video/mp4") != -1) result = path + 'mp4.svg'
                else if (file.type.indexOf("video/avi") != -1) result = path + 'avi.svg'
                else if (file.type.indexOf("text/css")  != -1) result = path + 'css.svg'
                else if (file.type.indexOf("text/html") != -1) result = path + 'html.svg'
                else if (file.type.indexOf("text/plain")!= -1) result = path + 'txt.svg'
                else if (file.type.indexOf("text/javascript") != -1) result = path + 'js.svg'
                else if (file.type.indexOf("application/json")!= -1) result = path + 'json.svg'
                else if (file.type.indexOf("application/pdf") != -1) result = path + 'pdf.svg'
                else if (file.type.indexOf("application/x-msdownload") != -1) result = path + 'exe.svg'
                else if (file.name.indexOf(".docx") != -1)result = path + 'docx.svg'
                else if (file.name.indexOf(".doc") != -1) result = path + 'doc.svg'
                else if (file.name.indexOf(".php") != -1) result = path + 'php.svg'
                else if (file.name.indexOf(".ppt") != -1) result = path + 'ppt.svg'
                else if (file.name.indexOf(".sql") != -1) result = path + 'sql.svg'
                else if (file.name.indexOf(".ttf") != -1) result = path + 'ttf.svg'
                else result = path + 'other.svg'
                
                return result
            },
            
            // push 文件
            pushFile(file, path = ''){
                if (file.name !== '.DS_Store') {
                    let obj = {
                        id   : this.uploads_id,
                        file ,
                        name : file.name,
                        size : inisHelper.format.bytes(file.size),
                        type : file.type,
                        path ,
                        ico  : this.setIco(file),
                        speed: 0,
                    }
                    
                    this.uploads_id++
                    this.preview.push(obj)
                    this.await_uploads.push(obj)
                }
                
                this.upload_is_show = true
                this.preview_length = this.preview.length
            },
            
            // 递归文件
            traverseFileTree(item, path = '', parent_dir) {
                
                if (item.isFile) {
                    item.file((file) => {
                        this.pushFile(file, item.fullPath)
                    })
                } else if (item.isDirectory) {
                    
                    let dirReader = item.createReader()
                    
                    dirReader.readEntries((entries) => {
                        
                        entries  = this.filterStore(entries)
                        let temp = this.filterFolder(item, entries.length, parent_dir)
                        
                        for (let i = 0; i < entries.length; i++) {
                            entries[i].attr = item.attr
                            this.traverseFileTree(entries[i], path + item.name + '/', temp)
                        }
                        
                    }, (e) => {
                        console.log(e)
                    })
                }
            },
            
            // 触发手动上传事件
            clickUpload(opt = '#btn-file'){
                document.querySelector(opt).click()
            },
            
            // 操作文件选择
            fileSelect(event, type = 1) {
                
                let items   = ''
                
                event.stopPropagation();
                event.preventDefault();
                
                // 拖拽上传
                if (type == 1) {
                    items = event.dataTransfer.items
                    for (let item of items) {
                        
                        let file = item.webkitGetAsEntry()
                        this.traverseFileTree(file)
                        this.uploads_status = true
                        
                        // if (file.isDirectory) {
                        //     // this.traverseFileTree(file)
                        //     console.log('文件夹')
                        // } else {
                        //     // this.traverseFileTree(file)
                        //     console.log('文件')
                        // }
                    }
                } else {  // 手动上传
                    items = event.target.files
                    for (let item of items) {
                        this.pushFile(item, '/'+item.webkitRelativePath)
                        this.uploads_status = true
                    }
                }
            },
            
            // 页面加载
            load(){
                
                const self = this
                
                const html = document.querySelector('html')
                html.addEventListener('dragover', self.dragOver,   false)
                html.addEventListener('drop',     self.fileSelect, false)
                
                // 鼠标右键菜单
                let content   = document.querySelector('.content-page')
                let mouse_dom = document.querySelector('#mouse-menu')
                
                LTEvent.addListener(content, "contextmenu", LTEvent.cancelBubble);
                LTEvent.addListener(content, "contextmenu", ()=>{
                    
                    let clintX = event.clientX;
    				let clintY = event.clientY;
    				// 当有下拉条的时候必须加上当前屏幕不可视范围的left,和top值
    				let scollTop  = document.documentElement.scrollTop  || document.body.scrollTop
    				let scollLeft = document.documentElement.scrollLeft || document.body.scrollLeft
    				
    				mouse_dom.style.left    = clintX + scollLeft + 'px';
    				mouse_dom.style.top     = clintY + scollTop  + 'px';
    				mouse_dom.style.display = 'block';
    				mouse_dom.style.zIndex  = 100;
    				// 设置方法动画
    				mouse_dom.classList.add("enlarge")
    				mouse_dom.classList.remove("narrow")
    				
    				let arr = []
    				let right_menu = []
    				
    				// 获取鼠标右键对应文件的数据
    				for (let item in event.path) {
    				    
    				    let className = event.path[item].className
    				    if (!inisHelper.is.empty(className)) if (className.indexOf("mouse-item") != -1) {
    				        let mouse_item  = event.path[item].getAttribute('mouse-item')
    				        self.mouse_menu = self.dir_info.info[mouse_item].info
    				        self.mouse_menu.item = mouse_item
    				        // 得到右键的文件数据
    				        // console.log(self.mouse_menu)
    				        // console.log(self.dir_info)
    				    }
    				    if (!inisHelper.is.empty(className)) arr.push(className.split(' '))
    				}
    				
    				for (let item in arr) for (let i in arr[item]) right_menu.push(arr[item][i])
    				
    				// 得到是否为右键文件或文件夹
    				if (inisHelper.in.array('mouse-item',right_menu)) {
    				    self.mouse_menu_type = true
    				} else self.mouse_menu_type = false
    				
                });
                
                LTEvent.addListener(document, "click", ()=>{
                    self.mouse_menu = []
                    self.mouse_menu_type = false
                    // 设置缩小动画
                    mouse_dom.classList.add("narrow")
    				mouse_dom.classList.remove("enlarge")
                    setTimeout(()=>{
                        mouse_dom.style.display = 'none'
                    }, 100)
                });
                
                /* 框选事件 */
                self.boxSelect()
            },
            
            // 操作文件拖拽
            dragOver(event) {
                event.stopPropagation();
                event.preventDefault();
                event.dataTransfer.dropEffect = 'copy'
            },
            
            // 框选事件
            boxSelect(){
                
                const self = this
                
                // 允许框选区域的DOM
                
                document.querySelector("#files").onmousedown = () => {
                    
                    let select_list = [];
                    
                    // 获取文件数量
                    let file_item = document.querySelectorAll(".file-item");
                    for (let item of file_item) {
                        if (item.className.indexOf("file-item") != -1) {
                            item.classList.add("file-item")
                            select_list.push(item)
                        }
                    }
                    
                    // 是否框选
                    let is_select = true;
                    let event  = window.event || arguments[0];
                    let startX = (event.x || event.clientX);
                    let startY = (event.y || event.clientY);
                    // 创建框选区域
                    let box_area = document.createElement("div");
                    box_area.style.cssText = "position:absolute;width:0px;height:0px;font-size:0px;margin:0px;padding:0px;border:1px dashed #0099FF;background-color:#C3D5ED;z-index:1000;filter:alpha(opacity:60);opacity:0.6;display:none;";
                    box_area.id = "box-area";
                    document.body.appendChild(box_area);
                    box_area.style.left = startX + "px";
                    box_area.style.top  = startY + "px";
                    let [x,y] = [null,null];
                    // 冒泡处理
                    self.clearBubble(event);
                    
                    // 允许框选的区域
                    document.querySelector('#files').onmousemove = () => {
                        event = window.event || arguments[0];
                        if (is_select) {
                            if (box_area.style.display == "none") {
                                box_area.style.display = ""
                            }
                            x = (event.x || event.clientX);
                            y = (event.y || event.clientY);
                            
                            box_area.style.left   = Math.min(x , startX) + "px";
                            box_area.style.top    = Math.min(y , startY) + "px";
                            box_area.style.width  = Math.abs(x - startX) + "px";
                            box_area.style.height = Math.abs(y - startY) + "px";
                            
                            let left   = box_area.offsetLeft;
                            let top    = box_area.offsetTop;
                            let width  = box_area.offsetWidth;
                            let hight  = box_area.offsetHeight;
                            // 偏移量
                            let offset = document.querySelector(".content-page").offsetLeft
                            
                            for (let item of select_list) {
                                
                                let itemX = item.offsetWidth  + item.offsetLeft + offset
                                let itemY = item.offsetHeight * 2 + item.offsetTop
                                
                                let factor1 = itemX > left
                                let factor2 = itemY > top
                                let factor3 = (item.offsetLeft + offset) < (left + width)
                                let factor4 = (item.offsetTop  + item.offsetHeight) < (top + hight)
                                
                                if (factor1 && factor2 && factor3 && factor4) {
                                    // 不存在 - 添加选中标记
                                    if (item.className.indexOf("sign") == -1) {
                                        item.classList.add("sign")
                                    }
                                } else {
                                    // 存在 - 移除选中标记
                                    if (item.className.indexOf("sign") != -1) {
                                        item.classList.remove("sign")
                                    }
                                }
                            }
                        }
                        self.clearBubble(event)
                    }
                    // 松开任意一个鼠标按钮时发生
                    document.onmouseup = () => {
                        is_select = false;
                        if (box_area) {
                            document.body.removeChild(box_area);
                            self.selectFiles(select_list)
                        }
                        [select_list,x,y,box_area,startX,startY,event] = [null,null,null,null,null,null,null]
                        // select_list = null,
                        // x = null,
                        // y = null,
                        // box_area = null,
                        // startX = null,
                        // startY = null,
                        // event = null
                    }
                }
            },
            
            // 去除框选
            clearBoxSelect(){
                let file_item = document.querySelectorAll(".file-item");
                for (let item of file_item) {
                    if (item.className.indexOf("sign") != -1) item.classList.remove("sign")
                }
            },
            
            // 冒泡事件
            clearBubble(event) {
                if (event.stopPropagation) event.stopPropagation();
                else event.cancelBubble = true;
                if (event.preventDefault) event.preventDefault();
                else event.returnValue = false
            },
            
            // 被框选的文件
            selectFiles(arr) {
                let count = 0;
                let selInfo = "";
                for (let item of arr) {
                    if (item.className.indexOf("sign") != -1) {
                        count++
                        selInfo += item.innerText + "\n"
                    }
                }
                if (count != 0) {
                    console.log("共选择 " + count + " 个文件，分别是：\n\n" + selInfo)
                }
            },
            
            // 窗口关闭前事件
            destroy(){
                
                window.onbeforeunload = (e = window.event) => {
                    
                    if (e) e.returnValue = 'Any string';
                    
                    return '您正在上传的数据尚未完成，确定要离开此页吗？';
                }
            },
            
            // 填充上传队列
            addQueue(){
                const self = this
                // 上传备份不为空 - 表示有数据需要上传
                if (!inisHelper.is.empty(self.await_uploads)) {
                    // 上传状态为 true 且 上传队列长度小于允许的长度
                    let count = self.queue_uploads.length
                    let await_count = self.await_count
                    if (self.uploads_status == true && count < await_count) {
                        for (let i = 0; i < self.await_count - count; i++) {
                            let first = self.await_uploads.shift()
                            if (!inisHelper.is.empty(first)) self.queue_uploads.push(first)
                        }
                    }
                }
            },
            
            // 上传
            uploads(item){
                
                const self   = this
                let params   = new FormData
                params.append('file',item.file)
                
                if (inisHelper.get.string.count(item.path,'/') > 1) {
                    let path = item.path
                    path = path.split('/')
                    path.shift()
                    path.pop()
                    path = path.join('/')
                    params.append('path',self.dir_info.path + path + '/')
                } else params.append('path',self.dir_info.path)
                
                const config = {
                    headers: { "Content-Type": "multipart/form-data" },
                    onUploadProgress: (speed) => {
                        if (speed.lengthComputable) {
                            let ratio = speed.loaded / speed.total;
                            // 只是上传到后端，后端并未真正保存成功
                            if (ratio < 1) self.preview[item.id].speed = ratio
                        }
                    }
                }
                
                axios.post("/index/file/uploadFileOne", params, config).then(res =>{
                    // 后端确认保存成功了
                    self.preview[item.id].speed = 1
                    self.getData()
                    for (let i in self.queue_uploads) {
                        if (item.id == self.queue_uploads[i].id) {
                            self.queue_uploads.splice(i,1)
                            self.addQueue()
                            // window.onbeforeunload = () => null;
                        }
                    }
                })
            },
            
            // 弹窗
            dialogbox(){
                
                let popUps     = document.querySelector("#pop-ups")
                let mask       = document.querySelector("#mask")
                let popUpsDrag = document.querySelector("#pop-ups-drag")
                let popUpsClose= document.querySelector("#pop-ups-close")
                let boxFill    = document.querySelector("#box-fill")
                let boxNarrow  = document.querySelector("#box-narrow")
                let aceEditor  = document.querySelector("#ace-editor")
                    
                // 禁止选中对话框内容
                if (document.attachEvent) {
                    // ie的事件监听，拖拽div时禁止选中内容，firefox与chrome已在css中设置过-moz-user-select: none; -webkit-user-select: none;
                    popUps.attachEvent('onselectstart', ()=>{
                      return false;
                    });
                }
                
                // 声明需要用到的变量
                let mx = 0,my = 0;      // 鼠标x、y轴坐标（相对于left，top）
                let dx = 0,dy = 0;      // 对话框坐标（同上）
                let isDraging = false;  // 不可拖动
                
                // 鼠标按下
                popUpsDrag.addEventListener('mousedown',(e)=>{
                    e  = e || window.event;
                    mx = e.pageX;            // 点击时鼠标X坐标
                    my = e.pageY;            // 点击时鼠标Y坐标
                    dx = popUps.offsetLeft;
                    dy = popUps.offsetTop;
                    isDraging = true;        // 标记对话框可拖动
                });
    
                // 鼠标移动更新窗口位置
                document.onmousemove = (e) => {
                    e = e || window.event;
                    let x = e.pageX;                        // 移动时鼠标X坐标
                    let y = e.pageY;                        // 移动时鼠标Y坐标
                    if(isDraging){                          // 判断对话框能否拖动
                        let moveX = dx + x - mx;            // 移动后对话框新的left值
                        let moveY = dy + y - my;            // 移动后对话框新的top值
                        let pageW = document.documentElement.clientWidth;
                        let pageH = document.documentElement.clientHeight;
                        let dialogW = popUps.offsetWidth;
                        let dialogH = popUps.offsetHeight;
                        let maxX = pageW - dialogW;         // X轴可拖动最大值
                        let maxY = pageH - dialogH;         // Y轴可拖动最大值
                        moveX = Math.min(Math.max(0,moveX),maxX);     // X轴可拖动范围
                        moveY = Math.min(Math.max(0,moveY),maxY);     // Y轴可拖动范围
                        popUps.style.left = moveX +'px';    // 重新设置对话框的left
                        popUps.style.top  =  moveY +'px';   // 重新设置对话框的top
                    };
                };
    
                // 鼠标离开
                popUpsDrag.addEventListener("mouseup", ()=>{
                    isDraging = false;
                }, true)
    
                // 点击关闭对话框
                popUpsClose.addEventListener("click", ()=>{
                    popUps.style.setProperty("display","none")
                    mask.style.setProperty("display","none")
                }, true)
    
                // 窗口大小改变时，对话框始终居中
                window.onresize = () => {
                    this.autoCenter(popUps);
                };
                
                // 窗口全屏
                boxFill.addEventListener("click", ()=>{
                    popUps.style.setProperty("top", "0")
                    popUps.style.setProperty("left", "0")
                    popUps.style.setProperty("width", "100%")
                    aceEditor.style.setProperty("height", "calc(100vh - 46px)")
                    boxFill.style.setProperty("display", "none")
                    boxNarrow.style.setProperty("display", "block")
                    // 更改编辑器的大小
                    this.aceEditor.resize()
                }, true)
                
                // 窗口小屏
                boxNarrow.addEventListener("click", ()=>{
                    popUps.style.setProperty("width", "70%")
                    aceEditor.style.setProperty("height", "60vh")
                    boxFill.style.setProperty("display", "block")
                    boxNarrow.style.setProperty("display", "none")
                    this.autoCenter(popUps)
                    // 更改编辑器的大小
                    this.aceEditor.resize()
                }, true)
            },
            
            // 弹出对话框
            openDialog(){
                // 点击弹出对话框
                let popUps     = document.querySelector("#pop-ups")
                let mask       = document.querySelector("#mask")
                popUps.style.setProperty("display","block")
                mask.style.setProperty("display","block")
                this.autoCenter(popUps);
                
                this.initAce();
            },
            
            // 自动居中对话框
            autoCenter(el){
                
                let bodyW = document.documentElement.clientWidth;
                let bodyH = document.documentElement.clientHeight;
                // 获取对话框宽、高
                let elW = el.offsetWidth;
                let elH = el.offsetHeight;
    
                el.style.left = (bodyW - elW) / 2 + 'px';
                el.style.top  = (bodyH - elH) / 2 + 'px';
            },
            
            // 初始化 ace 编辑器
            initAce(){
                let self = this
                this.aceEditor = ace.edit("ace-editor");
                // 启用提示菜单
                ace.require("ace/ext/language_tools");
                this.aceEditor.setOptions({
                    wrap:"free",                        // 自动换行,设置为off关闭
                    enableBasicAutocompletion: true,    // 自动提示语法
                    enableSnippets: true,               // 自动提示语法
                    enableLiveAutocompletion: true      // 代码补全
                });
                // 设置编辑器主题
                this.aceEditor.setTheme("ace/theme/monokai");
                // 设置字体大小
                this.aceEditor.setFontSize(14);
                // 设置编辑器内容
                this.aceEditor.getSession().setValue(this.file_data.data)
                // 设置编辑器语言
                this.aceEditor.getSession().setMode("ace/mode/" + this.file_data.info.ext);
                // 自定义命令或快捷键
                this.aceEditor.commands.addCommand({
                    // 命令名称
                    name: 'save',
                    // 快捷键
                    bindKey: {win: 'Ctrl-S',  mac: 'Command-S'},
                    exec: (editor) => {
                        self.saveEditFile()
                    },
                    readOnly: true // false 只读模式
                });
            },
            // 保存编辑文件
            saveEditFile(){
                this.loading.save_file = true
                let params = new FormData
                let path   = this.dir_info.path
                
                params.append("path", path + this.file_data.info.file_name)
                params.append("text", this.aceEditor.getValue())
                
                axios.post("/index/file/write", params).then(res=>{
                    if (res.data.code == 200) {
                        $.NotificationApp.send("提示！", "保存成功！", "top-right", "rgba(0,0,0,0.2)", "info");
                    } else $.NotificationApp.send("错误！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "warning");
                    this.loading.save_file = false
                })
            },
            // 刷新编辑文件
            refreshEditFile(){
                this.loading.read_file = true
                let params = new FormData
                let path   = this.dir_info.path
                
                params.append("path", path + this.file_data.info.file_name)
                
                axios.post("/index/file/read", params).then(res=>{
                    if (res.data.code == 200) {
                        this.file_data = res.data.data
                        this.aceEditor.setValue(this.file_data.data)
                    } else $.NotificationApp.send("错误！", res.data.msg, "top-right", "rgba(0,0,0,0.2)", "warning");
                    this.loading.read_file = false
                })
            }
        },
        computed: {
            file_data:{
                get(){
                    return this.file_data
                },
                set(value){
                    if (value.info.ext == "js") value.info.ext = "javascript"
                    else if (value.info.ext == "txt") value.info.ext = "text"
                }
            }
        },
        watch: {
            // 上传队列
            queue_uploads: {
                handler(newValue,oldValue){
                    
                    const self = this
                    
                    newValue.forEach((item)=>{
                        if (item.speed == 0 && !inisHelper.in.array(item.id,self.uplpading_id)) {
                            self.uplpading_id.push(item.id)
                            self.uploads(item)
                        }
                    })
                    
                },
                immediate: true,
                deep: true,
            },
            // 预览长度
            preview_length: function(newValue,oldValue){
                
                const self = this
                
                // 上传未完成，关闭窗口提示
                self.destroy()
                
                self.addQueue()
            },
        }
    }).mount('#filesystem')
}()