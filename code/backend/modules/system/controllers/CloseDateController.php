<?php

namespace backend\modules\system\controllers;

use Yii;
use common\models\QfbDayOff;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\QfbAdmin;
use yii\filters\AccessControl;

/**
 * CloseDateController implements the CRUD actions for QfbDayOff model.
 */
class CloseDateController extends Controller
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
     * Lists all QfbDayOff models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => QfbDayOff::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Creates a new QfbDayOff model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $params = Yii::$app->request->post();
        $model = new QfbDayOff();
        if (!empty($params)) {
            $model->load($params);
            $model->time = strtotime($params['QfbDayOff']['time']);
            $model->operator = QfbAdmin::findOne(\Yii::$app->user->id)->true_name;
            if ($model->save()) {
                return $this->redirect(['index']);
            }

        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }

    }


    /**
     * Deletes an existing QfbDayOff model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the QfbDayOff model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbDayOff the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = QfbDayOff::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
