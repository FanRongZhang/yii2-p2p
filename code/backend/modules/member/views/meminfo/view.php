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
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
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
            'level',
            'relations',
            'r_member_id',
            'layer',
            'mobile',
            'account',
            'access_token',
            'channel_id',
            'last_access_time:datetime',
            'imei',
            'zf_pwd',
            'last_ip',
            'operator',
            'experience',
            'is_newer',
        ],
    ]) ?>

</div>
