<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\enum\VouchersEnum;

?>
<div class="detail-view">

    <?php echo $this->render('_search_view', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app','返回代金券列表'), ['index'], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',

            [
                'label' => '会员账号',
                'attribute' => 'member.account',
            ],
            [
                'label' => '会员姓名',
                'attribute' => 'member.memberInfo.realname',
            ],
            
            
            [
                'attribute' => 'status',
                'value' => function($model){
                    return $model->status ? '已使用' : ( ($model->invalid_time > time()) ? '未使用' : '已失效') ;
                }
                ],
            [
                'attribute'=>'receive_time',
                'value'=>function ($model){
                    return date('Y-m-d H:i:s',$model->receive_time);
                }
            ],
            
            [
                'attribute'=>'invalid_time',
                'value'=>function ($model){
                    return date('Y-m-d H:i:s',$model->invalid_time);
                }
            ],
        ],
    ]); ?>

</div>
