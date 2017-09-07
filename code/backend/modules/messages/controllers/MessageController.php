<?php

namespace backend\modules\messages\controllers;

use Yii;
use common\models\QfbMessage;
use backend\modules\messages\models\MessageSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * MessageController implements the CRUD actions for QfbMessage model.
 */
class MessageController extends Controller
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
     * Lists all QfbMessage models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MessageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single QfbMessage model.
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
     * Creates a new QfbMessage model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new QfbMessage();
        if ($model->load(Yii::$app->request->post())) {
            $post = Yii::$app->request->post();
            if($post['QfbMessage']['send_ob_value0']) {
                if ($post['QfbMessage']['send_ob_value0'][0] == '0') {
                    unset($post['QfbMessage']['send_ob_value0'][0]);
                }
                $model->send_ob_value = json_encode($post['QfbMessage']['send_ob_value0']);
            }
          if ($post['QfbMessage']['send_ob_value1']) {
              $send_ob_value1 = explode(',',$post['QfbMessage']['send_ob_value1']);
              $model->send_ob_value = json_encode($send_ob_value1);
          }
            $model->send_mode = 0;
            $model->create_time = time();
            $model->send_time = time();
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }else{
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing QfbMessage model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {

            $post = Yii::$app->request->post();
            if($post['QfbMessage']['send_ob_value0']) {
                if ($post['QfbMessage']['send_ob_value0'][0] == '0') {
                    unset($post['QfbMessage']['send_ob_value0'][0]);
                }
                $model->send_ob_value = json_encode($post['QfbMessage']['send_ob_value0']);
            }
            if ($post['QfbMessage']['send_ob_value1']) {
                $send_ob_value1 = explode(',',$post['QfbMessage']['send_ob_value1']);
                $model->send_ob_value = json_encode($send_ob_value1);
            }
            $model->send_mode = 0;
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        } else {
            if ($model->send_ob === 0) {
                $model->send_ob_value0 = $model->send_ob_value;
            } elseif($model->send_ob === 1) {
                $model->send_ob_value1 = implode(',',json_decode($model->send_ob_value,true));
            }
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing QfbMessage model.
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
     * Finds the QfbMessage model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbMessage the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = QfbMessage::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
