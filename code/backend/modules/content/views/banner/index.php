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
            echo Html::a('添加广告', ['create'], ['class' => 'btn btn-success']) ?>
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
                [
                    'label'=>'广告投放位置',
                    'attribute'=>'location_push',
                    'value'=>function($data)
                    {
                        if ($data->location_push == 1)
                        {
                            return '首页';
                        }
                        elseif ($data->location_push == 2)
                        {
                            return '活动';

                        } elseif ($data->location_push == 3) {
                            
                            return 'PC_banner';
                        } elseif ($data->location_push == 4) {
                            
                            return 'LOGO';
                        } elseif ($data->location_push == 5) {
                            
                            return '合作方';
                        } elseif ($data->location_push == 6) {
                            
                            return '首页底部';
                        }
                    }
                ],
                'linkurl:url',
                [
                    'label'=>'广告状态',
                    'attribute'=>'status',
                    'value'=>function($data)
                    {
                        if ($data->status == 1)
                        {
                            return '已发布';
                        }
                        else
                        {
                            return '未发布';
                        }
                    }
                ],
                [
                    'label'=>'	发布时间',
                    'attribute'=>'display_start_time',
                    'value'=>function($data)
                    {
                        return isset($data->display_start_time)?date("Y-m-d H:i:s",$data->display_start_time):'未知';
                    }
                ],
                [
                    'label'=>'	结束时间',
                    'attribute'=>'display_end_time',
                    'value'=>function($data)
                    {
                        return isset($data->display_end_time)?date("Y-m-d H:i:s",$data->display_end_time):'未知';
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
                [
                    'label'=>'跳转类型',
                    'attribute'=>'type',
                    'value'=>function($data)
                    {
                        switch ($data->type)
                        {
                            case 0:
                                return '无跳转';
                                break;
                            case 1:
                                return '钱富宝原生';
                                break;
                            case 2:
                                return '商城广告';
                                break;
                            case 3:
                                return 'url无token';
                                break;
                            case 4:
                                return '手机充值';
                                break;
                            case 5:
                                return '定期理财';
                                break;
                            default:
                                return '无跳转';
                        }
                    }
                ],
                'sortord',
            ],
        ]);
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            [
                'label'=>'广告投放位置',
                'attribute'=>'location_push',
                'value'=>function($data)
                {
                    if ($data->location_push == 1)
                        {
                            return '首页';
                        }
                        elseif ($data->location_push == 2)
                        {
                            return '活动';

                        } elseif ($data->location_push == 3) {
                            
                            return 'PC_banner';
                        } elseif ($data->location_push == 4) {
                            
                            return 'LOGO';
                        } elseif ($data->location_push == 5) {
                            
                            return '合作方';
                        } elseif ($data->location_push == 6) {
                            
                            return '首页底部';
                        } elseif ($data->location_push == 7) {
                            
                            return '关于我们';
                        }
                }
            ],
            'linkurl:url',
            [
                'label'=>'广告状态',
                'attribute'=>'status',
                'value'=>function($data)
                {
                    if ($data->status == 1)
                    {
                        return '已发布';
                    }
                    else
                    {
                        return '未发布';
                    }
                }
            ],
            
            [
                'header' => '图片预览',
                'attribute' => 'imgurl',
                'content' => function($data){
                     if ($data['imgurl'] != '') {
                         return Html::img(Yii::$app->fileStorage->baseUrl.'/'.$data->imgurl,['width'=>'80px','height'=>'70px']);    
                     } else {
                         return Html::encode('暂无图片');
                     }
                }
            ],
            [
                'label'=>'	发布时间',
                'attribute'=>'display_start_time',
                'value'=>function($data)
                {
                    return isset($data->display_start_time)?date("Y-m-d H:i:s",$data->display_start_time):'未知';
                }
            ],
            [
                'label'=>'	结束时间',
                'attribute'=>'display_end_time',
                'value'=>function($data)
                {
                    return isset($data->display_end_time)?date("Y-m-d H:i:s",$data->display_end_time):'未知';
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
            [
                'label'=>'跳转类型',
                'attribute'=>'type',
                'value'=>function($data)
                {
                    switch ($data->type)
                    {
                        case 0:
                            return '无跳转';
                        break;
                        case 1:
                            return '钱富宝原生';
                        break;
                        case 2:
                            return '商城广告';
                            break;
                        case 3:
                            return 'url无token';
                            break;
                        case 4:
                            return '手机充值';
                            break;
                        case 5:
                            return '定期理财';
                            break;
                        default:
                            return '无跳转';
                    }
                }
            ],
            // 'share_type',
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
