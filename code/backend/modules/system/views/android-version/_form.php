<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use trntv\filekit\widget\Upload;
use yii\helpers\Url;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $model common\models\QfbVersion */
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
    <?php
        echo $form->field($model, 'url_file')->widget(
        Upload::className(),
        [
            'url' => ['upload'],
            'acceptFileTypes' => new JsExpression('/(\.|\/)(apk)$/i'),
            'maxFileSize' => 50000000, // 50 MiB
        ]);
    ?>   
</div>        
<div class="row">
    <?= $form->field($model, 'ver_code',['options'=>['class'=>'control-group span8']])->textInput() ?>
</div>
<div class="row">
    <?= $form->field($model, 'ver_name',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
</div>
<div class="row">    
    <?= $form->field($model, 'is_force',['options'=>['class'=>'control-group span8']])->dropDownList(['0' => '建议更新','1' => '强制更新']) ?>
</div>
<div class="row">
    <?= $form->field($model, 'channel',['options'=>['class'=>'control-group span8']])->radioList(['0' => '否','1' => '是']) ?>
</div>
<div class="row">  
    <?= $form->field($model, 'content',['options'=>['class'=>'control-group span8']])->textarea(['rows' => 6]) ?>
</div>
<div class="row">
    <?php
        echo $form->field($model, 'imprint')->widget(
            \yii\imperavi\Widget::className(),
            [
                'plugins' => ['fullscreen', 'fontcolor', 'video'],
                'options' => [
                    'minHeight' => 400,
                    'maxHeight' => 400,
                    'buttonSource' => true,
                    'convertDivs' => false,
                    'removeEmptyTags' => false,
                    'imageUpload' => Url::to(['upload-imperavi'])
                ]
            ]
        ); 
    ?>
</div>


<div class="row-btn">
    <div class="btn-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '编辑'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>   
    </div>
    <?php ActiveForm::end(); ?>

</div>
