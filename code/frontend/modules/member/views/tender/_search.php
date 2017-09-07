<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
?>



<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options'=>[
        //'class'=>"form-inline",
        'data-pjax' => true, //开启pjax搜索
    ]
]); ?>

<span class="opennamer">时间：</span>
<div class="openinputer clearfix">
    <div class="fl">
        <input type="text" id="tendersearch-start_time" class="openiw" name="TenderSearch[start_time]" readonly="readonly" onclick='laydate({"istime":true,"format":"YYYY-MM-DD","id":"tendersearch-start_time","elem":"#tendersearch-start_time"})'>
        <span class="openline">—</span>
        <input type="text" id="tendersearch-end_time" class="openiw" name="TenderSearch[end_time]" readonly="readonly" onclick='laydate({"istime":true,"format":"YYYY-MM-DD","id":"tendersearch-end_time","elem":"#tendersearch-end_time"})'>
    </div>
    <?= Html::submitButton('查询', ['class' => 'add-producter orderpostion fl']) ?>
</div>

<?php ActiveForm::end(); ?>
