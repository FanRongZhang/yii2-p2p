<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ArticleCategory */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Article Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="article-category-view">

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('Back to list', ['index'], ['class' => 'btn btn-info']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'parent_id',
                'value'=>$model->parent ? $model->parent->title : '-',
            ],
            'title',
            'slug',
            [
                'attribute'=>'created_at',
                'value'=>date("Y-m-d H:i:s",$model->created_at),
            ],
            [
                'attribute'=>'updated_at',
                'value'=>date("Y-m-d H:i:s",$model->updated_at),
            ],
            [
                'attribute'=>'status',
                'value'=>$model::getStatusText($model->status),
            ],
        ],
    ]) ?>

</div>
