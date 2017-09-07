<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use common\widgets\datepicker\DatePicker;
use common\enum\ContentEnum;
/* @var $this yii\web\View */
/* @var $model common\models\QfbNotice */
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

    
    
<div class="row" style="margin-left: 40px">
    <div class="row">
        <?= $form->field($model, 'title',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    </div>
    <div class="row">
        <?= $form->field($model, 'summary',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    </div>

    <div class="row" style="margin-left: 5px">
        <?= $form->field($model, 'content')->widget(\yii\imperavi\Widget::className(),
            [
                'plugins' => ['fullscreen','fontsize', 'fontcolor', 'video','image'],
                'options' =>
                    [
                        'minHeight' => 400,
                        'maxHeight' => 400,
                        'buttonSource' => false,
                        'convertDivs' => false,
                        'removeEmptyTags' => false,
                        'imageUpload' => Url::to(['upload-imperavi'])
                    ]
            ]
        );
        ?>
    </div>
    <div class="row">
        <?= $form->field($model, 'show_end_time',['options'=>['class'=>'control-group span8']])->widget(DatePicker::className(),[
            'options'=>[
                'istime'=>true,
                'format'=>'YYYY-MM-DD',
                'readonly'=>true
            ]
        ])
        ?>
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
