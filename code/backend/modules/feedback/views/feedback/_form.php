<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\QfbFeedback */
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

    
    
    
    <?= $form->field($model, 'title')->textInput(['style' => 'width:185px']) ?>

    <?= $form->field($model, 'content')->textarea(['rows' => 6,'cols' => 120,'disabled'=>'disabled']) ?>

    <?= $form->field($model, 'reply_content')->textarea(['rows' => 6,'cols' => 120]) ?>



<div class="row-btn">
    <div class="btn-group">
        <?= Html::submitButton($model->isNewRecord ? '保存' : '回复', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>  
    </div>
    <div class="btn-group">
    <?= Html::a(Yii::t('app', 'Goback list'), ['index'], ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
