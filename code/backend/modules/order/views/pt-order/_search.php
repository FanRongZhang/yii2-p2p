<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\order\models\PtOrderSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="search-form">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>



    <?= $form->field($model, 'sn') ?>


    <?php // echo $form->field($model, 'create_time') ?>

    <?php // echo $form->field($model, 'complete_time') ?>

    <?php  echo $form->field($model, 'sorts')->dropDownList([0=>'其他',1=>'充值',2=>'提现'],['prompt'=>'全部']) ?>

    <?php // echo $form->field($model, 'fee') ?>

    <?php // echo $form->field($model, 'money') ?>

    <?php // echo $form->field($model, 'bank_type') ?>

    <?php // echo $form->field($model, 'out_type') ?>

    <div class="form-group search-button">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
