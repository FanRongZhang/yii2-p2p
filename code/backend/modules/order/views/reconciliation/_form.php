<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\QfbReconciliationLog */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="qfb-reconciliation-log-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'ls_sn')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'platform_money')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'account_money')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->textInput() ?>

    <?= $form->field($model, 'create_time')->textInput() ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?= $form->field($model, 'remark')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
