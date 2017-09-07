<?php

namespace backend\modules\order\controllers;

use Yii;
use common\models\QfbReconciliationLog;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * OrderCurrentController implements the CRUD actions for QfbReconciliationLog model.
 */
class ReconciliationController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    /*过滤不需要登录的页面[
                        'actions' => ['index'],
                        'allow' => true,
                    ],*/
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all QfbReconciliationLog models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OrderCurrentSearch();
        $params = Yii::$app->request->queryParams;
        $params['OrderCurrentSearch']['type'] = $params['type'];

        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'type'  => $params['type'],
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    
}
