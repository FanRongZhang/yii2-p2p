<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Article */

$this->title = 'Update Article';
$this->params['breadcrumbs'][] = ['label' => 'Articles', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="article-update">

    <?php echo $this->render('_form', [
        'model' => $model,
        'categories' => $categories,
    ]) ?>

</div>
