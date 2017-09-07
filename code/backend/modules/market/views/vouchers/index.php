<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\enum\VouchersEnum;
use common\enum\ProductEnum;
use common\enum\LevelEnum;
use common\service\AdminService;
use common\enum\PermissionEnum;
use kartik\export\ExportMenu;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\market\models\VouchersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Qfb Vouchers');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php
        //判断是否拥有创建按钮权限
        if(AdminService::hasPermision($this,PermissionEnum::ADD))
            echo Html::a(Yii::t('app', '添加代金券'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php
    
    $gridColumns = [
        'id',
        'name',
        [
            'attribute' => 'type',
            'value' => function($model){
                return VouchersEnum::getRule($model->type) ;
            }
        ],
        'valid_days',
        'money',
        'use_money',
        [
            'attribute' => 'use_members',
            'value' => function($model){
                $members = explode(',', $model->use_members);
                $result = '';
                foreach ($members as $val){
                    $result .= LevelEnum::getName($val).'，';
                }
                return mb_substr($result, 0, -1,'utf8');
            }
        ],
        [
            'attribute' => 'use_type',
            'value' => function($model){
                return ProductEnum::getType($model->use_type) ;
            }
        ],
        [
            'attribute' => 'status',
            'value' => function($model){
                return VouchersEnum::getStatus($model->status) ;
            }
        ],
        [
            'attribute'=>'create_time',
            'value'=>function ($model){
                return date('Y-m-d H:i:s',$model->create_time);
            }
        ],
        
        common\service\AdminService::getGrideViewButtons($this,
            ([
        
                [
                    'view',
                    PermissionEnum::VIEW,
                    function($url, $model){
                        return Html::a('<i class="fa fa-ban"></i> 发放明细',
                            ['view?vouchers_id='.$model->id],
                            [
                                'class' => 'btn btn-default btn-xs',
                            ]
                        );
                    }
                ],
                [
                    'update',
                    PermissionEnum::UPDATE,
                    function($url, $model){
                        return Html::a('<i class="fa fa-ban"></i> '.( $model->status == 1 ? '关闭' : '开启'),
                            [$url.'&status='.$model->status],
                            [
                                'style' => 'margin-left: 20px;',
                                'class' => 'btn btn-default btn-xs',
                                'data' => ['confirm' => '你确定要'. ( $model->status == 1 ? '关闭' : '开启') .'这个代金券吗？',]
                            ]
                        );
                    }
        
                ],
//                     [
//                         'delete',
//                         PermissionEnum::DELETE,
//                         function($url,$model,$key){
//                             $options=[
//                                 'title'=>Yii::t('app','btn_delete'),
//                                 'aria-label'=>Yii::t('app','btn_delete'),
//                                 'data-pjax'=>0
//                             ];
//                             return Html::a('<i class="fa fa-ban"></i>删除',$url,
//                                 [
//                                     'style' => 'margin-left: 20px;',
//                                     'class' => 'btn btn-default btn-xs',
//                                     'data' => ['confirm' => '你确定要删除这个代金券吗？',]
//                                 ]);
//                         }

//                     ],
            ]),'{view} {update}'
                )
    ];
    
    $menuColumns = $gridColumns;
    unset($menuColumns[count($menuColumns)-1]);
    
    // Renders a export dropdown menu
    echo ExportMenu::widget([
        'dataProvider' => $dataProvider,
        'pager'=>array(
            'firstPageLabel'=>'首页',
            'lastPageLabel'=>'尾页',
            'nextPageLabel'=>'下一页',
            'prevPageLabel'=>'前一页',
        ),
        'columns' => $menuColumns
    ]);

    // You can choose to render your own GridView separately
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'columns' => $gridColumns,

    ]);
    

    ?>
    

</div>
