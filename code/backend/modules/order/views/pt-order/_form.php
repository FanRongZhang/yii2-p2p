<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\QfbPtOrder */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="create-form">

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
    <div class="row">
        <?= $form->field($model, 'pt_number',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true,'readonly'=>'readonly']) ?>
        <?= $form->field($model, 'price',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '充值') : Yii::t('app', '编辑'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
    
</div>
    <?php ActiveForm::end(); ?>

</div>

