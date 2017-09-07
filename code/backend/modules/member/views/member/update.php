<?php
use yii\helpers\Html;


$this->title=Yii::t('app', '编辑会员信息') . ':  ' . $model->vid;
?>
<div class="create-form">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
