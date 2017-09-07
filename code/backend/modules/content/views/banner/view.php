<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

?>
<div class="detail-view">

    <p>
        <?= Html::a(Yii::t('app','Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app','Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a(Yii::t('app','Goback list'), ['index'], ['class' => 'btn btn-primary']) ?>
        
    </p>

    <?= DetailView::widget([ 
        'model' => $model,
        'template' => '<tr><th>{label}</th><td>{value}</td></tr>',
        'attributes' => [
            'id',
            'name',
            [
                'attribute'=>'location_push',
                'value'=> $model->location_push==1 ?"首页": "活动",

            ],
            // 'location_push',
            [
                'attribute'=>'imgurl',
                'content'=>
                function(){ return Html::img(Yii::$app->fileStorage->baseUrl.'/'.$model->imgurl,['width'=>'70px','height'=>'50px']);} 

            ],


            'imgurl:url',
            'linkurl:url',
            [
                'attribute'=>'status',
                'value'=> $model->status==1 ?"发布": "未发布",

            ],
            // 'status',
            'display_start_time:datetime',
            'display_end_time:datetime',
            'create_time:datetime',
            [
                'attribute'=>'type',
                'value'=> $model->type==0 ?"无跳转" : ($model->type==1 ? "钱富宝原生" : ($model->type==2 ? "商城广告" : ($model->type==3 ? "url无token" : ($model->type==4 ? "手机端" : "定期理财")))),

            ],
            // 'type',
            [
                'attribute'=>'share_type',
                'value'=> $model->share_type==0 ?"没有分享": "邀请送流量linkurl有值",

            ],
            // 'share_type',
            'sortord',
        ],
    ]) ?>

</div>
