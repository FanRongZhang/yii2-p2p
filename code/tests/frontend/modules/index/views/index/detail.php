<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use frontend\assets\AppAsset;
use yii\grid\GridView; 
use yii\widgets\Pjax;

    $this->title = '产品详情';
    AppAsset::register($this);
    $this->registerJsFile('@web/js/jquery.js');


/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

$columns = [
    [
        'header'  => '投资人',
        'attribute' => 'member_id',
        'content' => function($order_model){
            return substr($order_model->info->realname,0,3).'**';
        },
    ],
    [
        'header'  => '投资金额',
        'attribute' => 'pay_money',
        'content' => function($order_model){
            //echo '<pre/>';var_dump($order_model->info);die;
            return $order_model->pay_money;
        },
    ],
    [
        'header'  => '投资时间',
        'attribute' => 'create_time',
        'content' => function($order_model){
            return date('Y-m-d H:i:s',$order_model->create_time);
        },
    ],
    [
        'header'  => '状态',
        'attribute' => 'status',
        'content' => function($order_model){
            if ($order_model->status == 0 || $order_model->status == 4) {
                return Html::encode('交易失败');
            } else {
                return Html::encode('交易成功');
            }
        },
    ],
    ];

?>

<!-- content product begin-->
<div class="main-cloum">
    <div class="q-wide">
        <div class="con-clo-main paddnones">
                <div class="con-list-box">
                    <div class="con-border-box">
                        <div class="con-list-tit"><span class="con-fnts f20"><?php echo $model['product_name'] ?></span>

                        <?php if ($model['category_id'] == 1) { ?>  
                        <span class="newsblues">新手专享</span>
                        <?php }?>
                           
                        </div>
                        <div class="con-times con-titmes-box">
                            <span class="fl">距结束</span><div id="timer6" class="fl con-time-data"></div>
                        </div>
                    </div>
                    <div class="clearfix">
                        <div class="pr-prdetails-box fl">
                            <div class="con-prdetails">
                                <div class="con-main-clounmt clearfix">
                                    <div class="fl per-box ied4">
                                        <div class="m01"><span class="f60 oranges"><?php echo $model['year_rate'] ?></span><span class="oranges f30">%</span>
                                        </div>
                                        <span class="gray1 f14">预期年化收益</span>
                                    </div>
                                    <div class="fl ied1-1"></div>
                                    <div class="fl per-box ied2 ied5">
                                        <div class="m02"><span class="black1 f24"><?php echo $model['stock_money'] ?></span><span class="f14 gray2">元</span></div>
                                        <span class="gray2 f14 pad10 dis">项目金额</span>
                                    </div>
                                    <div class="fl per-box ied2 ied5">
                                        <div class="m02"><span class="black1 f24"><?php echo $model['invest_day'] ?></span><span class="f14 gray2">天</span></div>
                                        <span class="gray2 f14 pad10 dis">投资期限</span>
                                    </div>
                                </div>
                            </div>
                            <div class="con-prdetails2 padb">
                                <div class="clearfix con-main-clounmt2">
                                    <div class="fl process-tip prdetial-tip">
                                        <span class="block" style="width: <?php echo $model['percent'] ?>%"></span>
                                    </div>
                                    <div class="fl jdnumber f18 gray3"><?php echo $model['percent'] ?>%</div>

                                
                                    <div class="fl padt10">剩余可投金额 <span class="f24 red1"><?php echo $model['residue'] ?>元</span>
                               
                               
                                </div>
                                </div>
                                <div class="clearfix con-main-clounmt2 mar30">
                                    <div class="fl"><i class="officicon pr-in1"></i><span class="pad25 black1">

                                    <?php if ($model['profit_type'] == 1) { ?>
                                        到期还本付息
                                    <?php }?>
                                    
                                     </span></div>
                                    <div class="fl marleft"><i class="officicon pr-in2"></i><span class="pad25 ">起投金额<?php echo $model['min_money'] ?>元</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="bord-conts fl"></div>
                        <div class="pr-prdetails-box2 fr">

                        <?php if (empty($member_data) || $member_data->is_dredge == 0) {?>
                            <div class="pr-bank-con" style="display:block">
                                <div>
                                    <img src="../../image/bankimg.png">
                                    <p class="blue1">为保障资金安全，本平台已接入海口联合农商银行资金存管系统，需开设资金存管账户并使用绑定银行卡充值才能参与投标。</p>
                                </div>
                                <div class="mar30">
                                    <button class="btn btnmains btn-wdetials"><a href="
                                    
                                    <?php if($member_data->id == 0) {?>
                                    /login/login/login
                                    <?php }else{?>
                                    /login/login/reg-bank?member_id=<?php echo $member_data->id ?>&member_type=<?php echo $member_data->member_type ?>
                                    <?php }?>
                                    ">同意协议并投资</a></button>
                                    <p class="gray2 mar6">限未成功投资过得新用户</p>
                                </div>
                            </div>
                        <?php }else{ ?>
                            <div style="display:block;">
                                <div class="pr-inp-box1">
                                    <p>投资金额</p>
                                    <div class="clearfix pad-botm">
                                        <div class="prform-inpt fl">
                                            <div class="fl"><input type="text" onblur="income()" class="printbox" placeholder="起投<?php echo $model['min_money'] ?>，递增<?php echo $model['step_money'] ?>"></div>元
                                        </div>
                                        <div class="fl xitoi-tit">限投<?php echo $model['max_money'] ?>元</div>

                                    </div>
                                    <div class="con-pamr">预期收益：<span id='income' class="orange1 f18">0</span>元</div>
                                </div>
                                <div class="btmarts">
                                    <div class="clearfix">
                                        <div class="fl">账户余额：<span class="orange1 f18"><?php echo $member_money ?></span>元</div>
                                        <div class="fl btnflts"><a href="#" class="btnyc-con">充值</a> </div>
                                    </div>
                                    <div>抵扣金额: <span class="orange1 f18">0</span>元</div>
                                    <div>实际支付: <span class="orange1 f18">0</span>元</div>
                                    <div class="smart-form td-pr-check gray2">
                                        <label class="checkbox fl">
                                            <input type="checkbox" name="RegisterForm[agreement]" checked="checked" id="CheckboxGroup1_0">
                                            <i></i>
                                        </label>
                                        <div class="damar1">暂无代金券</div>
                                    </div>
                                </div>

                                <?php if ($model['status'] == 1) {?>
                                <div class="mar15"><button type='button' onclick="invest()" class="btn btnmains btn-wdetials">立即投资</button></div>
                                <?php }elseif($model['status'] == 2) {?>
                                <div class="mar15"><button type='button' class="btn btnmains btn-wdetials btnfgrays">已满额</button></div>
                                <?php }elseif($model['status'] == 6) {?>
                                <div class="mar15"><button type='button' class="btn btnmains btn-wdetials btnfgrays">还款中</button></div>
                                <?php }else {?>
                                <div class="mar15"><button type='button' class="btn btnmains btn-wdetials btnfgrays">已结束</button></div>
                                <?php }?>

                            </div>
                        <?php }?>

                        </div>
                    </div>
                </div>


            </div>

        <div class="con-pr-project">
            <div class="con-prdpages">
                <ul class="tabs clearfix">
                    <li class="pr-active"><a href="#tab1">项目介绍</a> </li>
                    <li><a href="#tab2">投资记录</a> </li>
                </ul>
            </div>
            <div class="tab_container">
                <div id="tab1" class="tab_content pad-conts">
                    <table width="100%">
                        <tbody>
                            <tr>
                                <td class="td1 gray3">项目名称</td>
                                <td class="td2 black1"><?php echo $model['product_name'] ?></td>
                                <td class="td1 gray3">起投金额</td>
                                <td class="td2 black1"><?php echo $model['min_money'] ?>元</td>
                            </tr>
                            <tr>
                                <td class="td1 gray3">预期年化收益</td>
                                <td class="td2 black1"><?php echo $model['year_rate']?>%</td>
                                <td class="td1 gray3">投资上限</td>
                                <td class="td2 black1"><?php echo $model['max_money'] ?>元</td>
                            </tr>
                            <tr>
                                <td class="td1 gray3">投资期限</td>
                                <td class="td2 black1"><?php echo $model['invest_day'] ?>天</td>
                                <td class="td1 gray3">投资限制</td>
                                <td class="td2 black1">只能使用零钱余额</td>
                            </tr>
                            <tr>
                                <td class="td1 gray3">项目金额</td>
                                <td class="td2 black1"><?php echo $model['stock_money'] ?>元</td>
                                <td class="td1 gray3">计息时间</td>
                                <td class="td2 black1">
                                    <?php if($model['profit_day'] ==10){?>
                                    投资日
                                    <?php }elseif($model['profit_day'] ==11){ ?>
                                    投资日+1
                                    <?php }elseif($model['profit_day'] ==20){ ?>
                                    满标日
                                    <?php }elseif($model['profit_day'] ==21){ ?>
                                    满标日+1
                                    <?php }?>
                                </td>
                            </tr>
                            <tr>
                                <td class="td1 gray3">发售时间</td>
                                <td class="td2 black1"><?php echo date('Y-m-d H:i:s',$model['start_time']) ?></td>
                                <td class="td1 gray3">结束时间</td>
                                <td class="td2 black1"><?php echo date('Y-m-d H:i:s',$model['end_time']) ?></td>
                            </tr>
                            <tr>
                                <td class="td1 gray3">收益方式</td>
                                <td class="td2 black1" colspan="3">
                                    <?php if($model['profit_type'] == 1) { ?>
                                    到期还本付息
                                    <?php }?>
                                </td>
                            </tr>
                            <tr>
                                <td class="td1 gray3">协议</td>
                                <td class="td2 black1" colspan="3">

                                <?php foreach ($model['product_agreement'] as $key => $v) { ?>
                                    <a href="/index/index/xy?id=<?php echo $v['id'] ?>">《<?php echo $v['title'] ?>》</a>
                                <?php }?>

                                </td>
                            </tr>
                            <tr>
                                <td class="td1 gray3 veterts">其他说明</td>
                                <td class="td2 black1" colspan="3">
                                   <?php echo $model['detail'] ?>
                                        <!-- <img src="img/rz.png"> -->
                                    </p>

                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div id="tab2" class="tab_content clearfix">
                    <?php Pjax::begin() ?>
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider,
                            'options' => ['class' => 'pr-record'],
                            /* 表格配置 */
                    //        'tableOptions' => ['class' => ''],
                            /* 重新排版 摘要、表格、分页 */
                            'layout' => '{items}<div class="page-box con-pagets"><div class="b-page clearfix">{summary}</div><div id="b-count" class="fl">{pager}</div></div>',
                            /* 配置摘要 */
                    //        'summaryOptions' => ['class' => 'pagination'],
                            /* 配置分页样式 */
                            'pager' => [
                                'options' => ['class'=>'pagination','style'=>'visibility: visible;'],
                                'nextPageLabel' => '下一页',
                                'prevPageLabel' => '上一页',
                                'firstPageLabel' => '第一页',
                                'lastPageLabel' => '最后页'
                            ],
                            /* 定义列表格式 */
                            'columns' => $columns,
                        ]); ?>

                    <?php Pjax::end() ?>
                    </div>

                    <!--分页 end-->
                </div>

            </div>

        </div>
    </div>

</div>
<!-- content product end-->
<!--充值弹框 begin-->
<div class="d-bodyeject bomberst">
    <div class="bombbox">
        <div class="bombbox4"><i class="officicon"></i></div>
        <div class="bombbox5">
            <div class="bombbox1 clearfix">
                <!-- <div class="fl imgters"><img src="img/banki.png" ></div> -->
                <div class="fl"><?php echo  $member_bank['name'] ?></div>
                <div class="fr">尾号：<?php echo substr($member_bank['no'],-4) ?></div>
            </div>
            <div class="bombbox6">注：单笔限额50万元，每日限额50万元</div>
        </div>

        <div class="bombbox2">
            <form>
                <div class="mar6">
                    <div class="clearfix mar6">
                        <label class="fl sn-pt1">账户余额</label>
                        <div class="fl lints"><span class="black1 f18 orange2"><?php echo $member_money ?></span>元</div>
                    </div>
                    <div class="clearfix mar6">
                        <label class="fl sn-pt1">充值金额</label>
                        <div class="prform-inpt prform-inptts fl">
                            <div class="fl"><input id='money' type="text" class="printbox fonwts" placeholder="请输入充值金额"></div>元
                        </div>
                    </div>
                </div>
                <div class="mar30">
                    <div><button onclick='recharge()' type='button' class="login-btnter lgbt-con">提交</button></div>
                </div>
            </form>
        </div>
        <div class="bombbox3">
            <div class="mar30">
                <p class="blue1"><i class="officicon eitrs"></i>温馨提示</p>
                <p class="grya6 mar6">需跳转至海口联合农商银行资金存管系统验证身份</p>
            </div>
        </div>
    </div>

</div>
<div class="d-bodybg dm-popup-box" id="ShowNewUserBox"></div>
<!--充值弹框 end-->
<!--常用弹框 begin-->
<div class="with-taste">
    <div class="with-con-box">
        <div class="withbox1"><i class="officicon with-iconclose"></i></div>
        <div class="withbox2">
            <div class="withbox2-1"><i  class="officicon with-erros"></i><span id='message'>投资金额有误，请重新输入</span></div>
            <div class="withbox2-2"><a href="#" class="close-sucess">确定</a></div>
        </div>
    </div>
</div>
<!--常用弹框 end-->


<?php $this->beginBlock('test'); ?>

<?php if (!empty($member_data) && !empty($member_bank)) {?>

//获取用户信息
var member_id=<?php echo $member_data->id ?>
//银行卡ID
var bank_id=<?php echo $member_bank->id ?>
//用户余额
var member_money=<?php echo $member_money ?>
<?php }?>
//产品ID
var product_id=<?php echo $model['id'] ?>
//最小投资金额
var min_money=<?php echo $model['min_money'] ?>
//最大金额
var max_money=<?php echo $model['max_money'] ?>
//递增金额
var step_money=<?php echo $model['step_money'] ?>
//剩余可投金额
var residue=<?php echo $model['residue'] ?>

//年化收益
var year_rate = <?php echo $model['year_rate'] ?>
//投资天数
var invest_day = <?php echo $model['invest_day'] ?>
//结束倒计时
addTimer("timer6", <?php echo $model['endtime']>0 ? $model['endtime'] : 0 ?>);

//充值弹窗
$(".btnyc-con").click(function(){
    $('.bomberst,.dm-popup-box').show();
    $("body").addClass("bodyshopcar");
});
$(".bombbox4").click(function(){
    $('.bomberst,.dm-popup-box').hide();
    $("body").removeClass("bodyshopcar");
});

//预期收益
function income() {
    var money=$('.printbox').val();
    money=money.replace(/\b(0+)/gi,"")
    $('.printbox').val(money)
    var re = /^[0-9]+$/ ;
   
    if (!re.test(money)) {
        $('.with-taste,.dm-popup-box').show();
        $("body").addClass("bodyshopcar");

        $('#income').html(0)
        $('.printbox').val('')
        return false 
    }
    if (money < min_money) {
        $('.with-taste,.dm-popup-box').show();
        $("body").addClass("bodyshopcar");
        $('#message').html('起投金额为'+min_money)
        return false
    }
    if (money > max_money){
        $('.with-taste,.dm-popup-box').show();
        $("body").addClass("bodyshopcar");
        $('#message').html('最大投资金额为'+max_money)
        return false
    }
    if (money > residue){
        $('.with-taste,.dm-popup-box').show();
        $("body").addClass("bodyshopcar");
        $('#message').html('剩余投资金额为'+residue)
        return false
    }
    
    var dizen = (money-min_money)/step_money;

    if (!re.test(dizen)) {
        $('.with-taste,.dm-popup-box').show();
        $("body").addClass("bodyshopcar");
        $('#message').html('递增投资金额为'+step_money)
        return false
    }

     if (money > member_money) {
        $('.with-taste,.dm-popup-box').show();
        $("body").addClass("bodyshopcar");
        $('#message').html('零钱余额不足，请先充值')
        return false
    }
    
    
    var come= money*year_rate/100*invest_day/365; 
    var income=come.toFixed(2)
    $('#income').html(income)
   
}

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

//充值提交
function recharge() {
    //验证表单
    var money=$('#money').val();
    money=money.replace(/\b(0+)/gi,"")
    var re = /^[0-9]+$/ ;
    if (!re.test(money) || money > 500000 || money < 1) {
        alert('充值金额错误');
        $('#money').val('')
        return false
    }
    
    $('#money').val(money)
    location.href='/index/index/recharge?member_id='+member_id+'&money='+money+'&bank_id='+bank_id
}

 $('.with-taste').hide();

    //投资
    function invest(){
        //验证投资金额
        var money=$('.printbox').val();
        //去除前面的0
        money=money.replace(/\b(0+)/gi,"")
        $('.printbox').val(money)
        var re = /^[0-9]+$/ ;
        if (!re.test(money)) {
            $('.with-taste,.dm-popup-box').show();
            $("body").addClass("bodyshopcar");

            $('#income').html(0)
            $('.printbox').val('')
            return false 
        }
        if (money < min_money) {
            $('.with-taste,.dm-popup-box').show();
            $("body").addClass("bodyshopcar");
            $('#message').html('起投金额为'+min_money)
            return false
        }
        if (money > max_money){
            $('.with-taste,.dm-popup-box').show();
            $("body").addClass("bodyshopcar");
            $('#message').html('最大投资金额为'+max_money)
            return false
        }
        if (money > residue){
            $('.with-taste,.dm-popup-box').show();
            $("body").addClass("bodyshopcar");
            $('#message').html('剩余投资金额为'+residue)
            return false
        }

        var dizen = (money-min_money)/step_money;

        if (!re.test(dizen)) {
            $('.with-taste,.dm-popup-box').show();
            $("body").addClass("bodyshopcar");
            $('#message').html('递增投资金额为'+step_money)
            return false
        }
        
        location.href='/index/index/invest?member_id='+member_id+'&money='+money+'&product_id='+product_id
    };

    $(".with-iconclose,.close-sucess").click(function(){
        $('.with-taste,.dm-popup-box').hide();
        $("body").removeClass("bodyshopcar");
    });

<?php $this->endBlock()  ?>
<!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>