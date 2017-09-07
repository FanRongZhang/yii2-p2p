<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Admins');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">


    <p>
        <?= Html::a(Yii::t('app', 'Create Admin'), ['create'], ['class' => 'btn btn-success']) ?>
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
            'account',
            'password',
            'enabled',
            'is_sys',
            // 'create_time:datetime',
            // 'last_login',
            // 'last_ip',
            // 'permission:ntext',
            // 'true_name',
            // 'department_id',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
