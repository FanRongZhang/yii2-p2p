<?php

namespace backend\modules\system\controllers;

use Yii;
use common\models\QfbMenu;
use backend\modules\system\models\MenuSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * MenuController implements the CRUD actions for QfbMenu model.
 */
class MenuController extends Controller
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
     * Lists all QfbMenu models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MenuSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single QfbMenu model.
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
     * Creates a new QfbMenu model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new QfbMenu();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing QfbMenu model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing QfbMenu model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $data = QfbMenu::find()->where('parent_id = :pid', [":pid" => $id])->one();
        // var_dump($data);die;
        if ($data) {
            echo '<script>alert("该分类下有子类，不允许删除")</script>';
            echo '<script>window.location.href="/system/menu/index"</script>';exit;
        }

        if($this->findModel($id)->delete()){
            echo '<script>alert("删除成功")</script>';
            echo '<script>window.location.href="/system/menu/index"</script>';exit;
        } else {
            echo '<script>alert("删除失败！")</script>';
            echo '<script>window.location.href="/system/menu/index"</script>';exit;
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the QfbMenu model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbMenu the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = QfbMenu::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
