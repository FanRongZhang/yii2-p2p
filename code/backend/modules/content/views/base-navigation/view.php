<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\QfbBaseNavigation */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Qfb Base Navigations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qfb-base-navigation-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('编辑', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('删除', ['delete', 'id' => $model->id], [
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
        'attributes' => [
            'id',
            'name',
            'pid',
            'url:url',
            'sort',
              [
                    'label'=>'状态',
                    'attribute'=>'status',
                    'value'=>$model->status==0 ? '开启' : '禁用'
            ],
        ],
    ]) ?>

</div>
