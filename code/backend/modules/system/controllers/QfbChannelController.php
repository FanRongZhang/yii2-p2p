<?php

namespace backend\modules\system\controllers;

use Yii;
use common\models\QfbChannel;
use backend\modules\system\models\QfbChannelSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
/**
 * QfbChannelController implements the CRUD actions for QfbChannel model.
 */
class QfbChannelController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all QfbChannel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new QfbChannelSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new QfbChannel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new QfbChannel();
        $model->create_time = time();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing QfbChannel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if (Yii::$app->request->post()) {
            $params = Yii::$app->request->post();
            if ($params ["QfbChannel"]['is_default']){
                $all = new QfbChannel();
                $result = $all->updateAll(['is_default'=>0],['is_default'=>1]);
            }
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['index']);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    //设为默认
    public function actionDefault(){
        $id = Yii::$app->request->get('id');
        if ($id) {
            //查询之前的默认通道
            $beforeDefault = QfbChannel::find()->where(['=','is_default',1])->one();
            $beforeDefault->is_default = 0;
            $beforeDefault->save();
        }
        $model = $this->findModel($id);
        $model->is_default = 1;
        $model->save();
        $this->redirect(['index']);
    }

    /**
     * Finds the QfbChannel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbChannel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = QfbChannel::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
