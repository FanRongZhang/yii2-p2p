<?php
	use yii\helpers\Url;
?>

<!-- 引入微信分享所用的Js文件 -->
<script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>

<div class="phone_index">
	<div class="share-header">
		<div class="logo-box">
			<div class="logo-main">
			    <a href="javascript:;" class="logo"></a>
			    <!--<img src="/img/logo.png"/>  -->
				<span class="make-wrap">
					<i class="make-wrap-before"></i>
			               活期产品预期年化收益率12%<br/><b>会赚钱的钱包</b>
		        </span>
			</div>

			<div class="logo-main logo-mainp clearfix">
			    <a href="javascript:;" class="logo fl"></a>
			    <!--<img src="/img/logo.png"/>  -->
				<div class="rloadbtn-box fr"><a href="#" class="rload-btn">下载客户端</a><a href="#" class="close-btn"><img src="/img/close.png"/></a></div>
			</div>
		</div>
	</div>
	<div class="share-content">

		<div class="cfcontent-top">

			<div class="cfbannerbg">
				<div class="txt">
					<div class="cftopert">
						您的好友 <span class="cr"><?=$nickname?></span><br>
						邀请您参加财富计划
					</div>
					<span class="cfrow"></span>
				</div>
			</div>
		</div>

		<div align="center" class="marque" id="wwwzzjsnet">
			<div id="www_zzjs_net_1">
					<?php if($cfList){ foreach($cfList as $k => $v){?>
		    			<li>恭喜用户<span><?=substr_replace($v->member->mobile,'****',3,4);?></span> 获得 <?=$v->money;?>元现金</li>
		    		<?php }}?>
			</div>
			<div id="www_zzjs_net_2"></div>
		</div>

		<form class="content-form">
			<div class="input-wrap">
			     <div class="input-box">
			          <label class="txt">手机号</label>
			          <div class="input-inbox"><input onblur="testMobile()" id="mobile" type="text" placeholder="请输入有效手机号"/></div>
			     </div>
			     <label class="err mobile-err"></label>
		     </div>
		     <div class="input-wrap">
			     <div class="input-box">
			          <label class="txt">短信验证码</label>
			          <div class="input-inbox"><input onblur="testVerify()" id="verify" type="text" placeholder="请输入短信验证码"/></div>
			          <button type="button" class="dm-get-btn">获取验证码</button>
			     </div>
			     <label class="err verify-err"></label>
		     </div>
		     <div class="input-wrap">
			     <div class="input-box">
			          <label class="txt">登录密码</label>
			          <div class="input-inbox"><input onblur="testPassword()" id="password" type="password" placeholder="请输入6-18位字母加数字的组合密码"/></div>
			     </div>
			     <label class="err password-err"></label>
		     </div>
		     <div class="service">同意<span><a href="http://store.v089.com/site/file?type=register" style="color:#0078ff">《钱富宝服务协议》</a></span></div>
		     <div class="share-btn">
					<button id="submit" type="button">
						注册领现金
					</button>
			</div>
		</form>
	</div>
	<div class="content-top cf-contenttop">
		<img src="/img/cfban1.png">
		<img src="/img/cfban2.png">
		<img src="/img/cfban3.png">
		<img src="/img/cfban4.png">
	</div>

</div>

<script>
	var title = "[有人@你]你创业，我出钱！点击领取900元！";
	var imgUrl = "http://i11.tietuku.com/5910fbc23dd8e642.png";
	var desc = "钱富宝20亿奖金任性送，900元现金来就有！存钱理财，即享12%超高预期年化收益率，安全稳健，品牌保障，是时候来一发了！";
	var link = "<?=Yii::$app->request->hostInfo.Url::to(['cf-register?member_id='.(isset($_GET['member_id']) ? $_GET['member_id'] : '')])?>";

	wx.config({
	    debug: false,//这里是开启测试，如果设置为true，则打开每个步骤，都会有提示，是否成功或者失败
	    appId: "<?=isset($wxParam['appId']) ? $wxParam['appId'] : '' ?>",
	    timestamp: "<?=isset($wxParam['timestamp']) ? $wxParam['timestamp'] : '' ?>",//这个一定要与上面的php代码里的一样。
	    nonceStr: "<?=isset($wxParam['nonceStr']) ? $wxParam['nonceStr'] : '' ?>",//这个一定要与上面的php代码里的一样。
	    signature: "<?=isset($wxParam['signature']) ? $wxParam['signature'] : '' ?>",
	    jsApiList: [
	      // 所有要调用的 API 都要加到这个列表中
	        'onMenuShareTimeline',
	        'onMenuShareAppMessage',
	        'onMenuShareQQ',
	        'onMenuShareWeibo'
	    ]
	});

	wx.ready(function () {
	    wx.onMenuShareTimeline({
	        title: title, // 分享标题
	        link: link, // 分享链接
	        imgUrl: imgUrl, // 分享图标
	        success: function () {
	            // 用户确认分享后执行的回调函数
	        },
	        cancel: function () {
	            // 用户取消分享后执行的回调函数
	        }
	    });
	    wx.onMenuShareAppMessage({
	        title: title, // 分享标题
	        desc: desc, // 分享描述
	        link: link, // 分享链接
	        imgUrl: imgUrl, // 分享图标
	        type: 'link', // 分享类型,music、video或link，不填默认为link
	        dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
	        success: function () {
	            // 用户确认分享后执行的回调函数
	        },
	        cancel: function () {
	            // 用户取消分享后执行的回调函数
	        }
	    });
	    wx.onMenuShareQQ({
	        title: title, // 分享标题
	        desc: desc, // 分享描述
	        link: link, // 分享链接
	        imgUrl: imgUrl, // 分享图标
	        success: function () {
	           // 用户确认分享后执行的回调函数
	        },
	        cancel: function () {
	           // 用户取消分享后执行的回调函数
	        }
	    });
	    wx.onMenuShareWeibo({
	        title: title, // 分享标题
	        desc: desc, // 分享描述
	        link: link, // 分享链接
	        imgUrl: imgUrl, // 分享图标
	        success: function () {
	           // 用户确认分享后执行的回调函数
	        },
	        cancel: function () {
	            // 用户取消分享后执行的回调函数
	        }
	    });
	});

    $(function(){
    	//获取验证码
    	$(".dm-get-btn").click(function(){
    		var mobile = $.trim($("#mobile").val());
    		var res = testMobile(mobile);
    		if(res){
    			var url = "<?=Url::to(['get-verify'])?>";
    			var btn = $(this);
    			var text = btn.text();
    			$.post(url,{mobile:mobile,'_csrf':'<?=Yii::$app->request->csrfToken?>'},
		            function(data){
		                var textTmp=60;
						btn.attr('disabled','disabled');
						btn.addClass("gray");
						btn.text(textTmp+"后重新发送");
						var int=self.setInterval(function(){
							textTmp=textTmp-1;
							btn.text(textTmp+"后重新发送");
							if(textTmp==0){
								btn.text(text);
								btn.removeAttr('disabled');
								btn.removeClass('gray');
								window.clearInterval(int);
							}
						},1000);
		                var res = eval('(' + data + ')');
		                if(parseInt(res.code) == 200){
		                	// $(".mobile-err").css("display","block");
		                	// $(".mobile-err").html(res.msg);
		                }else{
		                	$(".mobile-err").css("display","block");
							$(".mobile-err").html(res.msg);
		                }
		            }
		        );
    		}
    	});

		//防止重复提交
		var isClicked = false;
		//提交表单
		$("#submit").click(function(){
			var res = true;
			var mobile = $.trim($("#mobile").val());
			var verify = $.trim($("#verify").val());
			var password = $.trim($("#password").val());
			if(!testMobile()){
				res = false;
			}
			if(!testVerify()){
				res = false;
			}
			if(!testPassword()){
				res = false;
			}
			if(res){
				//如果提交过，则不让提交
				if(isClicked) return false;
				//改提交标识状态
				isClicked = true;
				var url = "<?=Url::to(['cf-register'])?>";
				var r_mobile = "<?=$mobile?>";
				$.post(url,{mobile:mobile,verify:verify,password:password,r_mobile:r_mobile,'_csrf':'<?=Yii::$app->request->csrfToken?>'},
		            function(data){
		                var res = eval('(' + data + ')');
		                if(parseInt(res.code) == 200){
		                	location.href = "<?=Url::to(['cf-success'])?>?member_id="+res.data.member_id;
		                }else if(parseInt(res.code) == 205){
		                	$(".verify-err").css("display","block");
							$(".verify-err").html(res.msg);
							isClicked = false;
		                }else{
		                	alert(res.msg);
		                	isClicked = false;
		                }
		            }
		        );
			}

		})

    })

	/**
	 * 验证验证码（只是验证最初的填写规范）
	 */
	function testVerify()
	{
		var verify = $.trim($("#verify").val());
		if(!verify){
			$(".verify-err").css("display","block");
			$(".verify-err").html("验证码不能为空");
			return false;
		}else{
			$(".verify-err").hide();
		}
		return true;
	}

	/**
	 * 验证密码
	 */
	function testPassword()
	{
		var regnumber = /\w*[0-9]+\w*$/;
    	var regletter = /\w*[a-zA-Z]+\w*$/;
		var password = $.trim($("#password").val());
		if(!password){
			$(".password-err").css("display","block");
			$(".password-err").html("密码不能为空");
			return false;
		}else{
			$(".password-err").hide();
			if(!regnumber.test(password) || !regletter.test(password)){
				$(".password-err").css("display","block");
				$(".password-err").html("密码必须为数字和字母组合");
				return false;
			}else{
				$(".password-err").hide();
			}
			if(password.length<6){
				$(".password-err").css("display","block");
				$(".password-err").html("密码不得少于6位");
				return false;
			}else{
				$(".password-err").hide();
			}
			if(password.length>12){
				$(".password-err").css("display","block");
				$(".password-err").html("密码不得超过12位");
				return false;
			}else{
				$(".password-err").hide();
			}
		}
		return true;
	}

	/**
	 * 验证手机号
	 */
    function testMobile()
    {
		var mobile = $.trim($("#mobile").val());
		//手机号为空
		if(!mobile){
			$(".mobile-err").css("display","block");
			$(".mobile-err").html("请输入手机号");
			return false;
		}else{
			$(".mobile-err").hide();
		}
		//手机号格式不正确
	 	var reg = /^(0|86|17951)?(13[0-9]|15[012356789]|17[03678]|18[0-9]|14[57])[0-9]{8}$/;
	 	if(!reg.test(mobile)){
	 		$(".mobile-err").css("display","block");
			$(".mobile-err").html("手机号格式错误");
	 		return false;
	 	}else{
	 		$(".mobile-err").hide();
	 	}
	 	return true;
    }

	var wwwzzjsnet = document.getElementById("wwwzzjsnet");
	var www_zzjs_net_1 = document.getElementById("www_zzjs_net_1");
	var www_zzjs_net_2 = document.getElementById("www_zzjs_net_2");
	var speed = 50; //滚动速度值，值越大速度越慢
	var count = <?=count($cfList)?>;
	var nnn = 0;
	if(count>0){
		nnn = 200 / www_zzjs_net_1.offsetHeight;
		$('#wwwzzjsnet').show();
	}else{
		nnn = 200 / 60;
		$('#wwwzzjsnet').hide();
	}

	for (i = 0; i < nnn; i++) {
		www_zzjs_net_1.innerHTML += "<br />" + www_zzjs_net_1.innerHTML
	}

	www_zzjs_net_2.innerHTML = www_zzjs_net_1.innerHTML //克隆www_zzjs_net_2为www_zzjs_net_1

	function Marquee() {
			if (www_zzjs_net_2.offsetTop - wwwzzjsnet.scrollTop <= 0) //当滚动至www_zzjs_net_1与www_zzjs_net_2交界时
				wwwzzjsnet.scrollTop -= www_zzjs_net_1.offsetHeight //wwwzzjsnet跳到最顶端
			else {
				wwwzzjsnet.scrollTop++ //如果是横向的 将 所有的 height top 改成 width left
			}
		} //欢迎来到站长特效网，我们的网址是www.zzjs.net，很好记，zz站长，js就是js特效，.net打造靓站，还有许多广告代码下载。
	var MyMar = setInterval(Marquee, speed); //设置定时器
	/*wwwzzjsnet.onmouseover = function() {
			clearInterval(MyMar)
		} //鼠标经过时清除定时器达到滚动停止的目的
	wwwzzjsnet.onmouseout = function() {
			MyMar = setInterval(Marquee, speed)
		} //鼠标移开时重设定时器*/
	$('.make-wrap').each(function(){
		var u = navigator.userAgent;
			if (u.indexOf('Android') > -1 || u.indexOf('Linux') > -1) {
			$('.make-wrap-before').css('top','1px');
        }
	});

	$('.close-btn').click(function(){
		$('.logo-mainp').hide();
	});

	//判断机型，进入不同下载页
	$('.rload-btn').click(function(){
		//是否上了appstore
		var isAlreadyInAppstore = <?=Yii::$app->params['is_already_in_appstore'] ? 1 : 0 ?>;
		//是否上了android市场
		var isAlreadyInAndroidMarket = <?=Yii::$app->params['is_already_in_android_market'] ? 1 : 0 ?>;
		//当前手机平台
		var platform;
		var u = navigator.userAgent;
		if(u.indexOf('Android') > -1 || u.indexOf('Linux') > -1){
			platform = "android"; //安卓手机
		}else if(u.indexOf('iPhone') > -1){
			platform = "ios"; //苹果手机
		}else{
			platform = 'other'; //其它平台暂不处理
		}
		if(isAlreadyInAppstore && isAlreadyInAndroidMarket){
			window.location.href = "http://a.app.qq.com/o/simple.jsp?pkgname=cn.d188.qfbao";
		}else{
			if(isAlreadyInAppstore){
				if (platform == "android") {
					window.location.href = "<?=Url::to(['download?platform=android'])?>";
				} else if (platform == 'ios') {
					window.location.href = "http://a.app.qq.com/o/simple.jsp?pkgname=cn.d188.qfbao";
				}
			}else if(isAlreadyInAndroidMarket){
				if (platform == "android") {
					window.location.href = "http://a.app.qq.com/o/simple.jsp?pkgname=cn.d188.qfbao";
				} else if (platform == "ios") {
					window.location.href = "<?=Url::to(['download?platform=ios'])?>";
				}
			}else{
				if (platform == "android") {
					window.location.href = "<?=Url::to(['download?platform=android'])?>";
				} else if (platform == "ios") {
					window.location.href = "<?=Url::to(['download?platform=ios'])?>";
				}
			}
		}
	});
</script>

