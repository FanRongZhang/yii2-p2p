<?php
use yii\helpers\Html;


$this->title=Yii::t('app', '更新报错类型') . ':  ' . $model->id;
?>
<div class="create-form">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
