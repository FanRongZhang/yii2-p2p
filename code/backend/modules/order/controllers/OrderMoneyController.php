<?php

namespace backend\modules\order\controllers;

use api\common\helpers\ReseponseCode as Code;
use common\service\ApiService;
use common\service\OrderService;
use Yii;
use common\models\QfbOrder;
use backend\modules\order\models\OrderMoneySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * OrderMoneyController implements the CRUD actions for QfbOrder model.
 */
class OrderMoneyController extends Controller
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
        $searchModel = new OrderMoneySearch();
        $params = Yii::$app->request->queryParams;
        $params['OrderMoneySearch']['type'] = $params['type'];

        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'type'  => $params['type'],
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all QfbOrder models.
     * @return mixed
     */
    public function actionRollOut()
    {
        $searchModel = new OrderMoneySearch();
        $params = Yii::$app->request->queryParams;
        $params['OrderMoneySearch']['type'] = $params['type'];

        $dataProvider = $searchModel->search($params);

        return $this->render('rollout', [
            'type'  => $params['type'],
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'data'      => $params['OrderMoneySearch'],
        ]);
    }

    //修改订单状态通过审核
    public function actionPass(){
        try {
            $orderService=new OrderService();
            $or_result=$orderService->changeOrderCheck(Yii::$app->request->post('id'),5);
            if($or_result==0){
                throw new Exception('订单记录错误');
            }
        } catch (Exception $e) {
            return ApiService::error(Code::COMMON_ERROR_CODE,$e->getMessage());
        }
        echo json_encode(['code'=>Code::HTTP_OK, 'msg' => '通过审核！']);
    }

    /**
     * 拒绝订单
     * @param integer $id
     *
     */
    public function actionRemove()
    {
        $order = $this->findModel(Yii::$app->request->post()['id']);

        $result = $this->WithdrawalsError($order,4);//提现失败返还

        if ($result['code']==Code::HTTP_OK) {
            echo json_encode(['code'=>Code::HTTP_OK, 'msg' => '操作成功！']);
        } else {
            echo json_encode(['code'=>Code::COMMON_ERROR_CODE, 'msg' => '操作失败！']);
        }
    }

    /*
     * 提现失败返还
     * */
    public function WithdrawalsError($order,$status){
        $tran = Yii::$app->db->beginTransaction();
        try {
            $orderService = new OrderService();
            $or_result=$orderService->changeOrderCheck($order['id'],$status);

            if($or_result==0){
                throw new Exception('订单记录错误');
            }

            //修改money添加日志
            $result=$orderService->changeReMoney($order['price'],$order['member_id']);
            if($result['code']!=Code::HTTP_OK) {
                throw new Exception($result['msg']);
            }
            $tran->commit();
        } catch (Exception $e) {
            $tran->rollback();
            return ApiService::error(Code::COMMON_ERROR_CODE,$e->getMessage());
        }
        return ApiService::success(Code::HTTP_OK,'提现失败，金额返还',$order);
    }


    /*
     *条件内订单通过审核
     */
    public function actionAudit()
    {
        $searchModel = new OrderMoneySearch();
        $data['OrderMoneySearch']  = Yii::$app->request->get();

        //要订单的id
        $ids   = $searchModel->searchAll($data)->select('qfb_order.id')->all();
        if($ids){
            foreach($ids as $val){
                $res[] = $val['id'];
            }
            $id = implode(',',$res);

            $sWhere = "`id` IN ({$id})";

            $transfer = QfbOrder::updateAll(['is_check' => 5],$sWhere);
            if($transfer){
                echo json_encode(['code'=>Code::HTTP_OK, 'msg' => '审核通过！']);
                exit;
            }
        }

        echo json_encode(['code'=>Code::COMMON_ERROR_CODE, 'msg' => '审核失败！检查是否合法']);
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
