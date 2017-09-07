<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\enum\LevelEnum;
/* @var $this yii\web\View */
/* @var $searchModel backend\modules\member\models\Membersearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '会员管理');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">



    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'product.product_name',
            'sn' ,
            'money',
            'pay_money',
            [
                'attribute'=>'status',
                //订单状态1.投资中2.收益中3.已到期,
                'content'=>function($model){
                    return common\enum\OrderEnum::getRegular($model->status);
                }
            ],

            [
                'attribute'=>'create_time',
                'content'=>function($model){
                    return date("Y-m-d H:i:s",$model->create_time);
                }
            ],

        ],
    ]); ?>

</div>
