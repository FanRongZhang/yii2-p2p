<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\enum\OrderEnum;
use yii\base\Widget;
?>

<div class="search-form">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'sn') ?>

    <?= $form->field($model, 'account') ?>

    <?= $form->field($model, 'realname') ?>

    <?= $form->field($model, 'product_name') ?>

    <?= $form->field($model, 'mark')->dropDownList(OrderEnum::getMark(),['style'=>'display:inline-block;width:inherit;margin-right:-60px;']) ?>
    <?= $form->field($model, 'numbers')?>

<div class='clear'></div>

    <?= $form->field($model, 'status')->dropDownList(['' => '全部','1' => '已支付','2' => '收益中','3' => '已退款']) ?>

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


    <div class="form-group search-button">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
