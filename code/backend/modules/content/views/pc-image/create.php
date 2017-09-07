<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\QfbPcImage */

$this->title = 'Create Qfb Pc Image';
$this->params['breadcrumbs'][] = ['label' => 'Qfb Pc Images', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qfb-pc-image-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
