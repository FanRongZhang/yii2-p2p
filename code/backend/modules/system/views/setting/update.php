<?php
use yii\helpers\Html;


$this->title=Yii::t('app', '修改全局设置') . ':  ' . $model->id;
?>
<div class="create-form">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
