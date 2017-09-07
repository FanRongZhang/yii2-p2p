<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
    
$this->title = '定期理财列表';
?>
<!-- content product begin-->
<div class="main-cloum">
    <div class="q-wide">
        <div class="product-tit-tab clearfix">
            <ul>
                <li><a href="/index/index/mortgagelist" class="sort-tit <?php if ($sort==1){ echo 'prtit-active';} ?>">综合排序</a> </li>
                <li><a href="#" onclick='sort1()' class="sort-tit <?php if ($sort==2 || $sort==3){ echo 'prtit-active';} ?>">收益利率<span id='span' class="officicon pr-icon-downs <?php if($sort==2){echo 'pr-icon-hdowns';}elseif($sort==3){echo 'pr-icon-up';} ?>"></span></a></li>
                <li><a href="#" onclick='sort2()' class="sort-tit <?php if ($sort==4 || $sort==5){ echo 'prtit-active';} ?>">投资期限<span id='span2' class="officicon pr-icon-downs <?php if($sort==4){echo 'pr-icon-hdowns';}elseif($sort==5){echo 'pr-icon-up';} ?>"></span></a></li>
            </ul>
        </div>
        <?php Pjax::begin() ?>
        <div class="con-cloum1">
			<?php if(is_array($list) && !empty($list)) :?>
                <?php foreach($list as $k => $v) :?>
		            <div class="con-clo-main">
		                <div class="con-list-box">
		                    <div class="con-indexts-box1">
		                        <div class="con-list-tit"><span class="con-fnts"><?php echo $v['product_name']; ?></span></div>
		                        <div class="con-times">
		                            <span class="fl">距结束</span><div id="timer_<?php echo $k;?>" class="fl con-time-data"></div>
		                        </div>
		                    </div>
		                    <div class="con-main-clounmt clearfix">
		                        <div class="fl per-box ied1">
		                            <div class="m01"><span class="f60 oranges"><?php echo intval(rtrim($v['year_rate'], '%')); ?></span><span class="oranges f30">%</span></div>
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
		                        <div class="con-buttons fl"><a href="/index/index/detail?id=<?php echo $v['id'] ?>" class="btn btnmains <?php if($v['status'] != 1){echo 'btngrays';} ?>">
		                        		<?php 
                                            if ($v['status'] == 2) {
                                                echo "已满额";
                                            } else if($v['status'] == 1 ) {
                                                echo "立即投资";
                                            } else if($v['status'] ==6){
                                                echo '还款中';
                                            } else {
                                                echo '已结束';
                                            }
                                        ?> </a></div>
		                    </div>
		                    <div class="clearfix con-main-clounmt2">
		                        <div class="fl"><i class="officicon pr-in1"></i><span class="pad25 black1">收益方式
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
		                         </span></div>
		                        <div class="fl marleft"><i class="officicon pr-in2"></i><span class="pad25 black1">起投金额<?php echo $v['min_money']; ?>元</span></div>
		                        <div class="fl marleft"><i class="officicon pr-in3"></i><span class="pad25 black1">限投金额<?php echo $v['max_money']; ?>元</span></div>
		                    </div>
		                </div>
		            </div>
				<?php endforeach;?>
            <?php endif;?>
        </div>
        <?php Pjax::end() ?>
    </div>

    <!--分页 begin-->
    <div class="page-box">
        <div class="b-page clearfix">
            <!-- 分页 start-->
	        <?= $this->render('../../../../views/_page', ['count' => $count, 'page' => $page]) ?>
	        <!-- 分页 end-->
        </div>
    </div>
    <!--分页 end-->
</div>
<!-- content product end-->


<?php $this->beginBlock('test'); ?>

<?php foreach($list as $k => $v){?>
    addTimer("timer_<?php echo $k?>", 
        <?php  if ($v['end_time']-time() > 0){
            echo $v['end_time']-time();
        } else {
                echo 0;
        } ?>)
<?php } ?>


/*升降排序 begin*/
    function sort1() {
    
        if ($('#span').hasClass("pr-icon-hdowns")) {

            $(this).addClass("prtit-active");
            $(this).children().addClass("pr-icon-up");
            $(this).children().removeClass("pr-icon-hdowns");
            location.href='/index/index/mortgagelist?sort=3'
                 
        } else {

            $(this).addClass("prtit-active");
            $(this).children().addClass("pr-icon-hdowns");
            $(this).children().removeClass("pr-icon-up");
            location.href='/index/index/mortgagelist?sort=2'
        }
        $(this).parent().siblings().children().children('.pr-icon-downs').removeClass('pr-icon-hdowns').removeClass('pr-icon-up');
        $(this).parent().siblings().children().removeClass('prtit-active');
    }

     function sort2() {

        if ($('#span2').hasClass("pr-icon-hdowns")) {
            $(this).addClass("prtit-active");
            $(this).children().addClass("pr-icon-hdowns");
            $(this).children().removeClass("pr-icon-up");
            location.href='/index/index/mortgagelist?sort=5'
     
        } else {

            $(this).addClass("prtit-active");
            $(this).children().addClass("pr-icon-up");
            $(this).children().removeClass("pr-icon-hdowns");
            location.href='/index/index/mortgagelist?sort=4'
        }
        $(this).parent().siblings().children().children('.pr-icon-downs').removeClass('pr-icon-hdowns').removeClass('pr-icon-up');
        $(this).parent().siblings().children().removeClass('prtit-active');
    }
    /*升降排序 end*/

<?php $this->endBlock()  ?>
<!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>