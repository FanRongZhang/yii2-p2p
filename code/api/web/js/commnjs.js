(function (win, doc) {
	var max = 720;

	var setFontSize = function () {
		var myHTML = doc.querySelector("html");
		var myWidth = doc.documentElement.clientWidth > max ? max : doc.documentElement.clientWidth;

		myHTML.style.fontSize = 174 * myWidth / max  + 'px';
	};

	win.onresize = function () {

		setFontSize();

	};
	setFontSize();

})(window, document);

/*头部滚动文字S*/
/*var wwwzzjsnet = document.getElementById("wwwzzjsnet");
	var www_zzjs_net_1 = document.getElementById("www_zzjs_net_1");
	var www_zzjs_net_2 = document.getElementById("www_zzjs_net_2");
	var speed = 60; //滚动速度值，值越大速度越慢
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
		} //欢迎来到站长特效网，我们的网址是www.zzjs.net，很好记，zz站长，js就是js特效，.net打造靓站，还有许多广告代码下载。
	var MyMar = setInterval(Marquee, speed); //设置定时器
	wwwzzjsnet.onmouseover = function() {
			clearInterval(MyMar)
		} //鼠标经过时清除定时器达到滚动停止的目的
	wwwzzjsnet.onmouseout = function() {
			MyMar = setInterval(Marquee, speed)
		} //鼠标移开时重设定时器*/
 /*头部滚动文字E*/   

/*弹框遮罩S*/
/*$(function(){
  	$('.bomb-btn').click(function(){
  		$('.bomb-wrap').show();
  		$('body').addClass('bomb-fiexd');
  	});
  	$('.bomb-wrap').click(function(){
  		$('.bomb-wrap').hide();
  		$('body').removeClass('bomb-fiexd');
  	});
  })*/
/*弹框遮罩E*/

/*担保协议 begin*/

$(document).ready(function(){
	$(".tabs li a").click(function(){
		$(".qfb-cencer").addClass("prcoltop");
	});
	$(window).scroll(function(event){
		$(".qfb-cencer").removeClass("prcoltop");
	});
	
	// 返回上一页
	$(".before").click(function(){
		window.history.go(-1);
	});
});

$(document).ready(function() {
	jQuery.jqtab = function(tabtit,tab_conbox,shijian) {
		$(tab_conbox).find("li").hide();
		$(tabtit).find("li:first").addClass("thistab").show();
		//$(tab_conbox).find("li:first").show();

		$(tabtit).find("li").bind(shijian,function(){
			$(this).addClass("thistab").siblings("li").removeClass("thistab");
			var activeindex = $(tabtit).find("li").index(this);
			$(tab_conbox).children().eq(activeindex).show().siblings().hide();
			return false;
		});

	};
	/*调用方法如下：*/
	$.jqtab("#tabs","#tab_conbox","click");


});
/*担保协议 end*/
