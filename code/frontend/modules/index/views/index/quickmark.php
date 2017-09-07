<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
    
    $this->title = '关于我们';
?>
<!DOCTYPE HTML>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>下载界面</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="stylesheet" href="../../css/css1.css">
    <script type="" src="../../js/jquery.js"></script>
</head>
<body>
<div>
    <img src="../../image/downtes.png" style="width: 100%" >
</div>
<div class="qfb-footermain">
    <div class="qfb-top-bottom">
        <a class="qfb-apple-btn apple  iphone-load bomb-btn" href="https://itunes.apple.com/us/app/%E9%92%B1%E5%AF%8C%E5%AE%9Dpro/id1262960542?mt=8" style="display: none">APP下载</a>
        <a class="qfb-android-btn android android-load abomb-btn" href="http://zhushou.360.cn/detail/index/soft_id/3873699?recrefer=SE_D_%E9%92%B1%E5%AF%8C%E5%AE%9DPro"  style="display: none;">APP下载</a>
    </div>
</div>

<div class="abomb-wrap"  style="display:none;">
    <div class="bomb-box">
        <div class="bombbg"></div>
        <div class="bombimg">
            <img src="../../image/appanzdor.png">
        </div>
    </div>
</div>

</body>
<script type="text/javascript">


    $(function(){

        //手机平台
        var platform = getPlatform();
        //是否在appstore上线
        var isAlreadyInAppstore = "1";
        //是否在android应用市场上线
        var isAlreadyInAndroidMarket = "";

        if(platform == "ios"){
            //显示IOS下载图标
            $(".iphone-load").show();
            $('.bomb-btn').click(function(){
                //如果已经在appstore上线，直接到appstore
                if(isAlreadyInAppstore){
                    location.href = "https://itunes.apple.com/us/app/%E9%92%B1%E5%AF%8C%E5%AE%9Dpro/id1262960542?mt=8";
                    return false;
                }
                else{
                    //判断微信浏览器
                    if(isWeixn()){
                        $('.bomb-wrap').show();
                        $('body').addClass('bomb-fiexd');
                        return false;
                    }
                    else{
                        //判断版本，如果是ios9或以上，跳转到设置页面,下载，同时打开设置
                        if(getIosVersion()>=9){
                            location.href = "/setting.html";
                        }
                    }
                }
            });

            //点击遮罩层，遮罩层消失
            $('.bomb-wrap').click(function(){
                $('.bomb-wrap').hide();
                $('body').removeClass('bomb-fiexd');
            });


        }else if(platform == "android"){
            //显示android下载图标
            $(".android-load").show();
            $('.abomb-btn').click(function(){
                //如果已经在android市场上线
                if(isAlreadyInAndroidMarket){
                    location.href = "http://zhushou.360.cn/detail/index/soft_id/3873699?recrefer=SE_D_%E9%92%B1%E5%AF%8C%E5%AE%9DPro";
                    return false;
                }else{
                    //微信浏览器提示用系统浏览器打开，其它的不管它
                    if(isWeixn()){
                        $('.abomb-wrap').show();
                        $('body').addClass('bomb-fiexd');
                        return false;
                    }
                }
            });

            //点击遮罩层，遮罩层消失
            $('.bomb-wrap').click(function(){
                $('.bomb-wrap').hide();
                $('body').removeClass('bomb-fiexd');
            });
            $('.abomb-wrap').click(function(){
                $('.abomb-wrap').hide();
                $('body').removeClass('bomb-fiexd');
            });

            /*弹框遮罩E*/
            $(window).each(function(){
                if ($(this).width() > 1600){
                    $('.service-pos').hide();
                }
            });
        }

        // 根据不同的终端，跳转到不同的地址
        // alert(platform);
        var theUrl = '';

        if(platform == 'android'){
            theUrl = 'http://zhushou.360.cn/detail/index/soft_id/3873699?recrefer=SE_D_%E9%92%B1%E5%AF%8C%E5%AE%9DPro';
        }else if(platform == 'ios'){
            theUrl = 'https://itunes.apple.com/us/app/%E9%92%B1%E5%AF%8C%E5%AE%9Dpro/id1262960542?mt=8';
        }

        location.href = theUrl;
    });

    /**
     * 判断机型
     */
    function getPlatform()
    {
        var platform;
        var u = navigator.userAgent;
        if (u.indexOf('Android') > -1 || u.indexOf('Linux') > -1) {
            platform = "android"; //安卓手机
        } else if (!!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/) > -1) {
            platform = "ios"; //苹果手机
        } else {
            platform = "other"; //其它平台暂不处理
        }
        return platform;
    }

    /**
     * 判断是不是微信浏览器
     */
    function isWeixn() {
        var ua = navigator.userAgent.toLowerCase();
        if (ua.match(/MicroMessenger/i) == "micromessenger") {
            return true;
        } else {
            return false;
        }
    }



</script>

</html>