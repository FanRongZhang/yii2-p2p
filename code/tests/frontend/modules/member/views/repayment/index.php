<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

$this->title = 'Member';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/datepicker/laydate.js');

$columns = [
    [
        'header'  => '标的名称',
        'attribute' => 'product_name',
    ],
    [
        'header'  => '还款状态',
        'attribute' => 'status',
        'content' => function($model){
            if($model->status == 8){
                $check =  '确认已还款';
            }elseif($model->status == 7){
                $check =  '已还款,待确认';
            }else{
                $check =  '未还款';
            }

            return $check;
        },
    ],
    [
        'header'  => '还款方式',
        'attribute' => 'profit_type',
        'content' => function($model){
            if($model->profit_type == 1){
                $profit = '到期还本付息';
            }elseif($model->profit_type == 2){
                $profit = '按月等额付息，到期还本';
            }elseif($model->profit_type == 3){
                $profit = '按日等额付息，到期还本';
            }elseif($model->profit_type == 4){
                $profit = '按月等额本息';
            }elseif($model->profit_type == 5){
                $profit = '按日等额本息';
            }elseif($model->profit_type == 6){
                $profit = '按月等额还本，到期付息';
            }elseif($model->profit_type == 7){
                $profit = '按日等额还本，到期付息';
            }

            return $profit;
        },
    ],
    [
        'header'  => '还款金额（元）',
        'content' => function($model){
            return $model->stock_money*(1+$model->year_rate/100);
        }
    ],
    [
        'header'  => '还款截止日期',
        'attribute' => 'repayment_end_time',
        'content'  => function($model){
            return date('Y-m-d H:i:s', $model->start_time+$model->invest_day*24*60*60);
        }
    ],
    [
        'header'  => '状态',
        'attribute' => 'status',
        'content' => function($model){
            if($model->status == 8){
                $check =  '成功';
            }else{
                $check =  '';
            }

            return $check;
        },
    ],
    [
        'class' => 'yii\grid\ActionColumn',
        'header' => '操作',
        'template' => ' {edit} ',
        //'options' => ['width' => '200px;'],
        'buttons' => [
            'edit' => function ($url, $model, $key) {
                return Html::a('<i class="fa fa-edit"></i> 还款', Url::to(['money/repayment']), [
                    'title' => Yii::t('app', '还款'),
                    'class' => 'btn btn-xs yellow'
                ]);
            },

        ],
    ],
];
?>
<div class="main-cloum">
    <div class="q-wide2">
        <div class="member-content clearfix">
            <div class="fl memberleft">
                <div class="con-member">
                    <h2 class="con-tit-mem"><i class="officicon"></i>我的账户</h2>
                    <div class="con-mem-list">
                        <ul>
                            <li><a href="#">账户总览</a></li>
                            <li><a href="#">基本信息</a></li>
                            <li><a href="#">消息（1）</a></li>
                        </ul>
                    </div>
                </div>
                <div class="con-member">
                    <h2 class="con-tit-mem con-tit-mem2"><i class="officicon"></i>资金</h2>
                    <div class="con-mem-list">
                        <ul>
                            <li><a href="#">充值</a></li>
                            <li><a href="#">提现</a></li>
                            <li><a href="#">借款</a></li>
                            <li class="memb-acitves"><a href="#">还款</a></li>
                        </ul>
                    </div>
                </div>

            </div>
            <div class="fr memberright">
                <div class="member-main-box1">
                    <h2 class="member-total-tit">我的投资</h2>
                    <?php $form = ActiveForm::begin([
                        'action' => ['index'],
                        'method' => 'post',
                        'options'=>[
                            //'class'=>"form-inline",
                            'data-pjax' => true, //开启pjax搜索
                        ]
                    ]); ?>
                    <div class="member-tment-main">
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
                                            <input type="text" id="repaymentsearch-start_time" class="openiw" name="RepaymentSearch[start_time]" readonly="readonly" onclick='laydate({"istime":true,"format":"YYYY-MM-DD","id":"repaymentsearch-start_time","elem":"#repaymentsearch-start_time"})'>
                                            <span class="openline">—</span>
                                            <input type="text" id="repaymentsearch-end_time" class="openiw" name="RepaymentSearch[end_time]" readonly="readonly" onclick='laydate({"istime":true,"format":"YYYY-MM-DD","id":"repaymentsearch-end_time","elem":"#repaymentsearch-end_time"})'>
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
                                        'layout' => '{items}<div class="page-box con-pagets paterts"><div class="tdb-page clearfix">{summary}<div id="b-count" class="fl">{pager}</div></div></div>',
                                        /* 配置摘要 */
//                                        'summaryOptions' => ['class' => 'pagination'],
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

                                </div>
                                <div id="tab2" class="tab_content ">
                                    <?= GridView::widget([
                                        'dataProvider' => $dataProvider,
                                        'options' => ['class' => 'tab-total-mains'],
                                        /* 表格配置 */
                                        'tableOptions' => ['style'=>'width:100%'],
                                        /* 重新排版 摘要、表格、分页 */
                                        'layout' => '{items}<div class="page-box con-pagets paterts"><div class="tdb-page clearfix">{summary}<div id="b-count" class="fl">{pager}</div></div></div>',
                                        /* 配置摘要 */
//                                        'summaryOptions' => ['class' => 'pagination'],
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

                                </div>
                                <div id="tab3" class="tab_content ">
                                    <?= GridView::widget([
                                        'dataProvider' => $dataProvider,
                                        'options' => ['class' => 'tab-total-mains'],
                                        /* 表格配置 */
                                        'tableOptions' => ['style'=>'width:100%'],
                                        /* 重新排版 摘要、表格、分页 */
                                        'layout' => '{items}<div class="page-box con-pagets paterts"><div class="tdb-page clearfix">{summary}<div id="b-count" class="fl">{pager}</div></div></div>',
                                        /* 配置摘要 */
//                                        'summaryOptions' => ['class' => 'pagination'],
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

                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>