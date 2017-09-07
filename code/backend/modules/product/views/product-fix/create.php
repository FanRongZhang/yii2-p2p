<?php

use yii\helpers\Html;

$this->title = Yii::t('app', '添加定期理财产品');
?>
<div class="create-form">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'profitmodel' => $profitmodel,
        'detailmodel' => $detailmodel,
        'agreemodel' => $agreemodel,
        'agreements' => $agreements,
        'warranty' => $warranty,
        'category' => $category
    ]) ?>

</div>
