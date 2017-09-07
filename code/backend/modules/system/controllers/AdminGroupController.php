<?php

namespace backend\modules\system\controllers;

use Yii;
use common\models\QfbAdminGroup;
use backend\modules\system\models\AdminGroupSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\service\MenuService;
use yii\base\Exception;

/**
 * AdminGroupController implements the CRUD actions for QfbAdminGroup model.
 */
class AdminGroupController extends Controller
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
     * Lists all QfbAdminGroup models.
     * @return mixed
     */
    public function actionIndex($msg = '')
    {
        $searchModel = new AdminGroupSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'msg' => $msg,
        ]);
    }

    /**
     * Displays a single QfbAdminGroup model.
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
     * Creates a new QfbAdminGroup model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new QfbAdminGroup();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing QfbAdminGroup model.
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
     * 编辑用户组权限
     * @param int $id 用户组id
     * @return mixed
     */
    public function actionUpdatepermission($id)
    {
        $model = $this->findModel($id);
        $menu = new MenuService();
        if(isset($_POST['AdminGroup']['permission']))
        {
            $per_value = [];
            $per_array=$_POST['AdminGroup']['permission'];
            if(count($per_array)>0)
            {
                foreach($per_array as $k=>$v)
                {
                    $p_v = 0;
                    foreach($v as $a=>$b)
                    {
                        $p_v+=intval($b);
                    }
                    if($p_v>0)
                    {
                        $per_value[$k]=$p_v;
                    }
                }
            }
            $model->permission=json_encode($per_value);
            if($model->save())
            {
                return $this->redirect('index');
            }
        }
        $menu_data = $menu->getMenus();
        return $this->render('permission',[
            'model'=>$model,
            'menu'=>$menu_data]);
    }

    /**
     * Deletes an existing QfbAdminGroup model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        try {
            $this->findModel($id)->delete();
        } catch (Exception $e) {
            return $this->redirect(['index','msg'=>'没有删除权限！']);
        }
        

        return $this->redirect(['index']);
    }

    /**
     * Finds the QfbAdminGroup model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbAdminGroup the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = QfbAdminGroup::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
