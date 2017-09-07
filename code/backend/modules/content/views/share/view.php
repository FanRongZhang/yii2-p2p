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
            'title',
            'content:ntext',
            'pic_url:url',
            'url:url',
            [
                'attribute'=>'type',
                'value'=> $model->type==1 ?"首页": "其它",

            ],
            // 'type',
            'create_time:datetime',
            [
                'attribute'=>'is_open',
                'value'=> $model->is_open==0 ?"否": "是",

            ],
            // 'is_open',
        ],
    ]) ?>

</div>
