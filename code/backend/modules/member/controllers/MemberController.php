<?php

namespace backend\modules\member\controllers;

use backend\modules\member\models\MoneyLogSearch;
use backend\modules\member\models\OrderFixSearch;
use common\models\QfbOrderFix;
use common\models\Vmember;
use common\service\MoneyLogService;
use common\service\OrderFixService;
use Yii;
use common\models\QfbMember;
use backend\modules\member\models\Membersearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\service\LoginRecordService;

/**
 * MemberController implements the CRUD actions for QfbMember model.
 */
class MemberController extends Controller
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
     * Lists all QfbMember models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new Membersearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single QfbMember model.
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
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * 会员资产
     */
    public function actionWealth($id){
        $this->layout="//member";
        return $this->render('wealth', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * 在投项目
     */
    public function actionProduct($id){
        $this->layout="//member";
        $searchModel =new OrderFixSearch();
        $dataProvider = $searchModel->search($id);
        return $this->render('product', [
            'model' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * 资产明细
     */
    public function actionMoneyInfo($id){
        $searchModel =new MoneyLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('money-info', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new QfbMember model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new QfbMember();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing QfbMember model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $params = Yii::$app->request->post();
        if (!empty($params)) {


            $memModel = new \common\service\MemberService();
            $memModel->updateMemberLevel($id,$params['Vmember']['vlevel']);
            $ucModel = new \common\service\UcenterService();
            $ucModel->updateMemberActive($id,$params['Vmember']['vstatus']);
            //修改状态 用户密码输入次数超过9次 start
            $login = new LoginRecordService($id);
            $count_record = $login->countFailLogin($id);
            if ($count_record > 8) {
                $login->deleteSuccessRecord($id);
            }
            // end
            return $this->redirect(['view', 'id' => $model->vid]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing QfbMember model.
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
     * Finds the QfbMember model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbMember the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Vmember::find()->where(['vid'=>$id])->one()) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
