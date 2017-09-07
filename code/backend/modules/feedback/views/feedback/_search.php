<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\widgets\datepicker\DatePicker;
?>

<div class="search-form">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>


    <?= $form->field($model, 'member_id') ?>

    <?= $form->field($model, 'reply')->dropDownList([''=>'全部','0'=>'未回复','1'=>'已回复']) ?>

    <?= $form->field($model,'create_time')->widget(DatePicker::className(),[
        'options'=>[
            'istime'=>true,
            'format'=>'YYYY-MM-DD',
            'readonly'=>true
        ]
    ])
    ?>

    <?= $form->field($model,'create_time_end')->widget(DatePicker::className(),[
        'options'=>[
            'istime'=>true,
            'format'=>'YYYY-MM-DD',
            'readonly'=>true
        ]
    ])
    ?>

    <div class="form-group search-button">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
