<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use trntv\filekit\widget\Upload;
use yii\web\JsExpression;
use common\enum\ContentEnum;
/* @var $this yii\web\View */
/* @var $model common\models\Qfbshare */
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
        <?= $form->field($model, 'title',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
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

    <div class="row" style="margin-left: 5px">
            <?= $form->field($model, 'content')->widget(\yii\imperavi\Widget::className(),
                [
                    'plugins' => ['fullscreen', 'fontcolor', 'video','image'],
                    'options' =>
                        [
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

    <div class="row">
        <?= $form->field($model, 'url',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    </div>
    <div class="row">
        <?= $form->field($model, 'type',['options'=>['class'=>'control-group span8']])->dropDownList(ContentEnum::getShareType(),['prompt'=>'请选择']) ?>
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
