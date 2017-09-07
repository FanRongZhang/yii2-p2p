<?php
namespace api\versions\v200\controllers;

use common\models\Article;
use yii\data\ActiveDataProvider;
use api\common\BaseActiveController;
use yii\filters\auth\QueryParamAuth;

/**
 * @author xiaoma <xiaomalover@gmail.com>
 * @since 2.0
 */
class ArticleController extends BaseActiveController
{
    public $modelClass = 'api\models\Article';

    public function actions()
    {
        $actions = parent::actions();
        //删除我们不需要的方法
        unset($actions['delete'], $actions['create']);

        //修改默认数据查询方法
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }


    /**
     * 数据查询方法
     * @return Object $dataProvider
     */
    public function prepareDataProvider()
    {
        /* @var $model Post */
        $model = new $this->modelClass;
        $query = $model::find();
        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $model->setAttribute('title', @$_GET['title']);
        $query->andFilterWhere(['like', 'title', $model->title]);

        return $dataProvider;
    }
}
