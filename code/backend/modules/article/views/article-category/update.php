<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ArticleCategory */

$this->title = 'Update Article Category: ';
$this->params['breadcrumbs'][] = ['label' => 'Article Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="article-category-update">

    <?php echo $this->render('_form', [
        'model' => $model,
        'categories' => $categories,
    ]) ?>

</div>
