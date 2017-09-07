<?php
use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>

<!-- 引入微信分享所用的Js文件 -->
<link href="/css/wapcss.css" rel="stylesheet" >
<script type="text/javascript" src="/js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="/js/commnjs.js"></script>
<script type="text/javascript" src="/js/hhSwipe.js"></script>

<!--首页-->
<div class="qfb-cencertd">
	<!--头部begin-->
	<div class="qfb-position qfb-positionp"></div>
	<div class="qfb-head">
		<div class="logo-mainer logo-mainpt clearfix">
			<a href="javascript:;" class="logo fl"></a>
                <span class="make-wraps">
                    <i class="make-wrap-before"></i>
                           <b>预期收益10%</b><br>会赚钱的钱包
                </span>
			<div class="qfb-btn-box qfb-btn-boxp">
				<a class="qfb-btn-btn iphone-load bomb-btn" href="itms-services://?action=download-manifest&amp;url=https://app.v089.com/DMWallet.plist" style="display: none;">
					<img src="/img/down.gif"><span>下载APP</span>
				</a>
				<a class="qfb-btn-btn android-load abomb-btn" href="http://qfb.v089.com/download/android/QianFuBao_Android.apk" style="display: none;"><img src="/img/down.gif"><span>下载APP</span><span>下载APP</span></a>
			</div>
		</div>
	</div>
	<!--头部end-->

	<!--内容begin-->
	<div class="qfb-content">
		<!-- banner bof-->
		<div class="addWrap">
			<div class="swipe" id="mySwipe" >
				<div class="swipe-wrap">
					<div><a href="javascript:;"><img class="img-responsive" src="http://img.qianfb.com/uploads/1/Zt3hzjoUZNlYPF9hV7Q211dJCbV7FhcF.jpg" title="banner1" alt=""></a></div>
					<div><a href="javascript:;"><img class="img-responsive" src="http://img.qianfb.com/uploads/1/-GWfM0QZmAnEMPtCJTGoH554jpPMyCHV.jpg" title="banner2" alt=""></a></div>
					<div><a href="javascript:;"><img class="img-responsive" src="http://img.qianfb.com/uploads/1/Zt3hzjoUZNlYPF9hV7Q211dJCbV7FhcF.jpg" title="banner1" alt=""></a></div>
					<div><a href="javascript:;"><img class="img-responsive" src="http://img.qianfb.com/uploads/1/-GWfM0QZmAnEMPtCJTGoH554jpPMyCHV.jpg" title="banner2" alt=""></a></div>
				</div>
			</div>

			<ul id="position">
				<li class=" "></li>
				<li class="cur"></li>
			</ul>
		</div>
		<!-- banner eof-->

		<!-- loginbox bof-->
		<?php if(!empty($nickname)): ?>
		<div class="share-recommend">
			<div id="anchor" class="anchor2"></div>
			您的好友<span> <?=$nickname?> </span>邀请您注册一能积金！
		</div>
		<?php endif; ?>

		<?php $form = ActiveForm::begin([
		    'options'=>[
		        'class'=>"content-ted ",
		        'id'=>'content-form'
		    ]
		]); ?>

		<input type="hidden" name="r_member_id" value="<?=$member_id?>">
			<div class="input-wraps">
				<div class="input-boxs">
					<div class="input-inboxs">
						<label class="txt">手机号</label>
						<input class="mobile" onblur="isRegisted();testMobileBlur();" onkeyup="isRegisted()" onfocus="clearMsg(this)" id="mobile" type="text" placeholder="输入注册手机号" name="RegisterForm[mobile]" value=""></div>
				</div>
				<div id="error_mobile">
				</div>
			</div>

			<div class="input-wraps">
				<div class="input-box1">
					<div class="input-inboxs input-border form-pic">
						<input type="text" id="captchaverify-captcha_code" class="form-control" name="CaptchaVerify[captcha_code]" placeholder="图形验证码">
					</div>
					<?php 
						echo yii\captcha\Captcha::widget([
							'name'=>'captchaimg',
							'captchaAction'=>'/share/captcha',
							'imageOptions'=>[
								'id'=>'w0-image', 
								'title'=>'换一个', 
								'alt'=>'换一个', 
								'class'=>'form-imger',
								'placeholder'=>'图形验证码',
							],
							'template'=>'{image}'
						]); 
					?>
				</div>
				<div id="error_cpv" class="q-infored" style="display: none;"></div>
			</div>

			<div class="input-wraps">
				<div class="input-box1">
					<div class="input-inboxs input-border">
						<input class="verify" onfocus="clearMsg(this)" id="verify_code" type="text" name="RegisterForm[verify_code]" value="" placeholder="短信验证码"></div>
					<button id="toget" type="button" class="qfb-button">获取验证码</button>
					<button id="autotime" href="javascript:void(0);" type="button" class=" qfb-buttonp" style="display:none">56秒后重新获取</button>
				</div>
				<div id="error_verify_code">
				</div>
			</div>
			<div class="input-wraps">
				<div class="input-boxs">

					<div class="input-inboxs">
						<label class="txt">登录密码</label>
						<input class="password" onfocus="clearMsg(this)" id="password" type="password" name="RegisterForm[password]" value="" placeholder="6-18位字母加数字的组合密码">

					</div>
				</div>
				<div id="error_password">
				</div>
			</div>
			<div class="service"><input type="checkbox" checked="checked" class="check" name="RegisterForm[agreement]"><i id="checked-ico"></i><span class="wapqfb-pad">同意</span><span><a href="/share/file?type=protocol" style="color:#0078ff">《钱富宝服务协议》</a>

            </span></div>
			<div id="error_agreement">
			</div>
			<input type="hidden" name="_csrf" value="UUJCREZyd0YkLQ9xaxoiETo1cCIHSh0jYzobJg0YImsAcjoGcUMFLw==">
			<div class="share-btn">
				<button id="submit" type="button" onclick="return check(this.form)">免费注册</button>
			</div>
		<?php ActiveForm::end(); ?>

		<!-- 中间展示图片 bof -->
		<div class="icontent-bottom">
			<img src="http://img.qianfb.com/uploads/1/9mxj4NzWZIhAJAZpED7tYLdiQtbVSQB5.png" alt="" title="1">
			<img src="http://img.qianfb.com/uploads/1/Md3LSBDBILlEdkioWfovldtCRPQvJ7nr.png" alt="" title="2">
			<img src="http://img.qianfb.com/uploads/1/OQw0Z9XLpl0OTZYXgUpp7YMkH_eKZOAZ.png" alt="" title="3">
		</div>
		<!-- 中间展示图片 eof -->
	</div>
	<!--内容end-->
	<!--底部begin-->
	<div class="qfb-footer">
		<div class="qfb-footerbox">
			<ul class="btncl">
				<li><a href="#anchor"><span class="qr-btn">注册</span></a></li>

				<li>
					<!--<a href=""><span class="qfb-imenu">客户端</span></a>-->
					<a class="iphone-load bomb-btn" href="itms-services://?action=download-manifest&amp;url=https://app.v089.com/DMWallet.plist" style="display: none;"><span>客户端</span></a>
					<a class="android-load abomb-btn" href="http://qfb.v089.com/download/android/QianFuBao_Android.apk" style="display: none;"><span>客户端</span></a>
				</li>
			</ul>

			<p class="qfb-copyright1">Copyright©2013-2015 qianfb.com All Rights Reserved<br>钱富宝版权所有</p>

		</div>

	</div>
	<!--底部end-->
</div>
<!--弹框 S-->
<div class="bomb-wrap" style="display: none;">
	<div class="bomb-box">
		<div class="bombbg"></div>
		<div class="bombimg">
			<img src="/img/regist-bomb.png">
		</div>
	</div>
</div>

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
<!--弹框 E-->

<script type="text/javascript">

	var _csrf = $("input[name='_csrf']").val() 

	/******************/

	//轮播
	var bullets = document.getElementById('position').getElementsByTagName('li');
	var banner = Swipe(document.getElementById('mySwipe'), {
		auto: 4000,
		continuous: true,
		disableScroll:false,
		callback: function(pos) {
			var i = bullets.length;
			var iw = bullets.length;
			while (i--) {
				bullets[i].className = ' ';
			}
			bullets[pos%iw].className = 'cur';
		}
	})

	$(function(){
		$('.service i').click(function(){
			$(this).toggleClass('nocheck');
		});

		$('.input-wraps input').click(function(){
			$(this).parent('.input-inboxs').addClass('blue-bd');
			$(this).parents('.input-wraps').siblings().find('.input-inboxs').removeClass('blue-bd');
		});
	})



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
					location.href = "http://a.app.qq.com/o/simple.jsp?pkgname=cn.d188.qfbao";
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
					location.href = "http://a.app.qq.com/o/simple.jsp?pkgname=cn.d188.qfbao";
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
	})


	/**
	 * 判断机型
	 */
	function getPlatform(){
		var platform;
		var u = navigator.userAgent;
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
			$('body').addClass('bomb-fiexd');

		});
		$('.bbomb-wrap').click(function(){
			$('.bbomb-wrap').hide();
			$('body').removeClass('bomb-fiexd');
		});
	})

	function setHeight() {
		var max_height = document.documentElement.clientHeight;
		// ??????????????????
		// var primary = document.getElementById('primary');
		// primary.style.minHeight = max_height + "px";
		/*primary.style.maxHeight = max_height + "px";*/

	};

	$('.btncl a').click(function(){
		$('.btncl a').find('span').removeClass('qfb-imenu');
		$(this).find('span').addClass('qfb-imenu');
	});

	//register start
	//获取验证码
	$(function(){
		
		$("#toget").click(function(){

			var mobile = $.trim($("#mobile").val());
			var catv = $("#captchaverify-captcha_code").val();
			var res = checkMobile(mobile);
			// 验证图形验证码
			var res_catv = checkCpv(catv);

			if(res && res_catv){

				var url = "/share/get-verify";

				$.post(url,{mobile:mobile, catv: catv, 'type':'6 ', '_csrf':_csrf},
					function(data){
						var res = eval('(' + data + ')');
						console.log(res);

						if(parseInt(res.code) == 200){
							var btn = $("#autotime");
							var toget = $("#toget");
							toget.hide();
							btn.show();
							var textTmp=60;
							btn.html(textTmp+"后重新发送");
							var int=self.setInterval(function(){
								textTmp=textTmp-1;
								btn.text(textTmp+"后重新发送");
								if(textTmp==0){
									toget.show();
									btn.hide();
									window.clearInterval(int);
								}
							},1000);

							$("#error_verify_code").html(res.msg);
							$("#error_verify_code").addClass("q-infored");
							
							// $("#error_mobile").html(res.msg);
							// $("#error_mobile").addClass("q-infored");
						}else{
							
							$("#w0-image").click();
							
							if (parseInt(res.code) == 10001){
								$("#error_verify_code").html(res.msg);
								$("#error_verify_code").addClass("q-infored");
							}else if(parseInt(res.code) == 401){
								$("#error_cpv").html(res.msg);
								$("#error_cpv").show();
							}
							
							// else {
							// 	$("#error_mobile").html(res.msg);
							// 	$("#error_mobile").addClass("q-infored");
							// }
						}
					}
				);
			}
		});

		$("#captchaverify-captcha_code").focus(function(){
			$("#error_cpv").hide();
		})
	});


	/**
	 * 验证图形验证码
	 */
	function checkCpv(catv)
	{
		if(!catv){
			$("#error_cpv").html("验证码错误，请重新输入");
			$("#error_cpv").show();
			return false;
		}else{
			$("#error_cpv").hide();
			return true;
		}
	}


	/**
	 * 验证手机号
	 */
	function checkMobile()
	{
		var mobile = $.trim($("#mobile").val());
		//手机号为空
		if(!mobile){
			$("#error_mobile").addClass("q-infored");
			$("#error_mobile").html("请输入手机号");
			return false;
		}else{
			$("#error_mobile").removeClass("q-infored");
			$("#error_mobile").html("");
		}

		//手机号格式不正确
		var reg = /^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/;
		if(!reg.test(mobile)){
			$("#error_mobile").addClass("q-infored");
			$("#error_mobile").html("手机号不合法");
			return false;
		}else{
			$("#error_mobile").removeClass("q-infored");
			$("#error_mobile").html("");
		}
		return true;
	}


	/**
	 * 验证验证码（只是验证最初的填写规范）
	 */
	function checkVerify()
	{
		var verify = $.trim($("#verify_code").val());
		if(!verify){
			$("#error_verify_code").addClass("q-infored");
			$("#error_verify_code").html("验证码不能为空");
			return false;
		}else{
			$("#error_verify_code").removeClass("q-infored");
			$("#error_verify_code").html("");
		}
		return true;
	}


	/**
	 * 验证密码（只是验证最初的填写规范）
	 */
	function checkPassword()
	{
		var regnumber = /\w*[0-9]+\w*$/;
		var regletter = /\w*[a-zA-Z]+\w*$/;
		var password = $.trim($("#password").val());
		if(!password){
			$("#error_password").addClass("q-infored");
			$("#error_password").html("密码不能为空");
			return false;
		}else{
			$("#error_password").removeClass("q-infored");
			$("#error_password").html("");

			if(!regnumber.test(password) || !regletter.test(password)){
				$("#error_password").addClass("q-infored");
				$("#error_password").html("密码必须为数字和字母组合");
				return false;
			}else{
				$("#error_password").removeClass("q-infored");
				$("#error_password").html("");
			}
			if(password.length<6){
				$("#error_password").addClass("q-infored");
				$("#error_password").html("密码不得少于6位");
				return false;
			}else{
				$("#error_password").removeClass("q-infored");
				$("#error_password").html("");
			}
			if(password.length>12){
				$("#error_password").addClass("q-infored");
				$("#error_password").html("密码不得超过12位");
				return false;
			}else{
				$("#error_password").removeClass("q-infored");
				$("#error_password").html("");
			}
		}
		return true;
	}

	/**
	 * 清楚
	 */
	function clearMsg(o)
	{
		var id = o.id;
		$("#error_"+id).removeClass("q-infored");
		$("#error_"+id).html("");
	}

	/**
	 * 验证推荐人手机号手机号
	 */
	// function checkRmobile()
	// {
	// 	var mobile = $.trim($("#r_mobile").val());

	// 	//手机号为空
	// 	if(!mobile){
	// 		$("#error_r_mobile").addClass("q-infored");
	// 		$("#error_r_mobile").html("推荐人不能为空");
	// 		return false;
	// 	}else{
	// 		//手机号格式不正确
	// 		var reg = /^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/;
	// 		if(!reg.test(mobile)){
	// 			$("#error_r_mobile").addClass("q-infored");
	// 			$("#error_r_mobile").html("推荐人手机号不合法");
	// 			return false;
	// 		}else{
	// 			$("#error_r_mobile").removeClass("q-infored");
	// 			$("#error_r_mobile").html("");
	// 		}
	// 	}
	// 	return true;
	// }

	// 注册账号
	function check()
	{	
		// 禁止重复点击
		$('#submit').attr('disabled', true);

		// 验证手机号
		var bool_mobile = checkMobile();
		// 验证短信验证码
		var bool_verify = checkVerify();
		// 验证密码
		var bool_password = checkPassword();
		// 验证手机号是否注册
		var bool_isRegisted =  !isRegisted();
		// 验证是否同意协议
		var bool_isAgree = isAgree();
		
		var mobile = $("#mobile").val();
		var verify = $("#verify_code").val();
		var password = $("#password").val();
		// 分享人手机号
		var r_mobile = "<?php echo $mobile ? $mobile : '';?>";

		if(bool_mobile && bool_verify && bool_password && bool_isRegisted && bool_isAgree){

			var url = "/share/register";
			
			$.post(url,{'mobile':mobile, 'verify': verify, 'password':password, 'r_mobile':r_mobile, 'type':'6 ', '_csrf':_csrf},
				function(data){
					var res = eval('(' + data + ')');
					if(parseInt(res.code) == 200){

						var member_id = res.data.member_id;
						var btn = $("#autotime");
						var toget = $("#toget");
						toget.hide();
						btn.show();
						var textTmp=60;
						btn.html(textTmp+"后重新发送");
						var int=self.setInterval(function(){
							textTmp=textTmp-1;
							btn.text(textTmp+"后重新发送");
							if(textTmp==0){
								toget.show();
								btn.hide();
								window.clearInterval(int);
							}
						},1000);

						// 跳转成功页面
						window.location.href = "/share/success?member_id="+member_id;

						// 图形验证码提示
						$("#error_agreement").html(res.msg);
						$("#error_agreement").addClass("q-infored");

					}else{

						$("#w0-image").click();

						if(parseInt(res.code) == 10005){
							// 短信验证码提示
							$("#error_verify_code").html(res.msg);
							$("#error_verify_code").addClass("q-infored");
						}else if(parseInt(res.code) == 10001){
							// 其他错误提示
							$("#error_agreement").html(res.msg);
							$("#error_agreement").addClass("q-infored");
						}else if(parseInt(res.code) == 401){
							// 图形验证码提示
							$("#error_cpv").html(res.msg);
							$("#error_cpv").show();
						}
					}
				}
			);

			$('#submit').attr('disabled', false);
			return true;
		}else{
			$('#submit').attr('disabled', false);
			return false;
		}
		
	}

	/**
	 * 判断有没有同意协议
	 */
	function isAgree()
	{
		var isAgree = $("#checked-ico").hasClass("nocheck") ? false : true;
		if(isAgree){
			$("#error_agreement").html("");
			$("#error_agreement").removeClass("q-infored");
		}else{
			$("#error_agreement").html("请先同意协议");
			$("#error_agreement").addClass("q-infored");
		}
		return isAgree;
	}

	/**
	 * 判断手机号是否注册
	 */
	function isRegisted()
	{
		var mobile = $("#mobile").val();
		var reg = /^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/;
		if(reg.test(mobile)){
			var url = "/share/is-registed";
			$.post(url,{mobile:mobile, type:'6', '_csrf':_csrf},
				function(data){
					if(data){
						$("#error_mobile").html("该手机号已注册");
						$("#error_mobile").addClass("q-infored");
					}else{
						$("#error_mobile").html("");
						$("#error_mobile").removeClass("q-infored");
						//$(".slide_v img").removeClass("ani");
					}
				}
			);
		}
	};

	/**
	 * 手机号不为空时判断
	 */
	function testMobileBlur()
	{
		var mobile = $.trim($("#mobile").val());
		if(mobile){
			checkMobile();
		}
	}

	var u = navigator.userAgent;
	if (u.indexOf('iPhone') > -1) {
		$('.input-wraps input').focus(function(){
			$('.qfb-head').css('position','static');
			$('.qfb-positionp').removeClass('qfb-position');
		});

		$('.input-wraps input').blur(function(){
			$('.qfb-head').css('position','fixed');
			$('.qfb-positionp').addClass('qfb-position');
		});

		$('#anchor').css('top','-6px');

		$(window).each(function(){
			if($($(this))[0].href==String(window.location))

				$('#anchor').css('top','-54px');
		});
		$('.qr-btn').click(function(){
			$('.anchor1').css('top','-6px');
			$('.anchor2').css('top','0');
		});


	}

	$(function(){
		$('.input-wraps input').focus(function(){
			$('.qfb-ifooter-bottom').css('position','static');
			$('.fqfb-positionp').removeClass('fqfb-position');
		});

		$('.input-wraps input').blur(function(){
			$('.qfb-ifooter-bottom').css('position','fixed');
			$('.qfb-positionp').addClass('qfb-position');
		});

		$('.qr-btn').click(function(){
			$('input.mobile').focus();
		});


	})

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
				location.href = "http://a.app.qq.com/o/simple.jsp?pkgname=cn.d188.qfbao";
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
				location.href = "http://a.app.qq.com/o/simple.jsp?pkgname=cn.d188.qfbao";
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

		/*弹框遮罩E*/
		$(window).each(function(){
			if ($(this).width() > 1600){
				$('.service-pos').hide();
			}
		});
	}

</script>







