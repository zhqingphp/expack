window.Frame = (function () {
    var self = {};
    /**
     * {title}   {title:'标题'}
     * @param html //html内容
     * @param data //对像
     */
    self.view = function (html, data) {
        return html.replace(new RegExp("{(\\w+)}", "ig"), function (text, key) {
            return data[key];
        });
    };
    /**
     * 倒计时
     * @param name  //定时器名称
     * @param top_time  //开始时间
     * @param end_time   /结束时间
     * @param way    //执行倒计时时方法
     * @param end   //结束时方法
     */
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
    self.fallStop = function (name) {
        if ((self.fallName || '')) {
            if (self.isKey(self.fallName, name)) {
                clearInterval((self.fallName)[name]);
            }
        }
    }
    self.date = function (time) {
        var t = {}, b = function (str) {
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
    /**
     * 获取url#号后的参数
     */
    self.getHash = function () {
        return (window.location.hash).substr(1);
    }
    /**
     *设置URL#号后面的参数
     */
    self.setHash = function (url) {
        window.history.replaceState(null, null, (window.location.pathname + window.location.search + '#' + url));
    }
    /**
     *
     * @param url
     */
    self.openUrl = function (url) {
        window.open("about:blank").location.href = url;
    }
    /**
     *
     */
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
    /**
     * 打开中间窗口
     * @param url
     * @param width
     * @param height
     * @param name
     */
    self.openWindow = function (url, width, height, name) {
        (window.open(url, name, 'resizable=yes, menubar=no,scrollbars=yes,location=no,status=yes,width=' + width + ',height=' + height + ',top=' + ((window.screen.availHeight - 30 - height) / 2) + ',left=' + ((window.screen.availWidth - 10 - width) / 2))).focus();
    }

    /**
     * layer 弹窗
     */
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
    /**
     * 返回val显示
     * @param data
     * @param fun
     */
    self.val = function (data, fun) {
        $.each((data || {}), function (k, v) {
            $('#' + k).val(v);
            if (self.isCallable(fun)) {
                fun(k, v);
            }
        });
    }
    /**
     * 返回选中
     * @param data
     * @param fun
     */
    self.checked = function (data, fun) {
        $.each((data || {}), function (k, v) {
            $('#' + k).attr('checked', (v == 1));
            if (self.isCallable(fun)) {
                fun(k, v);
            }
        });
    }
    /**
     * 返回选择
     * @param data
     * @param fun
     */
    self.select = function (data, fun) {
        $.each((data || {}), function (k, v) {
            $("#" + k + " option[value='" + v + "']").prop("selected", true);
            if (self.isCallable(fun)) {
                fun(k, v);
            }
        });
    }
    /**
     * 格式化金额
     * @param number
     * @param decimals
     * @param thousands
     * @param separator
     */
    self.number_format = function (number, decimals, thousands, separator) {
        number = parseFloat((number || '0.00')).toFixed((decimals || 2));
        separator = separator ? separator : '.';
        thousands = thousands ? thousands : ',';
        var source = String(number).split(".");
        source[0] = source[0].replace(new RegExp('(\\d)(?=(\\d{3})+$)', 'ig'), "$1" + thousands);
        return source.join(separator);
    }
    /**
     * 合并对像/数组
     * @param arr
     * @param array
     */
    self.arrayMerge = function (arr, array) {
        return $.extend(arr, array);
    }
    /**
     * 是否字符串
     * @param data
     */
    self.isString = function (data) {
        return typeof (data) === 'string';
    }
    /**
     * 是否function
     * @param data
     */
    self.isCallable = function (data) {
        return typeof (data) === 'function';
    }
    /**
     * 是否数组
     * @param data
     */
    self.isArray = function (data) {
        return $.isArray(data);
    }
    /**
     * 是否对像
     * @param data
     * @returns {*}
     */
    self.isObject = function (data) {
        return $.isPlainObject(data);
    }
    /**
     * 数组/对像的key是否存在
     * @param data
     * @param key
     */
    self.isKey = function (data, key) {
        return data.hasOwnProperty(key);
    }
    /**
     * 数组/对像是否有值
     * @param data
     */
    self.isArr = function (data) {
        if (typeof (data) === 'object') {
            if (JSON.stringify(data) === '{}' || JSON.stringify(data) === '[]') {
                return false;
            }
        }
        return true;
    }
    /**
     * 对像转json
     * @param data
     */
    self.json = function (data) {
        return JSON.stringify(data);
    }
    /**
     * 变量是否有值
     * @param data
     */
    self.isset = function (data) {
        if (typeof (data) === 'undefined' || data === 'undefined' || data === undefined || data === 'null' || data == null || !data) {
            return false;
        } else if (!self.isArr(data)) {
            return false;
        }
        return true;
    }
    /**
     * 获取对像key的内容
     * @param data
     * @param key
     * @param val
     */
    self.getObj = function (data, key, val) {
        return self.isset(data) ? (self.isKey(data, key) ? (self.isset(data[key]) ? data[key] : val) : val) : val;
    }
    /**
     * 获取数组key的内容
     * @param data
     * @param key
     * @param val
     */
    self.getArr = function (data, key, val) {
        $.each(key.split('.'), function (k, v) {
            if (self.isKey(data, v)) {
                data = self.getObj(data, v);
            }
        });
        return self.isset(data) ? data : val;
    }
    /**
     * 对像转数组
     * @param data
     */
    self.toArr = function (data) {
        return Object.keys(data).map(function (v) {
            return data[v];
        });
    }
    /**
     * 数组转对像
     * @param data
     */
    self.toObj = function (data) {
        return JSON.parse(data)
    }

    /**
     * 获取url参数
     * @param key
     */
    self.getQuery = function (key) {
        var i, Query = window.location.search.substring(1), Val = Query.split("&");
        for (i = 0; i < Val.length; i++) {
            var Arr = Val[i].split("=");
            if (Arr[0] === key) {
                return Arr[1];
            }
        }
        return '';
    }
    /**
     * URL的get参数转为array
     * @param url
     */
    self.urlToObj = function (url) {
        var arr = url.split("?")[1].split("&");
        var obj = {};
        for (var i of arr) {
            obj[i.split("=")[0]] = i.split("=")[1];
        }
        return obj;
    }
    /**
     * 生成指定字符串数量
     * 这个方法支持IE
     * @param str   //字符串
     * @param num  //生成数量
     */
    self.repeat = function (str, num) {
        return new Array(num + 1).join(str);
    }
    /**
     * 随机生成字符串
     * @returns {string}
     */
    self.randStr = function () {
        return (Math.random() * 10000000).toString(16).substr(0, 4) + (new Date()).getTime() + Math.random().toString().substr(2, 5);
    }
    /**
     * 生成随机数字
     * @param int
     */
    self.randInt = function (int) {
        return (Math.floor(Math.random() * (int || 10)));
    }
    /**
     * 复制
     * @param dom
     */
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
    /**
     * 复制
     * @param dom
     */
    self.copy = function (dom) {
        dom.css('cursor', 'pointer');
        dom.click(function () {
            $(this).select();
            document.execCommand("Copy");
            layui.layer.msg('复制成功', {zIndex: layer.zIndex});
        })
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
     */
    self.playAudio = function (set) {
        var selfObj = {}, play_rand = self.randStr(), i_play = 0, is_play = false,
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

    /**
     * 生成二维码
     * @param dom
     * @param str
     */
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
    /**
     * 转utf8
     * @param str
     */
    self.toUtf8 = function (str) {
        var out, i, len, c;
        out = "";
        len = str.length;
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
    /**
     * 获取表单对像
     * @param form
     */
    self.getForm = function (form) {
        var o = {};
        var a = $(form).serializeArray();
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

    /**
     * 判断浏览器
     * @param width
     */
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
    /**
     * 全屏操作
     */
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
    /**
     * 内容上下居中
     * @param dom
     * @param main
     * @param device
     */
    self.mainTop = function (dom, main, device) {
        var padding = function () {
            var bodyHeight = (window.innerHeight - 10), domHeight = (isNaN(dom) ? dom.innerHeight() : dom);
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
    /**
     * 获取域名
     */
    self.domain = {
        /**
         * 获取协议
         */
        protocol: function () {
            return window.location.protocol.split(':').slice(0, 1).join('');
        },
        /**
         * 获取一级域名
         */
        one: function () {
            return document.domain.split('.').slice(-2).join('.');
        },
        /**
         * 获取当前链接
         */
        get: function () {
            return window.location.href;
        },
        /**
         * 获取当前域名
         */
        domain: function () {
            return this.protocol() + "://" + (window.location.href.split('/').slice(2, 3).join(''));
        }
    }
    /**
     * 获取来路
     */
    self.referrer = {
        /**
         * 获取来路协议
         */
        protocol: function () {
            return document.referrer.split('://').slice(0, 1).join('');
        },
        /**
         * 获取来路一级域名
         */
        one: function () {
            return (document.referrer.split('/').slice(2, 3).join('')).split('.').slice(-2).join('.');
        },
        /**
         * 获取来路链接
         */
        get: function () {
            return document.referrer;
        },
        /**
         * 获取来路域名
         */
        domain: function () {
            return this.protocol() + "//" + (document.referrer.split('/').slice(2, 3).join(''));
        }
    }
    /**
     *
     * 是否禁用右键
     */
    self.stopMenu = function () {
        $(document).bind('contextmenu', function () {
            return false;
        });
        return self;
    }
    /**
     * 禁用选择
     */
    self.stopSelect = function () {
        $(document).bind('selectstart', function () {
            return false;
        });
        return self;
    }
    /**
     * 禁用复制
     */
    self.stopCopy = function () {
        $(document).keydown(function () {
            return key(arguments[0])
        });
        return self;
    }
    /**
     * 添加css
     * @param name
     * @param data
     */
    self.addStyle = function (name, data) {
        var dom = ($('head') || $('body'));
        if (dom.find('style[' + name + ']').length === 0) {
            dom.append('<style rel="stylesheet" ' + name + '>' + data + '</style>');
        }
        return name;
    }
    /**
     * 加载js css文件
     */
    self.loader = {
        /**
         * 加载js文件
         * @param file
         */
        script: function (file) {
            $.each((self.isString(file) ? file.split(',') : file), function (k, v) {
                $.getScript(v);
            });
        },
        /**
         * 加载css文件
         * @param file
         */
        style: function (file) {
            $("<link>").attr({rel: "stylesheet", type: "text/css", href: file}).appendTo("head");
        },
        /**
         * 加载js文件
         * @param file
         */
        js: function (file) {
            var obj = document.createElement('script');
            obj.setAttribute('type', 'text/javascript');
            obj.setAttribute('src', file);
            document.getElementsByTagName('head')[0].appendChild(obj);
        },
        /**
         * 加载css文件
         * @param file
         */
        css: function (file) {
            var obj = document.createElement('link');
            obj.setAttribute('type', 'text/css');
            obj.setAttribute('rel', 'stylesheet');
            obj.setAttribute('href', file);
            document.getElementsByTagName("head")[0].appendChild(obj);
        },
    }
    /**
     * 验证方法
     */
    self.check = {
        //是否包含数字
        in_num: function (data) {
            return /\d/g.test(data);
        },
        //是否包含大写
        in_c: function (data) {
            return /[A-Z]+/g.test(data);
        },
        //是否包含小写
        in_l: function (data) {
            return /[a-z]+/g.test(data);
        },
        //是否包含汉字
        in_str: function (data) {
            return /[\u4E00-\u9FA5]+/g.test(data);
        },
        /**
         * 验证邮箱
         * @param data
         */
        mail: function (data) {
            return /\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/.test(data);
        },
        /**
         * 验证手机
         * @param data
         */
        mobile: function (data) {
            return /^1(3|4|5|6|7|8|9)\d{9}$/.test(data);
        },
        /**
         * 验证姓名
         * @param data
         */
        name: function (data) {
            return /^[\u4E00-\u9FA5]{2,4}$/.test(data);
        },
        /**
         * 验证帐号
         * @param data
         * @param top
         * @param end
         * @param type  是否支持符号(true=支持)
         */
        user: function (data, top, end, type) {
            return (new RegExp((self.isset(type) ? "^[A-Za-z0-9\x20-\x7f]{" + (top || 4) + "," + (end || 10) + "}$" : "^[A-Za-z0-9]{" + (top || 4) + "," + (end || 10) + "}$"))).test(data);
        },
        /**
         * 验证密码
         * @param a 密码
         * @param e 最小位数
         * @param t 最大位数
         * @param d 是否支持符号
         * @param o array|data|code
         * @returns {*}
         */
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
    self.axios = {
        axiosDemo: function () {
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
    self.cookie = {
        set: function (name, val, hours) {
            var Str = name + "=" + escape(val), Data = new Date();
            if ((hours || 0) > 0) {
                Data.setTime(Data.getTime() + (hours * 1000));
                Str += "; expires=" + Data.toGMTString();
            }
            document.cookie = Str + ";path=/;domain=" + document.domain + ";";
        },
        get: function (name, def) {
            var i, Arr = document.cookie.split("; ");
            for (i = 0; i < Arr.length; i++) {
                var data = Arr[i].split("=");
                if (data[0] === name) {
                    return unescape(data[1]);
                }
            }
            return (def || '');
        },
        del: function (name) {
            var Exp = new Date()
            Exp.setTime(Exp.getTime() - 1);
            document.cookie = name + "=;path=/;domain=" + document.domain + ";expires=" + Exp.toUTCString();
        },
        delete: function () {
            var keys = document.cookie.match(/[^ =;]+(?=\=)/g);
            if (keys) {
                for (var i = keys.length; i--;) {
                    document.cookie = keys[i] + '=0;path=/;expires=' + new Date(0).toUTCString();
                    document.cookie = keys[i] + '=0;path=/;domain=' + document.domain + ';expires=' + new Date(0).toUTCString();
                }
            }
        }
    }
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
    /**
     * md5加密
     * @param data
     * @returns {*}
     */
    self.md5 = function (data) {
        return CryptoJS.MD5(data).toString();
    }
    /**
     * base64加解密
     */
    self.base = {
        encrypt: function (data) {
            return CryptoJS.enc.Base64.stringify(CryptoJS.enc.Utf8.parse(data));
        },
        decrypt: function (data) {
            return CryptoJS.enc.Base64.parse(data).toString(CryptoJS.enc.Utf8);
        },
    }
    self.sha1 = function (data) {
        return CryptoJS.SHA1(data).toString();
    }
    self.sha3 = function (data) {
        return CryptoJS.SHA3(data).toString();
    }
    self.sha256 = function (data) {
        return CryptoJS.SHA256(data).toString();
    }
    self.sha512 = function (data) {
        return CryptoJS.SHA512(data).toString();
    }
    /**
     * aes加解密方法
     */
    self.aes = {
        /**
         * aes加密
         * @param data
         * @param key
         * @param iv
         */
        encrypt: function (data, key, iv) {
            return ((iv || '') && (iv || '').length < 16) ? "iv length 16 bits" : (CryptoJS.AES.encrypt(CryptoJS.enc.Utf8.parse(data)
                , CryptoJS.enc.Utf8.parse((key.length > 16 ? key.substr(0, 16) : key))
                , ((iv || '') ? {
                    iv: CryptoJS.enc.Utf8.parse((iv.length > 16 ? iv.substr(0, 16) : iv)),
                    mode: CryptoJS.mode.CBC,
                    padding: CryptoJS.pad.Pkcs7
                } : {
                    mode: CryptoJS.mode.ECB,
                    padding: CryptoJS.pad.Pkcs7
                })).toString());
        },
        /**
         * aes解密
         * @param data
         * @param key
         * @param iv
         */
        decrypt: function (data, key, iv) {
            return ((iv || '') && (iv || '').length < 16) ? "iv length 16 bits" : (CryptoJS.AES.decrypt(data
                , CryptoJS.enc.Utf8.parse((key.length > 16 ? key.substr(0, 16) : key))
                , ((iv || '') ? {
                    iv: CryptoJS.enc.Utf8.parse((iv.length > 16 ? iv.substr(0, 16) : iv)),
                    mode: CryptoJS.mode.CBC,
                    padding: CryptoJS.pad.Pkcs7
                } : {
                    mode: CryptoJS.mode.ECB,
                    padding: CryptoJS.pad.Pkcs7
                })).toString(CryptoJS.enc.Utf8));
        },
        /**
         * 动态加密
         * @param data
         */
        en: function (data) {
            var rand = self.randStr().toString(), iv = (Math.floor(Math.random() * 17));
            return {
                random: rand + (iv.toString().length === 1 ? '0' + iv : iv),
                data: this.encrypt(self.json(data), (self.md5(rand)).substr(iv, 16))
            };
        },
        /**
         * 动态解密
         * @param data
         * @param random
         */
        de: function (data, random) {
            return self.toObj(this.decrypt(data, (self.md5(random.substr(0, (random.length - 2)))).substr(parseInt(random.substr(-2)), 16)))
        }
    }
    self.des = {
        /**
         * DES3加密
         * @param data
         * @param key
         * @param iv
         */
        encrypt: function (data, key, iv) {
            return ((iv || '') && (iv || '').length < 8) ? "iv length 8 bits" : (CryptoJS.TripleDES.encrypt(CryptoJS.enc.Utf8.parse(data)
                , CryptoJS.enc.Utf8.parse((key.length > 24 ? key.substr(0, 24) : key))
                , ((iv || '') ? {
                    iv: CryptoJS.enc.Utf8.parse((iv.length > 8 ? iv.substr(0, 8) : iv)),
                    mode: CryptoJS.mode.CBC,
                    padding: CryptoJS.pad.Pkcs7
                } : {
                    mode: CryptoJS.mode.ECB,
                    padding: CryptoJS.pad.Pkcs7
                })).toString());
        },
        /**
         * DES3加密
         * @param data
         * @param key
         * @param iv
         */
        decrypt: function (data, key, iv) {
            return ((iv || '') && (iv || '').length < 8) ? "iv length 8 bits" : (CryptoJS.TripleDES.decrypt(data
                , CryptoJS.enc.Utf8.parse((key.length > 24 ? key.substr(0, 24) : key))
                , ((iv || '') ? {
                    iv: CryptoJS.enc.Utf8.parse((iv.length > 8 ? iv.substr(0, 8) : iv)),
                    mode: CryptoJS.mode.CBC,
                    padding: CryptoJS.pad.Pkcs7
                } : {
                    mode: CryptoJS.mode.ECB,
                    padding: CryptoJS.pad.Pkcs7
                })).toString(CryptoJS.enc.Utf8));
        },
        /**
         * DES加密
         * @param data
         * @param key
         * @param iv
         */
        encryption: function (data, key, iv) {
            return ((iv || '') && (iv || '').length < 8) ? "iv length 8 bits" : (CryptoJS.DES.encrypt(CryptoJS.enc.Utf8.parse(data)
                , CryptoJS.enc.Utf8.parse((key.length > 24 ? key.substr(0, 24) : key))
                , ((iv || '') ? {
                    iv: CryptoJS.enc.Utf8.parse((iv.length > 8 ? iv.substr(0, 8) : iv)),
                    mode: CryptoJS.mode.CBC,
                    padding: CryptoJS.pad.Pkcs7
                } : {
                    mode: CryptoJS.mode.ECB,
                    padding: CryptoJS.pad.Pkcs7
                })).toString());
        },
        /**
         * DES解密
         * @param data
         * @param key
         * @param iv
         */
        decoding: function (data, key, iv) {
            return ((iv || '') && (iv || '').length < 8) ? "iv length 8 bits" : (CryptoJS.DES.decrypt(data
                , CryptoJS.enc.Utf8.parse((key.length > 24 ? key.substr(0, 24) : key))
                , ((iv || '') ? {
                    iv: CryptoJS.enc.Utf8.parse((iv.length > 8 ? iv.substr(0, 8) : iv)),
                    mode: CryptoJS.mode.CBC,
                    padding: CryptoJS.pad.Pkcs7
                } : {
                    mode: CryptoJS.mode.ECB,
                    padding: CryptoJS.pad.Pkcs7
                })).toString(CryptoJS.enc.Utf8));
        },
        /**
         * DES3动态加密
         * @param data
         */
        en: function (data) {
            var rand = self.randStr().toString(), iv = (Math.floor(Math.random() * 9));
            return {
                random: rand + (iv.toString().length === 1 ? '0' + iv : iv),
                data: this.encrypt(self.json(data), (self.md5(rand)).substr(iv, 24))
            };
        },
        /**
         * DES3动态解密
         * @param data
         * @param random
         */
        de: function (data, random) {
            return self.toObj(this.decrypt(data, (self.md5(random.substr(0, (random.length - 2)))).substr(parseInt(random.substr(-2)), 24)))
        },
        /**
         * DES动态加密
         * @param data
         */
        eng: function (data) {
            var rand = self.randStr().toString(), iv = (Math.floor(Math.random() * 9));
            return {
                random: rand + (iv.toString().length === 1 ? '0' + iv : iv),
                data: this.encryption(self.json(data), (self.md5(rand)).substr(iv, 24))
            };
        },
        /**
         * DES动态解密
         * @param data
         * @param random
         */
        deg: function (data, random) {
            return self.toObj(this.decoding(data, (self.md5(random.substr(0, (random.length - 2)))).substr(parseInt(random.substr(-2)), 24)))
        }
    }
    /**
     * rsa加密解密
     */
    self.rsa = {
        /**
         * 公钥加密
         * @param data
         * @param key
         * @param is
         * @param cert
         */
        publicEncrypt: function (data, key, is, cert) {
            var encrypt = new JSEncrypt();
            encrypt.setPublicKey(((is || '') ? '-----BEGIN ' + (cert || 'PUBLIC') + ' KEY-----' + key + '-----END ' + (cert || 'PUBLIC') + ' KEY-----' : key));
            return encrypt.encrypt(data);
        },
        /**
         * 私钥解密
         * @param data
         * @param key
         * @param is
         * @param cert
         * @returns {*}
         */
        privateDecrypt: function (data, key, is, cert) {
            var decrypt = new JSEncrypt();
            decrypt.setPrivateKey(((is || '') ? '-----BEGIN ' + (cert || 'RSA PRIVATE') + ' KEY-----' + key + '-----END ' + (cert || 'RSA PRIVATE') + ' KEY-----' : key));
            return decrypt.decrypt(data)
        }
    }
    return self;
})();
