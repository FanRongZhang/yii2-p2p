<?php

namespace backend\modules\order\controllers;

use common\models\QfbBank;
use common\service\BankService;
use Yii;
use common\models\QfbOrder;
use backend\modules\order\models\OrderCurrentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * OrderCurrentController implements the CRUD actions for QfbOrder model.
 */
class OrderCurrentController extends Controller
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
     * Lists all QfbOrder models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OrderCurrentSearch();
        $params = Yii::$app->request->queryParams;
        $params['OrderCurrentSearch']['type'] = $params['type'];

        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'type'  => $params['type'],
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**解绑银行卡*/
    public function actionRemoveBank()
    {
        $model = new QfbBank();

        $queryParams=Yii::$app->request->post();
        if($queryParams){
            $mobile = $queryParams['QfbBank']['mobile'];
            $no     = $queryParams['QfbBank']['no'];

            $service = new BankService();
            $result =   $service->delete($mobile,$no);//修改卡为删除状态

            if($result){
                echo '<script>alert("取消绑定成功")</script>';
                echo '<script>window.location.href="/order/order-current/remove-bank"</script>';exit;
            }else{
                echo '<script>alert("取消绑定失败,请确认银行信息")</script>';
                echo '<script>window.location.href="/order/order-current/remove-bank"</script>';exit;
            }
        }
        return $this->render("removebank",["model"=>$model]);
    }

    /**
     * Finds the QfbOrder model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbOrder the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = QfbOrder::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
