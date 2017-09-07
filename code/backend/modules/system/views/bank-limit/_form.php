<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\QfbBankLimit */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="update-form">

    <?php $form = ActiveForm::begin([
        'id' => 'member-form',
        'options' => ['class' => 'form-horizontal bui-form-horizontal bui-form bui-form-field-container'], 
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"controls\">{input}<span class=\"valid-text\">{error}</span></div>",
            'labelOptions' => ['class' => 'lable-text control-label'],
            'errorOptions'=>['class'=>'valid-text']
        ],
    ]); ?>

    
    
    
<div class="row">
    <?= $form->field($model, 'name',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>
<div class="row">    
    <?= $form->field($model, 'trade_num',['options'=>['class'=>'control-group span8']])->textInput() ?>
</div>
<div class="row">    
    <?= $form->field($model, 'one_trade',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>
<div class="row">    
    <?= $form->field($model, 'day_trade',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>
<div class="row">
    <?= $form->field($model, 'month_trade',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>
<div class="row">    
    <?= $form->field($model, 'iss_users',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>
<div class="row">    
    <?= $form->field($model, 'is_support',['options'=>['class'=>'control-group span8']])->radioList(['0' => '否','1' => '是']) ?>
</div>
<div class="row">
    <?= $form->field($model, 'bank_abbr',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>


<div class="row-btn">
    <div class="btn-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '编辑'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>   
    </div>
    <?php ActiveForm::end(); ?>

</div>
