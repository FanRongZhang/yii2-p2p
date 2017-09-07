<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\QfbMember */
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
    <?= $form->field($model, 'level',['options'=>['class'=>'control-group span8']])->textInput() ?>
    <?= $form->field($model, 'relations',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'r_member_id',['options'=>['class'=>'control-group span8']])->textInput() ?>
    <?= $form->field($model, 'layer',['options'=>['class'=>'control-group span8']])->textInput() ?>
</div>
<div class="row">
    <?= $form->field($model, 'mobile',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'account',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'access_token',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'channel_id',['options'=>['class'=>'control-group span8']])->textInput() ?>
</div>
<div class="row">
    <?= $form->field($model, 'last_access_time',['options'=>['class'=>'control-group span8']])->textInput() ?>
    <?= $form->field($model, 'imei',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'is_newer',['options'=>['class'=>'control-group span8']])->textInput() ?>
</div>


<div class="row-btn">
    <div class="btn-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    
    </div>
     <div class="btn-group">
    <?= Html::a(Yii::t('app', 'Goback list'), ['index'], ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
