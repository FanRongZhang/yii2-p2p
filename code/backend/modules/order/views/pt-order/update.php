<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\QfbPtOrder */

$this->title = 'Update Qfb Pt Order: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Qfb Pt Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="qfb-pt-order-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
