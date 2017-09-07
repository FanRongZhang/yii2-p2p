<?php

namespace backend\modules\market\controllers;

use Yii;
use common\models\QfbExperienceMoney;
use backend\modules\market\models\ExperienceSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\service\LevelService;
use yii\filters\AccessControl;

/**
 * ExperienceController implements the CRUD actions for QfbExperienceMoney model.
 */
class ExperienceController extends Controller
{
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
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all QfbExperienceMoney models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ExperienceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single QfbExperienceMoney model.
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
     * Creates a new QfbExperienceMoney model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new QfbExperienceMoney();

        $post = Yii::$app->request->post();
        if ($post){
            $post['QfbExperienceMoney']['create_time'] = time();
        }
        
        if ($model->load($post) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            
            return $this->render('create', [
                'model' => $model,
                'members' => $members,
            ]);
        }
    }

    /**
     * Updates an existing QfbExperienceMoney model.
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
     * Deletes an existing QfbExperienceMoney model.
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
     * Finds the QfbExperienceMoney model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbExperienceMoney the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = QfbExperienceMoney::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
