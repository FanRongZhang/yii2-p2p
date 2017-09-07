<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use common\enum\OrderEnum;
use common\models\QfbMember;
use common\models\QfbMemberInfo;
use common\models\QfbMemberVouchers;
use common\models\QfbVouchers;
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\order\models\OrderFixSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '定期理财订单');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        
    </p>

</div>

<?php
$gridColumns = [
    ['class' => 'yii\grid\SerialColumn'],

    'id',

    'sn',

    ['attribute' => 'account','content' => function($data) {
            return !empty(QfbMember::findOne($data->member_id)) ? QfbMember::findOne($data->member_id)->account : '';
        }
    ],

    ['attribute' => 'realname','content' => function($data) {
            return !empty(QfbMemberInfo::find()->where(['=','member_id',$data->member_id])->one()) ? QfbMemberInfo::find()->where(['=','member_id',$data->member_id])->one()->realname : '';
        }
    ],

    //'product_id',

    'product.product_name',

    'money',

    'pay_money',

    ['attribute' => 'vouchers','content' => function($data) {
            $voucher = QfbMemberVouchers::find()->where(['product_id'=>$data->product_id,'member_id'=>$data->member_id])->one();
            if (!empty($voucher)) {
                if ($voucher->status === 0 ) {
                    return '未使用';
                } else {
                    return !empty(QfbVouchers::findOne($voucher->id)) ? QfbVouchers::findOne($voucher->id)->money : '';
                }
            }
        }
    ],

    ['attribute' => 'status','content' => function($data) {
            return OrderEnum::getRegular($data->status);
        }
    ],

    ['attribute' => 'create_time','content' => function($data) {
            return date("Y-m-d H:i:s",$data->create_time);
        }
    ],

    common\service\AdminService::getGrideViewButtons($this,
        ([
    
            [
                'view',
                PermissionEnum::VIEW,
                function($url,$model,$key){
                    $options=[
                        'title'=>Yii::t('app','btn_view'),
                        'aria-label'=>Yii::t('app','btn_view'),
                        'data-pjax'=>0
                    ];
                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>查看详情', '/order/order-fix/view?id='.$model->id, $options);
                }
            ],
            // [
            //     'make',
            //     PermissionEnum::UPDATE,
            //     function($url,$model,$key){
            //         $options=[
            //             'title'=>Yii::t('app','btn_update'),
            //             'aria-label'=>Yii::t('app','btn_update'),
            //             'data-pjax'=>0
            //         ];
            //         return Html::a('<span class="glyphicon glyphicon-eye-open"></span>放款', '/order/order-fix/make-loans?id='.$model->id, $options);
            //     }
    
            // ],
        ]),'{view} {make}'
    )
];

$menuColumns = $gridColumns;
unset($menuColumns[count($menuColumns)-1]);

// Renders a export dropdown menu
echo ExportMenu::widget([
    'dataProvider' => $dataProvider,
    'columns' => $menuColumns
]);

// You can choose to render your own GridView separately
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'pager'=>array(
            'firstPageLabel'=>'首页',
            'lastPageLabel'=>'尾页',
            'nextPageLabel'=>'下一页',
            'prevPageLabel'=>'前一页',
    ),
    //'filterModel' => $searchModel,
    'columns' => $gridColumns,
    'export' => false,
]);
