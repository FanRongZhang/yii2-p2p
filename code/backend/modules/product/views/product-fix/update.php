<?php
use yii\helpers\Html;


$this->title=Yii::t('app', '编辑') . ':  ' . $model->id;
?>
<div class="create-form">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'profitmodel' => $profitmodel,
        'detailmodel' => $detailmodel,
        'category' => $category,
        'agreemodel' => $agreemodel,
        'warranty' => $warranty,
        'agreements' => $agreements
    ]) ?>

</div>
