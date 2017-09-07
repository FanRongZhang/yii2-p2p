<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use common\widgets\datepicker\DatePicker;
/* @var $this yii\web\View */
/* @var $model common\models\QfbMessage */
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

    

<div class="clear" style="margin-left: 50px">
    <div class="row">
        <?= $form->field($model, 'title',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    </div>
    <div class="row" style="margin-left: 5px">
        <?php $model['send_ob']=json_decode( $model['send_ob'],'array');echo $form->field($model, 'send_ob')->radioList(common\enum\ContentEnum::getMessageObject()) ?>
    </div>
    <div class="row">
        <?php
            $model['send_ob_value0']=json_decode( $model['send_ob_value0'],true);
            echo $form->field($model, 'send_ob_value0',['options' => ['style' => 'display: none;']])->checkboxList(common\enum\ContentEnum::getSendObject() )
        ?>
    </div>
    <div class="row">
        <?php
            echo $form->field($model, 'send_ob_value1',['options' => ['style' => 'display: none;']])->textarea(['rows' => 6])
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
    <script type="text/javascript">
        $(function() {
            $('#qfbmessage-send_ob_value0 input:first').click(function(){
                if($(this).is(':checked')) {
                    $('#qfbmessage-send_ob_value0 input').each(function(){
                        var v = $(this)[0];
                        v.checked = true;
                    });
                }else{
                    $('#qfbmessage-send_ob_value0 input').removeAttr("checked");
                }
            });

        });

        $(function(){
            $("#qfbmessage-send_ob input").click(function(){
                if($(this)[0].checked){
                    var i = $(this).val();
                    $(".field-qfbmessage-send_ob_value0").hide();
                    $(".field-qfbmessage-send_ob_value1").hide();
                    $(".field-qfbmessage-send_ob_value2").hide();
                    $(".field-qfbmessage-send_ob_value"+i).show();
                }
            });

            $("#qfbmessage-send_ob input").each(function(){
                if($(this)[0].checked){
                    var i = $(this).val();
                    $(".field-qfbmessage-send_ob_value"+i).show();
                }

            });
        });
    </script>
