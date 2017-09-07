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
        <input type="text" id="ordersearch-start_time" class="openiw" name="OrderSearch[start_time]" readonly="readonly" onclick='laydate({"istime":true,"format":"YYYY-MM-DD","id":"ordersearch-start_time","elem":"#ordersearch-start_time"})'>
        <span class="openline">—</span>
        <input type="text" id="ordersearch-end_time" class="openiw" name="OrderSearch[end_time]" readonly="readonly" onclick='laydate({"istime":true,"format":"YYYY-MM-DD","id":"ordersearch-end_time","elem":"#ordersearch-end_time"})'>
    </div>

    <?= Html::submitButton('查询', ['class' => 'add-producter orderpostion fl']) ?>
</div>

<?php ActiveForm::end(); ?>
