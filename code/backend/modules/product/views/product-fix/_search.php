<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\enum\ProductEnum;
use yii\base\Widget;
?>

<div class="search-form">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'product_name') ?>

    <?= $form->field($model, 'product_type')->dropDownList(ProductEnum::getType()) ?>

    <?= $form->field($model, 'status')->dropDownList(ProductEnum::getStatus()) ?>

    <?= $form->field($model, 'profit_type')->dropDownList(ProductEnum::getProfitType()) ?>

<div class='clear'></div>
    <?=$form->field($model, 'create_time')->widget(common\widgets\datepicker\DatePicker::className(),[
            'options'=>[
                'istime'=>true,
                'readonly'=>true,
                'format'=>'YYYY-MM-DD'
            ]
    ])?>
    <?=$form->field($model, 'create_time_end')->widget(common\widgets\datepicker\DatePicker::className(),[
        'options'=>[
            'istime'=>true,
            'readonly'=>true,
            'format'=>'YYYY-MM-DD'
        ]
    ])?>

    <?=$form->field($model, 'end_time')->widget(common\widgets\datepicker\DatePicker::className(),[
            'options'=>[
                'istime'=>true,
                'readonly'=>true,
                'format'=>'YYYY-MM-DD'
            ]
    ])?>
    <?=$form->field($model, 'end_time_end')->widget(common\widgets\datepicker\DatePicker::className(),[
        'options'=>[
            'istime'=>true,
            'readonly'=>true,
            'format'=>'YYYY-MM-DD'
        ]
    ])?>

    <div class="form-group search-button">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
