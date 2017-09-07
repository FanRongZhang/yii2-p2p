<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\QfbChannel */
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
    <?= $form->field($model, 'ds_rate',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>
<div class="row">    
    <?= $form->field($model, 'df_money',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>
<div class="row">    
    <?= $form->field($model, 'in_status',['options'=>['class'=>'control-group span8']])->radioList(['0' => '否','1' => '是']) ?>
</div>
<div class="row">
    <?= $form->field($model, 'out_status',['options'=>['class'=>'control-group span8']])->radioList(['0' => '否','1' => '是']) ?>
</div>
    <div class="row">
        <?= $form->field($model, 'is_default',['options'=>['class'=>'control-group span8']])->radioList(['0' => '否','1' => '是']) ?>
    </div>
<div class="row">
    <?= $form->field($model, 'need_certification',['options'=>['class'=>'control-group span8']])->radioList(['0' => '否','1' => '是']) ?>
</div>
<div class="row">    
    <?= $form->field($model, 'sort',['options'=>['class'=>'control-group span8']])->textInput() ?>
</div>

<div class="row-btn">
    <div class="btn-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    
    </div>
    <?php ActiveForm::end(); ?>

</div>
