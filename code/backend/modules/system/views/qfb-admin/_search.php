<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\base\Widget;
use common\widgets\datepicker\DatePicker;

?>

<div class="search-form">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>


    <?= $form->field($model, 'account') ?>

    <?= $form->field($model, 'enabled')->dropDownList([0=>'否',1=>'是'],['prompt'=>'全部']) ?>

    <?= $form->field($model, 'is_sys')->dropDownList([0=>'否',1=>'是'],['prompt'=>'全部']) ?>
    

    <?=$form->field($model, 'last_login')->widget(DatePicker::className(),[
        'options'=>[
            'istime'=>true,
            'readonly'=>true,
            'format'=>'YYYY-MM-DD'
        ]
    ])?>
    <?=$form->field($model, 'last_login_end')->widget(DatePicker::className(),[
        'options'=>[
            'istime'=>true,
            'readonly'=>true,
            'format'=>'YYYY-MM-DD'
        ]
    ])->label('至')?>

    <div class='clear'></div>
    <div class="form-group search-button">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
