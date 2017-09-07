<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\order\models\OrderCurrentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Qfb Orders');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">

    <?php echo $this->render('_search', ['model' => $searchModel,'type'=>$type]); ?>

    <p></p>

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
            'memberInfo.realname',
            'price',
            [
                'attribute'=>'complete_time',
                'content'=>function($model){
                    return $model->complete_time ? date('Y-m-d H:i',$model->complete_time) : '--';
                }
            ],
        ],
    ]); ?>

</div>
