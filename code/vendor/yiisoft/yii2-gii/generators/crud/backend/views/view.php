<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\DetailView;

?>
<div class="detail-view">

    <p>
        <?= "<?= " ?>Html::a(Yii::t('app','Update'), ['update', <?= $urlParams ?>], ['class' => 'btn btn-primary']) ?>
        <?= "<?= " ?>Html::a(Yii::t('app','Delete'), ['delete', <?= $urlParams ?>], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => <?= $generator->generateString('Are you sure you want to delete this item?') ?>,
                'method' => 'post',
            ],
        ]) ?>
        <?= "<?= " ?>Html::a(Yii::t('app','Goback list'), ['index'], ['class' => 'btn btn-primary']) ?>
        
    </p>

    <?= "<?= " ?>DetailView::widget([
        'model' => $model,
        'template' => '<tr><th>{label}</th><td>{value}</td></tr>',
        'attributes' => [
<?php
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        echo "            '" . $name . "',\n";
    }
} else {
    foreach ($generator->getTableSchema()->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        echo "            '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
    }
}
?>
        ],
    ]) ?>

</div>
