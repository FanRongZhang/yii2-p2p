<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>
use yii\helpers\Html;


$this->title=<?= $generator->generateString('Update '.Inflector::camel2words(StringHelper::basename($generator->modelClass)).'') ?> . ':  ' . $model-><?= $generator->getNameAttribute() ?>;
?>
<div class="create-form">

    <h1><?= "<?= " ?>Html::encode($this->title) ?></h1>

    <?= "<?= " ?>$this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
