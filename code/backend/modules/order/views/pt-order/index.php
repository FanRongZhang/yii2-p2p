<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\order\models\PtOrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '平台交易列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qfb-pt-order-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            'sn',
            'pt_number',
            'price',
            [
                'label'=>'状态',
                'attribute'=>'is_check',
                'value'=>function($data)
                {
                    if ($data->is_check==3) {
                        return '处理中';
                    } else if ($data->is_check==1) {
                        return '已完成';
                    } else {
                        return '失败';
                    }
                }
            ],
            [
                'label'=>'创建时间',
                'attribute'=>'create_time',
                'value'=>function($data)
                {
                    return date('Y-m-d H:i:s',$data->create_time);
                }
            ],
            [
                'label'=>'完成时间',
                'attribute'=>'complete_time',
                'value'=>function($data)
                {
                    if ($data->complete_time != 0) {
                        return date('Y-m-d H:i:s',$data->complete_time);
                    }   
                }
            ],
            [
                'label'=>'类型',
                'attribute'=>'sorts',
                'value'=>function($data)
                {
                    if ($data->sorts==1) {
                        return '充值';
                    } else if($data->sorts==2) {
                        return '提现';
                    } else {
                        return '其他';
                    }
                }
            ],
             //'fee',
             'money',
            // 'bank_type',
             //'out_type',

            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
