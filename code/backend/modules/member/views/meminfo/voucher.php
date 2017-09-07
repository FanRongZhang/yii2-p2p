<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */
/* @var $searchModel backend\modules\member\models\Membersearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '代金券明细');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="list-index">
    <p>
        <h1>
            有效代金券:<?= $no_use_count?>张
        </h1>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [ 'label'=>"代金券面额",'content'=>function($data){
                    return !empty($data->vouchers->money) ? $data->vouchers->money : "";
                }
            ],

            [ 'label'=>"使用条件",'content'=>function($data){
                    return !empty($data->vouchers->use_money) ? "满".$data->vouchers->use_money."元可用" : "";
                }
            ],

            [ 'label'=>"代金券类型",'content'=>function($data){
                    return !empty($data->vouchers->type) ? $data->vouchers->type == 0 ? "固定规则发放" : "活动发放" : "";
                }
            ],

            [ 'label'=>"领取时间",'content'=>function($data){
                    return !empty($data->receive_time) ? date("Y-m-d H:i:s",$data->receive_time) : "";
                }
            ],

            [ 'label'=>"有效期至",'content'=>function($data){
                    return !empty($data->invalid_time) ? date("Y-m-d H:i:s",$data->invalid_time) : "";
                }
            ],

            [ 'label'=>"状态",'content'=>function($data){
                    return !empty($data->status) ? ($data->status === 0 ? "未使用" : "已使用") : "未使用";
                }
            ],

            [ 'label'=>"代金券来源",'content'=>function($data){
                    return !empty($data->remark) ? $data->remark : "";
                }
            ],

        ],
    ]); ?>

</div>
