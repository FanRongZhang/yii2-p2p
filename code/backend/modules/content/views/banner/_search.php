<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\widgets\datepicker\DatePicker;
use common\enum\ContentEnum;
?>

<div class="search-form">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'name') ?>

     <?=$form->field($model, 'location_push')->dropDownList(ContentEnum::getLocationValue(),['prompt'=>'全部']) ?>

    <?= $form->field($model, 'display_start_time',['options'=>['class'=>'control-group span8']])->widget(DatePicker::className(),[
        'options'=>[
            'istime'=>true,
            'format'=>'YYYY-MM-DD',
            'readonly'=>true
        ]
    ])
    ?>

    <?= $form->field($model, 'display_time_end',['options'=>['class'=>'control-group span8']])->widget(DatePicker::className(),[
        'options'=>[
            'istime'=>true,
            'format'=>'YYYY-MM-DD',
            'readonly'=>true
        ]
    ])
    ?>

    <div class="form-group search-button">
        <?= Html::submitButton(Yii::t('app', '检索'), ['class' => 'btn btn-sm btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-sm btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
