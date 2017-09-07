<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="search-form">

    <?= "<?php " ?>$form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

<?php
$count = 0;
foreach ($generator->getColumnNames() as $attribute) {
    if (++$count < 6) {
        if($count%5==0)
            echo "<div class='clear'></div>\n";
        echo "    <?= " . $generator->generateActiveSearchField($attribute) . " ?>\n\n";
    } else {
        if($count%5==0)
            echo "<!-- <div class='clear'></div> --> \n";
        echo "    <?php // echo " . $generator->generateActiveSearchField($attribute) . " ?>\n\n";
    }
}
?>
    <div class="form-group search-button">
        <?= "<?= " ?>Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= "<?= " ?>Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?= "<?php " ?>ActiveForm::end(); ?>

</div>
