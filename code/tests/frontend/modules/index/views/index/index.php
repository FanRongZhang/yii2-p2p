<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use frontend\assets\AppAsset;
    
    $this->title = '首页';
    AppAsset::register($this);
    $this->registerJsFile('@web/js/jquery.js');
?>

<!--banner begin-->
<div class="z-slider">
    <div class="banner">
        <ul>
        <?php foreach ($banner as $key => $value) { ?>
            <li><a href="<?php echo $value['url'] ?>"> <img src="<?php echo \Yii::$app->fileStorage->baseUrl.'/'. $value['image'];?>" alt="" width="100%" height="402"></a></li>
        <?php } ?>
        </ul>
        <div class="arrow-box">
            <a href="javascript:void(0);" class="unslider-arrow prev"><span class="arrow" id="al" ></span></a>
            <a href="javascript:void(0);" class="unslider-arrow next"><span class="arrow" id="ar" ></span></a>
        </div>
    </div>
</div>
<!--banner end-->
<!--notice begin-->
<div class="notice-box">
    <div class="q-wide">
        <div class="not-title clearfix">
            <div class="fl writetitel"><i class="officicon noticeicon"></i><h1>平台公告</h1></div>
            <div class="fr commmore"><a href="/index/index/newslist" >查看更多<i class="officicon moreicon"></i></a></div>
        </div>

        <div id="broadcast" class="newsbar clearfix">
            <div id="newsbox" class="fl newslist">
                <ul class="mingdan" id="holder">
                    <?php if(is_array($notice) && !empty($notice)) :?>
                        <?php foreach($notice as $k => $value) :?>
                            <li>
                                <a href="/index/index/newsdetail?id=<?php echo $value['id'] ?>"><?php echo $value['title'];?><span class="marleft fr"><?php echo date('Y-m-d H:i:s', $value['create_time']);?></span></a>
                            </li>
                        <?php endforeach;?>
                    <?php endif;?>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--notice end-->

<!-- content product begin-->
<div class="main-cloum">
    <div class="q-wide">
        <div class="con-cloum1">
            <div class="con-cloumtitle clearfix">
                <div class="con-clo-tit fl">
                    <h2>新手理财</h2><span>限未投资过得新用户</span>
                </div>
            </div>
            <?php if(is_array($xinshou) && !empty($xinshou)) :?>
                <?php foreach($xinshou as $k => $value) :?>
                    <div class="con-clo-main">
                        <div class="con-list-box">
                            <div class="con-indexts-box1">
                                <div class="con-list-tit"><span class="con-fnts">新手专享理财</span><span class="newsblues">新手专享</span></div>
                                <div class="con-times">
                                    <span class="fl">距结束</span><div id="timer_xinshou" class="fl con-time-data"></div>
                                </div>
                            </div>
                            <div class="con-main-clounmt clearfix">
                                <div class="fl per-box ied1">
                                    <div class="m01"><span class="f60 oranges"><?php echo intval(rtrim($value['rate'], '%')); ?></span><span class="oranges f30">%</span></div>
                                    <span class="gray1 f14">预期年化收益</span>
                                </div>
                                <div class="fl ied1-1"></div>
                                <div class="fl per-box ied2">
                                    <div class="m02"><span class="black1 f24"><?php echo $value['stock_money']; ?></span><span class="f14 gray2">元</span></div>
                                    <span class="gray2 f14 pad10 dis">项目金额</span>
                                </div>
                                <div class="fl per-box ied2">
                                    <div class="m02"><span class="black1 f24"><?php echo $value['invest_day']; ?></span><span class="f14 gray2">天</span></div>
                                    <span class="gray2 f14 pad10 dis">投资期限</span>
                                </div>
                                <div class="fl per-box ied3">
                                    <div class="fl process-tip">
                                        <span class="block" style="width: <?php echo $value['has_money']/$value['stock_money']*100; ?>%"></span>
                                    </div>
                                    <div class="fl jdnumber f18 gray2"><?php echo sprintf("%.2f", $value['has_money']/$value['stock_money']*100); ?>%</div>
                                    <span class="gray2 f14 pad10 dis">剩余可投金额 <span class="red1 f20 <?php if($value['stock_money'] - $value['has_money'] <= 0){echo "gray2";}?>"><?php echo $value['stock_money'] - $value['has_money']; ?></span>元</span>
                                </div>

                                <div class="con-buttons fl"><a href="<?php echo Url::to(['/index/index/detail', 'id'=>$value['id']]); ?>" class="btn btnmains <?php if($value['type'] != 1){echo 'btngrays';} ?>">立即投资</a></div>
                            </div>
                            <div class="clearfix con-main-clounmt2">
                                <div class="fl">
                                    <i class="officicon pr-in1"></i>
                                    <span class="pad25 black1">收益方式
                                        <?php 
                                            if ($value['profit_type'] == 1) {
                                                echo "到期还本付息";
                                            } elseif($value['profit_type'] == 2) {
                                                echo "按月等额付息，到期还本";
                                            } elseif($value['profit_type'] == 3) {
                                                echo "按日等额付息，到期还本";
                                            } elseif($value['profit_type'] == 4) {
                                                echo "按月等额本息";
                                            } elseif($value['profit_type'] == 5) {
                                                echo "按日等额本息";
                                            } elseif($value['profit_type'] == 6) {
                                                echo "按月等额还本，到期付息";
                                            } elseif($value['profit_type'] == 7) {
                                                echo "按日等额还本，到期付息";
                                            }
                                        ?> 
                                    </span>
                                </div>
                                <div class="fl marleft"><i class="officicon pr-in2"></i><span class="pad25 black1">起投金额<?php echo $value['min_money']; ?>元</span></div>
                                <div class="fl marleft"><i class="officicon pr-in3"></i><span class="pad25 black1">限投金额<?php echo $value['max_money']; ?>元</span></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach;?>
            <?php endif;?>
        </div>

        <div class="con-cloum1">
            <div class="con-cloumtitle clearfix">
                <div class="con-clo-tit fl">
                    <h2>汽车抵押贷</h2>
                </div>
                <div class="fr commmore"><a href="<?php echo Url::to(['/index/index/mortgagelist']); ?>" >查看更多<i class="officicon moreicon"></i></a></div>
            </div>
            <?php if(is_array($dingqi) && !empty($dingqi)) :?>
                <?php foreach($dingqi as $k => $v) :?>
                    <div class="con-clo-main">
                        <div class="con-list-box">
                            <div class="con-indexts-box1">
                                <div class="con-list-tit"><span class="con-fnts"><?php echo $v['name']; ?></span></div>
                                <div class="con-times">
                                    <span class="fl">距结束</span><div id="timer_<?php echo $k ?>" class="fl con-time-data"></div>
                                </div>
                            </div>
                            <div class="con-main-clounmt clearfix">
                                <div class="fl per-box ied1">
                                    <div class="m01"><span class="f60 oranges"><?php echo intval(rtrim($v['rate'], '%')); ?></span><span class="oranges f30">%</span></div>
                                    <span class="gray1 f14">预期年化收益</span>
                                </div>
                                <div class="fl ied1-1"></div>
                                <div class="fl per-box ied2">
                                    <div class="m02"><span class="black1 f24"><?php echo $v['stock_money']; ?></span><span class="f14 gray2">元</span></div>
                                    <span class="gray2 f14 pad10 dis">项目金额</span>
                                </div>
                                <div class="fl per-box ied2">
                                    <div class="m02"><span class="black1 f24"><?php echo $v['invest_day']; ?></span><span class="f14 gray2">天</span></div>
                                    <span class="gray2 f14 pad10 dis">投资期限</span>
                                </div>
                                <div class="fl per-box ied3">
                                    <div class="fl process-tip">
                                        <span class="block" style="width: <?php echo $v['has_money']/$v['stock_money']*100; ?>%"></span>
                                    </div>
                                    <div class="fl jdnumber f18 gray2"><?php echo sprintf("%.2f", $v['has_money']/$v['stock_money']*100); ?>%</div>
                                    <span class="gray2 f14 pad10 dis">剩余可投金额 <span class="red1 f20 <?php if($v['stock_money'] - $v['has_money'] <= 0){echo "gray2";}?>"><?php echo $v['stock_money'] - $v['has_money']; ?></span>元</span>
                                </div>
                                <div class="con-buttons fl">
                                    <a href="<?php echo Url::to(['/index/index/detail', 'id'=>$v['id']]); ?>" class="btn btnmains <?php if($v['type'] != 1){echo 'btngrays';} ?>">
                                        <?php 
                                            if ($v['type'] != 1) {
                                                echo "已满额";
                                            } else {
                                                echo "立即投资";
                                            }
                                        ?> 
                                        
                                    </a>
                                </div>
                            </div>
                            <div class="clearfix con-main-clounmt2">
                                <div class="fl">
                                    <i class="officicon pr-in1"></i>
                                    <span class="pad25 black1">收益方式
                                        <?php 
                                            if ($v['profit_type'] == 1) {
                                                echo "到期还本付息";
                                            } elseif($v['profit_type'] == 2) {
                                                echo "按月等额付息，到期还本";
                                            } elseif($v['profit_type'] == 3) {
                                                echo "按日等额付息，到期还本";
                                            } elseif($v['profit_type'] == 4) {
                                                echo "按月等额本息";
                                            } elseif($v['profit_type'] == 5) {
                                                echo "按日等额本息";
                                            } elseif($v['profit_type'] == 6) {
                                                echo "按月等额还本，到期付息";
                                            } elseif($v['profit_type'] == 7) {
                                                echo "按日等额还本，到期付息";
                                            }
                                        ?> 
                                    </span>
                                </div>
                                <div class="fl marleft"><i class="officicon pr-in2"></i><span class="pad25 black1">起投金额<?php echo $v['min_money']; ?>元</span></div>
                                <div class="fl marleft"><i class="officicon pr-in3"></i><span class="pad25 black1">限投金额<?php echo $v['max_money']; ?>元</span></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach;?>
            <?php endif;?>
        </div>

    </div>
</div>

<div class="q-wide">
    <div class="pad40">
        <div class="con-clo-tit padborder"><h2>合作机构</h2></div>
        <?php foreach ($partner as $key => $value) { ?>
            <div class="con-clo-img"><img src="<?php echo \Yii::$app->fileStorage->baseUrl.'/'. $value['image'];?>"></div>
        <?php } ?>
    </div>
</div>

<!--回到顶部--S-->
<div class="back-top" id="toolBackTop">
    <a title="返回顶部" onclick="window.scrollTo(0,0);return false;" href="#top" class="backtop"></a>
</div>

<!--回到顶部--E-->

<!-- content product end-->
<?php $this->beginBlock('test'); ?>

    
    <?php foreach ($xinshou as $k => $value) { ?>

        addTimer("timer_xinshou", <?php echo $value['end_time']/1000 ?>);
    <?php } ?>

    <?php foreach ($dingqi as $k => $value) { ?>
        addTimer("timer_<?php echo $k ?>", <?php echo $value['end_time']/1000 ?>);
    <?php } ?>
    
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

<?php $this->endBlock()  ?>
<!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>