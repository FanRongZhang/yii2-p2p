<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\export\ExportMenu;
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\content\models\aboutMeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '关于我们';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qfb-about-me-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php
        //判断是否拥有创建按钮权限
        if(AdminService::hasPermision($this,PermissionEnum::ADD))
            echo Html::a('添加', ['create'], ['class' => 'btn btn-success']) ?>
    </p> 

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
                'position',
                [
                    'header' => '头像预览',
                    'attribute' => 'image',
                    'content' => function($data){
                         if ($data['image'] != '') {
                             return Html::img(Yii::$app->fileStorage->baseUrl.'/'.$data->image,['width'=>'80px','height'=>'70px']);    
                         } else {
                             return Html::encode('暂无图片');
                         }
                    }
                ],
                'content',
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
            ],
        ]);
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
             'name',
                'position',
                [
                    'header' => '头像预览',
                    'attribute' => 'image',
                    'content' => function($data){
                         if ($data['image'] != '') {
                             return Html::img(Yii::$app->fileStorage->baseUrl.'/'.$data->image,['width'=>'100px','height'=>'50px']);    
                         } else {
                             return Html::encode('暂无图片');
                         }
                    }
                ],
                [
                    'label'=>'简介',
                    'attribute'=>'content',
                    'value'=>function($data)
                    {
                        if (strlen($data->content) > 30)
                        {
                            return strip_tags(substr($data->content,0,30).'...');
                        }
                        else
                        {
                            return strip_tags($data->content);
                        }
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
                            return Html::a('<span class="glyphicon glyphicon-trash"></span>删除',$url,$options);
                        }
            
                    ],
                ]),'{view} {update} {delete}'
            )
        ],
    ]); ?>

</div>

</div>
