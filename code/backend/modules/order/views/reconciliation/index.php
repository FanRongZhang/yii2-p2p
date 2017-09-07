<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\order\models\ReconciliationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '对账异常记录');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qfb-reconciliation-log-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php  //echo $this->render('_search', ['model' => $searchModel]); ?>
    <p></p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'pager'=>array(
            'firstPageLabel'=>'首页',
            'lastPageLabel'=>'尾页',
            'nextPageLabel'=>'下一页',
            'prevPageLabel'=>'前一页',
        ),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'ls_sn',
            'platform_money',
            'account_money',
            // 'type',
            [
                // 'label'=>'状态',
                'attribute'=>'type',
                'value'=>function($model)
                {
                    if ($model->type == 0){
                        return '提现';
                    }elseif($model->type == 1){
                        return '放款';
                    }elseif($model->type == 2){
                        return '充值';
                    }elseif($model->type == 3){
                        return '交易';
                    }
                }
            ],
            [
                'attribute'=>'create_time',
                'content'=>function($model){
                    return $model->create_time ? date('Y-m-d H:i',$model->create_time) : '--';
                }
            ],
            // 'create_time:datetime',
            // 'status',
            [
                // 'label'=>'状态',
                'attribute'=>'status',
                'value'=>function($model)
                {
                    if ($model->status == 0)
                    {
                        return '否';
                    }
                    else
                    {
                        return '是';
                    }
                }
            ],
            'remark:ntext',

            ['class' => 'yii\grid\ActionColumn',
                'template' => ' {delete}',
                'buttons' => [
                    'delete' => function ($url,$model) {
                        $options = [
                            'title' => Yii::t('yii', '删除'),
                            'aria-label' => Yii::t('yii', 'delete'),
                            'data-pjax' => '0',
                        ];
                   
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', '/order/reconciliation/delete?id='.$model->id, $options); 
                    }
                ],
            ],
        ],
    ]); ?>

</div>
