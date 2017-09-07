<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use trntv\filekit\widget\Upload;
use yii\web\JsExpression;
use common\widgets\datepicker\DatePicker; 
use common\enum\ContentEnum;
/* @var $this yii\web\View */
/* @var $model common\models\QfbBanner */
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

    <div class="row" style="margin-left:50px; ">
    <div class="row">
        <?= $form->field($model, 'name',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    </div>
    <div class="row">
        <?= $form->field($model, 'location_push',['options'=>['class'=>'control-group span8']])->dropDownList(ContentEnum::getLocationValue(),['prompt'=>'请选择']) ?>
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
        <?= $form->field($model, 'linkurl',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    </div>
    <div class="row">
        <?= $form->field($model, 'status',['options'=>['class'=>'control-group span8']])->dropDownList(ContentEnum::getActive()) ?>
    </div>

    <div class="row">
        <?= $form->field($model, 'display_start_time',['options'=>['class'=>'control-group span8']])->widget(DatePicker::className(),[
            'options'=>[
                'istime'=>true,
                'format'=>'YYYY-MM-DD',
                'readonly'=>true
            ]
        ])
        ?>
    </div>
    <div class="row">
        <?= $form->field($model, 'display_end_time',['options'=>['class'=>'control-group span8']])->widget(DatePicker::className(),[
            'options'=>[
                'istime'=>true,
                'format'=>'YYYY-MM-DD',
                'readonly'=>true
            ]
        ])
        ?>
    </div>

    <div class="row">
        <?= $form->field($model, 'type',['options'=>['class'=>'control-group span8']])->dropDownList(ContentEnum::getBannerStatus(),['prompt'=>'请选择']) ?>
    </div>
    <div class="row">
        <?= $form->field($model, 'share_type',['options'=>['class'=>'control-group span8']])->dropDownList(ContentEnum::getShareValue(),['prompt'=>'请选择']) ?>
    </div>
    <div class="row">
        <?= $form->field($model, 'sortord',['options'=>['class'=>'control-group span8']])->textInput() ?>
    </div>
</div>
<div class="row-btn">
    <div class="btn-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '保存') : Yii::t('app', '编辑'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
     <div class="btn-group">
    <?= Html::a(Yii::t('app', '返回'), ['index'], ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
