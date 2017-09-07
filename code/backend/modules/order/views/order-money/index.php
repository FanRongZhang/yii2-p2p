<?php

use yii\grid\GridView;
use kartik\export\ExportMenu;
/* @var $this yii\web\View */
/* @var $searchModel backend\modules\order\models\OrderMoneySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Qfb Orders');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">

    <?php echo $this->render('_search', ['model' => $searchModel,'type'=>$type]); ?>

    <p></p>
    <div class="index" style="margin: 5px">
    <?php
    echo ExportMenu::widget([
    'dataProvider' => $dataProvider,
        'pager'=>array(
            'firstPageLabel'=>'首页',
            'lastPageLabel'=>'尾页',
            'nextPageLabel'=>'下一页',
            'prevPageLabel'=>'前一页',
        ),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'sn',
            'member.account',
            'bank.username',
            'price',

            [
                'attribute'=>'bank_type',
                'content'=>function($model){
                    return \common\enum\OrderEnum::getChannel($model->bank_type);
                }
            ],

            [
                'attribute'=>'is_check',
                'content'=>function($model){
                    return \common\enum\OrderEnum::getIsCheck($model->is_check);
                }
            ],

            'bank.name',
            'bank.no',
            'bank_sn',
            [
                'attribute'=>'create_time',
                'content'=>function($model){
                    return $model->create_time ? date('Y-m-d H:i',$model->create_time) : '--';
                }
            ],
        ],
        'filename'=>'CZ'.date('Y-m-d',time())
    ]);
    ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'pager'=>array(
            'firstPageLabel'=>'首页',
            'lastPageLabel'=>'尾页',
            'nextPageLabel'=>'下一页',
            'prevPageLabel'=>'前一页',
        ),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'sn',
            'member.account',
            'bank.username',
            'price',

            [
                'attribute'=>'bank_type',
                'content'=>function($model){
                    return \common\enum\OrderEnum::getChannel($model->bank_type);
                }
            ],

            [
                'attribute'=>'is_check',
                'content'=>function($model){
                    return \common\enum\OrderEnum::getIsCheck($model->is_check);
                }
            ],

            'bank.name',
            'bank.no',
            'bank_sn',
            [
                'attribute'=>'create_time',
                'content'=>function($model){
                    return $model->create_time ? date('Y-m-d H:i',$model->create_time) : '--';
                }
            ],
        ],
    ]); ?>

</div>
