<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\order\models\ReconciliationSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="search-form">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'ls_sn') ?>

    <?= $form->field($model, 'platform_money') ?>

    <?= $form->field($model, 'account_money') ?>

    <?= $form->field($model, 'type') ?>

    <?php // echo $form->field($model, 'create_time') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'remark') ?>

    <div class="form-group search-button">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
