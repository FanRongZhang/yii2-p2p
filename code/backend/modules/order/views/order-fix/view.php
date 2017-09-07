<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\QfbProduct;
use common\models\QfbMember;
use common\models\QfbMemberInfo;
use common\enum\OrderEnum;
?>
<div class="detail-view">

    <p>
        <?= Html::a(Yii::t('app','返回列表'), ['index'], ['class' => 'btn btn-primary']) ?>       
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'template' => '<tr><th>{label}</th><td>{value}</td></tr>',
        'attributes' => [
            'id',

            'sn',

            ['attribute' => 'account','value' => !empty(QfbMember::findOne($model->member_id)) ? QfbMember::findOne($model->member_id)->account : ''],

            ['attribute' => 'realname','value' => !empty(QfbMemberInfo::find()->where(['=','member_id',$model->member_id])->one()) ? QfbMemberInfo::find()->where(['=','member_id',$model->member_id])->one()->realname : ''],

            ['attribute' => 'product_name','value' => !empty(QfbProduct::findOne($model->product_id)) ? QfbProduct::findOne($model->product_id)->product_name : ''],

            'money',

            'pay_money',

            ['attribute' => 'status','value' => OrderEnum::getRegular($model->status)],

            ['attribute' => 'create_time','value' => !empty($model->create_time) ? date("Y-m-d H:i:s",$model->create_time) : ''],

            ['attribute' => 'next_profit_time','value' => !empty($model->next_profit_time) ? date("Y-m-d H:i:s",$model->next_profit_time) : ''],

            ['attribute' => 'end_time','value' => !empty($model->end_time) ? date("Y-m-d H:i:s",$model->end_time) : ''],

            'year_rate',

            'number',

            ['attribute' => 'last_profit_time','value' => !empty($model->last_profit_time) ? date("Y-m-d H:i:s",$model->last_profit_time) : ''],

            'profit_money',
        ],
    ]) ?>

</div>
