<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\market\models\ExperienceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Qfb Experience Moneys');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', '添加体验金'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

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

            'id',
            'name',
            'type',
            'valid_days',
            'money',
            // 'use_members',
            // 'status',
            // 'create_time:datetime',
            // 'start_time:datetime',
            // 'end_time:datetime',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
