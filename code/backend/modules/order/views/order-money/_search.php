<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\enum\OrderEnum;
?>

<div class="search-form">

    <?php if($type==1){
        $form = ActiveForm::begin([
            'action' => ['index?type='.$type],
            'method' => 'get',
        ]);
    }else{
        $form = ActiveForm::begin([
            'action' => ['roll-out?type='.$type],
            'method' => 'get',
        ]);
    } ?>

    <?= $form->field($model, 'sn') ?>

    <?= $form->field($model, 'account') ?>
    <?= $form->field($model, 'username') ?>

    <?php if($type==1){?>
        <?= $form->field($model, 'mark')->dropDownList(OrderEnum::getMark(),['style'=>'display:inline-block;width:inherit;margin-right:-60px;']) ?>
        <?= $form->field($model, 'numbers')?>
    <?php }elseif($type==2){?>
        <?= $form->field($model, 'out_type')->dropDownList(OrderEnum::getOutType(null),['style'=>'display:inline-block;width:inherit;margin-right:-60px;']) ?>
    <?php }?>

    <div class='clear'></div>
    <?php if(\Yii::$app->request->get('type') != 2){?>
    <?= $form->field($model, 'bank_type')->dropDownList(OrderEnum::getChannel(null),['style'=>'display:inline-block;width:inherit;']) ?>
    <?php } ?>
    <?= $form->field($model, 'is_check')->dropDownList(OrderEnum::getIsCheck(null),['style'=>'display:inline-block;width:inherit;']) ?>

    <?=$form->field($model, 'create_time')->widget(common\widgets\datepicker\DatePicker::className(),[
        'options'=>[
            'istime'=>true,
            'readonly'=>true,
            'format'=>'YYYY-MM-DD'
        ],
    ])
    ?>
    <?=$form->field($model, 'create_time_end')->widget(common\widgets\datepicker\DatePicker::className(),[
        'options'=>[
            'istime'=>true,
            'readonly'=>true,
            'format'=>'YYYY-MM-DD',
        ]
    ])?>

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
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
