<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\QfbBaseNavigation */

$this->title = '编辑底部导航: ' . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Qfb Base Navigations', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="create-form">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
