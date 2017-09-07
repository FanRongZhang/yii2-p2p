<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\content\models\operationSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="search-form">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'phone') ?>

    <?= $form->field($model, 'time') ?>

    <?= $form->field($model, 'bottom') ?>

    <?php // echo $form->field($model, 'status') ?>

    <div class="form-group search-button">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
