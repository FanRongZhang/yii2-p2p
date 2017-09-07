<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\QfbAboutMe */

$this->title = 'Create Qfb About Me';
$this->params['breadcrumbs'][] = ['label' => 'Qfb About Mes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qfb-about-me-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
