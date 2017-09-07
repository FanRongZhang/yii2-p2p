<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\enum\VouchersEnum;
use common\widgets\datepicker\DatePicker;

?>

<div class="search-form">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>


    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'type')->dropDownList(VouchersEnum::getRule(),['prompt'=>'全部']) ?>

    <?php  echo $form->field($model, 'create_time')->widget(DatePicker::className(),[
        'options'=>[
            'istime'=>true,
            'format'=>'YYYY-MM-DD',
            'readonly'=>true
        ]
    ]) ?>

    <?php  echo $form->field($model, 'create_time_end')->widget(DatePicker::className(),[
        'options'=>[
            'istime'=>true,
            'format'=>'YYYY-MM-DD',
            'readonly'=>true
        ]
    ])->label('至') ?>
<div class='clear'></div>
    

    <div class="form-group search-button">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
