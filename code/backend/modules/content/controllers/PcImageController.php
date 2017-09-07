<?php

namespace backend\modules\content\controllers;

use Yii;
use common\models\QfbPcImage;
use backend\modules\content\models\PcImageSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PcImageController implements the CRUD actions for QfbPcImage model.
 */
class PcImageController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['get'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'upload' => [
                'class' => 'trntv\filekit\actions\UploadAction',
                'deleteRoute' => 'upload-delete'
            ],
            'upload-delete' => [
                'class' => 'trntv\filekit\actions\DeleteAction'
            ],
        ];
    }

    /**
     * Lists all QfbPcImage models.
     * @return mixed
     */
    public function actionIndex()
    {
        $type=Yii::$app->request->get('type');
        $searchModel = new PcImageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$type);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'type' => $type
        ]);
    }

    /**
     * Displays a single QfbPcImage model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new QfbPcImage model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new QfbPcImage();

        $model->type=Yii::$app->request->get('type');

        $model->time=time();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing QfbPcImage model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing QfbPcImage model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $type= Yii::$app->request->get('type');
        //var_dump($type);exit;
        $this->findModel($id)->delete();

        return $this->redirect(["index",'type'=>$type]);
    }

    /**
     * Finds the QfbPcImage model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbPcImage the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = QfbPcImage::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
