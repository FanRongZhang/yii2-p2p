<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use common\widgets\datepicker\DatePicker;

$this->title = 'Member';
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
        'header'  => '类型',
        'attribute' => 'sorts',
        'content' => function($model){
            if($model->sorts == 1){
                $sorts = '充值';
            }elseif($model->sorts == 2){
                $sorts = '提现';
            }elseif($model->sorts == 3){
                $sorts =  '投标';
            }else{
                $sorts =  '其他';
            }

            return $sorts;
        },
    ],
    [
        'header'  => '订单号',
        'attribute' => 'sn',
    ],
    [
        'header'  => '金额',
        'attribute' => 'money',
    ],
    [
        'header'  => '状态',
        'attribute' => 'is_check',
        'content' => function($model){
            if($model->is_check == 1){
                $check =  '已支付';
            }elseif($model->is_check == 2){
                $check =  '失败';
            }elseif($model->is_check == 3){
                $check =  '处理中';
            }elseif($model->is_check == 4){
                $check =  '无此交易';
            }elseif($model->is_check == 5){
                $check =  '通过审核';
            }else{
                $check =  '待支付';
            }

            return $check;
        },
    ]
    ];
?>
<!-- content product begin-->
<div class="main-cloum">
    <div class="q-wide2">
        <div class="member-content clearfix">
            <div class="fl memberleft">
                <div class="con-member">
                    <h2 class="con-tit-mem"><i class="officicon"></i>我的账户</h2>
                    <div class="con-mem-list">
                        <ul>
                            <li class="memb-acitves"><a href="<?php echo Url::to(['member/index']);?>">账户总览</a></li>
                            <li><a href="<?php echo Url::to(['member-info/index']);?>">基本信息</a></li>
                            <li><a href="<?php echo Url::to(['message/index']);?>">消息（1）</a></li>
                        </ul>
                    </div>
                </div>
                <div class="con-member">
                    <h2 class="con-tit-mem con-tit-mem2"><i class="officicon"></i>资金</h2>
                    <div class="con-mem-list">
                        <ul>
                            <li><a href="<?php echo Url::to(['money/recharge']);?>">充值</a></li>
                            <li><a href="<?php echo Url::to(['money/withdraw', 'type'=>1]);?>">提现</a></li>
                            <li><a href="<?php echo Url::to(['invest/index']);?>">我的投资</a></li>
                        </ul>
                    </div>
                </div>

            </div>
            <div class="fr memberright">
                <div class="member-main-box1">
                    <h2 class="member-total-tit">账户总览</h2>
                    <div class="clearfix member-photo-name">
                        <div class="fl member-photo-img"><img src="<?php echo $member['avatar'];?>"></div>
                        <div class="fl member-name-ip">
                            <ul>
                                <li>手机号码：<span class="gray3"><?php echo $member['mobile'];?></span></li>
                                <li>姓名：<?php
                                    if($member['is_dredge'] == 1){
                                        echo '已认证';
                                    }elseif($member['is_dredge'] == 9){
                                        echo '<a href="'.Url::to(['auth/index']).'" class="blue1">认证异常</a>';
                                    }else{
                                        echo '<a href="'.Url::to(['auth/index']).'" class="blue1">未认证</a>';
                                    }
                                    ?>
                                </li>
                                <li>上次登录：<?php echo $member['last_access_time'];?> 广东 深圳 <?php echo $member['last_ip'];?><br><span class="red1 f12">不在常用地址登录，建议修改登录密码</span></li>
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
                                <p class="red1 f20"><?php echo $member['all_profit'];?></p>
                                <p class="f14 black1">累计收益（元）</p>
                            </li>
                            <li>
                                <p class="red1 f20"><?php echo $member['yesterday_profit'];?></p>
                                <p class="f14 black1">昨日收益（元）</p>
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
                                        <p class="f20 black1"><?php echo $member['fix_money']?></p>
                                        <p class="f14 gray3">投资金额（元）</p>
                                    </li>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="member-main-box4">
                    <h2 class="member-stais-tit my-touzi">我的投资</h2>
                    <?php $form = ActiveForm::begin([
                        'action' => ['index'],
                        'method' => 'post',
                        'options'=>[
                            //'class'=>"form-inline",
                            'data-pjax' => true, //开启pjax搜索
                        ]
                    ]); ?>
                    <div class="member-stais-listmy ">
                        <div class="con-prdpages member-con-tents">
                            <ul class="tabs clearfix">
                                <li class="pr-active"><a href="#tab1">全部</a> </li>
                                <li><a href="#tab2">参与中</a></li>
                                <li><a href="#tab3">已结束</a></li>
                            </ul>
                            <div class="ordertop orderright">
                                <span class="opennamer">时间：</span>
                                <div class="openinputer clearfix">
                                    <div class="fl">
                                        <input type="text" id="ordersearch-start_time" class="openiw" name="OrderSearch[start_time]" readonly="readonly" onclick='laydate({"istime":true,"format":"YYYY-MM-DD","id":"ordersearch-start_time","elem":"#ordersearch-start_time"})'>
                                        <span class="openline">—</span>
                                        <input type="text" id="ordersearch-end_time" class="openiw" name="OrderSearch[end_time]" readonly="readonly" onclick='laydate({"istime":true,"format":"YYYY-MM-DD","id":"ordersearch-end_time","elem":"#ordersearch-end_time"})'>
                                    </div>

                                    <?= Html::submitButton('查询', ['class' => 'add-producter orderpostion fl']) ?>
                                </div>
                            </div>
                        </div>
                        <div class="tab_container mar15">
                            <div id="tab1" class="tab_content ">
                                    <?= GridView::widget([
                                        'dataProvider' => $dataProvider,
                                        'options' => ['class' => 'tab-total-mains'],
                                        /* 表格配置 */
                                        'tableOptions' => ['style'=>'width:100%'],
                                        /* 重新排版 摘要、表格、分页 */
                                        'layout' => '{items}<div class="page-box con-pagets paterts"><div class="tdb-page clearfix">{pager}<div class="col-md-7 col-sm-7"><div id="b-count" class="fl">{summary}</div></div></div></div>',                                        /* 配置摘要 */
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
                            <div id="tab2" class="tab_content clearfix">
                                <?= GridView::widget([
                                    'dataProvider' => $dataProvider,
                                    'options' => ['class' => 'tab-total-mains'],
                                    /* 表格配置 */
                                    'tableOptions' => ['style'=>'width:100%'],
                                    /* 重新排版 摘要、表格、分页 */
                                    'layout' => '{items}<div class="page-box con-pagets paterts"><div class="tdb-page clearfix">{pager}<div class="col-md-7 col-sm-7"><div id="b-count" class="fl">{summary}</div></div></div></div>',                                        /* 配置摘要 */
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
                            <div id="tab3" class="tab_content">
                                <?= GridView::widget([
                                    'dataProvider' => $dataProvider,
                                    'options' => ['class' => 'tab-total-mains'],
                                    /* 表格配置 */
                                    'tableOptions' => ['style'=>'width:100%'],
                                    /* 重新排版 摘要、表格、分页 */
                                    'layout' => '{items}<div class="page-box con-pagets paterts"><div class="tdb-page clearfix">{pager}<div class="col-md-7 col-sm-7"><div id="b-count" class="fl">{summary}</div></div></div></div>',                                        /* 配置摘要 */
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
                    <?php ActiveForm::end(); ?>
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<?php
$this->registerJs("
    var app = {};
    var myChart = echarts.init(document.getElementById('main'));

    option = {
        tooltip: {
            trigger: 'item',
            formatter: \"{a} <br/>{b}: {c} ({d}%)\"
        },
        legend: {
            orient: 'vertical',
            x: 'right',
            y:'center',
            data:['零钱余额','投资金额','冻结金额']
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
                    {value:".$member['fix_money'].", name:'投资金额'},
                    {value:".$member['lock_money'].", name:'冻结金额'},
                ]
            }
        ]
    };
    app.currentIndex = -1;

    setInterval(function () {
        var dataLen = option.series[0].data.length;
        // 取消之前高亮的图形
        myChart.dispatchAction({
            type: 'downplay',
            seriesIndex: 0,
            dataIndex: app.currentIndex
        });
        app.currentIndex = (app.currentIndex + 1) % dataLen;
        // 高亮当前图形
        myChart.dispatchAction({
            type: 'highlight',
            seriesIndex: 0,
            dataIndex: app.currentIndex
        });
        // 显示 tooltip
        myChart.dispatchAction({
            type: 'showTip',
            seriesIndex: 0,
            dataIndex: app.currentIndex
        });
    }, 1000);

    myChart.setOption(option);
", \yii\web\View::POS_END);
