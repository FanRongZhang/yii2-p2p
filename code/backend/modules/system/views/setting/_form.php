<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\QfbSystemSettings */
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
    <?= $form->field($model, 'min_money',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'money_fee',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'fast_rate',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>
<div class="row">
    <?= $form->field($model, 'slow_rate',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'per_money',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'day_money',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>
<div class="row">
    <?= $form->field($model, 'open_start_time',['options'=>['class'=>'control-group span8']])->textInput() ?>
    <?= $form->field($model, 'open_end_time',['options'=>['class'=>'control-group span8']])->textInput() ?>
    <?= $form->field($model, 'close_content',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
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
