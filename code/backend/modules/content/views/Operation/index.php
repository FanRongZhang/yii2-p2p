<?php

use yii\helpers\Html;
use yii\grid\GridView; 
use kartik\export\ExportMenu;
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\content\models\operationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '营运管理';
$this->params['breadcrumbs'][] = $this->title;
?>

<!-- <div class="row" style="padding: 20px">
    <?= Html::a('添加广告', ['create'], ['class' => 'btn btn-success']) ?>
</div> -->

<div class="list-index">

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
                'name',
                'phone',
                'time',
                'bottom',
                'status'
            ],
        ]);
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
                'name',
                [
                'header' => 'logo',
                'attribute' => 'logo',
                'content' => function($data){
                     if ($data['logo'] != '') {
                         return Html::img(Yii::$app->fileStorage->baseUrl.'/'.$data->logo,['width'=>'80px','height'=>'70px']);    
                     } else {
                         return Html::encode('暂无图片');
                     }
                }
            ],
                'phone',
                'time',
                [
                    'label'=>'底栏字样',
                    'attribute'=>'bottom',
                    'value'=>function($data)
                    {
                        if (strlen($data->bottom) > 30)
                        {
                            return Html::encode(substr($data->bottom,0,30).'...');
                        }
                        else
                        {
                            return Html::encode($data->bottom);
                        }
                    }
                ],
                 [
                    'label'=>'状态',
                    'attribute'=>'status',
                    'value'=>function($data)
                    {
                        if ($data->status == 1)
                        {
                            return Html::encode('开启');
                        }
                        else
                        {
                            return Html::encode('禁用');
                        }
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
                ]),'{view} {update}'
            )
        ],
    ]); ?>

</div>
