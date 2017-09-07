<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\QfbAboutMe */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Qfb About Mes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qfb-about-me-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a(Yii::t('app',' 返回'), ['index'], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'position',
            'image',
            'content',
            [
                'attribute'=>'status',
                'value'=> $model->status==1 ?"启用": "禁用",

            ],
            // 'status',
            'sort',
        ],
    ]) ?>

</div>
