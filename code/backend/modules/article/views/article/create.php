<?php
/* @var $this yii\web\View */
/* @var $model common\models\Article */
/* @var $categories common\models\ArticleCategory[] */

$this->title =  'Article';
$this->params['breadcrumbs'][] = ['label' => 'Articles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="article-create">

    <?php echo $this->render('_form', [
        'model' => $model,
        'categories' => $categories
    ]) ?>

</div>
