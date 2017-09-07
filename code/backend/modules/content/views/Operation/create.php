<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\QfbOperation */

$this->title = 'Create Qfb Operation';
$this->params['breadcrumbs'][] = ['label' => 'Qfb Operations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qfb-operation-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
