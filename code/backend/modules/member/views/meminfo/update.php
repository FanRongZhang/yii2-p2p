<?php
use yii\helpers\Html;


$this->title=Yii::t('app', 'Update Qfb Member') . ':  ' . $model->id;
?>
<div class="create-form">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
