<?php
require __DIR__ . '/../vendor/autoload.php';

use zhqing\extend\Curl;


$curl = Curl::url('https://ip138.com/')//是否json提交

//是否json提交
//->json() //可选 不设置则使用http_build_query提交

//设置来源,参数为空会自动设置当前请求的域名
//->referer()//可选

//是否检查证书公用名,不设置默认不检查
//->sslHost(false) //可选

//是否检查证书,不设置默认不检查
//->sslPeer(false) //可选

//设置头部信息可设置string|array|可连贯
//->setHead(['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'])//可选
//设置头部信息可设置string|array|可连贯
//->setHead('REFERER', 'https://baidu.com')//可选

//设置请求伪装ip
//->reqIp('127.0.0.1')//可选

//是否模拟ajax(true=开启,false=关闭)不设置默认false
//->ajax()//可选

//设置浏览器信息(不设置则会自动设置)
//->userAgent('Mozilla/5.0.......')//可选

//设置解码名称
//->encoding('gzip')//可选

//超时时间,设置为0，则无限等待,默认5
//->timeOut(6)//可选

//连接时间,设置为0，则无限等待,默认5
//->timeConnect(6)//可选

//是否自动跳转(不设置默认不跳转)
//->follow(false)//可选

//设置转换编码
//->coding('utf8', 'gb2312')//可选

//设置cookie()可设置string|array|可连贯
//->cookie('user=admin;pass=admin')//可选
//设置cookie()可设置string|array|可连贯
//->cookie(['token' => '1FE5215A7AB5475F'])//可选
//->cookie('token', '1FE5215A7AB5475F')//可选

//设置代理ip(第二个bool参数为开关)//可选
->proxy([
    'ip' => '代理ip',
    'port' => '代理ip端口',
    'userPass' => '帐号:密码',
    'type' => '代理模式:http|socks5或者自定',
    'auth' => '认证模式:basic|ntlm或者自定'
], false)
    //自定义curl
    //->curl(CURLOPT_TIMEOUT, 15)//可选|可连贯

    //执行访问
    ->exec();
//调试信息
//$curl->debug();
ps($curl->header());
ts($curl->full());

//返回状态码
//var_dump($curl->code());

//返回头部信息
//var_dump($curl->header());

//返回内容
//var_dump($curl->body());

//获取返回的cookie
//var_dump($curl->getCookie());

//返回curl_getinfo信息
//var_dump($curl->info());

//把内容转为array(内容不是array时会返回空的array)
//var_dump($curl->array());

//把body内容原样保存到文件
//var_dump($curl->saveFile(__DIR__ . '/demo.html'));