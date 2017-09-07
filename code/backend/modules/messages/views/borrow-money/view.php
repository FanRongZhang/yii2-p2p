<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\QfbBorrowMoney */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Qfb Borrow Moneys', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qfb-borrow-money-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if ($model->status ==1) {
            echo Html::a('回访操作', ['option', 'id' => $model->id], ['class' => 'btn btn-primary']);
            } else {
                echo "<font size='5'>已回访</font>";
            }
        ?>
        <?= Html::a('返回', ['index', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'type',
                'value'=>$model->type == 1 ? '抵押贷款' : '其它'
            ],
            'money',
            'sey',
            'guarantee',
            'purpose',
            'name',
            'tel',
            [
                'attribute' => 'status',
                'value'=>$model->status == 1 ? '未联系' : '已联系'
            ],
            'time:datetime',
            'reply_time:datetime',
        ],
    ]) ?>

</div>
