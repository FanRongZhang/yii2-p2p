<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\content\models\aboutMeSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="search-form">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get', 
    ]); ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'status')->dropDownList([''=>'全部',1=>'启用',2=>'禁用'])  ?>

    <div class="form-group search-button">
        <?= Html::submitButton('检索', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
