<?php

namespace backend\modules\system\controllers;

use Yii;
use common\models\QfbVersion;
use backend\modules\system\models\AndroidVersionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
/**
 * AndroidVersionController implements the CRUD actions for QfbVersion model.
 */
class AndroidVersionController extends Controller
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
     *图片上传、富文本
     * @inheritdoc
     */
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
            'upload-imperavi' => [
                'class' => 'trntv\filekit\actions\UploadAction',
                'fileparam' => 'file',
                'responseUrlParam'=> 'filelink',
                'multiple' => false,
                'disableCsrf' => true
            ],
        ];
    }

    /**
     * Lists all QfbVersion models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AndroidVersionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }


    /**
     * Creates a new QfbVersion model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new QfbVersion();
        $model->create_time = time();
        $model->type = 1;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing QfbVersion model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Finds the QfbVersion model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbVersion the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = QfbVersion::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
