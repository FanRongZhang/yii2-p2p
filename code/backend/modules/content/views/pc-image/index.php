<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\export\ExportMenu;
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\content\models\BannerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Qfb Banners';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row" style="padding: 20px">
    <?php
        //判断是否拥有创建按钮权限
        if(AdminService::hasPermision($this,PermissionEnum::ADD))
            echo Html::a('添加图片', ['create','type'=>$type], ['class' => 'btn btn-success']) ?>
</div>

<div class="list-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

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
            'image',
            'type',
            'url:url',
            'time:datetime',
            'sort',
            'status',
            ],
        ]);
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            [
                'header' => '图片预览',
                'attribute' => 'image',
                'content' => function($data){
                     if ($data['image'] != '') {
                         return Html::img(Yii::$app->fileStorage->baseUrl.'/'.$data->image,['width'=>'80px','height'=>'70px']);    
                     } else {
                         return Html::encode('暂无图片');
                     }
                }
            ],
            'url:url',
            [
                'label'=>'添加时间',
                'attribute'=>'time',
                'value'=>function($data)
                {
                    return HTML::encode(date('Y-m-d H:i:s',$data->time));
                }
            ],
            'sort',
            [
                'label'=>'状态',
                'attribute'=>'status',
                'value'=>function($data)
                {
                    if ($data->status == 1)
                    {
                        return '启用';
                    }
                    else
                    {
                        return '禁用';
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
                    [
                        'delete',
                        PermissionEnum::DELETE,
                        function($url,$model,$key){
                            $options=[
                                'title'=>Yii::t('app','btn_delete'),
                                'aria-label'=>Yii::t('app','btn_delete'),
                                'data-pjax'=>0
                            ];
                            return Html::a('<span class="glyphicon glyphicon-trash"></span>删除', '/content/pc-image/delete?type='.$model->type.'&id='.$model->id, $options); 
                        }
            
                    ],
                ]),'{view} {update} {delete}'
            )
        ]
    ]); ?>

</div>

