<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', '解绑银行卡');
?>
<div class="create-form">

    <h1><?= Html::encode($this->title) ?></h1>

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
            <?= $form->field($model, 'mobile',['options'=>['class'=>'control-group span8']])->textInput() ?>
            <?= $form->field($model, 'no',['options'=>['class'=>'control-group span8']])->textInput() ?>
        </div>


        <div class="row-btn">
            <div class="btn-group">
                <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '提交解绑') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>

            </div>
            <?php ActiveForm::end(); ?>

        </div>

</div>
