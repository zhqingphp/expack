<!DOCTYPE html>
<html>
<title><?php echo $title; ?></title>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-siteapp"/>
    <meta http-equiv="Cache-Control" content="no-transform"/>
    <meta name="applicable-device" content="pc,mobile">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #E6EAEB;
            font-family: Arial, '微软雅黑', '宋体', sans-serif
        }

        a {
            text-decoration: none;
            color: #7B7B7B;
        }

        .alert-box {
            display: none;
            position: relative;
            margin: 96px auto 0;
            padding: 180px 85px 22px;
            border-radius: 10px 10px 0 0;
            background: #FFF;
            box-shadow: 5px 9px 17px rgba(102, 102, 102, 0.75);
            width: 286px;
            color: #FFF;
            text-align: center
        }

        .alert-box p {
            margin: 0
        }

        .alert-circle {
            position: absolute;
            top: -50px;
            left: 111px
        }

        .alert-sec-circle {
            stroke-dashoffset: 0;
            stroke-dasharray: 735;
            transition: stroke-dashoffset 1s linear
        }

        .alert-sec-text {
            position: absolute;
            top: 11px;
            left: 190px;
            width: 76px;
            color: #000;
            font-size: 68px
        }

        .alert-sec-unit {
            font-size: 34px
        }

        .alert-body {
            margin: 35px 0;
            margin-bottom: 30px;
        }

        .alert-head {
            color: #242424;
            font-size: 28px
        }

        .alert-concent {
            margin: 25px 0 14px;
            color: #7B7B7B;
            font-size: 18px
        }

        .alert-concent p {
            line-height: 27px
        }

        .alert-btn {
            display: block;
            border-radius: 10px;
            background-color: #ff5656;
            height: 55px;
            line-height: 55px;
            width: 286px;
            color: #FFF;
            font-size: 20px;
            text-decoration: none;
            letter-spacing: 2px
        }

        .alert-btn:hover {
            background-color: #ff5656
        }

        .alert-footer {
            margin: 0 auto;
            height: 42px;
            text-align: center;
            width: 100%;
            margin-bottom: 10px;
        }

        .alert-footer-icon {
            float: left
        }

        .alert-footer-text {
            float: left;
            border-left: 2px solid #EEE;
            padding: 3px 0 0 5px;
            height: 40px;
            color: #0B85CC;
            font-size: 12px;
            text-align: left
        }

        .alert-footer-text p {
            color: #7A7A7A;
            font-size: 22px;
            line-height: 18px
        }
    </style>
</head>
<body>
<div id="js-alert-box" class="alert-box">
    <svg class="alert-circle" width="234" height="234">
        <circle cx="117" cy="117" r="108" fill="#FFF" stroke="#ff5656" stroke-width="17"></circle>
        <circle id="js-sec-circle" class="alert-sec-circle" cx="117" cy="117" r="108" fill="transparent"
                stroke="#F4F1F1" stroke-width="18" transform="rotate(-90 117 117)"></circle>
        <text class="alert-sec-unit" x="100" y="172" fill="#BDBDBD">秒</text>
    </svg>
    <div id="js-sec-text" class="alert-sec-text"></div>
    <div class="alert-body">
        <div id="js-alert-head" class="alert-head"></div>
        <div class="alert-concent">
            <p><?php echo $title; ?></p>
        </div>
        <a id="js-alert-btn" class="alert-btn" href='<?php echo $url; ?>'>立即前往</a>
    </div>
    <div class="alert-footer clearfix"></div>
</div>
<script type="text/javascript">
    function alertSet(t, v) {
        document.getElementById("js-sec-circle").style.strokeDashoffset = Math.round(t * 735)
        document.getElementById("js-alert-box").style.display = "block";
        document.getElementById("js-alert-head").innerHTML = v;
        document.getElementById("js-sec-text").innerHTML = t;
        var timer = setInterval(function () {
            if (0 === t) {
                clearTimeout(timer);
                top.location.href = '<?php echo $url;?>';
            } else {
                t = t - 1
                document.getElementById("js-sec-circle").style.strokeDashoffset = Math.round(t * 735)
                document.getElementById("js-sec-text").innerHTML = t;
            }
        }, 1000);
    }

    alertSet('<?php echo $time;?>', '正在前往，请稍后..');
</script>
</body>
</html>
