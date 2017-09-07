<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="search-form">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'type') ?>

    <?= $form->field($model, 'valid_days') ?>

<div class='clear'></div>
    <?= $form->field($model, 'money') ?>

    <?php // echo $form->field($model, 'use_members') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'create_time') ?>

    <?php // echo $form->field($model, 'start_time') ?>

<!-- <div class='clear'></div> --> 
    <?php // echo $form->field($model, 'end_time') ?>

    <div class="form-group search-button">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
