<?php

use yii\helpers\Html;

$this->title = Yii::t('app', '添加代金券');
?>
<div class="create-form">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'members' => $members,
    ]) ?>

</div>
