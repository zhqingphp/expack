window.Frame = (function () {
    let self = {};
    /**
     *
     * @param id
     * @param way
     */
    self.copy = function (id, way) {
        let text = ((typeof (id) === 'function') ? id() : document.getElementById(id).innerText);
        let input = document.createElement('input');
        input.setAttribute('id', 'copyInputDataBody');
        input.setAttribute('value', text);
        document.getElementsByTagName('body')[0].appendChild(input);
        document.getElementById('copyInputDataBody').select();
        if (document.execCommand('copy')) {
            if (typeof (way) === 'function') {
                way(text, function (id) {
                    document.getElementById(id).select();
                });
            }
        }
        document.getElementById('copyInputDataBody').remove();
    }

    /**
     * 倒计时功能(返回stop方法)
     * @param end_time //结束时间
     * @param top_time //开始时间
     * @param way      //执行时方法
     * @param end      //结束时间方
     */
    self.downTime = function (end_time, top_time, way, end) {
        if (typeof (top_time) === 'function') {
            let way_ = top_time, end_ = way;
            top_time = end;
            way = way_;
            end = end_;
        }
        let tow = function (n) {
            return n >= 0 && n < 10 ? '0' + n : n;
        }, t = 0, fun = function (end_time, top_time, t) {
            let now_time = top_time ? new Date(top_time.replace(/-/g, "/")) : new Date(),
                foot_time = new Date(end_time.replace(/-/g, "/")),
                time = foot_time.getTime() - now_time.getTime() - (t * 1000),
                d = tow(Math.floor(time / (1000 * 60 * 60 * 24))),
                h = tow(Math.floor(time / (1000 * 60 * 60) % 24)),
                m = tow(Math.floor(time / (1000 * 60) % 60)),
                s = tow(Math.floor(time / 1000 % 60)),
                str = d + '<span>天</span>' + h + '<span>小时</span>' + m + '<span>分钟</span>' + s + '<span>秒</span>';
            return {d: d, h: h, m: m, s: s, str: str, count: parseInt(d) + parseInt(h) + parseInt(m) + parseInt(s)};
        }, self = {}, clear = setInterval(function () {
            ++t;
            let obj = fun(end_time, top_time, t);
            if (obj.count > 0) {
                if (typeof (way) === 'function') {
                    way(obj, clear);
                }
            } else {
                if (typeof (end) === 'function') {
                    end(obj, clear);
                }
                clearInterval(clear);
            }
        }, 1000);
        self.stop = function (way) {
            if (typeof (way) === 'function') {
                way(clear);
            }
            clearInterval(clear);
        }
        return self
    }

    //是否数字
    self.isInt = function (data) {
        return typeof (data) === 'number'
    }

    //是否字符串
    self.isString = function (data) {
        return typeof (data) === 'string';
    }

    //是否function
    self.isCallable = function (data) {
        return typeof (data) === 'function';
    }

    //是否数组
    self.isArray = function (data) {
        return $.isArray(data);
    }

    //是否对像
    self.isObject = function (data) {
        return $.isPlainObject(data);
    }

    //数组/对像的key是否存在
    self.isKey = function (data, key) {
        return data.hasOwnProperty(key);
    }

    //是否有值
    self.isset = function (data) {
        if (typeof (data) === 'object') {
            if (JSON.stringify(data) === '{}' || JSON.stringify(data) === '[]') {
                return false;
            }
        } else if (typeof (data) === 'undefined' || data === 'undefined' || data === undefined || data === 'null' || data == null || !data) {
            return false
        }
        return true
    }

    //数组/对像是否有值
    self.isArr = function (data) {
        if (typeof (data) === 'object') {
            if (JSON.stringify(data) === '{}' || JSON.stringify(data) === '[]') {
                return false;
            }
        }
        return true;
    }
    //获取数组key的内容
    self.getArr = function (data, key, val) {
        $.each(key.split('.'), function (k, v) {
            if (self.isKey(data, v)) {
                data = self.getObj(data, v);
            }
        });
        return self.isset(data) ? data : val;
    }

    //获取对像key的内容
    self.getObj = function (data, key, val) {
        return self.isset(data) ? (self.isKey(data, key) ? (self.isset(data[key]) ? data[key] : val) : val) : val;
    }
    //对像转数组
    self.toArr = function (data) {
        return Object.keys(data).map(function (v) {
            return data[v]
        });
    }

    //GET参数转array
    self.getToArr = function (url) {
        let arr = url.split("?")[1].split("&"), obj = {};
        for (let i of arr) {
            obj[i.split("=")[0]] = i.split("=")[1]
        }
        return obj
    }

    //获取get
    self.get = function (key, def = '') {
        let i, Query = window.location.search.substring(1), Val = Query.split("&");
        for (i = 0; i < Val.length; i++) {
            let Arr = Val[i].split("=")
            if (Arr[0] === key) {
                def = Arr[1]
            }
        }
        return def;
    }

    ///n替换成br
    self.ntoBr = function (txt) {
        return txt.replace(/\n/g, "<br>")
    }

    //br替换成/n
    self.boToN = function (txt) {
        return txt.replace(/<br>/g, "\n")
    }
    //删除前后空格
    self.trim = function (txt) {
        return txt.replace(/(^\s*)|(\s*$)/g, "")
    }
    //隐藏手机号
    self.hidePhone = function (txt) {
        return txt.toString().replace(/^(\d{3})(\d{4})(\d{4})$/, "$1****$3")
    }

    //生成指定字符串数量
    self.randStr = function (num = 10, str) {
        return new Array(num + 1).join(str)
    }

    //html内容({title}),对像({title:'标题'})
    self.view = function (html, data) {
        return html.replace(new RegExp("{(\\w+)}", "ig"), function (text, key) {
            return data[key];
        });
    };

    //获取url#号后的参数
    self.getHash = function () {
        return (window.location.hash).substr(1);
    }

    //设置URL#号后面的参数
    self.setHash = function (url) {
        window.history.replaceState(null, null, (window.location.pathname + window.location.search + '#' + url));
    }

    //打开新窗口
    self.openUrl = function (url) {
        window.open("about:blank").location.href = url;
    }

    //关闭窗口
    self.closeWindow = function () {
        if (navigator.userAgent.indexOf("Firefox") !== -1 || navigator.userAgent.indexOf("Chrome") !== -1) {
            window.location.href = "about:blank";
            window.close();
        } else {
            window.opener = null;
            window.open("", "_self");
            window.close();
        }
    }

    //打开中间窗口
    self.openWindow = function (url, width, height, name) {
        (window.open(url, name, 'resizable=yes, menubar=no,scrollbars=yes,location=no,status=yes,width=' + width + ',height=' + height + ',top=' + ((window.screen.availHeight - 30 - height) / 2) + ',left=' + ((window.screen.availWidth - 10 - width) / 2))).focus();
    }

    //格式化金额
    self.number_format = function (number, decimals, thousands, separator) {
        number = parseFloat((number || '0.00')).toFixed((decimals || 2));
        separator = separator ? separator : '.';
        thousands = thousands ? thousands : ',';
        let source = String(number).split(".");
        source[0] = source[0].replace(new RegExp('(\\d)(?=(\\d{3})+$)', 'ig'), "$1" + thousands);
        return source.join(separator);
    }

    //时间
    self.date = function (time) {
        let t = {}, b = function (str) {
                return ((str.toString().length === 1) ? ('0' + str) : str);
            }, arr = ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六"],
            date = (time ? new Date(time) : new Date());
        t.y = date.getFullYear();
        t.m = b(date.getMonth() + 1);
        t.d = b(date.getDate());
        t.h = b(date.getHours());
        t.i = b(date.getMinutes());
        t.s = b(date.getSeconds());
        t.day = date.getDay();
        t.week = arr[t.day];
        t.time = date.getTime();
        t.date = t.y + "-" + t.m + "-" + t.d + " " + t.h + ":" + t.i + ":" + t.s;
        t.dh = t.y + "-" + t.m + "-" + t.d + " 12:00:00";
        return t;
    };

    //合并对像，数组转对像
    self.arrToObj = function (obj, arr) {
        return Object.assign(obj, arr)
    }

    //合并对像/数组
    self.arrayMerge = function (arr, array) {
        return $.extend(arr, array);
    }

    //转json
    self.json = function (data) {
        try {
            return JSON.stringify(data)
        } catch (e) {
            return data
        }
    }

    //转对像
    self.toObj = function (data) {
        try {
            return JSON.parse(data)
        } catch (e) {
            return data
        }
    }

    //随机
    self.rand = function () {
        let rand = (Math.random() * 10000000).toString(16), time = Math.random().toString()
        return rand.substr(0, 4) + (new Date()).getTime() + time.substr(2, 5)
    }

    //生成随机数字
    self.randInt = function (int) {
        return (Math.floor(Math.random() * (int || 10)));
    }
    //cookie
    self.cookie = {
        set: function (name, val, hours) {
            let Str = name + "=" + escape(val), Data = new Date();
            if ((hours || 0) > 0) {
                Data.setTime(Data.getTime() + (hours * 1000));
                Str += "; expires=" + Data.toGMTString();
            }
            document.cookie = Str + ";path=/;domain=" + document.domain + ";";
        },
        get: function (name, def) {
            let i, Arr = document.cookie.split("; ");
            for (i = 0; i < Arr.length; i++) {
                let data = Arr[i].split("=");
                if (data[0] === name) {
                    return unescape(data[1]);
                }
            }
            return (def || '');
        },
        del: function (name) {
            let Exp = new Date()
            Exp.setTime(Exp.getTime() - 1);
            document.cookie = name + "=;path=/;domain=" + document.domain + ";expires=" + Exp.toUTCString();
        },
        delete: function () {
            let keys = document.cookie.match(/[^ =;]+(?=\=)/g);
            if (keys) {
                for (let i = keys.length; i--;) {
                    document.cookie = keys[i] + '=0;path=/;expires=' + new Date(0).toUTCString();
                    document.cookie = keys[i] + '=0;path=/;domain=' + document.domain + ';expires=' + new Date(0).toUTCString();
                }
            }
        }
    }
    //session
    self.session = {
        set: function (name, val) {
            window.sessionStorage.setItem(name, val);
        },
        get: function (name, def) {
            return (window.sessionStorage.getItem(name) || (def || ''));
        },
        del: function (name) {
            window.sessionStorage.removeItem(name);
        },
        delete: function () {
            window.sessionStorage.clear();
        }
    }
    //localStorage
    self.storage = {
        set: function (name, val) {
            window.localStorage.setItem(name, val);
        },
        get: function (name, def) {
            return (window.localStorage.getItem(name) || (def || ''));
        },
        del: function (name) {
            window.localStorage.removeItem(name);
        },
        delete: function () {
            window.localStorage.clear();
        }
    }
    //cookie+session
    self.temp = {
        set: function (name, val, hours) {
            self.session.set(name, val);
            self.cookie.set(name, val, hours);
        },
        get: function (name, def) {
            return (self.cookie.get(name, def) || self.session.get(name, def));
        },
        del: function (name) {
            self.session.del(name);
            self.cookie.del(name);
        },
        delete: function () {
            self.session.delete();
            self.cookie.delete();
        }
    }
    //cookie+session+localStorage
    self.cache = {
        set: function (name, val, hours) {
            self.session.set(name, val);
            self.cookie.set(name, val, hours);
            self.storage.set(name, val);
        },
        get: function (name, def) {
            return (self.storage.get(name, def) || self.cookie.get(name, def) || self.session.get(name, def));
        },
        del: function (name) {
            self.session.del(name);
            self.cookie.del(name);
            self.storage.del(name);
        },
        delete: function () {
            self.session.delete();
            self.cookie.delete();
            self.storage.delete();
        }
    }
    //ajax
    self.ajax = {
        post: function (url, data, then, error, time) {
            $.ajax({
                cache: false,
                type: 'POST',
                url: url,
                timeout: (time || 5000),
                data: (self.isCallable(data) ? {} : data),
                dataType: "json",
                success: function (obj) {
                    if (self.isCallable(data)) {
                        data(obj);
                    } else if (self.isCallable(then)) {
                        then(obj);
                    } else {
                        console.log(obj);
                    }
                },
                error: function (err) {
                    if (self.isCallable(data) && self.isCallable(then)) {
                        then(err);
                    } else if (self.isCallable(error)) {
                        error(err);
                    } else {
                        console.log(err);
                    }
                }
            });
        },
        get: function (url, then, error, time) {
            $.ajax({
                cache: false,
                type: 'GET',
                url: url,
                timeout: (time || 5000),
                success: function (obj) {
                    if (self.isCallable(then)) {
                        then(obj);
                    } else {
                        console.log(obj);
                    }
                },
                error: function (err) {
                    if (self.isCallable(error)) {
                        error(err);
                    } else {
                        console.log(err);
                    }
                }
            });
        }
    }
    //axios
    self.axios = {
        demo: function () {
            var ajax = axios.create({
                baseURL: '/back/',
                timeout: 3000,
                responseType: "json",
                withCredentials: true,
                //headers: {"Content-Type": "application/json;charset=utf-8"},
            });
            //传参序列化(添加请求拦截器)
            axios.interceptors.request.use(
                (config) => {
                    config.data = JSON.stringify(config.data)
                    return config;
                },
                (error) => {
                    return Promise.reject(error);
                }
            );
            //返回状态判断(添加响应拦截器)
            axios.interceptors.response.use(
                (res) => {
                    return res;
                },
                (error) => {
                    return Promise.reject(error);
                }
            );
            return axios;
        },
        post: function (url, data, then, error) {
            axios.post(url, (self.isCallable(data) ? {} : data)).then(function (response) {
                if (self.isCallable(data)) {
                    data(response.data, response);
                } else if (self.isCallable(then)) {
                    then(response.data, response);
                } else {
                    console.log(response);
                }
            }).catch(function (err) {
                if (self.isCallable(data) && self.isCallable(then)) {
                    then(err);
                } else if (self.isCallable(error)) {
                    error(err);
                } else {
                    console.log(err);
                }
            });
        },
        get: function (url, then, error) {
            axios.get(url).then(function (response) {
                if (self.isCallable(then)) {
                    then(response.data, response);
                } else {
                    console.log(response);
                }
            }).catch(function (err) {
                if (self.isCallable(error)) {
                    error(err);
                } else {
                    console.log(err);
                }
            });
        }
    }
    //域名
    self.domain = {
        //获取协议
        protocol: function () {
            return window.location.protocol.split(':').slice(0, 1).join('')
        },
        //获取一级域名
        one: function () {
            return document.domain.split('.').slice(-2).join('.')
        },
        //获取当前链接
        get: function () {
            return window.location.href
        },
        //获取当前域名
        domain: function () {
            return this.protocol() + "://" + (window.location.href.split('/').slice(2, 3).join(''))
        }
    }
    //来路
    self.referrer = {
        //获取来路协议
        protocol: function () {
            return document.referrer.split('://').slice(0, 1).join('')
        },
        //获取来路一级域名
        one: function () {
            return (document.referrer.split('/').slice(2, 3).join('')).split('.').slice(-2).join('.')
        },
        //获取来路链接
        get: function () {
            return document.referrer
        },
        //获取来路域名
        domain: function () {
            return this.protocol() + "://" + (document.referrer.split('/').slice(2, 3).join(''))
        }
    }
    //是否禁用右键
    self.stopMenu = function () {
        $(document).bind('contextmenu', function () {
            return false;
        });
        return self;
    }
    //禁用选择
    self.stopSelect = function () {
        $(document).bind('selectstart', function () {
            return false;
        });
        return self;
    }
    //禁用复制
    self.stopCopy = function () {
        $(document).keydown(function () {
            return key(arguments[0])
        });
        return self;
    }
    //添加css
    self.addStyle = function (name, data) {
        let dom = ($('head') || $('body'));
        if (dom.find('style[' + name + ']').length === 0) {
            dom.append('<style rel="stylesheet" ' + name + '>' + data + '</style>');
        }
        return name;
    }
    //生成二维码
    self.qrCode = function (dom, str) {
        $(dom).qrcode({
            render: "canvas",
            width: 200,
            height: 200,
            background: "#FFFFFF",
            foreground: "#000000",
            correctLevel: 0,
            text: self.toUtf8(str)
        });
    }

    //返回val显示
    self.val = function (data, fun) {
        $.each((data || {}), function (k, v) {
            $('#' + k).val(v);
            if (self.isCallable(fun)) {
                fun(k, v);
            }
        });
    }
    //返回选中
    self.checked = function (data, fun) {
        $.each((data || {}), function (k, v) {
            $('#' + k).attr('checked', (v == 1));
            if (self.isCallable(fun)) {
                fun(k, v);
            }
        });
    }
    //返回选择
    self.select = function (data, fun) {
        $.each((data || {}), function (k, v) {
            $("#" + k + " option[value='" + v + "']").prop("selected", true);
            if (self.isCallable(fun)) {
                fun(k, v);
            }
        });
    }
    //转utf8
    self.toUtf8 = function (str) {
        let out = '', i, len = str.length, c;
        for (i = 0; i < len; i++) {
            c = str.charCodeAt(i);
            if ((c >= 0x0001) && (c <= 0x007F)) {
                out += str.charAt(i);
            } else if (c > 0x07FF) {
                out += String.fromCharCode(0xE0 | ((c >> 12) & 0x0F));
                out += String.fromCharCode(0x80 | ((c >> 6) & 0x3F));
                out += String.fromCharCode(0x80 | ((c >> 0) & 0x3F));
            } else {
                out += String.fromCharCode(0xC0 | ((c >> 6) & 0x1F));
                out += String.fromCharCode(0x80 | ((c >> 0) & 0x3F));
            }
        }
        return out;
    }
    //获取表单对像
    self.getForm = function (form) {
        let o = {}, a = $(form).serializeArray();
        $.each(a, function (k, v) {
            if (o[v.name] !== undefined) {
                if (!o[v.name].push) {
                    o[v.name] = [o[v.name]];
                }
                o[v.name].push(v.value || '');
            } else {
                o[v.name] = v.value || '';
            }
        });
        return o;
    }
    //复制
    self.cp = function (dom) {
        $(dom).css('cursor', 'pointer');
        var clipboard = new ClipboardJS(dom);
        clipboard.on('success', function (e) {
            layui.layer.msg('复制成功', {zIndex: layer.zIndex});
            e.clearSelection();
        });
        clipboard.on('error', function (e) {
            layui.layer.msg('浏览器不支持复制功能,请手动复制', {zIndex: layer.zIndex});
        });
    };
    //加载js css文件
    self.loader = {
        //加载js文件
        script: function (file) {
            $.each((self.isString(file) ? file.split(',') : file), function (k, v) {
                $.getScript(v);
            });
        },
        //加载css文件
        style: function (file) {
            $("<link>").attr({rel: "stylesheet", type: "text/css", href: file}).appendTo("head");
        },
        //加载js文件
        js: function (file) {
            let obj = document.createElement('script');
            obj.setAttribute('type', 'text/javascript');
            obj.setAttribute('src', file);
            document.getElementsByTagName('head')[0].appendChild(obj);
        },
        //加载css文件
        css: function (file) {
            let obj = document.createElement('link');
            obj.setAttribute('type', 'text/css');
            obj.setAttribute('rel', 'stylesheet');
            obj.setAttribute('href', file);
            document.getElementsByTagName("head")[0].appendChild(obj);
        },
    }
    //内容上下居中
    self.mainTop = function (dom, main, device) {
        let padding = function () {
            let bodyHeight = (window.innerHeight - 10), domHeight = (isNaN(dom) ? dom.innerHeight() : dom);
            if (bodyHeight > domHeight) {
                main.css('padding-top', ((bodyHeight - domHeight) / 2) + 'px');
            }
        }
        if (!(!self.isset(device) ? false : (self.isBrowsers().mobile))) {
            $(window).resize(function () {
                padding();
            });
            padding();
        }
    }
    //判断浏览器
    self.isBrowsers = function (width) {
        var useragent = navigator.userAgent.toLowerCase(), screen = window.screen.width, mobility = 1024;
        return {
            windows: /windows/.test(useragent),  //是否Windows系统
            mac: /mac/.test(useragent) && !/\(i[^;]+;( U;)? cpu.+mac os x/.test(useragent) && screen > mobility,  //是否Mac系统
            linux: /linux/.test(useragent) && !/android|adr/.test(useragent), //是否Linux系统
            ios: (/iphone|ipod|ipad|ios/.test(useragent) || (/mac/.test(useragent) && screen <= mobility)), //是否Ios系统
            android: /android|adr/.test(useragent),  //是否安卓系统
            mobile: /mobile|symbianos|windows phone|iphone|ipod|ipad|ios|android|adr|qq|micromessenger/.test(useragent) || screen <= mobility,//是否移动端
            iphone: /iphone/.test(useragent),//是否iphone
            ipad: (/iphone|ipod|ipad/.test(useragent) || (/mac/.test(useragent) && !/iphone/.test(useragent) && screen <= mobility)),  //是否ipad  测试有的ipad头信息和mac safari一样,只能通过屏幕分辩率来区分
            weixin: /micromessenger/.test(useragent),  //是否微信
            qq: /qq/.test(useragent), //是否qq
            weibo: /weibo/.test(useragent), //是否新浪微博客户端
            safari: useragent.indexOf('version') > 0 && /mac os x/.test(useragent), //苹果内核
            ie: /trident|msie|edg/.test(useragent) || window.ActiveXObject || "ActiveXObject" in window, //IE内核
            chrome: /chrome/.test(useragent), //谷歌内核
            firefox: useragent.indexOf('gecko') > -1 && useragent.indexOf('khtml') < 0, //火狐内核
            presto: /presto/.test(useragent), //opera内核
            mini: (document.documentElement.clientWidth <= (width || 768)),  //是否小窗口
            lang: (navigator.browserLanguage || navigator.language).toLowerCase() //语言
        };
    }
    //layer 弹窗
    self.lay = {
        /**
         * 左右居中
         * @param width
         * @param height
         */
        topLeft: function (width, height) {
            return [((window.innerHeight - (height || 0)) / 2) + 'px', ((window.innerWidth - (width || 0)) / 2) + 'px']
        },
        /**
         * layer宽高自动
         * @param width
         * @param height
         * @param set  [mw:手机宽,mh:手机高,m:开启手机]
         */
        heightWidth: function (width, height, set) {
            var bodyWidth = window.innerWidth,
                bodyHeight = window.innerHeight,
                intWidth = parseFloat(width),
                intHeight = parseFloat(height),
                mWidth = (set.mw || '100%'),
                mHeight = (set.mh || '100%');
            if ((width.substr(-1)) !== '%') {
                if (intWidth >= bodyWidth) {
                    width = mWidth;
                }
            }
            if ((height.substr(-1)) !== '%') {
                if (intHeight >= bodyHeight) {
                    height = mHeight;
                }
            }
            if (bodyWidth <= 720 && (set.m || '')) {
                width = mWidth;
                height = mHeight;
            }
            return [width, height];
        },
        /**
         *
         * @param url
         * @param title
         * @param width
         * @param height
         * @param set
         */
        use: function (url, title, width, height, set) {
            var cssVar = "";
            cssVar += ".skin_web_use{";
            cssVar += "border: 5px solid #8D8D8D;border-radius: 5px;box-shadow: none;";
            cssVar += "}";
            self.addStyle('lay_web_main', cssVar);
            return self.lay.open(url, title, width, height, self.arrayMerge({
                skin: 'skin_web_use'
            }, set))
        },
        /**
         *
         * @param url
         * @param title
         * @param width
         * @param height
         * @param set
         */
        user: function (url, title, width, height, set) {
            var cssVar = "";
            cssVar += ".skin_web_dark{";
            cssVar += "    border: 2px solid #423939;";
            cssVar += "    background-color: #1F2125";
            cssVar += "}";
            cssVar += ".skin_web_dark .layui-layer-title {";
            cssVar += "    padding: 0 20px 0 20px;";
            cssVar += "    font-size: 19px;";
            cssVar += "    font-style: inherit;";
            cssVar += "    font-weight: inherit;";
            cssVar += "    color: #ffffff;";
            cssVar += "    background-color: #555041;";
            cssVar += "    background: linear-gradient(225deg, hsl(242deg 48% 62% / 0%), #7c7156);";
            cssVar += "    border-bottom: 0;";
            cssVar += "}";
            self.addStyle('skin_web_dark', cssVar);
            return self.lay.open(url, title, width, height, self.arrayMerge({
                skin: 'skin_web_dark',
                success: function () {
                    $('.skin_web_dark').removeClass('layui-layer-page');
                    $('.skin_web_dark .layui-layer-setwin .layui-layer-close').removeClass('layui-layer-ico').html('✕').css({
                        'font-weight': 'bold',
                        'margin-top': '-8px',
                        'right': '3px',
                        'color': '#FFFFFF',
                        'font-size': '22px'
                    });
                }
            }, set))
        },
        /**
         *
         * @param url
         * @param title
         * @param width
         * @param height
         * @param set
         */
        dark: function (url, title, width, height, set) {
            var cssVar = "";
            cssVar += ".skin_web_dark{";
            cssVar += "    border: 0;";
            cssVar += "    border-radius: " + (set.b || '15') + "px;";
            cssVar += "    background-color: #1F2125";
            cssVar += "}";
            cssVar += ".skin_web_dark .layui-layer-title {";
            cssVar += "    padding: 0 20px 0 20px;";
            cssVar += "    border-radius: " + (set.b || '15') + "px;";
            cssVar += "    font-size: 19px;";
            cssVar += "    font-style: inherit;";
            cssVar += "    font-weight: inherit;";
            cssVar += "    color: #ffffff;";
            cssVar += "    background: #1f2125;";
            cssVar += "    border-bottom: 0;";
            cssVar += "}";
            self.addStyle('skin_web_dark', cssVar);
            return self.lay.open(url, title, width, height, self.arrayMerge({
                skin: 'skin_web_dark',
                success: function () {
                    $('.skin_web_dark').removeClass('layui-layer-page');
                    $('.skin_web_dark .layui-layer-setwin .layui-layer-close').removeClass('layui-layer-ico').html('✕').css({
                        'font-weight': 'bold',
                        'margin-top': '-8px',
                        'right': '3px',
                        'color': '#FFFFFF',
                        'font-size': '22px'
                    });
                }
            }, set))
        },
        //https://layuion.com/docs/modules/layer.html#type
        /**
         * 打开窗口
         * @param url
         * @param title
         * @param width
         * @param height
         * @param set
         */
        open: function (url, title, width, height, set) {
            set = self.arrayMerge({
                type: 2,//0（信息框，默认）1（页面层）2（iframe层）3（加载层）4（tips层）
                title: title,//标题,可以 ['文本', 'font-size:18px;']，不想显示标题栏，你可以title: false
                content: url,//内容
                area: self.lay.heightWidth(width, height, set), //宽高,默认：'auto'
                closeBtn: 1,//关闭按钮,默认：1,可通过配置1和2来展示，如果不显示，则closeBtn: 0
                shade: 0.6,//遮罩
                anim: 0,//弹出动画,0平滑放大,1从上掉落,2从最底部往上滑入,3从左滑入,4从左翻滚,5渐显,6抖动
                resize: false,//是否允许拉伸,//该参数对loading、tips层无效
                scrollbar: false,//默认：true允许浏览器滚动，如果设定scrollbar: false，则屏蔽
                move: false,//不能拉动
                //fixed: true,//固定,//即鼠标滚动时，层是否固定在可视区域。如果不想，设置fixed: false即可
                //offset: 'auto',//坐标垂直水平居中,top、left坐标
                //shadeClose: false,//是否点击遮罩关闭
                //isOutAnim: true,//关闭动画，关闭层时会有一个过度动画。如果你不想开启，设置 isOutAnim: false 即可
                //maxmin: false,//最大最小化,该参数值对type:1和type:2有效。默认不显示最大小化按钮。需要显示配置maxmin: true即可
                //skin: '',//样式类名  //内置 layui-layer-lan layui-layer-molv
                //监听窗口拉伸动作:resizing
                //创建窗口完毕动作:success
                //右上角关闭完动作:cancel
                //层销毁后触发动作:end
                //最大化触发完动作:full
                //最大化触发完动作:min
                //还原触发完毕动作:restore
            }, (set || {}));
            var index = layer.open(set);
            if ((set.type || 2) === 1) {
                layui.form.render();
            }
            return index;
        }
    }
    //开始倒计时(定时器名称,开始时间,结束时间,执行倒计时时方法,结束时方法)
    self.fallStart = function (name, top_time, end_time, way, end) {
        var t = 0, fallArr = {}, fun = function (top_time, end_time, t) {
            var nowtime = new Date(top_time.replace(/-/g, "/")),  //获取当前时间
                endtime = new Date(end_time.replace(/-/g, "/"));  //定义结束时间
            var lefttime = endtime.getTime() - nowtime.getTime() - (t * 1000),  //距离结束时间的毫秒数
                leftd = Math.floor(lefttime / (1000 * 60 * 60 * 24)),  //计算天数
                lefth = Math.floor(lefttime / (1000 * 60 * 60) % 24),  //计算小时数
                leftm = Math.floor(lefttime / (1000 * 60) % 60),  //计算分钟数
                lefts = Math.floor(lefttime / 1000 % 60);  //计算秒数
            return (lefttime > 0 ? ((leftd > 0 ? leftd + "天" : "") + (lefth <= 9 ? '0' + lefth : lefth) + ":" + (leftm <= 9 ? '0' + leftm : leftm) + ":" + (lefts <= 9 ? '0' + lefts : lefts)) : 0);
        };
        way = (self.isCallable(way) ? way : function (time) {
            console.log(time);
        });
        end = (self.isCallable(end) ? end : function () {
            console.log('end');
        });
        way(fun(top_time, end_time, t));
        var timeName = setInterval(function () {
            ++t;
            var count = fun(top_time, end_time, t);
            if (count <= 0) {
                end();
                clearInterval(timeName);
            } else {
                way(count);
            }
        }, 1000);
        fallArr[name] = timeName;
        self.fallName = self.arrayMerge(self.fallName, fallArr);
    }
    //停止倒计时
    self.fallStop = function (name) {
        if ((self.fallName || '')) {
            if (self.isKey(self.fallName, name)) {
                clearInterval((self.fallName)[name]);
            }
        }
    }
    //全屏操作
    self.fullScreen = {
        /**
         *
         * @param enter  进入全屏时执行的方法
         * @param exit   退出全屏时执行的方法
         * @param error  不支持全屏时执行的方法
         */
        start: function (enter, exit, error) {
            if (this.isFull()) {
                if (this.is()) {
                    this.exit();
                    if (self.isCallable(exit)) {
                        exit();
                    }
                } else {
                    this.enter();
                    if (self.isCallable(enter)) {
                        enter();
                    }
                }
                return;
            }
            console.log('不支持全屏');
            if (self.isCallable(error)) {
                error();
            }
        },
        /**
         * 进入全屏
         */
        enter: function () {
            var ele = document.documentElement;
            if (ele.requestFullscreen) {
                ele.requestFullscreen();
            } else if (ele.mozRequestFullScreen) {
                ele.mozRequestFullScreen();
            } else if (ele.webkitRequestFullscreen) {
                ele.webkitRequestFullscreen();
            } else if (ele.msRequestFullscreen) {
                ele.msRequestFullscreen();
            }
        },
        /**
         * 退出全屏
         */
        exit: function () {
            if (document.exitFullScreen) {
                document.exitFullScreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        },
        /**
         * 判断当前是否全屏
         */
        is: function () {
            return !!(
                document.fullscreenElement ||
                document.mozFullScreen ||
                document.webkitIsFullScreen ||
                document.webkitFullScreen ||
                document.msFullScreen
            );
        },
        /**
         * 判断当前文档是否能切换到全屏
         */
        isFull: function () {
            return (
                document.fullscreenEnabled ||
                document.mozFullScreenEnabled ||
                document.webkitFullscreenEnabled ||
                document.msFullscreenEnabled
            );
        },
        /**
         * 获取当前全屏的节点
         */
        getFull: function () {
            return (
                document.fullscreenElement ||
                document.mozFullScreenElement ||
                document.msFullScreenElement ||
                document.webkitFullscreenElement || null
            );
        }
    }
    //验证
    self.check = {
        //验证身份证位数
        idcardNumtest: function (txt) {
            return txt.toString().length === 15 || txt.toString().length == 18 ? true : false
        },
        //是否包含数字
        inInt: function (data) {
            return /\d/g.test(data)
        },
        //是否包含大写
        inD: function (data) {
            return /[A-Z]+/g.test(data)
        },
        //是否包含小写
        inL: function (data) {
            return /[a-z]+/g.test(data)
        },
        //是否包含汉字
        inStr: function (data) {
            return /[\u4E00-\u9FA5]+/g.test(data)
        },
        //验证邮箱
        mail: function (data) {
            return /\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/.test(data)
        },
        //验证手机
        mobile: function (data) {
            return /^1(3|4|5|6|7|8|9)\d{9}$/.test(data)
        },
        //验证姓名
        name: function (data) {
            return /^[\u4E00-\u9FA5]{2,4}$/.test(data)
        },
        //验证帐号
        user: function (data, top, end, type) {
            return (new RegExp((self.isset(type) ? "^[A-Za-z0-9\x20-\x7f]{" + (top || 4) + "," + (end || 10) + "}$" : "^[A-Za-z0-9]{" + (top || 4) + "," + (end || 10) + "}$"))).test(data);
        },
        //验证密码 (密码,最小位数,最大位数,是否支持符号,array|data|code)
        pass: function (a, e, t, d, o) {
            var r, c, h, A = 0, C = 0, f = !1, l = !1, i = !1, n = !1, s = 0,
                g = [{code: 0, data: "密码须含数字/字母"}, {code: 1, data: "密码内太多重复字串"}, {
                    code: 2,
                    data: "密码不可使用空白非英文语系的字"
                }, {code: 3, data: "密码中太多顺序字串"}, {
                    code: 4,
                    data: "密码由" + (e || 6) + "-" + (t || 6) + "位数字/字母" + (d ? "/符号" : "") + "组成"
                }, {code: 5, data: "密码不能带有符号"}, {code: 6, data: "密码最少包含一个字母"}, {code: 7, data: "密码安全程序底"}, {
                    code: 8,
                    data: "密码安全程序中"
                }, {code: 9, data: "密码安全程序高"}];
            if (/^(?![^a-zA-Z]+$)(?!\D+$)/.test(a) && ((a.length < (e || 6) || a.length > (t || 16)) && (s = 4), d || /^[a-zA-Z0-9]{1,}$/.test(a) || (s = 5), !s)) {
                for (r = 0; r < a.length; r++) if ((c = a.split(a.substr(r, 1))).length > 4) {
                    for (C = 0, h = 0; h < c.length; h++) 0 === c[h].length && h > 0 && C++;
                    if (C >= 3) {
                        A = 1;
                        break
                    }
                } else if (a.charCodeAt(r) >= 33 && a.charCodeAt(r) <= 47 || a.charCodeAt(r) >= 58 && a.charCodeAt(r) <= 64 || a.charCodeAt(r) >= 91 && a.charCodeAt(r) <= 96 || a.charCodeAt(r) >= 123 && a.charCodeAt(r) <= 126) A += 10, f = !0; else if (a.charCodeAt(r) >= 48 && a.charCodeAt(r) <= 57) A += 4, l = !0; else {
                    if (!(a.charCodeAt(r) >= 65 && a.charCodeAt(r) <= 90 || a.charCodeAt(r) >= 97 && a.charCodeAt(r) <= 122)) {
                        A = 2;
                        break
                    }
                    A += 7, a.charCodeAt(r) >= 65 && a.charCodeAt(r) <= 90 ? n = !0 : i = !0
                }
                if (A > 0) {
                    if ((f && l || f && n || f && i) && (A += 10), (l && n || l && i) && (A += 8), n && i && (A += 8), a.length >= 4) for (r = 0; r < a.length - 3; r++) if ((a.charCodeAt(r) >= 48 && a.charCodeAt(r) <= 57 || a.charCodeAt(r) >= 65 && a.charCodeAt(r) <= 90 || a.charCodeAt(r) >= 97 && a.charCodeAt(r) <= 122) && 1 === Math.abs(a.charCodeAt(r) - a.charCodeAt(r + 1)) && 1 === Math.abs(a.charCodeAt(r + 1) - a.charCodeAt(r + 2)) && 1 === Math.abs(a.charCodeAt(r + 2) - a.charCodeAt(r + 3))) {
                        A = 3;
                        break
                    }
                    A = A > 100 ? 100 : 0 > A ? 0 : A
                }
                s = A
            }
            return 32 === s ? s = 6 : s > 5 && s <= 45 ? s = 7 : s > 45 && s <= 59 ? s = 8 : s > 59 && (s = 9), "array" === (o || "array") ? g[s] : g[s][o]
        }
    }
    //打开窗口
    self.open = {
        html: function (url, name, width, height, skin) {
            self.ajax.get(url, function (data) {
                layer.open({
                    type: 1,
                    title: name,
                    closeBtn: 1,
                    shadeClose: false,
                    shade: 0.8,
                    move: false,
                    skin: this.css(skin),
                    area: (self.isset(self.isBrowsers().mobile) ? ['100%', '100%'] : this.window(width, height)),
                    content: data
                });
            });
        },
        web: function (url, name, width, height, skin) {
            layer.open({
                type: 2,
                title: name,
                closeBtn: 1,
                shadeClose: false,
                shade: 0.8,
                move: false,
                skin: this.css(skin),
                area: (self.isset(self.isBrowsers().mobile) ? ['100%', '100%'] : this.window(width, height)),
                content: url
            });
        },
        window: function (width, height) {
            var innerWidth = window.innerWidth;
            return [(innerWidth > 720 ? (width || "720px") : '100%'), (height ? (innerWidth > 720 ? ($(document).height() >= parseInt(height) ? height : "80%") : '100%') : "95%")];
        },
        css: function (skin) {
            var head = $('head');
            if (head.find('style[OpenWebHtml]').length === 0) {
                head.append('<style rel="stylesheet" OpenWebHtml>.open_layer_skin {' + ((skin || '') ? skin : 'border: 6px solid #8D8D8D;border-radius: 5px;box-shadow: none;') + '}</style>');
            }
            return 'open_layer_skin';
        },
        post: function (url, data, then, error) {
            var index = layer.load(1, {shade: [0.8, 'rgb(0, 0, 0)']});
            $.ajax({
                cache: false,
                type: 'POST',
                url: url,
                timeout: 5000,
                data: (self.isCallable(data) ? {} : data),
                dataType: "json",
                success: function (obj) {
                    if (self.isCallable(data)) {
                        data(obj);
                    } else if (self.isCallable(then)) {
                        then(obj);
                    } else {
                        console.log(obj);
                    }
                    layer.close(index);
                },
                error: function (err) {
                    if (self.isCallable(data) && self.isCallable(then)) {
                        then(err);
                    } else if (self.isCallable(error)) {
                        error(err);
                    } else {
                        console.log(err);
                    }
                    layer.close(index);
                }
            });
        },
        get: function (url, then, error) {
            var index = layer.load(1, {shade: [0.8, 'rgb(0, 0, 0)']});
            $.ajax({
                cache: false,
                type: 'GET',
                url: url,
                timeout: 5000,
                success: function (obj) {
                    if (self.isCallable(then)) {
                        then(obj);
                    } else {
                        console.log(obj);
                    }
                    layer.close(index);
                },
                error: function (err) {
                    if (self.isCallable(error)) {
                        error(err);
                    } else {
                        console.log(err);
                    }
                    layer.close(index);
                }
            });
        }
    }
    /**
     * 自动播放
     * 在线格式转换  https://convertio.co/zh/ogg-converter/
     * file: '/style/music/hint',//文件名,不带格式
     * start: function () { console.log('start');},//启动时执行操作
     * stop: function () {console.log('stop');},//停止时执行操作
     * wav:true,//关闭wav(默认开启)
     * ogg:true,//关闭ogg(默认开启)
     * mp3:true //关闭mp3(默认开启)
     * start(播放次数,间隔时间) 启动播放
     * stop() 停止播放
     * @param set
     * @param play_rand
     */
    self.playAudio = function (set, play_rand) {
        var selfObj = {}, i_play = 0, is_play = false,
            play_start = (set.start || function () {
                console.log('start');
            }), play_stop = (set.stop || function () {
                console.log('stop');
            }), play_audio = function () {
                return $('#web_play_' + play_rand).get(0);
            }, play_html = function () {
                $("body").append('<div style="display: none;"><audio id="web_play_' + play_rand + '" muted="muted">' + ((set.wav || '') ? '' : '<source src="' + (set.file) + '.wav" type="audio/wav">') + ((set.ogg || '') ? '' : '<source src="' + (set.file) + '.ogg" type="audio/ogg">') + ((set.mp3 || '') ? '' : '<source src="' + (set.file) + '.mp3" type="audio/mpeg"><embed height="50" width="50" src="' + (set.file) + '.mp3">') + '</div>');
                return selfObj;
            };
        $(document).mousedown(function () {
            if (!is_play) {
                play_audio().muted = true;
                play_audio().play();
                play_audio().currentTime = 0;
                play_audio().pause();
                is_play = true;
            }
        });
        selfObj.start = function (hits, time) {
            play_start();
            play_audio().muted = false;
            play_audio().play();
            selfObj.playId = setInterval(function () {
                ++i_play;
                if (i_play < hits) {
                    play_audio().play();
                } else {
                    selfObj.stop();
                }
            }, ((time || 8) * 1000));
            return selfObj;
        };
        selfObj.stop = function () {
            i_play = 0;
            play_stop();
            play_audio().currentTime = 0;
            play_audio().pause();
            clearInterval(selfObj.playId);
            return selfObj;
        }
        return play_html();
    }
    return self;
})();