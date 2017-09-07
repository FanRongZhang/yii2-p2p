<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\enum\LevelEnum;
use common\service\MoneyLogService;
use common\service\ProductService;
use common\models\QfbMoneyLog;

$plan = QfbMoneyLog::find()->where(['type'=>1,'money_type'=>1,'action'=>14])->andWhere(['=','member_id',$model->vid])->sum('money');
if(empty($plan))
	$plan = 0;
?>

<?= DetailView::widget([
    'model' => $model,
    'template' => '<tr><th>{label}</th><td>{value}</td></tr>',
    'attributes' => [
        [
            'label'=>"在投总额",
            'value'=> $model->vlive_money+$model->vfix_money+$model->vpre_live_money,

        ],
        [
            'label'=>"累计投资总额",
            'value'=> MoneyLogService::getAllInvestment($model->vid),

        ],
        [
            'label'=>"账号零钱",
            'value'=> $model->vmoney,

        ],

        [
            'attribute'=>'vlive_money',
            'value'=> $model->vlive_money+$model->vpre_live_money,

        ],
        [
            'label'=>"累计活期投资",
            'value'=> MoneyLogService::getLiveInvestment($model->vid),

        ],

        [
            'label'=>"累计活期收益",
            'value'=> MoneyLogService::getLiveIncome($model->vid),

        ],
        'vfix_money',
        [
            'label'=>"累计定期投资",
            'value'=> MoneyLogService::getFixInvestment($model->vid),

        ],
        [
            'label'=>"累计定期收益",
            'value'=> (string) (MoneyLogService::getProfitByMemberId($model->vid) + $plan_profit)
                // MoneyLogService::getFixIncome($model->vid),

        ],

        [
            'label'=>"推荐奖励",
            'value'=> MoneyLogService::getRecommandProfit($model->vid),

        ],
        [
            'label'=>"有效体验金",
            'value'=> $model->vstatus==1 ?"正常": "冻结",

        ],
        [
            'label'=>"有效代金券",
            'value'=> $model->vstatus==1 ?"正常": "冻结",

        ],
        [
            'label'=>"有效加息券",
            'value'=> $model->vstatus==1 ?"正常": "冻结",

        ],
        [
            'label'=>"积分",
            'value'=> $model->vstatus==1 ?"正常": "冻结",

        ],
        [
            'label'=>"累计放款金额",
            'value'=> @ProductService::getProductByMoney($model->vid),

        ],
        [
            'label'=>"累计平台收益",
            'value'=> @ProductService::getPlatform($model->vid),

        ],
    ],
]) ?>

