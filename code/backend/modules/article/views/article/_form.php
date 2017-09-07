<?php

use trntv\filekit\widget\Upload;
use kartik\datetime\DateTimePicker;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model common\models\Article */
/* @var $categories common\models\ArticleCategory[] */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="article-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?php
        echo $form->field($model, 'slug')
        ->hint('If you\'ll leave this field empty, slug will be generated automatically')
        ->textInput(['maxlength' => true])
    ?>

    <?php
        echo $form->field($model, 'category_id')->dropDownList(\yii\helpers\ArrayHelper::map(
            $categories,
            'id',
            'title'
        ), ['prompt'=>''])
    ?>

    <?php
        echo $form->field($model, 'body')->widget(
        \yii\imperavi\Widget::className(),
        [
            'plugins' => ['fullscreen', 'fontcolor', 'video'],
            'options' => [
                'minHeight' => 400,
                'maxHeight' => 400,
                'buttonSource' => true,
                'convertDivs' => false,
                'removeEmptyTags' => false,
                'imageUpload' => Yii::$app->urlManager->createUrl(['/article/article/upload-imperavi'])
            ]
        ]
        )
    ?>

    <?php
        echo $form->field($model, 'thumbnail')->widget(
        Upload::className(),
        [
            'url' => ['upload'],
            'acceptFileTypes' => new JsExpression('/(\.|\/)(gif|jpe?g|png)$/i'),
            'maxFileSize' => 5000000, // 5 MiB
        ]);
    ?>

    <?php
        echo $form->field($model, 'attachments')->widget(
        Upload::className(),
        [
            'url' => ['upload'],
            'sortable' => false,
            'maxFileSize' => 10000000, // 10 MiB
            'maxNumberOfFiles' => 10
        ]);
    ?>

    <?php echo $form->field($model, 'view')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'status')->checkbox() ?>

    <?php echo $form->field($model, 'published_at')->textInput() ?>

    <div class="form-group">
        <?php echo Html::submitButton(
            $model->isNewRecord ? 'Create' : 'Update',
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
