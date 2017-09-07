<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\enum\OrderEnum;
?>

<div class="search-form">

    <?php
        $form = ActiveForm::begin([
            'action' => ['index?type='.$type],
            'method' => 'get',
        ]);
    ?>

    <?= $form->field($model, 'sn') ?>

    <?= $form->field($model, 'account') ?>
    <?= $form->field($model, 'username') ?>

    <?php if($type==1){?>
        <?= $form->field($model, 'mark')->dropDownList(OrderEnum::getMark(),['style'=>'display:inline-block;width:inherit;margin-right:-60px;']) ?>
        <?= $form->field($model, 'numbers')?>
    <?php }?>


<div class='clear'></div>
    <?=$form->field($model, 'complete_time')->widget(common\widgets\datepicker\DatePicker::className(),[
        'options'=>[
            'istime'=>true,
            'readonly'=>true,
            'format'=>'YYYY-MM-DD'
        ],
    ])
    ?>
    <?=$form->field($model, 'complete_time_end')->widget(common\widgets\datepicker\DatePicker::className(),[
        'options'=>[
            'istime'=>true,
            'readonly'=>true,
            'format'=>'YYYY-MM-DD',
        ]
    ])?>

    <div class="form-group search-button">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
