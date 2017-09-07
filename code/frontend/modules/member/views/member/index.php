<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use common\widgets\datepicker\DatePicker;
use yii\widgets\Pjax;

$this->title = '账户预览';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/js/echarts.min.js');
$this->registerJsFile('@web/datepicker/laydate.js');

$columns = [
    [
        'header'  => '时间',
        'attribute' => 'create_time',
        'content' => function($model){
            return date('Y-m-d H:i:s',$model->create_time);
        },
    ],
    [
        'header'  => '类型',
        'attribute' => 'type',
        'content' => function($model){
            return $model->type == 1 ? '收入':'支出';
        },
    ],
    [
        'header'  => '业务',
        'attribute' => 'action',
        'content' => function($model){
            $sorts = '';
            if($model->action == 2){
                $sorts = '充值';
            }elseif($model->action == 7){
                $sorts = '提现';
            }elseif($model->action == 16){
                $sorts =  '定期收益';
            }elseif($model->action == 22){
                $sorts =  '放款';
            }elseif($model->action == 21){
                $sorts =  '还款';
            }elseif($model->action == 12){
                $sorts =  $model->type == 1 ? ($model->money_type == 3 ? '投资增加' : '本金返还') : '投标';
            }

            return $sorts;
        },
    ],
    [
        'header'  => '金额',
        'attribute' => 'money',
    ]
];
?>
    <!-- content product begin-->
    <div class="fr memberright">
        <div class="member-main-box1">
            <h2 class="member-total-tit">账户总览</h2>
            <div class="clearfix member-photo-name">
                <div class="fl member-photo-img"><img src="<?php echo $member['avatar']?\Yii::$app->fileStorage->baseUrl.'/'.$member['avatar']:'/image/mrtx.png';?>"></div>
                <div class="fl member-name-ip">
                    <ul>
                        <li>手机号码：<span class="gray3"><?php echo substr($member['mobile'],0,3).'****'.substr($member['mobile'],7);?></span></li>
                        <li>姓名：<?php
                            if($member['is_dredge'] == 1){
                                echo '*'.substr($member['realname'],3);
                            }elseif($member['is_dredge'] == 9){
                                echo '<a href="'.Url::to(['/member/auth/index']).'" class="blue1">认证异常</a>';
                            }else{
                                echo '<a href="'.Url::to(['/member/auth/index']).'" class="blue1">未认证</a>';
                            }
                            ?>
                        </li>
                        <li>上次登录：<?php if($member['last_access_time'] != 0) { echo date('Y-m-d H:i:s', $member['last_access_time']);}?> <?php if(!empty($member['address'])){ echo $member['address'];}?> <?php if(!empty($member['ip'])){ echo $member['ip'];}?><br><?php if($member['ip_status'] == 0){ echo '<span class="red1 f12">不在常用地址登录，建议修改登录密码</span>';}?></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="member-main-box2">
            <div class="member-total-price clearfix">
                <ul>
                    <li>
                        <p class="red1 f20"><?php echo $member['all_money'];?></p>
                        <p class="f14 black1">总资产（元）</p>
                    </li>
                    <li>
                        <p class="red1 f20">
                            <?php 
                                if ($member['member_type'] == 1) {
                                    echo $member['all_profit'];
                                } else {
                                    echo $member['all_credit'];
                                }
                                
                            ?>
                        </p>
                        <p class="f14 black1">
                            <?php
                            if ($member['member_type'] == 1) {
                                echo "累计收益（元）";
                            } else {
                                echo "累计放款（元）";
                            }
                            ?>
                        </p>
                    </li>
                    <li>
                        <p class="red1 f20">
                            <?php 
                                if ($member['member_type'] == 1) {
                                    echo $member['yesterday_profit'];
                                } else {
                                    echo $member['yesterday_credit'];
                                }
                            ?>
                        </p>
                        <p class="f14 black1">
                            <?php
                            if ($member['member_type'] == 1) {
                                echo "昨日收益（元）";
                            } else {
                                echo "昨日放款（元）";
                            }
                            ?>
                        </p>
                    </li>
                </ul>
            </div>

        </div>
        <div class="member-main-box3">
            <div class="member-statistics clearfix">
                <div class="member-stais-left fl">
                    <h2 class="member-stais-tit">资产分布</h2>
                    <div class="member-stais-mians" id="main" style="width: 400px;height:300px;">
                    </div>
                </div>
                <div class="member-stais-right fl">
                    <div class="member-balance">
                        <p class="f20 black1"><?php echo $member['money']?></p>
                        <p class="f14 gray3">零钱余额（元）</p>
                    </div>
                    <div class="member-investment clearfix">
                        <ul>
                            <li>
                                <p class="f20 black1"><?php echo $member['lock_money']?></p>
                                <p class="f14 gray3">冻结金额（元）</p>
                            </li>
                            <li>
                                <p class="f20 black1">
                                    <?php 
                                        if ($member['member_type'] == 1) {
                                            echo $member['fix_money'];
                                        } else {
                                            echo $member['dai_money'];
                                        }
                                    ?>
                                </p>
                                <p class="f14 gray3">
                                    <?php
                                    if ($member['member_type'] == 1) {
                                        echo "投资金额（元）";
                                    } else {
                                        echo "待还金额（元）";
                                    }
                                    ?>
                                    
                                </p>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
        <div class="member-main-box4">
            <h2 class="member-stais-tit my-touzi">资金记录</h2>
            <div class="member-stais-listmy ">
                <div class="con-prdpages member-con-tents">
                    <ul class="tabs clearfix">
                        <li class="pr-active"> </li>
                    </ul>
                    <div class="ordertop orderright">
                        <?= $this->render('_search', ['model' => $model]); ?>
                    </div>
                </div>
                <div class="tab_container mar15">
                    <?php  Pjax::begin();?>
                    <div id="tab1" class="tab_content ">
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider,
                            'options' => ['class' => 'tab-total-mains'],
                            /* 表格配置 */
                            'tableOptions' => ['style'=>'width:100%'],
                            /* 重新排版 摘要、表格、分页 */
                            'layout' => '{items}<div class="page-box"><div class="tdb-page clearfix"><div class="b-count">{pager}</div>{summary}</div></div>',                                        /* 配置摘要 */
//                                        'summaryOptions' => ['class' => 'pagination'],
                            /* 配置分页样式 */
                            'pager' => [
//                                            'options' => ['class'=>'pagination','style'=>'visibility: visible;'],
                                'nextPageLabel' => '下一页',
                                'prevPageLabel' => '上一页',
                                'firstPageLabel' => '首页',
                                'lastPageLabel' => '末页'
                            ],
                            /* 定义列表格式 */
                            'columns' => $columns,
                        ]); ?>
                    </div>
                    <?php  Pjax::end();?>

                </div>

            </div>

        </div>
    </div>

<?php
$this->registerJs("
$(function(){
$('ul.tabs li').unbind('click');
});
    var app = {};
    var myChart = echarts.init(document.getElementById('main'));
    var type = ".$member['member_type'].";
    var touzhi = money = 0;

    if(type == 1){
        touzhi = '投资金额';
        money = ".$member['fix_money'].";
    }else{
        touzhi = '待还金额';
        money = ".$member['dai_money'].";
    }

    option = {
        tooltip: {
            trigger: 'item',
            formatter: \"{a} <br/>{b}: {c} ({d}%)\"
        },
        legend: {
            orient: 'vertical',
            x: 'right',
            y:'center',
            data:['零钱余额',touzhi,'冻结金额']
        },
        series: [
            {
                name:'钱包',
                type:'pie',
                radius: ['50%', '70%'],
                avoidLabelOverlap: false,
                label: {
                    normal: {
                        show: false,
                        position: 'center'
                    },
                    emphasis: {
                        show: true,
                        textStyle: {
                            fontSize: '30',
                            fontWeight: 'bold'
                        }
                    }
                },
                labelLine: {
                    normal: {
                        show: false
                    }
                },
                data:[
                    {value:".$member['money'].", name:'零钱余额'},
                    {value:money, name:touzhi},
                    {value:".$member['lock_money'].", name:'冻结金额'},
                ]
            }
        ]
    };

    myChart.setOption(option);
", \yii\web\View::POS_END);
