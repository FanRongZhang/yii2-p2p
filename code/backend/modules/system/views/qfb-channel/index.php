<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\system\models\QfbChannelSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '通道列表');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php
        //判断是否拥有创建按钮权限
        if(AdminService::hasPermision($this,PermissionEnum::ADD))
            echo Html::a(Yii::t('app', '+ 添加通道'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

</div>

<?php
    $gridColumns = [
            ['class' => 'yii\grid\SerialColumn'],

            'name',

            'ds_rate',

            'df_money',

            ['attribute' => 'in_status','content' => function($data) {
                    return $data->in_status === 0 ? '否' : '是';
                }
            ],

            ['attribute' => 'out_status','content' => function($data) {
                    return $data->out_status === 0 ? '否' : '是';
                }
            ],
        ['attribute' => 'is_default','content' => function($data) {
            return $data->is_default === 0 ? '否' : '是';
        }
        ],

            ['attribute' => 'need_certification','content' => function($data) {
                    return $data->need_certification === 0 ? '否' : '是';
                }
            ],


            /*['attribute' => 'is_default','content' => function($data) {
                    return $data->is_default === 0 ? '否' : '是';
                }
            ],*/

            'sort',

            ['attribute' => 'create_time','content' => function($data) {
                    return date("Y-m-d H:i:s",$data->create_time);
                }
            ],

            common\service\AdminService::getGrideViewButtons($this,
                ([
                    [
                        'update',
                        PermissionEnum::UPDATE,
                        function($url,$model,$key){
                            $options=[
                                'title'=>Yii::t('app','btn_update'),
                                'aria-label'=>Yii::t('app','btn_update'),
                                'data-pjax'=>0
                            ];
                            return Html::a('编辑', '/system/qfb-channel/update?id='.$model->id, $options);
                        }
            
                    ],
                    [
                        'support',
                        PermissionEnum::DETAILS,
                        function($url,$model,$key){
                            $options=[
                                'title'=>Yii::t('app','btn_delete'),
                                'aria-label'=>Yii::t('app','btn_delete'),
                                'data-pjax'=>0
                            ];
                            return Html::a('支持银行', '/system/bank-limit/index?pt_type='.$model->id, $options);
                        }
            
                    ],
                    // [
                    //     'default',
                    //     PermissionEnum::DETAILS,
                    //     function($url,$model,$key){
                    //         $options=[
                    //             'title'=>Yii::t('app','btn_delete'),
                    //             'aria-label'=>Yii::t('app','btn_delete'),
                    //             'data-pjax'=>0
                    //         ];
                    //         $html = '';
                    //         if ($model->is_default === 0) {
                    //             $html = Html::a('设为默认', $url, [
                    //                 'title' => Yii::t('yii', '设为默认'),
                    //                 'data-confirm' => '您确定要将该通道设为默认通道？',
                    //                 'data-method' => 'post',
                    //                 'data-pjax' => '0',
                    //             ]);
                    //         }
                    //         return $html;
                    //     }
            
                    // ],
                ]),'{view} {default} {support}'
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



