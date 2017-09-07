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
        <input type="text" id="investsearch-start_time" class="openiw" name="InvestSearch[start_time]" readonly="readonly" onclick='laydate({"istime":true,"format":"YYYY-MM-DD","id":"investsearch-start_time","elem":"#investsearch-start_time"})'>
        <span class="openline">—</span>
        <input type="text" id="investsearch-end_time" class="openiw" name="InvestSearch[end_time]" readonly="readonly" onclick='laydate({"istime":true,"format":"YYYY-MM-DD","id":"investsearch-end_time","elem":"#investsearch-end_time"})'>
    </div>
    <?= Html::submitButton('查询', ['class' => 'add-producter orderpostion fl']) ?>
</div>

<?php ActiveForm::end(); ?>
