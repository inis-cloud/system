// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | 作用：助手函数 - 支持链式操作
// +----------------------------------------------------------------------

class helper{
    /**
     * @name 构造器
     * @description 初始化函数
     */
    constructor()
    {
        /* 定义 get 方法 */
        const get_cookie = (name) => this.getCookie(name)
        const query      = {string:(name)=>this.getQueryString(name)}
        const page       = {name:()=>this.getPageName()}
        const str_count  = {count:(string,search)=>this.getStringCount(string,search)}
        const get_storage= (namespace,key) => this.getStorage(namespace,key)
        const get_browser= () => this.getBrowser()
        const get_random_num= {num:(min,max) => this.getRandomNum(min,max)}
        
        // 链式操作 get 属性
        this.get         = { cookie:get_cookie, query, page, string:str_count, storage:get_storage, browser:get_browser, random:get_random_num }
        
        
        
        /* 定义 set 方法 */
        const set_cookie = (name,value,exdays)   => this.setCookie(name,value,exdays)
        const set_storage= (namespace,key,value) => this.setStorage(namespace,key,value)
        const css        = (classOrId,css,cover) => this.setCss(classOrId,css,cover)
        const copy_text  = {text: (text,remark)  => this.setCopyText(text,remark)}
        
        // 链式操作 set 属性
        this.set         = { cookie:set_cookie, storage:set_storage, css, copy:copy_text }
        
        
        
        /* 定义 delete 方法 */
        const del_cookie = (name) => this.deleteCookie(name)
        
        // 链式操作 delete 属性
        this.delete      = { cookie:del_cookie }
        
        
        
        /* 定义 create 方法 */
        const paging = (item,page,numb) => this.createPaging(item,page,numb)
        const create_array  = (min,max,step)   => this.createArray(min,max,step)
        
        // 链式操作 create 属性
        this.create  = { paging, array:create_array }
        
        
        
        /* 定义 is 方法 */
        const mobile = () => this.isMobile()
        const NULL   = (value) => this.isNull(value)
        const empty  = (value) => this.isEmpty(value)
        const email  = (value,bool) => this.isEmail(value,bool)
        const is_url = (url) => this.isUrl(url)
        
        // 链式操作 is 属性
        this.is      = { mobile, NULL, empty, email, url:is_url }
        
        
        
        /* 定义 in 方法 */
        const in_array = (search,array) => this.inArray(search,array)
        
        // 链式操作 in 属性
        this.in        = { array:in_array }
        
        
        
        /* 定义 time 方法 */
        const nature  = (timestamp,type) => this.natureTime(timestamp,type)
        const to      = {date:(unixTime,type)=>this.timeToDate(unixTime,type)}
        const response= () =>this.responseTime()
        
        // 链式操作 time 属性
        this.time     = { nature, to, response }
        
        
        
        
        /* 定义 date 方法 */
        const date_to = {time:(date)=>this.dateToTime(date)}
        
        // 链式操作 date 属性
        this.date     = { to:date_to }
        
        
        
        
        /* 定义 trim 方法 */
        const trim_array  = (arr) => this.trimArray(arr)
        const trim_string = (value,type) => this.trimString(value,type)
        
        // 链式操作 trim 属性
        this.trim   = {array:trim_array, string:trim_string} 
        
        
        
        /* 定义 format 方法 */
        const bytes = (bytes,decimals,unit) => this.formatBytes(bytes,decimals,unit)
        const number= (number,unit) => this.formatNumber(number,unit)
        
        // 链式操作 format 属性
        this.format = { bytes, number }
        
        
        
        /* 定义 array 方法 */
        const unique = (array) => this.arrayUnique(array)
        const arraySortTwo = {two:(array,key,sort) => this.arraySortTwo(array,key,sort)}
        
        // 链式操作 array 属性
        this.array = { unique, sort:arraySortTwo }
        
        
        
        
        /* 定义 compare 方法 */
        const CompareVersion = (versionA,versionB,parting) => this.CompareVersion(versionA,versionB,parting)
        
        // 链式操作 compare 属性
        this.compare = { version:CompareVersion }
        
        
        
        
        
        
        
        /* 定义 to 方法 */
        const toScroll = (y,time) => this.toScroll(y,time)
        
        // 链式操作 to 属性
        this.to = { scroll:toScroll }
        
    };
    
    /**
     * @name 生成分页码
     * @description 生成一个分页码
     * @param {number} item 当前页码
     * @param {number} page 一共几页
     * @param {number} numb 显示页脚数量
     * @return {Array} result 返回一维数组
     */
    createPaging(item,page,numb)
    {
        item = item || 1;
        page = page || 4;
        numb = numb || 7;
        
        let [result, mean] = [[],[]];

        if(item > page) item = page;
        if(item <= 0) item = 1;

        if(numb % 2 == 0) mean = numb / 2;
        else mean = (numb-1)/2;

        let min = ((item - mean) <= 0) ? 1 : item - mean;
        let max = item + mean;
        
        if(numb % 2 == 0){
            if(min <= 0 || item <= numb) [min = 1, max = numb];
            else if(item > page-numb) [min = page-numb+1, max = page];
            else [min = item-mean+1, max = min + numb-1];
            if(max >= page) max = page;
        }else{
            if(min <= 0) min = 1;
            if(max >= page) max = page;
            else if(item <= mean+1 && item <= page) max = numb;
            // if(item > page-(mean+1)) min = page-(numb-1);
        }

        result = this.createArray(min,max)

        return result;
    };

    /** 
     * @name 生成一维数组
     * @description 可以搭配分页使用
     * @param {number} [min=1] 从哪里开始
     * @param {number} [max=7] 到哪里结束
     * @param {number} [step=1] 步长是多少
     * @return {Array} result 返回一维数组
     */
    createArray(min = 1 , max = 7, step = 1)
    {
        let result = [];
        let len = Math.abs(max - min);
        if (len <= 0) result;
        let arr = new Array(len);
        let cNum = min;
        let cIndex = 0;
        let addArr = (index) => {
            if (cNum >= min && cNum <= max) {
                arr[index] = cNum;
                cNum++;
                cIndex++;
                addArr(cIndex, cNum)
            }
        }
        addArr(cIndex, cNum);
        result = arr.filter(item => item % step == 0);
        return result;
    };
    
    /**
     * @name 获取URL GET参数
     * @param {string} name 
     * @return {*}
     */
    getQueryString(name)
    {
        let result = [];
        let reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
        let param = window.location.search.substr(1).match(reg);
        if(param != null) result = unescape(param[2]); else result = null;
        return result;
    };
    
    /**
     * @name 获取当前页面名称
     * @return {string}
     */
    getPageName()
    {
        let strUrl = window.location.href;
        let arrUrl = strUrl.split("/");
        let strPage = arrUrl[arrUrl.length - 1];
        if (strPage.indexOf("?") > -1) {
            let pageName = strPage.split("?");
            strPage = pageName[0];
        }
        return strPage;
    };
    
    /**
     * @name 获取字符串中包含某个字符串是次数
     * @param {string} string
     * @param {string} search
     * @return {number}
     */
    getStringCount(string,search)
    {
        // 使用g表示整个字符串都要匹配
        let regex  =  new RegExp(search, 'g');
        let result =  string.match(regex);
        let count  =! result ? 0 : result.length;
        return count;
    }
    
    /**
     * @name 获取localStorage数据
     * @param {string} namespace [localStorage的key值]
     * @param {string} key [localStorage的value中JSON对象的key值]
     * @return {string}
     */
    getStorage(namespace, key = true)
    {
        let result = false
        let time   = Math.round(new Date / 1000)
        
        if (this.isEmpty(namespace)) console.log('请输入需要查询的key!')
        else {
            
            let storage = localStorage.getItem(namespace)
            
            if (!this.isEmpty(storage)) {
                if (typeof key == "boolean" && key) {
                    
                    result = JSON.parse(storage)
                    // 判断是否已过期
                    if (result.hasOwnProperty("end_time") && !this.isEmpty(result.end_time) && result.end_time <= time) result = "expire"
                    
                } else if (typeof key == "boolean") result = storage
                else {
                    
                    storage = JSON.parse(storage)
                    
                    if (this.isEmpty(storage[key])) result = null
                    else {
                        result = storage[key]
                        // 判断是否已过期
                        if (typeof result == "object" && result.hasOwnProperty("end_time") && !this.isEmpty(result.end_time) && result.end_time <= time) result = "expire"
                    }
                }
            }
        }
        
        return result
    };
    
    /**
     * @name 设置localStorage数据
     * @param {string} namespace [localStorage的key值]
     * @param {string || object} key [localStorage的value中JSON对象的key值]
     * @param {string} value [localStorage的value中JSON对象的value值]
     * @return {boolean}
     */
    setStorage(namespace, key, value)
    {
        // 返回结果
        let result = false
        
        if (this.isEmpty(namespace)) console.log('请输入需要存储的key名称！')
        else if (this.isEmpty(key))  console.log('键值key不得为空！')
        else {
            
            let storage  = localStorage.getItem(namespace)
            
            // 如果不存在，则新建
            if (!storage) storage = {}
            else storage = JSON.parse(storage)
            
            if (typeof key == 'string') {
                
                // 如果不存在，则新建
                if (!storage[key]) storage[key] = {}
                
                if (typeof value == 'object') {
                    
                    // 动态保存
                    for (let item in value) {
                        if (item == 'time' && this.isEmpty(value[item])) storage[key]["end_time"] = null
                        else if (item == 'time') storage[key]["end_time"] = value[item] + Math.round(new Date / 1000)
                        else storage[key][item] = value[item]
                    }
                    
                } else storage[key]["value"] = value
                
            } else if (typeof key == 'object') for (let item in key) {
                
                if (item == 'time' && this.isEmpty(key[item])) storage["end_time"] = null
                else if (item == 'time') storage["end_time"] = key[item] + Math.round(new Date / 1000)
                else storage[item] = key[item]
            }
            
            result = true
            localStorage.setItem(namespace, JSON.stringify(storage))
        }
        
        return result
    };
    
    /**
     * @name 设置CSS
     * @param {string} classOrId [class或ID]
     * @param {string} css [CSS]
     * @param {boolean} cover [是否覆盖]
     * @return {boolean}
     */
    setCss(classOrId, css, cover = false)
    {
        let result = false
        if (this.isEmpty(classOrId)) console.log('请选择需要设置的DOM元素')
        else if (this.isEmpty(css))  console.log('请设置CSS')
        else {
            
            let DOM = document.querySelector(classOrId)
            
            // 覆盖
            if (cover) DOM.style.cssText = css
            else {
                
                let css_arr = this.trimArray(css.split(';'))
                for (let item of css_arr) {
                    
                    let arr = item.split(":")
                    
                    if (item.indexOf('!important') != -1) {
                        let suffix = arr[1].split("!")
                        DOM.style.setProperty(this.trimString(arr[0], 2), this.trimString(suffix[0], 2), this.trimString(suffix[1], 2))
                    } else DOM.style.setProperty(this.trimString(arr[0], 2), this.trimString(arr[1], 2))
                }
            }
            
            result = true
        }
        
        return result
    };
    
    /**
     * @name 根据操作系统
     * @return {boolean}
     */
    isMobile()
    {
        let isMobile = {
            Android: function () {
                 return navigator.userAgent.match(/Android/i) ? true : false;
            },
            BlackBerry: function () {
                 return navigator.userAgent.match(/BlackBerry/i) ? true : false;
            },
            iOS: function () {
                 return navigator.userAgent.match(/iPhone|iPad|iPod/i) ? true : false;
            },
            Windows: function () {
                 return navigator.userAgent.match(/IEMobile/i) ? true : false;
            },
            any: function () {
                 return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Windows());
            }
         };
         return isMobile.any(); 
    };
    
    /**
     * @name 人性化时间
     * @param {number} timestamp 时间戳
     * @param {number} type 1-详细日期 2-简易日期 3-时间戳 4-日期格式
     * @return {string} result
     */
    natureTime(timestamp,type)
    {
        let result = Math.round(new Date() / 1000)
        type = type || 1
        timestamp = timestamp || result

        if (type == 1) {
            
            let zeroize = (num) => { return (String(num).length == 1 ? '0': '') + num; }
            let curTimestamp = parseInt(new Date().getTime() / 1000);
            let timestampDiff = curTimestamp - timestamp;
            let curDate = new Date(curTimestamp * 1000); 
            let tmDate = new Date(timestamp * 1000); 
    
            let Y = tmDate.getFullYear(),
            m = tmDate.getMonth() + 1,
            d = tmDate.getDate();
    
            let H = tmDate.getHours(),
            i = tmDate.getMinutes(),
            s = tmDate.getSeconds();
    
            if (timestampDiff < 60) {
                result = "刚刚";
            } else if (timestampDiff < 3600) { 
                result = Math.floor(timestampDiff / 60) + "分钟前";
            } else if (curDate.getFullYear() == Y && curDate.getMonth() + 1 == m && curDate.getDate() == d) {
                result = '今天' + zeroize(H) + ':' + zeroize(i);
            } else {
                let newDate = new Date((curTimestamp - 86400) * 1000); 
                
                if (newDate.getFullYear() == Y && newDate.getMonth() + 1 == m && newDate.getDate() == d) {
                    result = '昨天' + zeroize(H) + ':' + zeroize(i);
                } else if (curDate.getFullYear() == Y) {
                    result = zeroize(m) + '-' + zeroize(d) + ' ' + zeroize(H) + ':' + zeroize(i);
                } else {
                    result = Y + '-' + zeroize(m) + '-' + zeroize(d) + ' ' + zeroize(H) + ':' + zeroize(i);
                }
            }
        } else if(type == 2){
            let mistiming = Math.round(new Date() / 1000) - timestamp;
            let postfix = mistiming > 0 ? "前" : "后";
            mistiming = Math.abs(mistiming);
            let arrr = ["年", "个月", "星期", "天", "小时", "分钟", "秒"];
            let arrn = [31536000, 2592000, 604800, 86400, 3600, 60, 1];
            
            for (let i = 0; i < 7; i++) {
                let inm = Math.floor(mistiming / arrn[i]);
                if (inm != 0) result = inm + arrr[i] + postfix;
            }
        } else if (type == 4) {
            result = new Date(parseInt(timestamp) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
        }
        
        return result;
    };
    
    /**
     * @name 时间戳转日期格式
     * @param {number} unixTime 时间戳
     * @param {string} type
     * @return {string} result
     * 
     * Y-m-d           = 2018-01-11
     * Y-m-d H:i       = 2018-01-11 11:08
     * Y-m-d H:i:s     = 2018-01-11 11:08:31
     * Y/m/d           = 2018/01/11
     * Y/m/d H:i:s     = 2018/01/11 11:08:31
     * Y年m月d日       = 2018年01月11日
     * Y年m月d日 H:i:s = 2018年01月11日 11:08:31
    */
    timeToDate(unixTime, type = "Y-M-D H:i:s")
    {
        let date = new Date(unixTime * 1000);
        let datetime = "";
        datetime += date.getFullYear() + type.substring(1, 2);
        datetime += (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + type.substring(3, 4);
        datetime += (date.getDate() < 10 ? '0' + (date.getDate()) : date.getDate());
        if (type.substring(5, 6)) {
            if (type.substring(5, 6).charCodeAt() > 255) {
                datetime += type.substring(5, 6);
                if (type.substring(7, 8)) {
                    datetime += " " + (date.getHours() < 10 ? '0' + (date.getHours()) : date.getHours());
                    if (type.substring(9, 10)) {
                        datetime += type.substring(8, 9) + (date.getMinutes() < 10 ? '0' + (date.getMinutes()) : date.getMinutes());
                        if (type.substring(11, 12)) {
                            datetime += type.substring(10, 11) + (date.getSeconds() < 10 ? '0' + (date.getSeconds()) : date.getSeconds());
                        };
                    };
                };
            } else {
                datetime += " " + (date.getHours() < 10 ? '0' + (date.getHours()) : date.getHours());
                if (type.substring(8, 9)) {
                    datetime += type.substring(7, 8) + (date.getMinutes() < 10 ? '0' + (date.getMinutes()) : date.getMinutes());
                    if (type.substring(10, 11)) {
                        datetime += type.substring(9, 10) + (date.getSeconds() < 10 ? '0' + (date.getSeconds()) : date.getSeconds());
                    };
                };
            };
        };
        return datetime;
    }
    
    /**
     * @name 日期格式转时间戳
     * @return {number} result
     */
    dateToTime(date)
    {
        if (this.isEmpty(date)) console.log("请输入一个日期格式，如：2021-5-20 13:14:00")
        else {
            const time = new Date(date);
            let result = Date.parse(time);
            result = result.toString()
            result = result.substring(0,10)
            result = parseInt(result)
            return result
        }
    }
    
    /**
     * @name 响应耗时
     * @return {object} ram=内存,tcp=tcp连接时间,res=响应耗时,dom=渲染时间
     */
    responseTime()
    {
        let ResTime = window.performance;
    
        let RAM = (size) => { return Math.floor(size / 1024 / 1024, 4) + 'MB'; };
        
        let consume = (time) => { return time + 'ms'; };
        
        let result = {
            'ram':RAM(ResTime.memory.usedJSHeapSize),
            'tcp':consume(ResTime.timing.connectEnd - ResTime.timing.connectStart),
            'res':consume(ResTime.timing.responseEnd - ResTime.timing.responseStart),
        };
        
        // render
        window.onload = () => {
            console.log("dom渲染耗时：" + consume(ResTime.timing.domComplete - ResTime.timing.domLoading));
        };
        
        return result;
    };
    
    /**
     * @name 是否为NULL
     * @param {string} value 字符串
     * @return {boolean} result
     */
    isNull(value = "")
    {
        let result = false;
        
        if (value == null || typeof(value) == 'undefined' || value === undefined || value.length === 0) result = true;
        else result = false;
        
        return result;
    };
    
    /**
     * @name 判断是否为空 (包括空字符串、空格、null,{})
     * @param {string} string 字符串
     * @return {boolean} result
     */
    isEmpty(string = "")
    {
        let result = false;
        
        if (Array.isArray(string)){
            
            if (Array.prototype.isPrototypeOf(string) && string.length === 0) result = true;
            
        } else if (!this.isNull(string)) {
            
            if (string instanceof Object) {
                
                if (JSON.stringify(string) == "{}") result = true
                
            } else if ((string + '').replace(/(^\s*)|(\s*$)/g, '').length === 0) result = true;
            
        } else result = true;
        
        return result;
    };
    
    /**
     * @name 判断是否为邮箱
     * @param {string} value 邮箱
     * @param {boolean} bool [bool=false] 严格模式
     * @return {boolean} result
     */
    isEmail(value, bool = false)
    {
        let [result, pattern]  = [false, ''];
        
        if (bool) {
            
            pattern = /^[0-9a-zA-Z_]{5,12}@(163|126|qq|yahoo|gmail|sina)\.(com|com\.cn|cn|la)$/;
            
        } else {
            
            pattern = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
        }
        
        result = pattern.test(value);
        
        return result;
    };
    
    /**
     * @name 设置cookie
     * @param {string} name
     * @param {string} value 
     * @param {number} exdays 
     */
    setCookie(name, value, exdays = 1)
    {
        let result = true;
        
        if (this.isEmpty(name)) result = '请设置 cookie 名称！'
        else {
            
            let time = new Date();
            
            time.setTime(time.getTime() + (exdays * 24 * 60 * 60 * 1000));
            
            let expires = "expires=" + time.toUTCString();
            
            document.cookie = name + "=" + value + "; " + expires;
        }
        
        return result;
    };
    
    /**
     * @name 获取cookie
     * @param {string} name 
     * @return {string} [result=null]
     */
    getCookie(name)
    {
        let result = null;
        if (this.isEmpty(name)) result = '请输入需要查询的 cookie 名称！'
        else if (document.cookie.length > 0) {
            
            let begin = document.cookie.indexOf(name + '=');
            
            if (begin !== -1) {
                // cookie值的初始位置
                begin += name.length + 1;
                // 结束位置
                let end = document.cookie.indexOf(';', begin); 
                if (end === -1) {
                    // 没有;则end为字符串结束位置
                    end = document.cookie.length; 
                }
                result = unescape(document.cookie.substring(begin, end));
            }
        }
        return result
    };
    
    /**
     * @name 清除cookie
     * @param {string} name
     */
    deleteCookie(name)
    {
        let result = true;
        
        if (this.isEmpty(name)) result = '请输入需要删除的 cookie 名称！'
        else this.setCookie(name, "", -1);
        
        return result;
    };
    
    /**
     * @name 判断字符串是否在数组里面
     * @param {string} search 
     * @param {array} arr 
     * @return {string} [result=false]
     */
    inArray(search,array)
    {
        let result = false;
        
        for (let i in array) if (array[i] == search) result = true;
        
        return result;
    };
    
    /**
     * @name 数组去空
     * @param {array} arr 
     * @return {string} [result=false]
     */
    trimArray(arr)
    {
        
        let result = arr.filter(function (s) {
           return s && s.trim();
        });
        
        return result;
    }
    
    /**
     * @name 去除字符串空格
     * @param {string} value 字符串
     * @param {number} type  1-所有空格  2-前后空格  3-前空格 4-后空格
     * @return {string} value
     */
    trimString(value, type = 1)
    {
        let result = value
        
        if(!this.isEmpty(value)){
            switch (type) {
            case 1:
                result = value.replace(/\s+/g, '')
                break;
            case 2:
                result = value.replace(/(^\s*)|(\s*$)/g, '')
                break;
            case 3:
                result = value.replace(/(^\s*)/g, '')
                break;
            case 4:
                result = value.replace(/(\s*$)/g, '')
                break;
            default:
                result = value
            }
        }
        
        return result;
    };
    
    arrayUnique(arr = [])
    {
        return Array.from(new Set(arr))
    };
    
    /*
     * @name Bytes格式化
     * @param {number} bytes 
     * @param {number} decimals 
     * @return {string}
     */
    formatBytes (bytes, decimals, unit = true)
    {
        let result = '';
        
        if (bytes === 0) result = '0';
        else {
            let k = 1024;
            let dm = decimals + 1 || 3;
            let sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            let i = Math.floor(Math.log(bytes) / Math.log(k));
            
            if (unit) result = (bytes / Math.pow(k, i)).toPrecision(dm) + ' ' + sizes[i];
            else result = (bytes / Math.pow(k, i)).toPrecision(dm)
        }
        
        return result;
    };
    
    /*
     * @name 格式化数字
     * @param {number} bytes 
     * @param {boolean} unit 
     * @return {string}
     */
    formatNumber (number = 1, unit = true)
    {
        number = parseInt(number)
        
        let [result,units] = [null,null]
        
        if (number >= 100000000) {
            units  = '亿'
            result = Math.round(number / 10000000) / 10
        } else if (number >= 10000) {
            units  = '万'
            result = Math.round(number / 1000) / 10
        } else result = number
        
        if (unit) result = result + units
        
        return result
    };
    
    /*
     * @name 图片转base64
     * @param {Object} config 
     */
    base64(config) {
        
        let options = {
            // 清晰度比率 0-1 越小照片大小越小，但越不清晰 默认0.8
            rate	 : config.rate 	   || 0.8,
            // 压缩后照片 最大宽度/高度
            maxWidth : config.maxNum   || 680,
            // 回调函数返回压缩成功后的
            callBack : config.callBack || (()=>{
                console.log("回调函数callBack未定义!")
            }),
            // 绑定的DOM元素的#id或.class
            el		 : config.el 	   || '',
            files    : config.files    || '',
        }
        
        if(this.is.empty(options.el)) handleImg(files)
        else document.querySelector(options.el).onchange = ((e) => {
            
            /* 多图上传 */
            let files = e.target.files;
            handleImg(files)
        })
        
        function handleImg(files){
            
            if(this.is.empty(files)) return (()=>{
                console.log('未找到files文件')
            })
            
            let imgData = []
            
            for(let item of files){
                
                let data = {
                    'file': item,
                    'name': item.name,
                    'size': (new helper()).formatBytes(item.size),
                    'type': item.type,
                }
                
                let reader = new FileReader();
                reader.readAsDataURL(item)
                reader.onload = function (e) {
                    // 得到base64 url
                    let dataUrl = this.result;
                    let image = new Image();
                    image.src = dataUrl;
                    image.onload = function (e) {
                        let width = image.width, height = image.height;
                        let scale = width / height;
                        let canvas = document.createElement("canvas");
                        let ctx = canvas.getContext('2d');
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                        if (width - height >= 0) {
                            let width1 = options.maxWidth;
                            let height1 = parseInt(width1 / scale);
                            if (width >= width1) {
                                canvas.width = width1;
                                canvas.height = height1;
                                ctx.drawImage(image, 0, 0, width1, height1);
                            } else {
                                canvas.width = width;
                                canvas.height = height;
                                ctx.drawImage(image, 0, 0, width, height);
                            }
                        } else {
                            scale = height / width;
                            height1 = options.maxWidth;
                            width1 = parseInt(height1 / scale);
                            if (height >= height1) {
                                canvas.width = width1;
                                canvas.height = height1;
                                ctx.drawImage(image, 0, 0, width1, height1);
                            } else {
                                canvas.width = width;
                                canvas.height = height;
                                ctx.drawImage(image, 0, 0, width, height);
                            }
                        }
                        let cropStr = canvas.toDataURL("image/jpeg", options.rate);
                        data['base64'] = cropStr
                        imgData = data
                        options.callBack(imgData);
                    }
                }
            }
        }
    };
    
    /*
     * @name 获取当前浏览器名称
     */
    getBrowser()
    {
        const UA = window.navigator.userAgent;
        let result = 'ohter'
        
        if (/MicroMessenger/.test(UA)) result = 'wechat'
        else if (/AlipayClient/.test(UA)) result = 'alipay'
        else result = 'ohter'
        
        return result;
    }
    
    /*
     * @name 获取范围内的随机数
     */
    getRandomNum(min = 0, max = 100)
    {
        let range = max - min;
        let rand = Math.random();
        // 四舍五入
        let result = min + Math.round(rand * range);
        return result;
    }
    
    /*
     * @name 设置鼠标复制内容
     * @param {string} text [设置鼠标文本内容]
     * @param {string} remark [如果不为空，则往内容末尾添加备注]
     * @return {boolean}
     */
    setCopyText(text = null, remark = null)
    {
        let result  = false
        const textarea = document.createElement("textarea");
        textarea.value = text
        document.body.appendChild(textarea);
        
        textarea.select();
        
        // 为textarea添加监听事件方便对剪贴板内容进行二次修改
        if (!this.isEmpty(remark)) textarea.addEventListener("copy", (event)=>{
            let clipboardData = event.clipboardData || window.clipboardData;
            if (!clipboardData) return;
            let text = window.getSelection().toString();
            if (text) {
                event.preventDefault();
                clipboardData.setData("text/plain", text + remark);
            }
        });
        
        // 执行复制操作
        if (document.execCommand("copy")) result = true
        
        // document.execCommand('copy') 如果内容复制的不全
        // document.execCommand('copy') 前先进行document.execCommand('selectAll')选中所有内容即可
        
        // 移除input框
        document.body.removeChild(textarea);
        
        return result;
    };
    
    /*
     * @name 二维数组冒泡排序 [{key:1},{key:2}]
     * @param {string} key [排序的键]
     * @param {string} sort [排序方式，升序或降序]
     * @return {array}
     */
    arraySortTwo(array, key, sort = 'acs') {
        if (sort == 'acs') {
            for (let i = 0; i < array.length; i++) for (let j = i; j < array.length; j++) if (array[i][key]>array[j][key]) {
                let temp = array[i]
                array[i] = array[j]
                array[j] = temp
            }
        } else if (sort == 'desc') {
            for (let i = 0; i < array.length; i++) for (let j = i; j < array.length; j++) if (array[i][key]<array[j][key]) {
                let temp = array[i]
                array[i] = array[j]
                array[j] = temp
            }
        }
        return array
    };
    
    /*
     * @name 是否为有效链接
     * @param {string} url [链接]
     * @return {boolean}
     */
    isUrl(str)
    {
        let v = new RegExp('^(?!mailto:)(?:(?:http|https|ftp)://|//)(?:\\S+(?::\\S*)?@)?(?:(?:(?:[1-9]\\d?|1\\d\\d|2[01]\\d|22[0-3])(?:\\.(?:1?\\d{1,2}|2[0-4]\\d|25[0-5])){2}(?:\\.(?:[0-9]\\d?|1\\d\\d|2[0-4]\\d|25[0-4]))|(?:(?:[a-z\\u00a1-\\uffff0-9]+-?)*[a-z\\u00a1-\\uffff0-9]+)(?:\\.(?:[a-z\\u00a1-\\uffff0-9]+-?)*[a-z\\u00a1-\\uffff0-9]+)*(?:\\.(?:[a-z\\u00a1-\\uffff]{2,})))|localhost)(?::\\d{2,5})?(?:(/|\\?|#)[^\\s]*)?$', 'i');
        return v.test(str);
    };
    
    /*
     * @name 版本比对
     * @param {string} versionA [新版本]
     * @param {string} versionB [旧版本]
     * @param {string} parting [分隔符]
     * @return {boolean}
     */
    CompareVersion(versionA, versionB, parting = '.')
    {
        if (versionA && versionB) {
            
            // 将两个版本号拆成数字
            let arrayA = versionA.split(parting)
            let arrayB = versionB.split(parting)
            let minLength = Math.min(arrayA.length, arrayB.length)
            let position = 0
            let diff = 0
            
            // 依次比较版本号每一位大小，当对比得出结果后跳出循环（后文有简单介绍）
            while (position < minLength && ((diff = parseInt(arrayA[position]) - parseInt(arrayB[position])) == 0)) position++;
            
            diff = (diff != 0) ? diff: (arrayA.length - arrayB.length);
            
            // 若versionA大于versionB，则返回true
            return diff > 0;
            
        } else {
            // 输入为空
            console.log("版本号不能为空");
            return false;
        }
    };
    
    /*
     * @name 定位滚动条
     * @param {number} number [Y坐标]
     * @param {number} time [时间]
     */
    toScroll(number = 0, time)
    {
        if (!time) {
            document.body.scrollTop = document.documentElement.scrollTop = number;
            return number;
        }
        const spacingTime = 20; // 设置循环的间隔时间  值越小消耗性能越高
        let spacingInex = time / spacingTime; // 计算循环的次数
        let nowTop = document.body.scrollTop + document.documentElement.scrollTop; // 获取当前滚动条位置
        let everTop = (number - nowTop) / spacingInex; // 计算每次滑动的距离
        let scrollTimer = setInterval(() => {
            if (spacingInex > 0) {
                spacingInex--;
                this.toScroll(nowTop += everTop);
            } else {
                clearInterval(scrollTimer); // 清除计时器
            }
        }, spacingTime);
    };
    
    /*
     * @name 自定义处理API
     * @param {string} url [API地址]
     * @param {string} api [API应用名]
     */
    customProcessApi(url = "", api = "api")
    {
        let result = url
        
        if (!this.is.empty(url)) {
            
            let prefix = "//"
            
            if (url.indexOf("https://") != -1)     prefix = "https://"
            else if (url.indexOf("http://") != -1) prefix = "http://"
            
            // 过滤http(s)://
            result = url.replace(/http(s)?:\/\//g,"")
            
            // URL转数组
            result = result.split("/")
            
            // 去除空数组
            result = result.filter((s)=>{
                return s && s.trim();
            });
            
            if (result.length == 1) result = prefix + result[0] + "/" + api + "/"
            else if (result.length == 2) {
                result = prefix + result[0] + "/" + result[1] + "/"
            }
        }
        return result
    };

    
    // END
}

const inisHelper = new helper