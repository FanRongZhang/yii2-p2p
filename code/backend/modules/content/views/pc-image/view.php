<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\QfbPcImage */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Qfb Pc Images', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qfb-pc-image-view">

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
        <?= Html::a(Yii::t('app',' 返回'), ['index','type'=>$model->type], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'image',
            'url:url',
            'time:datetime',
            'sort',
              [
                    'label'=>'状态',
                    'attribute'=>'status',
                    'value'=>$model->status==1 ? '开启' : '禁用'
            ],
        ],
    ]) ?>

</div>
