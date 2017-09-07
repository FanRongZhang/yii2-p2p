<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\QfbPtOrder */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Qfb Pt Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qfb-pt-order-view">

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
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'sn',
            'pt_number',
            'price',
            'is_check',
            'create_time:datetime',
            'complete_time:datetime',
            'sorts',
            'fee',
            'money',
            'bank_type',
            'out_type',
        ],
    ]) ?>

</div>
