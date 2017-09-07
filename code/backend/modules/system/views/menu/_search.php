<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\system\models\MenuSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="search-form">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'name') ?>

    <?php // echo $form->field($model, 'url') ?>

    <?php // echo $form->field($model, 'permision_value') ?>

    <?php // echo $form->field($model, 'sorts') ?>

    <div class="form-group search-button">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
