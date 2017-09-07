<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\QfbNavigation */

$this->title = '添加导航栏目';
$this->params['breadcrumbs'][] = ['label' => 'Qfb Navigations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="create-form">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
