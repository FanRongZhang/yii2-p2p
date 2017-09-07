<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\QfbPcImage */

$this->title = 'Update Qfb Pc Image: ' . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Qfb Pc Images', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="qfb-pc-image-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
