<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\QfbAboutMe */

$this->title = 'Update Qfb About Me: ' . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Qfb About Mes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="qfb-about-me-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
