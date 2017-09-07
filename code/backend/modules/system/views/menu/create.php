<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\QfbMenu */

$this->title = 'Create Qfb Menu';
$this->params['breadcrumbs'][] = ['label' => 'Qfb Menus', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qfb-menu-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
