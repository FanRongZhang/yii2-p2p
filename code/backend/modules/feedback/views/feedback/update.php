<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\DwalletFeedback */

$this->title = '回复: ' . ' ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => '回复', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="dwallet-feedback-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>