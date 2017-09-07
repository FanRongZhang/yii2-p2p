<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\QfbPtOrder */

$this->title = '平台账户充值';
$this->params['breadcrumbs'][] = ['label' => 'Qfb Pt Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qfb-pt-order-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
