<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\order\models\PtAccountSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '平台账户列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qfb-pt-account-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>



    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            //'id',
            'name',
            'zn_name',
            'money',
            'frozen',
            'commutation_money',
            'bank',
            'bank_code',
            // 'is_open',

            [
                'class' => 'yii\grid\ActionColumn',
                'header' => '操作',
                'template' => ' {open} {withdraw} {recharge}',
                //'options' => ['width' => '200px;'],
                'buttons' => [
                    'open' => function ($url, $model, $key) {
                        if ($model->is_open == 0) {
                            return Html::a('<i class="fa fa-edit"></i> 绑卡', ['open','name'=>$model->name], [
                                'title' => Yii::t('app', '绑卡'),
                                'class' => 'btn btn-xs yellow'
                            ]);
                        }
                    },

                    'withdraw' => function ($url, $model, $key) {
                        if ($model->is_open == 1) {
                            return Html::a('<i class="fa fa-times"></i>提现', ['withdraw', 'name'=>$model->name], [
                                'title' => Yii::t('app', '提现'),
                                'class' => 'btn btn-xs red ajax-get confirm'
                            ]);
                        }
                    },
                    'recharge' => function ($url, $model, $key) {
                        return Html::a('<i class="fa fa-times"></i>充值', ['recharge', 'name'=>$model->name], [
                            'title' => Yii::t('app', '充值'),
                            'class' => 'btn btn-xs red ajax-get confirm'
                        ]);
                    }
                ],
            ],
        ],
    ]); ?>

</div>
