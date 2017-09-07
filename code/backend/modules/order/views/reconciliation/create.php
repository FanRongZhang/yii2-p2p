<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\QfbReconciliationLog */

$this->title = 'Create Qfb Reconciliation Log';
$this->params['breadcrumbs'][] = ['label' => 'Qfb Reconciliation Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qfb-reconciliation-log-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
