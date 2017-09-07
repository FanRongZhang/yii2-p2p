<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\content\models\ShareSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Qfbshares';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index" style="margin-left: 10px">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php
        //判断是否拥有创建按钮权限
        if(AdminService::hasPermision($this,PermissionEnum::ADD))
            echo Html::a('添加分享', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

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
            'title',
            'url:url',
            [
                'label'=>'分享类型',
                'attribute'=>'type',
                'value'=>function($data)
                {
                    if ($data->type == 1) {
                        return '首页';
                    } else {
                        return '其他';
                    }
                }
            ],
            [
                'label'=>'	创建时间',
                'attribute'=>'create_time',
                'value'=>function($data)
                {
                    return isset($data->create_time)?date("Y-m-d H:i:s",$data->create_time):'未知';
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
