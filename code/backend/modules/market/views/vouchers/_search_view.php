<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\enum\VouchersEnum;
use common\widgets\datepicker\DatePicker;

?>

<div class="search-form">

    <?php $form = ActiveForm::begin([
        'action' => ['view'],
        'method' => 'get',
    ]); ?>
    <?php  echo $form->field($model, 'vouchers_id',['options' => ['style' => 'display: none;']] )->hiddenInput()->label('') ?>
    <?php  echo $form->field($model, 'accout')->label('会员账号') ?>
    <?php  echo $form->field($model, 'realname')->label('会员姓名') ?>

    <?= $form->field($model, 'status')->dropDownList(['66'=>'全部',0=>'未使用',1=>'已使用',2=>'已失效']) ?>

    <?php  echo $form->field($model, 'receive_time')->widget(DatePicker::className(),[
        'options'=>[
            'istime'=>true,
            'format'=>'YYYY-MM-DD',
            'readonly'=>true
        ]
    ]) ?>

    <?php  echo $form->field($model, 'receive_time_end')->widget(DatePicker::className(),[
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
