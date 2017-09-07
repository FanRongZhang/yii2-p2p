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
        <input type="text" id="repaymentsearch-start_time" class="openiw" name="RepaymentSearch[start_time]" readonly="readonly" onclick='laydate({"istime":true,"format":"YYYY-MM-DD","id":"repaymentsearch-start_time","elem":"#repaymentsearch-start_time"})'>
        <span class="openline">—</span>
        <input type="text" id="repaymentsearch-end_time" class="openiw" name="RepaymentSearch[end_time]" readonly="readonly" onclick='laydate({"istime":true,"format":"YYYY-MM-DD","id":"repaymentsearch-end_time","elem":"#repaymentsearch-end_time"})'>
    </div>
    <?= Html::submitButton('查询', ['class' => 'add-producter orderpostion fl']) ?>
</div>
<?php ActiveForm::end(); ?>
