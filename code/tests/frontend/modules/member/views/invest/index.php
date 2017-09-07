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
        'header'  => '预期年化利率',
        'attribute' => 'year_rate',
        'content' => function($model){
            return ($model->year_rate/100).'%';
        },
    ],
    [
        'header'  => '期限（天）',
        'attribute' => 'time',
        'content' => function($model){
            return ceil(($model->end_time-$model->start_time)/(24*60*60));
        },
    ],
    [
        'header'  => '投资金额（元）',
        'attribute' => 'money',
    ],
    [
        'header'  => '预期收益（元）',
        'content' => function($model){
            if($model->profit_day == 10){
                $profitDay = ceil(($model->finish_time-$model->create_time)/(24*3600))+$model->invest_day;
            }elseif($model->profit_day == 11){
                $profitDay = ceil(($model->finish_time-$model->create_time)/(24*3600))+$model->invest_day-1;
            }elseif($model->profit_day == 20){
                $profitDay = $model->invest_day;
            }else{
                $profitDay = $model->invest_day-1;
            }
            $money = $model->money*($model->year_rate/100)*$profitDay/\common\service\OrderFixService::yearDay();
            return sprintf("%.2f",substr(sprintf("%.3f", $money), 0, -1));
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
        'header'  => '加入时间',
        'attribute' => 'order_create_time',
        'content'  => function($model){
            return date('Y-m-d H:i:s', $model->order_create_time);
        }
    ],
    [
        'header'  => '结束时间',
        'attribute' => 'end_time',
        'content'  => function($model){
            return date('Y-m-d H:i:s', $model->end_time);
        }
    ],
    [
        'header'  => '状态',
        'attribute' => 'status',
        'content' => function($model){
            if($model->status == 1){
                $check =  '募集中';
            }elseif($model->status == 2){
                $check =  '售罄';
            }elseif($model->status == 3){
                $check =  '流标';
            }elseif($model->status == 4){
                $check =  '关闭';
            }else{
                $check =  '已创建';
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
                            <li class="memb-acitves"><a href="#">我的投资</a></li>
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
                                                <input type="text" id="investsearch-start_time" class="openiw" name="InvestSearch[start_time]" readonly="readonly" onclick='laydate({"istime":true,"format":"YYYY-MM-DD","id":"investsearch-start_time","elem":"#investsearch-start_time"})'>
                                                <span class="openline">—</span>
                                                <input type="text" id="investsearch-end_time" class="openiw" name="InvestSearch[end_time]" readonly="readonly" onclick='laydate({"istime":true,"format":"YYYY-MM-DD","id":"investsearch-end_time","elem":"#investsearch-end_time"})'>
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