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

     <?=$form->field($model, 'status')->dropDownList([1=>'启用',2=>'禁用'],['prompt'=>'全部']) ?>
     <?= Html::activeHiddenInput($model,'type',array('value'=>$model->type)) ?>

    <div class="form-group search-button">
        <?= Html::submitButton(Yii::t('app', '检索'), ['class' => 'btn btn-sm btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-sm btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
