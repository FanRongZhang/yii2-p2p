
/****回到顶部的设置* S***/
$(document).ready(function() {
    var bt = $('#toolBackTop');
    var sw = $(document.body)[0].clientWidth;
    var limitsw = (sw - 840) / 2 - 80;
    if (limitsw > 0) {
        limitsw = parseInt(limitsw);
        bt.css("right", limitsw);
    }
    $(window).scroll(function() {
        var st = $(window).scrollTop();
        if (st > 30) {
            bt.show();
        } else {
            bt.hide();
        }
    });
});
/****回到顶部的设置* E***/

/*banner 轮播 begin*/
$(document).ready(function(e) {
	    var unslider = $('.z-slider .banner').unslider({
			dots: true
		}),

		data = unslider.data('unslider');

		$('.unslider-arrow').click(function() {
	        var fn = this.className.split(' ')[1];
	        data[fn]();
	    });
	    $('.z-slider .banner').bind({
	    	mouseover:function(){$('.z-slider .unslider-arrow').css('display','block')},
	    	mouseout:function(){$('.z-slider .unslider-arrow').css('display','none')}
	    });

	    var dotlen=$('.dot').length;
	    if(dotlen<2){
	    	$('.dots').hide();
	    	$('.z-slider .banner').mouseover(function(){
	    		$('.z-slider .unslider-arrow').css('display','none')
	    	});
	    }
	})
/*banner 轮播 end*/


/*新闻滚动 begin*/
function AutoScroll(obj) {
    $(obj).find("ul:first").animate({
            marginTop: "-22px"
        },
        500,
        function() {
            $(this).css({
                marginTop: "0px"
            }).find("li:first").appendTo(this);
        });
	}
$(document).ready(function() {
    setInterval('AutoScroll("#newsbox")', 1000)
});
/*新闻滚动 end*/

/*倒计时 begin*/
var addTimer = function () {
    var list = [],
        interval;

    return function (id, time) {
        if (!interval)
            interval = setInterval(go, 1000);
        list.push({ ele: document.getElementById(id), time: time });
    }

    function go() {
        for (var i = 0; i < list.length; i++) {
            list[i].ele.innerHTML = getTimerString(list[i].time ? list[i].time -= 1 : 0);
            if (!list[i].time)
                list.splice(i--, 1);
        }
    }

    function getTimerString(time) {
        var not0 = !!time,
            d = Math.floor(time / 86400),
            h = Math.floor((time %= 86400) / 3600),
            m = Math.floor((time %= 3600) / 60),
            s = time % 60;
        if (not0)
            return  '<i class="textorange1">'+d+'</i>' + "天" +'<i class="texorange2">'+h+'</i>' + "时" + '<i class="texorange3">'+m+'</i>' + "分" + '<i class="texorange4">'+s+'</i>' + "秒";
        else return  '<i class="textorange1">'+d+'</i>' + "天" +'<i class="texorange2">'+h+'</i>' + "时" + '<i class="texorange3">'+m+'</i>' + "分" + '<i class="texorange4">'+s+'</i>' + "秒";
    }
} ();
/*倒计时 end*/

/*翻页 begin*/
$(function(){
    $('#page').jqPaginator({
        totalPages: 14,
        visiblePages:4,
        currentPage: 1,
        totalCounts:40,
        onPageChange: function (num, type) {
            $('#text').html('共' + num + '页');
        }
    });
});
/*翻页 end*/

/*tab切换 begin*/
$(document).ready(function() {
    $(".tab_content").hide(); //Hide all content
    $("ul.tabs li:first").addClass("pr-active").show(); //Activate first tab
    $(".tab_content:first").show(); //Show first tab content

    //On Click Event
    $("ul.tabs li").click(function() {
        $("ul.tabs li").removeClass("pr-active"); //Remove any "active" class
        $(this).addClass("pr-active"); //Add "active" class to selected tab
        $(".tab_content").hide(); //Hide all tab content
        var activeTab = $(this).find("a").attr("href"); //Find the rel attribute value to identify the active tab + content
        $(activeTab).fadeIn(); //Fade in the active content
        return false;
    });

});
/*tab切换 end*/

// 验证码点击效果
$(function(){
    $(".regyzdbtn").click(function(){
        $(this).addClass("btn-titgrya");
        $(".regyzdbtn").html("55秒后重发")

    })
})









