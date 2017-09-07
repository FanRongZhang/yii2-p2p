<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\export\ExportMenu;
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\content\models\ArticleSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Qfb Articles';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php
        //判断是否拥有创建按钮权限
        if(AdminService::hasPermision($this,PermissionEnum::ADD))
            echo Html::a('添加文章', ['create'], ['class' => 'btn btn-sm btn-success']) ?>
    </p>

    <div class="index" style="margin: 5px">
    <?php
    echo ExportMenu::widget([
    'dataProvider' => $dataProvider,
    'pager'=>array(
            'firstPageLabel'=>'首页',
            'lastPageLabel'=>'尾页',
            'nextPageLabel'=>'下一页',
            'prevPageLabel'=>'前一页',
        ),
    'columns' => [
        'title',
        [
            'label'=>'	创建时间',
            'attribute'=>'create_time',
            'value'=>function($data)
            {
                return isset($data->create_time)?date("Y-m-d H:i:s",$data->create_time):'未知';
            }
        ],
        [
            'label'=>'	修改时间',
            'attribute'=>'update_time',
            'value'=>function($data)
            {
                return isset($data->create_time)?date("Y-m-d H:i:s",$data->update_time):'未知';
            }
        ],
        'sortord',
        ],
    ]);
    ?>
    </div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'title',
            [
                'label'=>'	创建时间',
                'attribute'=>'create_time',
                'value'=>function($data)
                {
                    return isset($data->create_time)?date("Y-m-d H:i:s",$data->create_time):'未知';
                }
            ],
            [
                'label'=>'	修改时间',
                'attribute'=>'update_time',
                'value'=>function($data)
                {
                    return isset($data->create_time)?date("Y-m-d H:i:s",$data->update_time):'未知';
                }
            ],
             'sortord',

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
        ],
    ]); ?>

</div>
