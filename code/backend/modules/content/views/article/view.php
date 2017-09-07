<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

?>
<div class="detail-view">

    <p>
        <?= Html::a(Yii::t('app','编辑'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app','返回'), ['index'], ['class' => 'btn btn-primary']) ?>
        
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'template' => '<tr><th>{label}</th><td>{value}</td></tr>',
        'attributes' => [
            'id',
            'title',
            'create_time:datetime',
            'content:ntext',
            'operator_id',
            'update_time:datetime',
            'sortord',
        ],
    ]) ?>

</div>
