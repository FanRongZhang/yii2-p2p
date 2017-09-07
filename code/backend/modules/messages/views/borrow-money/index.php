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

<!-- <div class="row" style="padding: 20px">
    <?= Html::a('添加广告', ['create'], ['class' => 'btn btn-success']) ?>
</div> -->

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
            'type',
            'money',
            'sey',
            'guarantee',
            'purpose',
            'name',
            'tel',
            'status',
            'time:datetime',
            'reply_time:datetime',

            ],
        ]);
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],            
            'name',
            'tel',
            [
                'label'=>'借款类型',
                'attribute'=>'type',
                'value'=>function($data)
                {
                    if ($data->type == 1)
                    {
                        return Html::encode('抵押贷');
                    }
                    else
                    {
                        return Html::encode('未知');
                    }
                }
            ],
            'money',
            'sey',
             [
                'label'=>'抵押物',
                'attribute'=>'guarantee',
                'value'=>function($data)
                {
                    if (strlen($data->guarantee) > 30)
                    {
                        return Html::encode(substr($data->guarantee,0,30).'...');
                    }
                    else
                    {
                        return Html::encode($data->guarantee);
                    }
                }
            ],
            [
                'label'=>'借款用途',
                'attribute'=>'purpose',
                'value'=>function($data)
                {
                    if (strlen($data->purpose) > 60)
                    {
                        return Html::encode(substr($data->purpose,0,60).'...');
                    }
                    else
                    {
                        return Html::encode($data->purpose);
                    }
                }
            ],
            [
                'label'=>'申请时间',
                'attribute'=>'time',
                'value'=>function($data)
                {
                    return HTML::encode(date('Y-m-d H:i:s',$data->time));
                }
            ],
            [
                'label'=>'回访状态',
                'attribute'=>'status',
                'value'=>function($data)
                {
                    if ($data->status == 1)
                    {
                        return Html::encode('未回访');
                    }
                    else
                    {
                        return Html::encode('已回访');
                    }
                }
            ],
             [
                'label'=>'回访时间',
                'attribute'=>'reply_time',
                'value'=>function($data)
                {
                    if ($data->reply_time == 0)
                    {
                        return Html::encode('暂未回访');
                    }
                    else
                    {
                        return HTML::encode(date('Y-m-d H:i:s',$data->reply_time));
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
                ]),'{view}'
            )

        ],
    ]); ?>

</div>

