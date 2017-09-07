<?php
namespace backend\modules\system\controllers;

use yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\QfbSystemMaintenance as Maintenance;

class MaintenanceController extends Controller
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

    public function actionIndex()
    {
        $model = Maintenance::find()->one();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->render('index', ['model' => $model]);
        }
        return $this->render('index', ['model' => $model]);
    }
}
