<?php
use yii\helpers\Url;
?>

<!-- 引入微信分享所用的Js文件 -->
<link href="/css/wapcss.css" rel="stylesheet" >
<script type="text/javascript" src="/js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="/js/commnjs.js"></script>
<script type="text/javascript" src="/js/hhSwipe.js"></script>

<div class="qfb-cencertd">
	<!--头部begin-->
	<div class="qfb-position"></div>
	<div class="qfb-head">
		<div class="logo-mainer">
			<!-- <a href="/site/wap-register?member_id=推荐人id'  ?>" class="before"></a> -->
			<span class="qfb-title rqfb-title">注册成功</span>
		</div>
	</div>
	<!--头部end-->
	<!--内容begin-->
	<div class="qfb-content">
		<div class="rscontent-top">
			<div class="index-slide" id="index-slide">
				<div class="qfb-rsbanner"></div>

				<div class="success-mainer">
					<img src="/img/sure.png">
					<p class="tits">恭喜您，注册成功！</p>
					<p>注册手机号：<?=$mobile?> <br/>请保管好您的账户密码！</p>
				</div>
			</div>
		</div>
	</div>
	<!--内容end-->

	<!--底部begin-->
	<div class="qfb-footer">
		<div class="qfb-footermain">
			<div class="qfb-share"></div>
			<div class="qfb-top-bottom">
				<a class="qfb-apple-btn apple  iphone-load bomb-btn" href="https://itunes.apple.com/us/app/%E9%92%B1%E5%AF%8C%E5%AE%9Dpro/id1262960542?mt=8" style="display: none;"></a>
				<a class="qfb-android-btn android android-load abomb-btn" href="http://zhushou.360.cn/detail/index/soft_id/3873699?recrefer=SE_D_%E9%92%B1%E5%AF%8C%E5%AE%9DPro" style="display: none;"></a>
			</div>
			<p class="qfb-rstext">下载“钱富宝”APP，会有更多惊喜哦~</p>
		</div>
	</div>
	<!--底部end-->
    <!--/*弹框 安卓 begin*/-->
    <div class="abomb-wrap" style="display:none;">
        <div class="bomb-box">
            <div class="bombbg"></div>
            <div class="bombimg">
                <img src="/img/appanzdor.png">
            </div>
        </div>
    </div>
    <div class="bbomb-wrap" style="display: none;">
        <div class="bomb-box">
            <div class="bombbg"></div>
            <div class="bombimg">
                <img src="/img/shareimg.png">
            </div>
        </div>
    </div>
    <!--/*弹框 end*/-->
</div>
<script type="text/javascript">


    /*判断系统*/
    var u = navigator.userAgent, app = navigator.appVersion;
    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1; //android终端或者uc浏览器
    var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
    //是否在appstore上线
    var isAlreadyInAppstore = "1";
    //是否在android应用市场上线
    var isAlreadyInAndroidMarket = "";
    if(isiOS){
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

        /*弹框遮罩E*/
        $(window).each(function(){
            if ($(this).width() > 1600){
                $('.service-pos').hide();
            }
        });
    }else if(isAndroid){

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
//                    $('body').addClass('bomb-fiexd');
                    return false;
                }
            }
        });

        //点击遮罩层，遮罩层消失
        $('.bomb-wrap').click(function(){
            $('.bomb-wrap').hide();
            $('body').removeClass('bomb-fiexd');
        });

        /*弹框遮罩E*/
        $(window).each(function(){
            if ($(this).width() > 1600){
                $('.service-pos').hide();
            }
        });
    }

	$(window).load(function(){
		if (isWeixn()||isqq()){
			$('.qfb-share').show();
			$(window).each(function(){
				if($(this).height()<540){
					$('.qfb-footerbox1').addClass('qfb-footerboxp1');
					$('.qfb-footermain').addClass('qfb-footermainp');
				}

			});
		}
	});
	/**
	 * 判断机型
	 */
	function getPlatform(){
		var platform;
		var u = navigator.userAgent, app = navigator.appVersion;
		if(u.indexOf('Android') > -1 || u.indexOf('Linux') > -1){
			platform = "android"; //安卓手机
		}else if(!!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/) > -1){
			platform = "ios"; //苹果手机
		}else{
			platform = "other"; //其它平台暂不处理
		}
		return platform;
	}

	/**
	 * 判断是不是微信浏览器
	 */
	function isWeixn(){
		var ua = navigator.userAgent.toLowerCase();
		if(ua.match(/MicroMessenger/i)=="micromessenger") {
			return true;
		} else {
			return false;
		}
	}
	function isqq(){
		var ua = navigator.userAgent.toLowerCase();
		if(ua.match(/QQ/i) == "qq") {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 获取ios版本号
	 */
	function getIosVersion()
	{
		var agent = navigator.userAgent.toLowerCase();
		var version = 8;
		if(agent.indexOf("like mac os x") > 0){
			//ios
			var regStr_saf = /os [\d._]*/gi ;
			var verinfo = agent.match(regStr_saf) ;
			version = (verinfo+"").replace(/[^0-9|_.]/ig,"").replace(/_/ig,".");
		}

		var version_str = version+"";
		if(version_str != "undefined" && version_str.length >0){
			version=version.substring(0,1);
		}
		return parseInt(version);
	}

	/**
	 * 判断是不是safari浏览器
	 */
	function isSafari()
	{
		var browserName = navigator.userAgent.toLowerCase();
		return /webkit/i.test(browserName) && /safari/i.test(browserName) &&  !(/chrome/i.test(browserName));
	}
	$(function(){
		$('.bomb-btnp').click(function(){
			$('.bbomb-wrap').show();

		});
		$('.bbomb-wrap').click(function(){
			$('.bbomb-wrap').hide();

		});
	})





	function setHeight() {
		var max_height = document.documentElement.clientHeight;
		var primary = document.getElementById('primary');
		// primary.style.minHeight = max_height + "px";
		/*primary.style.maxHeight = max_height + "px";*/

	};

	$(function(){
		$('.qfb-share').click(function(){
			$('.bbomb-wrap').show();
		});
		$('.bomb-wrap').click(function(){
			$('.bbomb-wrap').hide();
		});

	});

	//记录下载量
	$(function(){
		var isClicked = false;
		$('.abomb-btn').click(function(){
			var num = 1;
			if(!isClicked){
				$.post('/download/loadnum',{data:num,'_csrf':'<?=Yii::$app->request->csrfToken?>' },function(data){});
				isClicked = true;
			}
		});
	});


</script>