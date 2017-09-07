<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\enum\OrderEnum;
/* @var $this yii\web\View */
/* @var $model common\models\QfbErrorMsg */
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
    <?= $form->field($model, 'channel_id',['options'=>['class'=>'control-group span8']])->dropDownList(OrderEnum::getChannel(null)) ?>
</div>
<div class="row">    
    <?= $form->field($model, 'code',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>
<div class="row">    
    <?= $form->field($model, 'msg',['options'=>['class'=>'control-group span8']])->textArea(['rows' => 5,'cols' => 15]) ?>
</div>


<div class="row-btn">
    <div class="btn-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '编辑'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    
    </div>
    <?php ActiveForm::end(); ?>

</div>
