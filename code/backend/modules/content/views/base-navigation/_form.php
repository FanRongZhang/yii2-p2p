<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\modules\content\controllers\BaseNavigationController;

/* @var $this yii\web\View */
/* @var $model common\models\QfbBaseNavigation */
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
        <?= $form->field($model, 'pid',['options'=>['class'=>'control-group span8']])->dropDownList(BaseNavigationController::findTop()) ?> 
    </div>

    <div class="row">
        <?= $form->field($model, 'url',['options'=>['class'=>'control-group span8']])->textInput(['placeholder' => '填写内部文章ID或外链']) ?>
    </div>

    <div class="row">
        <?= $form->field($model, 'status',['options'=>['class'=>'control-group span8']])->radioList(['0'=>'启用','1'=>'禁用']) ?>
    </div>
    <div class="row">
        <?= $form->field($model, 'sort',['options'=>['class'=>'control-group span8']])->textInput() ?>
    </div>
</div>
<div class="row-btn" style="margin-left:180px; ">
    <div class="btn-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '保存') : Yii::t('app', '编辑'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
     <div class="btn-group">
    <?= Html::a(Yii::t('app', '返回'), ['index'], ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
