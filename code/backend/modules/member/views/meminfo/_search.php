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

    <?= $form->field($model, 'level') ?>

    <?= $form->field($model, 'relations') ?>

    <?= $form->field($model, 'r_member_id') ?>

<div class='clear'></div>
    <?= $form->field($model, 'layer') ?>

    <?php // echo $form->field($model, 'mobile') ?>

    <?php // echo $form->field($model, 'account') ?>

    <?php // echo $form->field($model, 'access_token') ?>

    <?php // echo $form->field($model, 'channel_id') ?>

<!-- <div class='clear'></div> --> 
    <?php // echo $form->field($model, 'last_access_time') ?>

    <?php // echo $form->field($model, 'imei') ?>

    <?php // echo $form->field($model, 'zf_pwd') ?>

    <?php // echo $form->field($model, 'last_ip') ?>

    <?php // echo $form->field($model, 'operator') ?>

<!-- <div class='clear'></div> --> 
    <?php // echo $form->field($model, 'experience') ?>

    <?php // echo $form->field($model, 'is_newer') ?>

    <div class="form-group search-button">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
