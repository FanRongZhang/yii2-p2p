<?php

use yii\helpers\Html;
use yii\grid\GridView; 
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\order\models\ReconciliationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '对账列表');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qfb-reconciliation-log-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php  //echo $this->render('_search', ['model' => $searchModel]); ?>
    <p><font size='4' color='red'><?php echo $msg ?></font></p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'pager'=>array(
            'firstPageLabel'=>'首页',
            'lastPageLabel'=>'尾页',
            'nextPageLabel'=>'下一页',
            'prevPageLabel'=>'前一页',
        ),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'file_name',
            // 'type',
            [
                // 'label'=>'状态',
                'attribute'=>'file_type',
                'value'=>function($model)
                {
                    if ($model->file_type == 0){
                        return '充值';
                    }elseif($model->file_type == 1){
                        return '提现';
                    }elseif($model->file_type == 2){
                        return '交易';
                    }elseif($model->file_type == 3){
                        return '佣金';
                    }
                }
            ],
            [
                // 'label'=>'状态',
                'attribute'=>'status',
                'value'=>function($model)
                {
                    if ($model->status == 0){
                        return '对账中...';
                    }elseif($model->status == 1){
                        return '失败';
                    }elseif($model->status == 2){
                        return '成功';
                    }
                }
            ],
            [
                // 'label'=>'状态',
                'attribute'=>'affirm_status',
                'value'=>function($model)
                {
                    if ($model->affirm_status == 0){
                        return '待对账确认';
                    }elseif($model->affirm_status == 1){
                        return '失败';
                    }elseif($model->affirm_status == 2){
                        return '成功';
                    }
                }
            ],
            [
                'attribute'=>'withhold_time',
                'content'=>function($model){
                    return $model->withhold_time ? date('Y-m-d H:i',$model->withhold_time) : '--';
                }
            ],
            [
                'attribute'=>'end_time',
                'content'=>function($model){
                    return $model->end_time ? date('Y-m-d H:i',$model->end_time) : '--';
                }
            ],
            'remark:ntext',

            common\service\AdminService::getGrideViewButtons($this,
                ([
                    [
                        'delete',
                        PermissionEnum::DELETE,
                        function($url,$model,$key){
                            $options=[
                                'title'=>Yii::t('app','btn_delete'),
                                'aria-label'=>Yii::t('app','btn_delete'),
                                'data-pjax'=>0
                            ];
                            return Html::a('删除', '/order/reconciliation/del?re_id='.$model->id, $options); 
                        }
            
                    ],
                    [
                        'support',
                        PermissionEnum::DETAILS,
                        function($url,$model,$key){
                            $options=[
                                'title'=>Yii::t('app','btn_update'),
                                'aria-label'=>Yii::t('app','btn_update'),
                                'data-pjax'=>0
                            ];
                            return Html::a('查看日志', '/order/reconciliation/index?r_id='.$model->id, $options);
                        }
            
                    ],
                ]),'{delete} {support}'
            )
        ],
    ]); ?>

</div>
