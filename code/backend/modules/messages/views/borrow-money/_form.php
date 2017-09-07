<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\QfbBorrowMoney */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="qfb-borrow-money-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'type')->textInput() ?>

    <?= $form->field($model, 'money')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sey')->textInput() ?>

    <?= $form->field($model, 'guarantee')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'purpose')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tel')->textInput() ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?= $form->field($model, 'time')->textInput() ?>

    <?= $form->field($model, 'reply_time')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
