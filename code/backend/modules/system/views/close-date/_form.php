<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\widgets\datepicker\DatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\QfbDayOff */
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
    <?= $form->field($model, 'time',['options'=>['class'=>'control-group span8']])->widget(DatePicker::className(),[
            'options'=>[
                'istime'=>true,
                'format'=>'YYYY-MM-DD',
                'readonly'=>true
            ]
        ])
    ?>
</div>


<div class="row-btn">
    <div class="btn-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '编辑'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    
    </div>
    <?php ActiveForm::end(); ?>

</div>
