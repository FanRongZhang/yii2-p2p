<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\system\models\Admin */
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
    <?= $form->field($model, 'enabled',['options'=>['class'=>'control-group span8']])->textInput() ?>
    <?= $form->field($model, 'is_sys',['options'=>['class'=>'control-group span8']])->textInput() ?>
</div>
<div class="row">
    <?= $form->field($model, 'create_time',['options'=>['class'=>'control-group span8']])->textInput() ?>
    <?= $form->field($model, 'last_login',['options'=>['class'=>'control-group span8']])->textInput() ?>
    <?= $form->field($model, 'last_ip',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'permission',['options'=>['class'=>'control-group span8']])->textarea(['rows' => 6]) ?>
</div>
<div class="row">
    <?= $form->field($model, 'true_name',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'department_id',['options'=>['class'=>'control-group span8']])->textInput() ?>
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
