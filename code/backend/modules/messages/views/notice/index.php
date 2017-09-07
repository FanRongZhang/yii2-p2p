<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\export\ExportMenu;
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\messages\models\NoticeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '公告';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index" style="margin-left: 20px">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php
        //判断是否拥有创建按钮权限
        if(AdminService::hasPermision($this,PermissionEnum::ADD))
            echo Html::a('添加公告', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

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
                'summary',
                [
                    'label'=>'	公告到期时间',
                    'attribute'=>'end_time',
                    'value'=>function($data)
                    {
                        return isset($data->end_time)?date("Y-m-d H:i:s",$data->end_time):'未知';
                    }
                ],
                [
                    'label'=>'	公告发送时间',
                    'attribute'=>'send_time',
                    'value'=>function($data)
                    {
                        return isset($data->send_time)?date("Y-m-d H:i:s",$data->send_time ):'未知';
                    }
                ],
                [
                    'label'=>'公告是否发送',
                    'attribute'=>'is_send',
                    'value'=>function($data)
                    {
                        if ($data->is_send == 0)
                        {
                            return '未发送';
                        }
                        elseif ($data->is_send == 1)
                        {
                            return '发送成功';
                        }
                        else
                        {
                            return '发送失败';
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

            'title',
            'summary',
            [
                'label'=>'	公告发送时间',
                'attribute'=>'send_time',
                'value'=>function($data)
                {
                    return isset($data->send_time)?date("Y-m-d H:i:s",$data->send_time ):'未知';
                }
            ],
            [
                'label'=>'	公告显示截止时间',
                'attribute'=>'show_end_time',
                'value'=>function($data)
                {
                    return isset($data->show_end_time)?date("Y-m-d H:i:s",$data->show_end_time ):'未知';
                }
            ],
            [
                'label'=>'公告是否发送',
                'attribute'=>'is_send',
                'value'=>function($data)
                {
                    if ($data->is_send == 0)
                    {
                        return '未发送';
                    }
                    elseif ($data->is_send == 1)
                    {
                        return '发送成功';
                    }
                    else
                    {
                        return '发送失败';
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
