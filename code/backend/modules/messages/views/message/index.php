<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\export\ExportMenu;
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\messages\models\MessageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '消息';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php
        //判断是否拥有创建按钮权限
        if(AdminService::hasPermision($this,PermissionEnum::ADD))
            echo Html::a('添加消息', ['create'], ['class' => 'btn btn-success']) ?>
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
                [
                    'label'=>'消息发送对象',
                    'attribute'=>'send_ob',
                    'value'=>function($data)
                    {
                        if ($data->send_ob == 0)
                        {
                            return '会员级别';
                        }
                        elseif($data->send_ob == 1)
                        {
                            return '会员账号';
                        }
                        elseif($data->send_ob == 2)
                        {
                            return '会员标签';
                        }
                    }
                ],
                [
                    'label'=>'	消息发送时间',
                    'attribute'=>'send_time',
                    'value'=>function($data)
                    {
                        return $data->send_time ?date("Y-m-d H:i:s",$data->send_time):'未发送';
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
            [
                'label'=>'消息发送对象',
                'attribute'=>'send_ob',
                'value'=>function($data)
                {
                    if ($data->send_ob == 0)
                    {
                        return '会员级别';
                    }
                    elseif($data->send_ob == 1)
                    {
                        return '会员账号';
                    }
                    elseif($data->send_ob == 2)
                    {
                        return '会员标签';
                    }
                }
            ],
            [
                'label'=>'	消息发送时间',
                'attribute'=>'send_time',
                'value'=>function($data)
                {
                    return $data->send_time ?date("Y-m-d H:i:s",$data->send_time):'未发送';
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
