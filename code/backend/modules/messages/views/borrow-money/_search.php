<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class="search-form">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'name') ?>

    <?php  echo $form->field($model, 'tel') ?>

     <?=$form->field($model, 'status')->dropDownList([1=>'未回访',2=>'已回访'],['prompt'=>'全部']) ?>

    <div class="form-group search-button">
        <?= Html::submitButton(Yii::t('app', '检索'), ['class' => 'btn btn-sm btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-sm btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

