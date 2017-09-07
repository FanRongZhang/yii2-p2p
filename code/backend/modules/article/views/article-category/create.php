<?php
/* @var $this yii\web\View */
/* @var $model common\models\ArticleCategory */
/* @var $categories common\models\ArticleCategory[] */

$this->title = 'Create Article Category';
$this->params['breadcrumbs'][] = ['label' => 'Article Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="article-category-create">

    <?php echo $this->render('_form', [
        'model' => $model,
        'categories' => $categories
    ]) ?>

</div>
