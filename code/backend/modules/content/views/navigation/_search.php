<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\content\models\navigationSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="search-form">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'status')->dropDownList([''=>'全部',0=>'启用',1=>'禁用'])  ?>

    <div class="form-group search-button">
        <?= Html::submitButton('查询', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
