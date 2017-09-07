<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

$this->title = 'Member';
$this->params['breadcrumbs'][] = $this->title;

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
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'options' => ['class' => 'grid-view table-scrollable'],
        /* 表格配置 */
        'tableOptions' => ['style'=>'width:100%'],
        /* 重新排版 摘要、表格、分页 */
        'layout' => '{items}<div class=""><div class="col-md-5 col-sm-5">{summary}</div><div class="col-md-7 col-sm-7"><div class="dataTables_paginate paging_bootstrap_full_number" style="text-align:right;">{pager}</div></div></div>',
        /* 配置摘要 */
        'summaryOptions' => ['class' => 'pagination'],
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
