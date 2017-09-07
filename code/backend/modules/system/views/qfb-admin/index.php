<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\export\ExportMenu;
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\system\models\AdminSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '管理员');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php
        //判断是否拥有创建按钮权限
        if(AdminService::hasPermision($this,PermissionEnum::ADD))
            echo Html::a(Yii::t('app', '新建管理员'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php
    
    $gridColumns = [
            'id',
            'account',
            'true_name',
            [
              'attribute'=>'enabled',
               'value'=>function($data){
                    return common\enum\StatusEnum::getEnabledText(sprintf("%d",$data->enabled));
                }
            ],
            [
            'attribute'=>'is_sys',
            'value'=>function($data){
                return common\enum\StatusEnum::getYesNoText(sprintf("%d",$data->is_sys));
            }
            ],
            //'create_time:datetime',
            ['attribute'=>'create_time',
	            'value'=> function($model){
	            return  date("Y-m-d H:i:s",$model->create_time);
	            },
	            'headerOptions' => ['width' => '180'],
            ],
            
            //'last_login:datetime',
            ['attribute'=>'last_login',
	            'value'=> function($model){
	            return  date("Y-m-d H:i:s",$model->last_login);
	            },
	            'headerOptions' => ['width' => '180'],
            ],
            //'last_ip',
            // 'store_id',
            // 'permission:ntext',
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
                            return Html::a('<span class="glyphicon glyphicon-eye-open"></span>查看',$url,$options);
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
                            return Html::a('<span class="glyphicon glyphicon-pencil"></span>编辑',$url,$options);
                        }
            
                    ],
                    [
                        'delete',
                        PermissionEnum::DELETE,
                        function($url,$model,$key){
                            $options=[
                                'title'=>Yii::t('app','btn_delete'),
                                'aria-label'=>Yii::t('app','btn_delete'),
                                'data-pjax'=>0
                            ];
                            return Html::a('<span class="glyphicon glyphicon-trash"></span>删除',$url,$options);
                        }
            
                    ],
                ]),'{view} {update} {delete}'
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
        
    ]);
    

    ?>

</div>


