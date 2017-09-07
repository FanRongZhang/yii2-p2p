<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use common\models\QfbMemberInfo;
use common\models\QfbMember;
use common\enum\LevelEnum;
use common\models\QfbMemberMoney;
use common\models\QfbMoneyDetail;
/* @var $this yii\web\View */
/* @var $searchModel backend\modules\member\models\Membersearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '二度人脉');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="list-index">
    <p>
        <h1>
            <span>二度人脉:<?=!empty($count['count'])? $count['count'] : 0.00 ?>人</span>&nbsp;&nbsp;&nbsp;&nbsp;
            <span>活期在投总额:<?=!empty($live_sum['live_money'])? $live_sum['live_money'] : 0.00 ?>元</span>&nbsp;&nbsp;&nbsp;&nbsp;
            <span>定期在投总额:<?=!empty($live_sum['fix_money'])? $live_sum['fix_money'] : 0.00 ?>元</span>&nbsp;&nbsp;&nbsp;&nbsp;
            <span>活期贡献总分润:<?=!empty($live_profit['money'])? $live_profit['money'] : 0.00 ?>元</span>&nbsp;&nbsp;&nbsp;&nbsp;
            <span>定期贡献总分润:<?=!empty($fix_profit['money'])? $fix_profit['money'] : 0.00 ?>元</span>
        </h1>       
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [ 'label'=>"会员账号",'content'=>function($data){
                    return !empty($data->account) ? $data->account : "";
                }
            ],

            [ 'label'=>"会员姓名",'content'=>function($data){
                    return !empty(QfbMemberInfo::find()->where(['member_id' => $data->id])->one()) ? QfbMemberInfo::find()->where(['member_id' => $data->id])->one()->realname : "";
                }
            ],

            [ 'label'=>"推荐人账号",'content'=>function($data){
                    return !empty(QfbMember::find()->where(['id' => $data->r_member_id])->one()) ? QfbMember::find()->where(['id' => $data->r_member_id])->one()->account : "";
                }
            ],

            [ 'label'=>"推荐人姓名",'content'=>function($data){
                    return !empty(QfbMemberInfo::find()->where(['member_id' => $data->r_member_id])->one()) ? QfbMemberInfo::find()->where(['member_id' => $data->r_member_id])->one()->realname : "";
                }
            ],

            [ 'label'=>"会员等级",'content'=>function($data){
                    return !empty($data->level) ? LevelEnum::getName($data->level) : "";
                }
            ],

            [ 'label'=>"活期在投金额",'content'=>function($data){
                    $membermoney = QfbMemberMoney::find()->where(['member_id' => $data->id])->one();
                    return !empty($membermoney) ? $membermoney->live_money + $membermoney->pre_live_money : "";
                }
            ],

            [ 'label'=>"定期在投金额",'content'=>function($data){
                    $membermoney = QfbMemberMoney::find()->where(['member_id' => $data->id])->one();
                    return !empty($membermoney) ? $membermoney->fix_money : "";
                }
            ],

            [ 'label'=>"活期贡献分润",'content'=>function($data){
                    $moneydetail = QfbMoneyDetail::find()->where(['member_id' => \Yii::$app->request->get('id'),'from_member_id' => $data->id,'money_type' => 2])->one();
                    return !empty($moneydetail) ? $moneydetail->money : "";
                }
            ],

            [ 'label'=>"定期贡献分润",'content'=>function($data){
                    $moneydetail = QfbMoneyDetail::find()->where(['member_id' => \Yii::$app->request->get('id'),'from_member_id' => $data->id,'money_type' => 2])->one();
                    return !empty($moneydetail) ? $moneydetail->money : "";
                }
            ],

        ],
    ]); ?>

</div>
