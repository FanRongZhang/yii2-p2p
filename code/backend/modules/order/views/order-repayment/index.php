<?php

use yii\grid\GridView;
use yii\helpers\Html;
use common\enum\PermissionEnum;
use common\service\AdminService;
use common\toolbox\Tool;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\order\models\OrderMoneySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Qfb Orders');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <p></p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'pager'=>array(
            'firstPageLabel'=>'首页',
            'lastPageLabel'=>'尾页',
            'nextPageLabel'=>'下一页',
            'prevPageLabel'=>'前一页',
        ),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute'=>'product.product_name',
            ],
            [
                'attribute'=>'member.account',
            ],
            [
                'attribute'=>'money',
            ],
            [
                'attribute'=>'interest',
            ],

            [
                'attribute'=>'status',
                'content'=>function($model){
                    return \common\enum\OrderEnum::getRepaymentStatus($model->status);
                }
            ],

            [
                'attribute'=>'create_time',
                'content'=>function($model){
                    return $model->create_time ? date('Y-m-d H:i',$model->create_time) : '--';
                }
            ],

            [
                'attribute'=>'is_overdue',
                'content'=>function($model){
                    return $model->is_overdue == 1 ? '是' : '否';
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
                            return Html::a('<span class="glyphicon glyphicon-eye-open"></span>查看详情', '/order/order-repayment/view?repayId='.$model->id, $options);
                        }
                    ],
                ]),'{view}'
            ),

            common\service\AdminService::getGrideViewButtons($this,
                ([

                    [
                        'view',
                        PermissionEnum::VIEW,
                        function($url,$model,$key){
                            $options=[
                                'title'=>Yii::t('app','btn_view'),
                                'aria-label'=>Yii::t('app','btn_view'),
                                'data-pjax'=>true,
                                'onclick' => 'compensatory('.$model->id.')',
                                'id' => 'compensatory_'.$model->id
                            ];

                            if($model->status == 0 && $model->is_commutation == 0 && $model->is_overdue == 1){
                                return Html::a('<span class="glyphicon glyphicon-eye-open"></span>代偿', 'javascript:void(0)', $options);
                            }
                        }
                    ],
                ]),'{view}'
            )

        ],
    ]); ?>

</div>

<?php $this->beginBlock('test'); ?>

function compensatory(id){
url = '/order/order-repayment/compensatory?repay_id='+id;
 $.get(url,function(res){
    if(res == 1){
        alert('代偿成功');
        $('#compensatory_'+id).remove();
    }else if(res == 2){
        alert('金额不足');
    }else if(res == 3){
        alert('当前时间小于还款截止时间');
    }else{
        alert('代偿失败');
    }
}
),'json'
}


<?php $this->endBlock()  ?>
<!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>
