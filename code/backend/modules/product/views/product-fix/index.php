<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use common\enum\ProductEnum;
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\product\models\ProductFixSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '定期理财');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php
        //判断是否拥有创建按钮权限
        if(AdminService::hasPermision($this,PermissionEnum::ADD))
            echo Html::a(Yii::t('app', '+ 添加'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

</div>

<?php
$gridColumns = [
    ['class' => 'yii\grid\SerialColumn'],

    'id',

    ['attribute' => 'product_type','content' => function($data) {
            return ProductEnum::getType($data->product_type);
        } 
    ],

    'product_name',

    'stock_money',

    'year_rate',

    'profit_settings.recommond_rate',

    'profit_settings.manage_rate',
    
    'profit_settings.agent_rate',

    'min_money',

    'step_money',

    'max_money',

    ['attribute' => 'profit_type','content' => function($data) {
            return ProductEnum::getProfitType($data->profit_type);
        }
    ],

    ['attribute' => 'is_newer','content' => function($data) {
            return $data->is_newer === 1 ? '是' : '否';
        }
    ],

    ['attribute' => 'lock_day','content' => function($data) {
            return $data->lock_day.'天';
        } 
    ],

    ['attribute' => 'invest_day','content' => function($data) {
            return $data->invest_day.'天';
        } 
    ],

    ['attribute' => 'profit_day','content' => function($data) {

            return ProductEnum::getProfitDay($data->profit_day);
        }
    ],

    ['attribute' => 'status','content' => function($data) {
            return ProductEnum::getStatus($data->status);
        }
    ],

    ['attribute' => 'create_time','content' => function($data) {
            return date("Y-m-d H:i:s",$data->create_time);
        }
    ],

    ['attribute' => 'end_time','content' => function($data) {
            return date("Y-m-d H:i:s",$data->end_time);
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
                            return Html::a('<span class="glyphicon glyphicon-eye-open"></span>查看', '/product/product-fix/view?id='.$model->id, $options);
                        }
                    ],
                    [
                        'update',
                        PermissionEnum::UPDATE,
                        function($url,$model,$key){
                            $options=[
                                'title'=>Yii::t('app','btn_update'),
                                'aria-label'=>Yii::t('app','btn_update'),
                                'data-pjax'=>0
                            ];
                            if ($model->status === 0) {
                                return Html::a('<span class="glyphicon glyphicon-pencil"></span>编辑', '/product/product-fix/update?id='.$model->id, $options);
                            }
                        }
            
                    ],
                    [
                        'loan',
                        PermissionEnum::LOAN_MONEY,
                        function($url,$model,$key){
                            $options=[
                                'title'=>Yii::t('app','btn_delete'),
                                'aria-label'=>Yii::t('app','btn_delete'),
                                'data-pjax'=>0
                            ];

                            $html = '';
                            if($model->status === 2 && $model->end_time > time() || $a=true){
                                $html = Html::a('<span class="glyphicon glyphicon-off"></span>放款', $url, [
                                    'title' => Yii::t('yii', '放款'),
                                    // 'data-confirm' => '您是否要关闭这一理财产品？',
                                    'data-method' => 'post',
                                    'data-pjax' => '0',
                                ]);
                            }
                            return $html;
                        }
            
                    ],
                    [
                        'ok',
                        PermissionEnum::PUBLISH,
                        function($url,$model,$key){
                            $options=[
                                'title'=>Yii::t('app','btn_delete'),
                                'aria-label'=>Yii::t('app','btn_delete'),
                                'data-pjax'=>0
                            ];
                            
                            $html = '';
                            if($model->status === 0){
                                if ($model->product_type === 2) {
                                    $html = Html::a('<span class="glyphicon glyphicon-ok"></span>发布', $url, [
                                    'title' => Yii::t('yii', '发布'),
                                    'data-confirm' => '您是否要发布这一定期理财产品？',
                                    'data-method' => 'post',
                                    'data-pjax' => '0',
                                    ]);
                                } else {
                                    $html = Html::a('<span class="glyphicon glyphicon-ok"></span>发布', $url, [
                                    'title' => Yii::t('yii', '发布'),
                                    'data-confirm' => '若发布该活期理财产品,募集中的活期产品将被下架？',
                                    'data-method' => 'post',
                                    'data-pjax' => '0',
                                    ]);
                                }

                            }
                            return $html;
                        }
            
                    ],
                    // [
                    //     'off',
                    //     PermissionEnum::LOAN_MONEY,
                    //     function($url,$model,$key){
                    //         $options=[
                    //             'title'=>Yii::t('app','btn_delete'),
                    //             'aria-label'=>Yii::t('app','btn_delete'),
                    //             'data-pjax'=>0
                    //         ];
                            
                    //         $html = '';
                    //         if($model->status === 1 && ($model->profit_day === 20 || $model->profit_day === 21)){
                    //             $html = Html::a('<span class="glyphicon glyphicon-off"></span>关闭', $url, [
                    //                 'title' => Yii::t('yii', '关闭'),
                    //                 'data-confirm' => '您是否要关闭这一理财产品？',
                    //                 'data-method' => 'post',
                    //                 'data-pjax' => '0',
                    //             ]);
                    //         }
                    //         return $html;
                    //     }
            
                    // ],
                ]),'{view} {update} {off} {ok} {loan}'
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

