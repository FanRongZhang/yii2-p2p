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
		<div class="content-top">
		     <!--<img src="img/share-bg.jpg">-->
		     <div class="txt">
		     	<?php if($flow){ ?>
			     	您的好友 <span class="cr"><?=$nickname?></span><br/>
			     	抢到了<b><?=$flow?>M</b>流量红包
		     	<?php }else{ ?>
			     	您的好友 <span class="cr"><?=$nickname?></span><br/>
			     	邀请您抢流量
		     	<?php } ?>
		     </div>
		</div>

		<div align="center" class="marque" id="wwwzzjsnet">
			<div id="www_zzjs_net_1">
				<?php if($flowList){
		    		foreach($flowList as $v){ ?>
		    			<li>恭喜用户<span><?=substr_replace($v->mobile,'****',3,4);?></span>获得手机流量<?=$v->flow?>M</li>
		    		<?php }
		    	}?>
			</div>
			<div id="www_zzjs_net_2"></div>
		</div>

		<form class="content-form">
			<div class="input-wrap">
			     <div class="input-box">
			          <label class="txt">手机号</label>
			          <div class="input-inbox"><input onblur="testMobile()" id="mobile" type="text"/></div>
			     </div>
			     <label class="err mobile-err"></label>
		     </div>
		     <div class="input-wrap">
			     <div class="input-box">
			          <label class="txt">短信验证码</label>
			          <div class="input-inbox"><input onblur="testVerify()" id="verify" type="text"/></div>
			          <button type="button" class="dm-get-btn">获取验证码</button>
			     </div>
			     <label class="err verify-err"></label>
		     </div>
		     <div class="input-wrap">
			     <div class="input-box">
			          <label class="txt">登录密码</label>
			          <div class="input-inbox"><input onblur="testPassword()" id="password" type="password"/></div>
			     </div>
			     <label class="err password-err"></label>
		     </div>
		     <div class="service">同意<span><a href="http://store.v089.com/site/file?type=register" style="color:#0078ff">《钱富宝服务协议》</a></span></div>
		     <div class="share-btn">
					<button id="submit" type="button">
						立即注册
					</button>
			</div>
		</form>
	</div>
	<div class="share-footer">
		<div class="active-regular">
			<div class="title"> 活动规则</div>
			<div class="content">
				<p>1. 活动期间在该分享页面注册的新用户，钱富宝将随机赠送10M-200M不等的手机流量；</p>

				<p>2. 活动期间共计送出流量888G，赠完即止；</p>

				<p>3. 活动赠送的流量当月有效，运营商进行流量划扣优先扣除赠送流量；</p>

				<p>4. 月末两天是移动运营商月结期，故移动用户流量赠送将延迟48小时到账，最迟72小时内到账。</p>

				<p>5. 号码处于欠费停机等异常状态的、手机号未进行实名认证的、已经注册过钱富宝的用户均不能参与活动；</p>

				<p>6. 客服联系电话：4006071818；</p>

				<p>7. 本活动最终解释权归深圳国控股权投资基金管理有限公司所有。</p>
			</div>
		</div>
	</div>

</div>

<script>
	var title = "活期收益比余额宝高4倍，我们都在用！注册就送流量！";
	var imgUrl = "http://i3.tietuku.com/d04ab4ca6068244f.jpg";
	var desc = "钱富宝,一个会赚钱的钱包！活期理财收益高达12%，安全稳健！当前最流行的理财方式，5秒极速注册，简单理财，轻松赚钱，幸福生活，乐享不停！";
	var link = "<?=Yii::$app->request->hostInfo.Url::to(['register?member_id='.$_GET['member_id']])?>";

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
				var url = "<?=Url::to(['register'])?>";
				var r_mobile = <?=$mobile?>;
				$.post(url,{mobile:mobile,verify:verify,password:password,r_mobile:r_mobile,'_csrf':'<?=Yii::$app->request->csrfToken?>'},
		            function(data){
		                var res = eval('(' + data + ')');
		                if(parseInt(res.code) == 200){
		                	location.href = "<?=Url::to(['success'])?>?member_id="+res.data.member_id;
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
	var nnn = 200 / www_zzjs_net_1.offsetHeight;
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
		}
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
		var isAlreadyInAppstore = 0 ?>;
		if(isAlreadyInAppstore){
			window.location.href = "http://a.app.qq.com/o/simple.jsp?pkgname=cn.d188.qfbao";
		}else{
			var u = navigator.userAgent;
			if (u.indexOf('Android') > -1 || u.indexOf('Linux') > -1) {//安卓手机
				window.location.href = "http://a.app.qq.com/o/simple.jsp?pkgname=cn.d188.qfbao";
			} else if (u.indexOf('iPhone') > -1) {//苹果手机
				window.location.href = "<?=Url::to(['download'])?>";
			} else if (u.indexOf('Windows Phone') > -1) {//winphone手机
				alert("目前不支持winphone手机");
			}
		}
	});
</script>

