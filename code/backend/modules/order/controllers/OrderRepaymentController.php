<?php

namespace backend\modules\order\controllers;

use common\models\QfbMember;
use common\models\QfbMemberMoney;
use common\models\QfbOrderFix;
use common\models\QfbOrderRepayment;
use common\models\QfbProduct;
use common\models\QfbPtAccount;
use common\service\AssetService;
use common\service\OrderFixService;
use common\service\OrderRepaymentService;
use common\service\ProductService;
use common\toolbox\Tool;
use Yii;
use common\models\QfbOrder;
use backend\modules\order\models\OrderRepaymentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\Pagination;

/**
 * OrderMoneyController implements the CRUD actions for QfbOrder model.
 */
class OrderRepaymentController extends Controller
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
        $searchModel = new OrderRepaymentSearch();
        $params = Yii::$app->request->queryParams;

        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single QfbOrderFix model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($repayId)
    {
        $data = QfbOrderFix::find()
            ->joinWith('member')
            ->joinWith('repayment')
            ->where([QfbOrderRepayment::tableName().'.id' => $repayId])
            ->andFilterWhere(['in', QfbOrderFix::tableName().'.status', [0,1,2,3]])
            ->select([QfbOrderFix::tableName().'.*',QfbMember::tableName().'.account',
                QfbOrderRepayment::tableName().'.money as order_money',QfbOrderRepayment::tableName().'.invest_day']);
        $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' => '10']);
       $model = $data->offset($pages->offset)->limit($pages->limit)->all();

       return $this->render('view',[
           'model' => $model,
           'pages' => $pages
       ]);
    }

    /**
     * 代偿
     */
    public function actionCompensatory()
    {
        $repayId = Yii::$app->request->get('repay_id', 0);
        $nowTime = time();

        $platformModel = QfbPtAccount::findOne(['id'=>Yii::$app->params['platform_id']]);

        $orderRepayment = QfbOrderRepayment::findOne(['id'=>$repayId, 'status'=>0, 'is_commutation'=>0, 'is_overdue' == 1]);

        $compensatoryMoney = $orderRepayment->money+$orderRepayment->interest;

        if($platformModel->money < $compensatoryMoney){
            echo 2;exit;
        }

        if($orderRepayment){
            $model = QfbProduct::findOne(['id'=>$orderRepayment->product_id]);
            if($orderRepayment->periods == 1 && $orderRepayment->is_end == 1){
                if($model->profit_day == 10){
                    $profitDay = $model->finish_time+$orderRepayment->invest_day*Tool::$dayTime;
                }elseif($model->profit_day == 11){
                    $profitDay = $model->finish_time+($orderRepayment->invest_day+1)*Tool::$dayTime;
                }elseif($model->profit_day == 20){
                    $profitDay = $model->finish_time+($orderRepayment->invest_day)*Tool::$dayTime;
                }else{
                    $profitDay = $model->finish_time+($orderRepayment->invest_day+1)*Tool::$dayTime;
                }
            }else{
                if($model->profit_day == 10){
                    $profitDay = $model->finish_time+Tool::$dayTime*(($orderRepayment->periods-1)*Tool::$periodsDay+$orderRepayment->invest_day);
                }elseif($model->profit_day == 11){
                    $profitDay = $model->finish_time+Tool::$dayTime*(($orderRepayment->periods-1)*Tool::$periodsDay+$orderRepayment->invest_day+1);
                }elseif($model->profit_day == 20){
                    $profitDay = $model->finish_time+Tool::$dayTime*(($orderRepayment->periods-1)*Tool::$periodsDay+$orderRepayment->invest_day);
                }else{
                    $profitDay = $model->finish_time+Tool::$dayTime*(($orderRepayment->periods-1)*Tool::$periodsDay+$orderRepayment->invest_day+1);
                }
            }

            $profitTime = Tool::endTime($profitDay);

            if($nowTime > $profitTime){
                $orderRepayment->is_commutation = 1;
                $orderRepayment->status = 1;
                $orderRepayment->save();

                $platformModel->money -= $compensatoryMoney;
                $platformModel->commutation_money += $compensatoryMoney;

                $platformModel->save();

                echo 1;exit;
            }

            echo 3;exit;

        }
        echo 0;
        exit;
    }

    /**
     * 确认或取消还款
     */
    public function actionOk()
    {
        $id = Yii::$app->request->get('id', 0);
        $type = Yii::$app->request->get('type', 0);
        if(!empty($id)){
            //todo::确认还款逻辑
            if($type == 1){
                $this->sure($id);
            }else{
                $this->cancel($id);
            }
        }
        $this->redirect(['index']);
    }

    /**
     * 取消还款
     * @int $id 还款订单id
     */
    public function cancel($id)
    {
        //todo::取消还款逻辑
    }

    /**
     * 确认还款
     * @int $id 还款订单id
     */
    public function sure($id)
    {

        $hkyh = \Yii::$app->Hkyh;

        //还款交易确认
        $serviceName = 'SYNC_TRANSACTION';

        $orderRepayment = QfbOrderRepayment::find()
            ->where(['id'=>$id])
            ->asArray()
            ->one();

        $product = QfbProduct::find()->where(['id'=>$orderRepayment['product_id']])->asArray()->one();
        $orderFix = QfbOrderFix::find()->where(['product_id'=>$product['id'], 'status'=>2])->asArray()->all();

        $status = true;

        foreach($orderFix as $k=>$v){

            $interval = date_diff(date_create(date('Y-m-d',$product['finish_time'])), date_create(date('Y-m-d',$v['create_time'])));

            if($product['profit_day'] == 10){
                $profitDay = $interval->days+$product['invest_day'];
            }elseif($product['profit_day'] == 11){
                $profitDay = $interval->days+$product['invest_day'];
            }elseif($product['profit_day'] == 20){
                $profitDay = $product['invest_day'];
            }else{
                $profitDay = $product['invest_day'];
            }

            $money = $v['money']*($v['year_rate']/100)*$profitDay/Tool::yearDay();

            $income = Tool::moneyCalculate($money);

            $details = [
                [
                    // 业务类型
                    'bizType'=>'REPAYMENT',
                    // 预处理流水号
                    'freezeRequestNo'=>$orderRepayment['sn'],
                    // 出库用户编号
                    'sourcePlatformUserNo'=>$product['member_id'],
                    // 收款用户编号
                    'targetPlatformUserNo'=>$v['member_id'],
                    // 扣除总额
                    'amount'=>$v['money'], //测试
                    // 利息
                    'income'=>$income,
                ]
            ];


            $assetService = new AssetService();
            // 请求流水号
            $reqData['requestNo'] = $assetService->getBindSn('QRHK');
            // 交易类型
            $reqData['tradeType'] = 'REPAYMENT';
            // 标的号
            $reqData['projectNo'] = $product['sn'];
            // 业务明细
            $reqData['details'] = $details;
            $result = $hkyh->createPostParam($serviceName,$reqData);
            if($result){
                $data = json_decode($result['data']);
                if($data->code == '0'){
                    if($data->status == 'SUCCESS'){
                        if($data->transactionStatus != 'SUCCESS'){
                            $status = false;
                            $msg = '标的号为'.$product['sn'].'且预处理流水号为'.$v['sn'].'的还款失败';
                        }else{
                            $msg = '标的号为'.$product['sn'].'且预处理流水号为'.$v['sn'].'的还款成功';
                        }
                    }else{
                        $msg = '标的号为'.$product['sn'].'且预处理流水号为'.$v['sn'].'的还款状态不对成功';
                    }
                }else{
                    $msg = '标的号为'.$product['sn'].'且预处理流水号为'.$v['sn'].'----错误代码'.$data->errorMessage;;
                }
            } else{
                $msg = '标的号为'.$product['sn'].'且预处理流水号为'.$v['sn'].'的请求银行失败';
            }

            $msg = $msg."\r\n银行请求数据：\r\n".var_export($result, true);
            $this->serviceWirteLog('confirm-repayment',$msg);
        }

        if($status == true){
            $productModel = QfbProduct::findOne($product['id']);
            $productModel->status = 8;
            if(!$productModel->save()){
                $this->serviceWirteLog('confirm-repayment','标的号为'.$product['sn'].'的产品表修改状态失败');
            }

            $orderRepaymentModel = QfbOrderRepayment::findOne(['product_id'=>$product['id']]);
            $orderRepaymentModel->status = 2;
            $orderRepaymentModel->repay_money = $orderRepaymentModel->money+$orderRepaymentModel->interest;
            if(!$orderRepaymentModel->save()){
                $this->serviceWirteLog('confirm-repayment','标的号为'.$product['sn'].'且预处理流水号为'.$v['sn'].'的还款订单表修改状态失败');
            }
        }

        return true;
    }

    protected function serviceWirteLog($fileName, $content='')
    {
        $notifyLog = \Yii::$app->getRuntimePath() . '/logs/' . $fileName;

        if (!file_exists($notifyLog)) {
            touch($notifyLog);
        }
        $fp = fopen($notifyLog, "a");
        fwrite($fp, $content);
        flock($fp, LOCK_UN);
        fclose($fp);
    }


    /**
     * Finds the QfbOrder model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbOrder the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($productId)
    {
        if (($model = QfbOrderFix::findAll(['product_id'=>$productId])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
