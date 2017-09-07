<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\QfbBorrowMoney */

$this->title = 'Update Qfb Borrow Money: ' . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Qfb Borrow Moneys', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="qfb-borrow-money-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
