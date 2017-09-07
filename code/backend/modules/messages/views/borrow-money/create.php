<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\QfbBorrowMoney */

$this->title = 'Create Qfb Borrow Money';
$this->params['breadcrumbs'][] = ['label' => 'Qfb Borrow Moneys', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qfb-borrow-money-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
