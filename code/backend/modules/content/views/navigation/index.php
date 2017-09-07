<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\export\ExportMenu;
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\content\models\navigationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '首页导航管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qfb-navigation-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php
        //判断是否拥有创建按钮权限
        if(AdminService::hasPermision($this,PermissionEnum::ADD))
            echo Html::a('添加导航', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <div class="list-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'label'=>'按钮名称',
                'attribute'=>'name',
                'value'=>function($data)
                {
                   return HTML::encode($data->name);
                }
            ],
            [
                'label'=>'跳转链接',
                'attribute'=>'url',
                'value'=>function($data)
                {
                    return HTML::encode($data->url);
                }
            ],
            [
                'label'=>'  排序',
                'attribute'=>'sort',
                'value'=>function($data)
                {
                    return HTML::encode($data->sort);
                }
            ],
            [
                'label'=>'  状态',
                'attribute'=>'status',
                'value'=>function($data)
                {
                    if ($data->status ==0) {
                        return HTML::encode('启用');
                    } else {
                        return HTML::encode('禁用');
                    }
                }
            ],
            // 'share_type',
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

</div>
