<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\QfbOperation */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Qfb Operations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qfb-operation-view">

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
        <?= Html::a(Yii::t('app','返回列表'), ['index'], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'phone',
            'time',
            'bottom',
             [
                    'label'=>'状态',
                    'attribute'=>'status',
                    'value'=>$model->status==1 ? '开启' : '禁用'
            ],
        ],
    ]) ?>

</div>
