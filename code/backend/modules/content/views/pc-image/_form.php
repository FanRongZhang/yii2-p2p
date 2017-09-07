<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use trntv\filekit\widget\Upload;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $model common\models\QfbBanner */
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

    <div class="row" style="margin-left:50px; ">
    <div class="row">
        <?= $form->field($model, 'name',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    </div>
    
    <div class="row" style="margin-left:5px; ">
        <?php
        
        echo $form->field($model, 'thumbnail')->widget(
            Upload::className(),
            [
                'url' => ['upload'],
                'acceptFileTypes' => new JsExpression('/(\.|\/)(gif|jpe?g|png)$/i'),
                'maxFileSize' => 5000000, // 5 MiB
            ]);
        ?>
    </div>
   <div class="row">
        <?= $form->field($model, 'url',['options'=>['class'=>'control-group span8']])->textInput(['placeholder' => '无跳转请留空'])?>
    </div>
    <div class="row">
        <?= $form->field($model, 'sort',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true])?>
    </div>
    <div class="row">
        <?= $form->field($model, 'status',['options'=>['class'=>'control-group span8']])->dropDownList([1=>'启用',2=>'禁用'])?>
    </div>
</div>
<div class="row-btn">
    <div class="btn-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '保存') : Yii::t('app', '编辑'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
     <div class="btn-group">
    <?= Html::a(Yii::t('app', '返回'), ['index','type'=>$model->type], ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>

