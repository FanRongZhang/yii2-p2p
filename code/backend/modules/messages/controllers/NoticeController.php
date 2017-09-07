<?php

namespace backend\modules\messages\controllers;

use Yii;
use common\models\QfbNotice;
use backend\modules\messages\models\NoticeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\widgets\datepicker\DatePicker;
use yii\filters\AccessControl;

/**
 * NoticeController implements the CRUD actions for QfbNotice model.
 */
class NoticeController extends Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'upload-imperavi' => [
                'class' => 'trntv\filekit\actions\UploadAction',
                'fileparam' => 'file',
                'responseUrlParam'=> 'filelink',
                'multiple' => false,
                'disableCsrf' => true
            ],
        ];
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['get'],
                ],
            ],
        ];
    }

    /**
     * Lists all QfbNotice models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new NoticeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single QfbNotice model.
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
     * Creates a new QfbNotice model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new QfbNotice();
        if ($model->load(Yii::$app->request->post())) {
            $model->send_time = time();
            $model->create_time = time();
            $model->is_up = 1;
            $model->is_send = 1;
            $model->show_end_time = strtotime(Yii::$app->request->post()['QfbNotice']['show_end_time']);
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing QfbNotice model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $params = Yii::$app->request->post();
        $model = $this->findModel($id);

        if ($model->load($params)) {
            $model->show_end_time = strtotime($params['QfbNotice']['show_end_time']);
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        }else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing QfbNotice model.
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
     * Finds the QfbNotice model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbNotice the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = QfbNotice::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
