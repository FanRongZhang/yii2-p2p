<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\ArticleSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Articles';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="article-index">

    <div id="advanced-search-form" style="display: none;"><?php echo $this->render('_search', ['model' => $searchModel]); ?></div>

    <p>
        <?= Html::a(Yii::t('article','Create Article'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Batch Delete', 'javascript:void(0);', ['class' => 'btn btn-danger', 'id' => 'batchDelete']) ?>
        <?= Html::a('Advanced Search', 'javascript:void(0);', ['class' => 'btn btn-info', 'id' => 'search']) ?>
    </p>

    <?php echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\CheckboxColumn'],
            'id',
            [
                'attribute' => 'thumbnail_path',
                'content'=>function($model){
                    return Html::img(\Yii::$app->fileStorage->baseUrl."/".$model->thumbnail_path,['style'=>'width:100px;height:100px;']);
                },
            ],
            'title',
            [
                'attribute'=>'category_id',
                'value'=>function ($model) {
                    return $model->category ? $model->category->title : null;
                },
                'filter'=>\yii\helpers\ArrayHelper::map(\common\models\ArticleCategory::find()->all(), 'id', 'title')
            ],
            [
                'attribute'=>'author_id',
                'value'=>function ($model) {
                    return $model->author->username;
                }
            ],

            'published_at:datetime',
            'created_at:datetime',

            // 'updated_at',

            [
                'label'=>'Operation',
                'format'=>'raw',
                'value' => function($data){
                    $viewUrl = Url::to(['view?id='.$data->id]);
                    $updateUrl = Url::to(['update?id='.$data->id]);
                    $deleteUrl = Url::to(['delete?id='.$data->id]);
                    return "<div class='btn-group'>".
                    Html::a('View', $viewUrl, ['title' => 'View','class'=>'btn btn-sm btn-info']).
                    Html::a('Update', $updateUrl, ['title' => 'Update','class'=>'btn btn-sm btn-primary']).
                    Html::a('Delete', $deleteUrl, ['title' => 'Delete','class'=>'btn btn-sm btn-danger','data-method'=>'post', 'data-confirm'=>'Are you sure you want to delete this item?']).
                    "</div>";
                },
                'options' => ['style' => 'width:175px;'],
            ],
        ]
    ]); ?>

</div>

<?php
$urlBatchDelete = \yii\helpers\Url::to(['batch-delete']);
$message = 'Are you sure to batch delete?';
$confirmBtn = 'Ok';
$cancleBtn = 'Cancle';
$js = <<<JS
jQuery(document).ready(function() {
    $("#search").click(function(){
        $("#advanced-search-form").toggle();
    });

    $("#batchDelete").click(function() {
        bootbox.confirm(
            {
                message: "{$message}",
                buttons: {
                    confirm: {
                        label: "{$confirmBtn}"
                    },
                    cancel: {
                        label: "{$cancleBtn}"
                    }
                },
                callback: function (confirmed) {
                    if (confirmed) {
                        var keys = $(".grid-view").yiiGridView("getSelectedRows");
                        $.ajax({
                            type: "POST",
                            url: "{$urlBatchDelete}",
                            dataType: "json",
                            data: {ids: keys}
                        });
                    }
                }
            }
        );
    });
});
JS;
$this->registerJs($js, \yii\web\View::POS_END);
