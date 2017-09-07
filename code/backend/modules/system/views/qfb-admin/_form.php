<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\QfbAdmin */
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
    <?= $form->field($model, 'account',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'password',['options'=>['class'=>'control-group span8']])->passwordInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'enabled',['options'=>['class'=>'control-group span8']])->dropDownList([0=>'否',1=>'是'],['prompt'=>'请选择']) ?>
    <?= $form->field($model, 'is_sys',['options'=>['class'=>'control-group span8']])->dropDownList([0=>'否'],['prompt'=>'请选择']) ?>
</div>
<div class="row">
    <?php /*
        <?= $form->field($model, 'permission',['options'=>['class'=>'control-group span8']])->textarea(['rows' => 6]) ?>
    <?php */ ?>
    <?= $form->field($model, 'true_name',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>


<div class="row-btn">
    <div class="btn-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    
    </div>
     <div class="btn-group">
    <?= Html::a(Yii::t('app', '返回列表'), ['index'], ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
