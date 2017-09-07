<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use common\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $model common\models\QfbAgreement */
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

    <div class="row cl">
            <?=$form->field($model, 'parent_id')->dropDownList(
                ArrayHelper::merge(['0'=>'一级栏目'],ArrayHelper::listDataLevel( \common\models\Menu::find()->asArray()->all(), 'id', 'name','id','parent_id')), ['class'=>'form-control select2','widthclass'=>'c-md-2'])->label('上级菜单')->hint('上级菜单描述') ?>
    </div>

    <div class="row cl">
        <?= $form->field($model, 'name',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    </div>

    <div class="row">
        <?= $form->field($model, 'display',['options'=>['class'=>'control-group span8']])->dropDownList(['0' => '不显示','1' => '显示']) ?>
    </div>

    <div class="row">
        <?= $form->field($model, 'level',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    </div>

    <div class="row">
        <?= $form->field($model, 'url',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    </div>

    <div class="row">
        <?= $form->field($model, 'permision_value',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    </div>

    <div class="row">
        <?= $form->field($model, 'sorts',['options'=>['class'=>'control-group span8']])->textInput(['maxlength' => true]) ?>
    </div>

    <div class="row-btn">
        <div class="btn-group">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        
        </div>
        <?php ActiveForm::end(); ?>

    </div>
</div>