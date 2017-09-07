<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\GoodsBrand */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="system-maintenance-form container">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'msg')->textArea(['maxlength' => true]) ?>

    <?= $form->field($model, 'is_maintenance')->checkBox(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('提 交', ['class' => 'btn btn-primary'])?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


