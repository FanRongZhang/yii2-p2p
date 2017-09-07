<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="search-form">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>




    <?php  echo $form->field($model, 'vmobile') ?>

    <?php  echo $form->field($model, 'vaccount') ?>
    <?= $form->field($model, 'vstatus')->dropDownList(['' => '全部','1' => '正常','0' => '冻结']) ?>
    <?= $form->field($model, 'vis_dredge')->dropDownList(['' => '全部','1' => '已认证','0' => '未认证','9'=>'认证中']) ?>
    <?= $form->field($model, 'vlevel')->dropDownList(\common\enum\LevelEnum::getName()) ?>
    <?= $form->field($model, 'vsource')->dropDownList(\common\enum\MemberEnum::getName()) ?>
    <div class='clear'></div>
    <?=$form->field($model, 'vcreate_time')->widget(common\widgets\datepicker\DatePicker::className(),[
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
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
