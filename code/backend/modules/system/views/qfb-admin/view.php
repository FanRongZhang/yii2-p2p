<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

?>
<div class="detail-view">

    <p>
        <?= Html::a(Yii::t('app','修改'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app','删除'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a(Yii::t('app','返回列表'), ['index'], ['class' => 'btn btn-primary']) ?>
        
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'template' => '<tr><th>{label}</th><td>{value}</td></tr>',
        'attributes' => [
            'id',
            'account',
            ['attribute'=>'enabled','value'=>\common\enum\StatusEnum::getEnabledText(sprintf("%d",$model->enabled))],
            ['attribute'=>'is_sys','value'=>\common\enum\StatusEnum::getYesNoText(sprintf("%d",$model->is_sys))],
            ['attribute'=>'create_time','value'=> date('Y-m-d H:i:s',$model->create_time) ],
            ['attribute'=>'last_login','value'=> date('Y-m-d H:i:s',$model->last_login) ],
            
            // 'permission:ntext',
            'true_name',
        ],
    ]) ?>

</div>
