<?php
namespace console\controllers;

use common\models\QfbBank;
use common\models\QfbOrderOverdue;
use common\models\QfbOrderRepaymentExtend;
use common\models\QfbPtAccount;
use common\models\QfbTemporaryCommission;
use common\models\QfbReconciliationLog;
use common\models\QfbTemporaryRecharge;
use common\models\QfbTemporaryReconciliation;
use common\models\QfbTemporaryTransaction;
use common\models\QfbTemporaryWithdraw;
use common\service\MemberMoneyService;
use common\service\OrderFixService;
use common\toolbox\File;
use common\toolbox\Tool;
use yii;
use yii\console\Controller;
use common\models\QfbOrder;
use common\models\QfbMemberMoney;
use common\models\QfbMoneyLog;
use common\models\QfbPlatformIncome;
use common\models\QfbOrderRepayment;
use common\service\MemberService;
use common\models\QfbProduct;
use common\models\QfbOrderFix;
use common\models\QfbMember;
use common\models\QfbOrderRepaymentLog;
use common\service\HkyhService;
use yii\console\Exception;
use common\service\LogService;
use common\service\MongoService\HkyhConsoleService as HkyhMongoService;
use common\models\QfbPtOrder;

class HkyhController extends Controller
{

    public $time_limit = 5;
    public $terminally_day = 30;
    public $overdue_rate = 0.003;

    /**
     * 查询提现接口
     */
    public function actionFindWithdraw()
    {
        $start_time = time();

        $funcName = 'find_with_draw';

        if($this->getLockFunc($funcName) === 1){
            exit;
        }
        $this->setLockFunc($funcName);

        $hkyh = \Yii::$app->Hkyh;

        // 对账
        $serviceName = 'QUERY_TRANSACTION';

        $reqData['transactionType'] = 'WITHDRAW';

        $optinTime = time() - 60 * $this->time_limit;

        //查询提现待处理的数据
        $data = QfbOrder::find()
            ->select('id,sn,member_id,fee,price,is_check')
            ->where(['type' => 2])
            ->andFilterWhere(['in', 'is_check', [0,3]])
            ->andFilterWhere(['<=', 'create_time', $optinTime])
            ->limit(100)
            ->orderBy('id asc')
            ->asArray()
            ->all();

        // 遍历订单
        foreach ($data as $key => $value) {

            $msg = '';
            $status = true;
            $reqData['requestNo'] = $value['sn'];
            $result = $hkyh->createPostParam($serviceName, $reqData);

            $trans = \Yii::$app->db->beginTransaction();

            try {

                // 判断是否提交成功
                if (trim(strtoupper($result['status'])) != 'SUCCESS')
                    throw new \Exception('请求流水号：' . $value['sn'] . ', 状态信息：' . $result['message']);

                $data = json_decode($result['data'], true);

                // 提现失败
                if (trim(strtoupper($data['status'])) != 'SUCCESS'){

                    unset($update_order_arr);
                    if ($data['errorCode'] == '100007') {
                        $msg = '无此交易';
                        $update_order_arr['is_check'] = 4;
                        $update_order_arr['complete_time'] = time();
                    } else {
                        $msg = '提现失败';
                        $update_order_arr['is_check'] = 2;
                        $update_order_arr['complete_time'] = time();

                        $msg = '银行处理失败---错误代码：' . $data['errorCode'] . ',错误信息' . $data['errorMessage'];
                    }

                    $update = QfbOrder::updateAll($update_order_arr, ['id'=>$value['id']]);
                    if(empty($update))
                        throw new \Exception('更新提现订单失败');
                }
                // 提现正常
                else {

                    // 遍历提现明细列表
                    foreach ($data['records'] as $d_key => $d_val) {

                        // 交易成功-删减冻结金额
                        if (trim(strtoupper($d_val['status'])) == 'SUCCESS') {

                            // 更新提现订单
                            unset($update_order_arr);
                            $update_order_arr['is_check'] = 1;
                            $update_order_arr['complete_time'] = time();
                            $update = QfbOrder::updateAll($update_order_arr, ['id' => $value['id']]);

                            if (empty($update))
                                throw new \Exception('更新提现订单失败');

                            // 更新用户金额和冻结金额
                            $memberMoneyModel = QfbMemberMoney::find()->select('lock_money')->where(['member_id' => $value['member_id']])->one();

                            unset($update_money_arr);
                            $update_money_arr['lock_money'] = $memberMoneyModel->lock_money-$value['price'];
                            $update = QfbMemberMoney::updateAll($update_money_arr, ['member_id'=>$value['member_id']]);
                            if (empty($update))
                                throw new \Exception('解冻冻结金额有误');

                            //添加平台收益
                            $platformIncomeModel = new QfbPlatformIncome();
                            $platformIncome = $platformIncomeModel->find()->where(['member_id'=>$value['member_id']])->orderBy('id DESC')->asArray()->one();

                            $platformIncomeModel->sn = $value['sn'];
                            $platformIncomeModel->product_name = '';
                            $platformIncomeModel->member_id = $value['member_id'];
                            $platformIncomeModel->remark = '提现手续费';
                            $platformIncomeModel->complete_time = time();
                            $platformIncomeModel->amount = $value['fee'];
                            $platformIncomeModel->balance = $platformIncome['balance'] + $value['fee'];
                            $platformIncomeModel->ls_sn = $value['sn'];
                            $platformIncomeModel->type = 1;
                            if (!$platformIncomeModel->save())
                                throw new \Exception('添加平台收益有误');

                            //更新平台账户表
                            $pt_account = QfbPtAccount::find()->where(['name'=>'SYS_GENERATE_004'])->one();
                            $pt_account->money += $value['fee'];
                            if (!$pt_account->save())
                                throw new \Exception('添加平台账户表有误');

                            $msg = '提现成功-冻结余额：'.$memberMoneyModel->lock_money.',扣减：'.$value['price'];

                        } // 已受理，出款中
                        else if (in_array(trim(strtoupper($d_val['status'])), ['ACCEPT', 'REMITING'])) {

                            // 未处理状态--说明提现操作回调有误，还没有冻结用户金额
                            if ($value['is_check'] != 3) {

                                $bankModel = new QfbBank();
                                $bank = $bankModel::findOne(['no' => $result->bankcardNo]);

                                // 更新order
                                unset($update_order_arr);
                                $update_order_arr['bank_id'] = $bank->id;
                                $update = QfbOrder::updateAll($update_order_arr, ['id' => $value['id']]);

                                if (empty($update))
                                    throw new \Exception('更新order bank_id 有误');

                                $memberMoneyModel = QfbMemberMoney::findOne(['member_id' => $value['member_id']]);
                                $oldMoney = $memberMoneyModel->money;

                                // 冻结用户金额  --- 扣减零钱 加上冻结金额
                                $memberMoneyModel->money -= $value['price'];
                                $memberMoneyModel->lock_money += $value['price'];
                                if ($memberMoneyModel->save())
                                    throw new \Exception('冻结用户提现有误');

                                //提现金额写入日志
                                $moneyLogModel = new QfbMoneyLog($result->platformUserNo);
                                $moneyLogModel->member_id = $result->platformUserNo;
                                $moneyLogModel->type = 2;  //1收入，2支出
                                $moneyLogModel->money_type = 1; //金额类型 1零钱 2活期 3定期
                                $moneyLogModel->money = $value['price'];
                                $moneyLogModel->create_time = time();
                                $moneyLogModel->old_money = $oldMoney;
                                $moneyLogModel->action = 7; //行为类型 1活期管理奖,2充值,3分润,4活期推荐奖,5定期推荐奖,6转账,7提现,8活期收益,9退款,10兑换,11(零钱活期转换),12(零钱定期转换),13店铺押金,14财富计划购买失败返回及收益,16定期收益,17定期管理奖,18定期分润,19现金奖励,20购买财富计划
                                $moneyLogModel->remark = '提现';

                                if ($moneyLogModel->save())
                                    throw new \Exception('写入提现冻结日志有误');
                            }
                        }
                        // 未确认状态，如待确认，不冻结
                        else {
                            throw new \Exception('未知状态，不处理');
                        }
                    }// 遍历提现明细列表
                }

                $trans->commit();

            }catch (\Exception $e) {
                $trans->rollback();
                $msg = $e->getMessage();

            }

            // 记录日志
            $fileName = "TX_".$serviceName . "_DIRECT.log";
            $content = "提现对账操作     执行时间：".date("Y-m-d H:i:s", time())."     请求流水号：".$reqData['requestNo']."     操作信息：".$msg.
                "     请求数据：".json_encode($reqData)."     响应数据:".json_encode($result)."\r\n";
            LogService::hkyh_write_log($fileName, $content);

        }// 遍历订单

        // 记录日志
        $fileName = "TX_".$serviceName . "_DIRECT.log";
        $content = "------------结束时间：".date("Y-m-d H:i:s",time())."---------本次执行耗时：" . (time() - $start_time) . " 秒-------\r\n\r\n";
        LogService::hkyh_write_log($fileName, $content);

        $this->delLockFunc($funcName);
        echo "end";
        exit;
    }

    //平台体现对账
    public function actionPtWithdraw()
    {
        $start_time = time();

        $funcName = 'pt_withdraw';

        if($this->getLockFunc($funcName) === 1){
            exit;
        }
        $this->setLockFunc($funcName);

        $hkyh = \Yii::$app->Hkyh;

        // 对账
        $serviceName = 'QUERY_TRANSACTION';

        $reqData['transactionType'] = 'WITHDRAW';

        $optinTime = time() - 60 * $this->time_limit;

        //查询提现待处理的数据
        $data = QfbPtOrder::find()
            ->where(['sorts' => 2])
            ->andFilterWhere(['in', 'is_check', [3]])
            ->andFilterWhere(['<=', 'create_time', $optinTime])
            ->limit(100)
            ->orderBy('id asc')
            ->asArray()
            ->all();
        //var_dump($data);exit;
        // 遍历订单
        foreach ($data as $key => $value) {

            $msg = '';
            $status = true;
            $reqData['requestNo'] = $value['sn'];
            $result = $hkyh->createPostParam($serviceName, $reqData);
            $data = json_decode($result['data']);
            $trans = \Yii::$app->db->beginTransaction();

            try {

                $pt_order = QfbPtOrder::find()->where(['sn'=>$value['sn']])->one();

                if ($data->status != 'SUCCESS' || $data->records[0]->status == 'FAIL' || $data->records[0]->status == 'ACCEPT_FAIL') {
                    //提现失败
                    //账户表
                    $pt_money = QfbPtAccount::find()->where(['name'=>$pt_order['pt_number']])->one();                    
                    $pt_money->money += $pt_order->price;
                    $pt_money->frozen -= $pt_order->price;

                    //订单表                    
                    $pt_order->is_check = 2;
                    
                    if (!$pt_money->save())
                            throw new \Exception('更新账户信息表有误');
                    if (!$pt_order->save())
                            throw new \Exception('更新订单表有误');
                    
                } else {
                    if ($data->records[0]->status == 'SUCCESS') {
                        //成功
                        //账户表
                        $pt_money = QfbPtAccount::find()->where(['name'=>$data->records[0]->platformUserNo])->one();
                        $pt_money->frozen -= $data->records[0]->amount;
                        //订单表
                        $pt_order->is_check=1;
                        $pt_order->complete_time=time();
                        //收益表
                        $pt_income_data=QfbPlatformIncome::find()->where(['platform_name'=>$data->records[0]->platformUserNo])->orderBy('complete_time desc')->one();
                        $pt_income = new QfbPlatformIncome;
                        $pt_income->platform_name = $data->records[0]->platformUserNo;
                        $pt_income->sn = $value['sn'];
                        $pt_income->remark = '平台账户提现';
                        $pt_income->complete_time = strtotime($data->records[0]->transactionTime);
                        $pt_income->amount = $data->records[0]->amount;
                        $pt_income->balance =intval($pt_income_data['balance'] - $data->records[0]->amount);
                        $pt_income->ls_sn = $value['sn'];
                        $pt_income->type = 1;
                        if (!$pt_money->save())
                            throw new \Exception('更新账户信息表有误');
                        if (!$pt_order->save())
                            throw new \Exception('更新订单表有误');
                        if (!$pt_income->save())
                            throw new \Exception('更新平台收益表有误');
                    }

                }
                $trans->commit();
                $msg = '提现成功';
            }catch (\Exception $e) {
                $trans->rollback();
                $msg = $e->getMessage();

            }

        }
        // 记录日志
        $fileName = "TX_".$serviceName . "_DIRECT.log";
        $content = "------------结束时间：".date("Y-m-d H:i:s",time())."-------本次执行耗时：" . (time() - $start_time) . " 秒-------\r\n\r\n";
        LogService::hkyh_write_log($fileName, $content);

        $this->delLockFunc($funcName);
        echo "end";
        exit;
    }

    /**
     * 查询充值接口
     */
    public function actionFindPay()
    {
        $msg='支付中';

        $start_time=time();

        $funcName = 'find_pay';

        if($this->getLockFunc($funcName) === 1){
            exit;
        }
        $this->setLockFunc($funcName);

        $hkyh = \Yii::$app->Hkyh;

        $serviceName = 'QUERY_TRANSACTION';

        $reqData['transactionType'] = 'RECHARGE';

        // 设置查询时间差
        $findtime = time() - 60 * $this->time_limit;

        $data = QfbOrder::find()
            ->where(['is_check' => 3, 'sorts' => 1,'type'=>1])
            ->andFilterWhere(['<', 'create_time', $findtime])
            ->limit(100)
            ->asArray()
            ->all();

        foreach ($data as $key => $value) {
            $status = true;
            $reqData['requestNo'] = $value['sn'];
            $result = $hkyh->createPostParam($serviceName, $reqData);

            //这里根据业务逻辑自行处理，如果是直连则根据$result数据做处理，如果是网关则不返回数据，

            if ($result['status'] == 'success') {
                //处理成功
                $data = json_decode($result['data']);
                if ($data->code === '0') {
                    if ($data->records[0]->status === 'SUCCESS') {
                        $trans = \Yii::$app->db->beginTransaction();

                        $orderModel = QfbOrder::findOne($value['id']);
                        $orderModel->is_check = 1; //已支付
                        if (!$orderModel->save()) {
                            $status = false;
                        }

                        $memberMoneyModel = QfbMemberMoney::findOne(['member_id' => $data->records[0]->platformUserNo]);
                        $oldMoney = $memberMoneyModel->money;
                        $memberMoneyModel->money += $value['money'];
                        if (!$memberMoneyModel->save()) {
                            $status = false;
                        }

                        //日志
                        $moneyLogModel = new QfbMoneyLog( $data->records[0]->platformUserNo);
                        $moneyLogModel->member_id = $data->records[0]->platformUserNo;
                        $moneyLogModel->type = 1;  //1收入，2支出
                        $moneyLogModel->money_type = 1; //金额类型 1零钱 2活期 3定期
                        $moneyLogModel->money = $value['money'];
                        $moneyLogModel->create_time = time();
                        $moneyLogModel->old_money = $oldMoney;
                        $moneyLogModel->action = 2; //行为类型 1活期管理奖,2充值,3分润,4活期推荐奖,5定期推荐奖,6转账,7提现,8活期收益,9退款,10兑换,11(零钱活期转换),12(零钱定期转换),13店铺押金,14财富计划购买失败返回及收益,16定期收益,17定期管理奖,18定期分润,19现金奖励,20购买财富计划
                        $moneyLogModel->remark = '充值';
                        if (!$moneyLogModel->save()) {
                            $status = false;
                        }

                        if ($status == true) {
                            $trans->commit();
                            $msg='充值成功-原金额：'.$oldMoney.", 充值：".$value['money'];
                        } else {
                            $trans->rollBack();
                            $msg='充值失败';
                        }
                    } else if ($data->records[0]->status === 'FAIL' || $data->records[0]->status === 'ERROR') {
                        //支付失败
                        $orderModel = QfbOrder::findOne($value['id']);
                        $orderModel->is_check = 2;
                        $orderModel->save();
                        $msg='充值失败';

                    }
                } else {
                    //查询流水号不存在
                    $orderModel = QfbOrder::findOne($value['id']);
                    $orderModel->is_check = 4;
                    $orderModel->save();
                    $msg='查询流水号不存在';
                }
            }
            // 记录日志
            $fileName = "CZ_".$serviceName . "_DIRECT.log";
            $content = "充值对账操作     执行时间：".date("Y-m-d H:i:s", time())."     请求流水号：".$reqData['requestNo']."     操作信息：".$msg.
                "     请求数据：".json_encode($reqData)."     响应数据:".json_encode($result)."\r\n";
            LogService::hkyh_write_log($fileName, $content);
        }

        // 记录日志
        $fileName = "CZ_".$serviceName . "_DIRECT.log";
        $content = "------------结束时间：".date("Y-m-d H:i:s",time())."-------本次执行耗时：" . (time() - $start_time) . " 秒-------\r\n\r\n";
        LogService::hkyh_write_log($fileName, $content);

        $this->delLockFunc($funcName);
        echo "end";
        exit;
    }

    //平台充值查询对账
    public function actionPtpay()
    {
        $msg='支付中';

        $start_time=time();

        $funcName = 'ptpay';

        /*if($this->getLockFunc($funcName) === 1){
            exit;
        }*/
        $this->setLockFunc($funcName);

        $hkyh = \Yii::$app->Hkyh;

        $serviceName = 'QUERY_TRANSACTION';

        $reqData['transactionType'] = 'RECHARGE';

        // 设置查询时间差
        $findtime = time() - 60 * $this->time_limit;

        $data = QfbPtOrder::find()
            ->where(['is_check' => 3, 'sorts' => 1])
            ->andFilterWhere(['<', 'create_time', $findtime])
            ->limit(100)
            ->asArray()
            ->all();

        foreach ($data as $key => $value) {
            $status = true;
            $reqData['requestNo'] = $value['sn'];
            $result = $hkyh->createPostParam($serviceName, $reqData);

            //这里根据业务逻辑自行处理，如果是直连则根据$result数据做处理，如果是网关则不返回数据，

            if ($result['status'] == 'success') {
                //处理成功
                $data = json_decode($result['data']);
                if ($data->code === '0') {
                    if ($data->records[0]->status === 'SUCCESS') {
                        $trans = \Yii::$app->db->beginTransaction();

                        $orderModel = QfbPtOrder::findOne($value['id']);
                        $orderModel->is_check = 1; //已支付
                        if (!$orderModel->save()) {
                            $status = false;
                        }

                        $pt_income_data=QfbPlatformIncome::find()->where(['platform_name'=>$data->records[0]->platformUserNo])->orderBy('complete_time desc')->one();

                        $pt_income = new QfbPlatformIncome;
                        //平台收益表操作
                        $pt_income->platform_name = $data->records[0]->platformUserNo;
                        $pt_income->sn = $value['sn'];
                        $pt_income->remark = '平台账户充值';
                        $pt_income->complete_time = strtotime($data->records[0]->transactionTime);
                        $pt_income->amount = $data->records[0]->amount;
                        $pt_income->balance =intval($pt_income_data['balance'] + $data->records[0]->amount);
                        $pt_income->ls_sn = $value['sn'];
                        $pt_income->type = 3;

                        if (!$pt_income->save()) {
                            $status = false;
                        }

                        //平台账户表操作
                        $pt_money = QfbPtAccount::find()->where(['name'=>$data->records[0]->platformUserNo])->one();
                        $pt_money->money += $data->records[0]->amount;

                        if (!$pt_money->save()) {
                            $status = false;
                        }
                        if ($status == true) {
                            $trans->commit();
                            $msg='充值成功';
                        } else {
                            $trans->rollBack();
                            $msg='充值失败';
                        }
                    } else if ($data->records[0]->status === 'FAIL' || $data->records[0]->status === 'ERROR') {
                        //支付失败
                        $orderModel = QfbPtOrder::findOne($value['id']);
                        $orderModel->is_check = 2;
                        $orderModel->save();
                        $msg='充值失败';

                    }
                } else {
                    //查询流水号不存在
                    $orderModel = QfbPtOrder::findOne($value['id']);
                    $orderModel->is_check = 4;
                    $orderModel->save();
                    $msg='查询流水号不存在';
                }
            }
            // 记录日志
            $fileName = "CZ_".$serviceName . "_DIRECT.log";
            $content = "充值对账操作     执行时间：".date("Y-m-d H:i:s", time())."     请求流水号：".$reqData['requestNo']."     操作信息：".$msg.
                "     请求数据：".json_encode($reqData)."     响应数据:".json_encode($result)."\r\n";
            LogService::hkyh_write_log($fileName, $content);
        }

        // 记录日志
        $fileName = "CZ_".$serviceName . "_DIRECT.log";
        $content = "------------结束时间：".date("Y-m-d H:i:s",time())."-------本次执行耗时：" . (time() - $start_time) . " 秒-------\r\n\r\n";
        LogService::hkyh_write_log($fileName, $content);

        $this->delLockFunc($funcName);
        echo "end";
        exit;
    }

    /**
     * 放款脚本
     */
    public function actionMakeLoans()
    {
        $start_time = time();

        $funcName = 'make_loans';

        if($this->getLockFunc($funcName) === 1){
            exit;
        }
        $this->setLockFunc($funcName);

        $hkyh = \Yii::$app->Hkyh;

        // 放款
        $serviceName = 'SYNC_TRANSACTION';

        // 设置查询时间差
        $option_time = time()-60*$this->time_limit;
        // 获取投资订单
        $order_fix = QfbOrderFix::find()
            ->select('id,sn,member_id,product_id,money')
            ->where('status=2 and option_status=10 and option_time<=:option_time',[':option_time'=>$option_time])
            ->limit(100)
            ->orderBy('product_id desc')
            ->asArray()->all();

        // 初始化
        $product_id = '';
        $projectNo = '';
        $targetPlatformUserNo = '';
        $total_credit_money = 0;
        $actual_credit_money = 0;

        // 遍历所有需要放款的订单
        foreach ($order_fix as $o_key => $o_val) {

            // 识别是否同一个产品
            if ($product_id != trim($o_val['product_id'])) {

                $product_id = trim($o_val['product_id']);

                $products_rows = QfbProduct::find()
                    ->select('sn,member_id,total_credit_money,actual_credit_money,platform_income_rate,product_name,credit_incomme')
                    ->where(['id' => $product_id])
                    ->asArray()->one();

                // 产品不存在
                if (empty($products_rows)) continue;

                // 平台收益率
                $platform_income_rate = $products_rows['platform_income_rate'];
                // 标的流水号
                $projectNo = $products_rows['sn'];
                // 收款人用户标号
                $targetPlatformUserNo = $products_rows['member_id'];
                // 标的应放总额
                $total_credit_money = $products_rows['total_credit_money'];
                // 标的已放金额
                $actual_credit_money = $products_rows['actual_credit_money'];
                // 已发放平台收益金额
                $credit_incomme = $products_rows['credit_incomme'];
            }

            // 平台收益 --保留两位小数 不四舍五入
            $commission_amout = Tool::moneyPlatform(($o_val['money'] * $platform_income_rate / 100));

            $details = [
                [
                    // 业务类型
                    'bizType' => 'TENDER',
                    // 预处理流水号
                    'freezeRequestNo' => $o_val['sn'],
                    // 出库用户编号
                    'sourcePlatformUserNo' => $o_val['member_id'],
                    // 收款用户编号
                    'targetPlatformUserNo' => $targetPlatformUserNo,
                    // 扣除总额
                    'amount' => $o_val['money'], //测试
                    // 利息
                    // 'income'=>'0',
                ],
                [
                    'bizType' => 'COMMISSION',
                    'sourcePlatformUserNo' => $targetPlatformUserNo,
                    'amount' => $commission_amout,
                ],
            ];

            // 请求流水号
            $reqData['requestNo'] = $this->getBindSn('FK');
            // 交易类型
            $reqData['tradeType'] = 'TENDER';
            // 标的号
            $reqData['projectNo'] = $projectNo;
            // 业务明细
            $reqData['details'] = $details;
            $result = $hkyh->createPostParam($serviceName, $reqData);

            // 请求无效返回
            if (!isset($result['data'])) continue;

            $tran = \Yii::$app->db->beginTransaction();
            try {

                // 判断是否提交成功
                if (trim(strtoupper($result['status'])) != 'SUCCESS')
                    throw new \Exception('订单流水号：' . $o_val['sn'] . ', 操作信息：' . $result['message']);

                $data = json_decode($result['data'], true);

                if (trim(strtoupper($data['status'])) != 'SUCCESS')
                    throw new \Exception('订单流水号：' . $o_val['sn'] . ', 错误信息：' . $data['errorMessage']);

                // 判断是否放款交易成功
                if (trim(strtoupper($data['transactionStatus'])) == 'SUCCESS') {

                    // 累加平台佣金收入
                    $credit_incomme += $commission_amout;

                    // 累加统计已放金额
                    $actual_credit_money += $o_val['money'];

                    // 更新标的产品 已放款金额
                    unset($product_arr);
                    $product_arr['actual_credit_money'] = $actual_credit_money;
                    $product_arr['credit_incomme'] = $credit_incomme;
                    $update = QfbProduct::updateAll($product_arr, ['id' => $product_id]);

                    if (empty($update))
                        throw new \Exception('更新标的已放款金额有误');
                }else{

                    // 标识放款失败--异常
                    unset($order_fix_arr_arr);
                    $order_fix_arr_arr['option_status'] = 19;
                    $order_fix_arr_arr['option_time'] = time();
                    QfbOrderFix::updateAll($order_fix_arr_arr, ['product_id'=>$product_id, 'status'=>'2']);

                    throw new \Exception('订单流水号：' . $o_val['sn'] . ', 放款失败');
                }

                // 更新购买订单操作
                unset($order_fix_arr);
                $order_fix_arr['option_status'] = 11;
                $order_fix_arr['credit_sn'] = $reqData['requestNo'];
                $update = QfbOrderFix::updateAll($order_fix_arr, ['id' => $o_val['id']]);
                if (empty($update))
                    throw new \Exception('标识操作状态有误');

                //平台收益表中进行记录
                $old_balance = 0;
                $balance = QfbPlatformIncome::find()->select('balance')->orderBy('id desc')->asArray()->limit(1)->one();
                if (!empty($balance))
                    $old_balance = $balance['balance'];

                $plat = new QfbPlatformIncome();
                // 标的号
                $plat->sn = $products_rows['sn'];
                // 请求流水号
                $plat->ls_sn = $reqData['requestNo'];
                $plat->product_name = $products_rows['product_name'];
                $plat->member_id = $products_rows['member_id'];
                $plat->remark = '';
                $plat->complete_time = time();
                $plat->amount = $commission_amout;
                $plat->balance = $old_balance + $commission_amout;

                //更新平台金额表
                $pt_account = QfbPtAccount::find()->where(['name'=>'SYS_GENERATE_004'])->one();
                $pt_account->money += $commission_amout;

                if (!$pt_account->save())
                    throw new \Exception('订单流水号：' . $o_val['sn'] . ', 更新平台账户有误');
                if (!$plat->save())
                    throw new \Exception('订单流水号：' . $o_val['sn'] . ', 记录平台收益有误');

                ////////////////////////////////////////////////////////////////////////////////
                // 操作标的
                // 应放款总额 = 已放款总额  -》更标的已完成放款
                if ($total_credit_money == $actual_credit_money) {

                    // 变更标的产品 -已放款，还款中
                    unset($product_arr);
                    // 标识已放款
                    $product_arr['status'] = 6;
                    // 操作状态：待变更标的状态
                    $product_arr['option_status'] = 20;
                    $product_arr['credit_time'] = time();
                    $update = QfbProduct::updateAll($product_arr, ['id' => $product_id]);
                    if (empty($update))
                        throw new \Exception('更新放款状态有误');

                    // 添加借款人零钱
                    $member_money = QfbMemberMoney::find()->select('member_id,money')->where(['member_id' => $targetPlatformUserNo])->asArray()->one();
                    // 原的金额
                    $old_money = $member_money['money'];
                    // 用金额 = 原金额+ 借款金额-平台佣金
                    $money = $member_money['money'] + $total_credit_money - $credit_incomme;

                    // 更新借款人金额
                    unset($member_money_arr);
                    $member_money_arr['money'] = $money;
                    $update = QfbMemberMoney::updateAll($member_money_arr, ['member_id' => $member_money['member_id']]);
                    if (empty($update))
                        throw new \Exception('放款至借款人零钱有误');

                    //日志
                    $moneyLogModel = new QfbMoneyLog($targetPlatformUserNo);
                    $moneyLogModel->member_id = $targetPlatformUserNo;
                    //1收入，2支出
                    $moneyLogModel->type = 1;
                    //金额类型 1零钱 2活期 3定期
                    $moneyLogModel->money_type = 1;
                    // 添加的金额
                    $moneyLogModel->money = $total_credit_money - $credit_incomme;
                    $moneyLogModel->create_time = time();
                    $moneyLogModel->old_money = $old_money;
                    $moneyLogModel->action = 22;
                    $moneyLogModel->remark = '已放款至借款人账户';
                    if (!$moneyLogModel->save())
                        throw new \Exception('放款借款人有误');
                }

                $tran->commit();
                $msg = '放款成功-借款用户原金额：'.$old_money.", 实收金额：".$total_credit_money.", 平台收益：".$credit_incomme;

            } catch (\Exception $e) {
                $tran->rollback();
                $msg = $e->getMessage();
            }

            // 记录日志
            $fileName = "FK_".$serviceName . "_DIRECT.log";
            $content = "放款操作     执行时间：".date("Y-m-d H:i:s", time())."     产品id：".$product_id." - 订单预处理流水号：".$o_val['sn']."     操作信息：".$msg.
                "     请求数据：".json_encode($reqData)."     响应数据:".json_encode($result)."\r\n";
            LogService::hkyh_write_log($fileName, $content);

        }// 遍历放款订单

        // 记录日志
        $fileName = "FK_".$serviceName . "_DIRECT.log";
        $content = "------------结束时间：".date("Y-m-d H:i:s",time())."-------本次执行耗时：" . (time() - $start_time) . " 秒-------\r\n\r\n";
        LogService::hkyh_write_log($fileName, $content);

        $this->delLockFunc($funcName);
        echo "end";
        exit;

    }

    /**
     * 变更标的状态
     */
    public function actionChangeProjectStatus()
    {

        // 初始化
        $start_time = time();

        $funcName = 'change_project_status';

        if($this->getLockFunc($funcName) === 1){
            exit;
        }
        $this->setLockFunc($funcName);

        $product_arr = [];

        $hkyh = \Yii::$app->Hkyh;
        $serviceName = 'MODIFY_PROJECT';

        // 查询需要变更标状态的产品
        $product_all = QfbProduct::find()
            ->where('option_status=20')
            ->orderBy('id asc ')
            ->limit(100)->asArray()->all();

        foreach ($product_all as $p_key => $p_val) {

            $projectNo = $p_val['sn'];

            // 更改标的状态
            $reqData['requestNo'] = $this->getBindSn('CP');
            $reqData['projectNo'] = $projectNo;
            // 还款中 状态
            $reqData['status'] = 'REPAYING';
            $result = $hkyh->createPostParam($serviceName, $reqData);

            try {

                // 判断是否提交成功
                if (trim(strtoupper($result['status'])) != 'SUCCESS')
                    throw new \Exception('标的编号：' . $projectNo . ', 状态信息：' . $result['message']);

                $data = json_decode($result['data'], true);

                if (trim(strtoupper($data['status'])) != 'SUCCESS')
                    throw new \Exception('标的编号：' . $projectNo . ', 错误信息：' . $data['errorMessage']);

                // 操作状态
                $option_status = 21;

                $msg = '变更标的状态成功';

            } catch (\Exception $e) {
                $msg = $e->getMessage();
                // 操作变更标的状态异常
                $option_status = 29;
            }

            // 变更标的状态
            $product_arr['option_status'] = $option_status;
            $update = QfbProduct::updateAll($product_arr, ['id' => $p_val['id']]);

            if (empty($update)){
                $msg = '变更标的状态异常';
            }else{
                $orderFix = QfbOrderFix::find()->where(['product_id'=>$p_val['id'], 'option_status'=>11])->asArray()->all();

                //如果存在订单则直接跳过
                $repay = QfbOrderRepayment::findOne(['product_id'=>$p_val['id']]);
                if($repay) break;

                //分期与不分期的逻辑
                if($p_val['profit_type'] == 2){
                    $term = ceil($p_val['invest_day']/$this->terminally_day);
                    for($i=1; $i<=$term; $i++){
                        $orderModel = new QfbOrderRepayment();

                        if($i == $term){
                            $orderModel->invest_day = $p_val['invest_day']-($i-1)*$this->terminally_day;
                            $orderModel->money = $p_val['stock_money'];
                        }else{
                            $orderModel->invest_day = $this->terminally_day;
                            $orderModel->is_end = 0;
                        }
                        $orderModel->interest = 0;
                        foreach($orderFix as $key=>$value){
                            $orderModel->interest += Tool::moneyCalculate($value['day_interest']*$orderModel->invest_day);
                        }
                        $orderModel->member_id = $p_val['member_id'];
                        $orderModel->product_id = $p_val['id'];
                        $orderModel->periods = $i;
                        $orderModel->create_time = time();
                        $orderModel->save();
                    }
                }else{
                    $orderModel = new QfbOrderRepayment();

                    $orderModel->interest = 0;
                    foreach($orderFix as $key=>$value){
                        $interval = date_diff(date_create(date('Y-m-d',$p_val['finish_time'])), date_create(date('Y-m-d',$value['create_time'])));
                        if ($p_val['profit_day'] == 10) {
                            $profitDay = $interval->days + $p_val['invest_day'];
                        } elseif ($p_val['profit_day'] == 11) {
                            $profitDay = $interval->days + $p_val['invest_day'];
                        } elseif ($p_val['profit_day'] == 20) {
                            $profitDay = $p_val['invest_day'];
                        } else {
                            $profitDay = $p_val['invest_day'];
                        }
                        $orderModel->interest += Tool::moneyCalculate($value['day_interest']*$profitDay);
                    }
                    $orderModel->member_id = $p_val['member_id'];
                    $orderModel->product_id = $p_val['id'];
                    $orderModel->money = $p_val['stock_money'];
                    $orderModel->create_time = time();
                    $orderModel->invest_day = $profitDay;
                    $orderModel->save();
                }
            }

            // 记录日志
            $fileName = "BG_".$serviceName . "_DIRECT.log";
            $content = "变更标的状态操作     执行时间：".date("Y-m-d H:i:s", time())."     请求流水号：".$reqData['requestNo']."     操作信息：".$msg.
                "     请求数据：".json_encode($reqData)."     响应数据:".json_encode($result)."\r\n";
            LogService::hkyh_write_log($fileName, $content);

        }

        // 记录日志
        $fileName = "BG_".$serviceName . "_DIRECT.log";
        $content = "------------结束时间：".date("Y-m-d H:i:s",time())."-------本次执行耗时：" . (time() - $start_time) . " 秒-------\n\n";
        LogService::hkyh_write_log($fileName, $content);

        $this->delLockFunc($funcName);
        echo "end";
        exit;
    }

    /**
     * 还款对账
     * @throws yii\db\Exception
     */
    public function actionFindRepayment()
    {
        $start_time = microtime(true);

        $funcName = 'find_repayment';

        if($this->getLockFunc($funcName) === 1){
            exit;
        }
        $this->setLockFunc($funcName);

        $msg = '';

        $content = '';

        $hkyh = \Yii::$app->Hkyh;

        $serviceName = 'QUERY_TRANSACTION';

        $reqData['transactionType'] = 'PRETRANSACTION';

        $optionTime = time()-$this->time_limit*60;

        //查询为还款的数据
        $data = QfbOrderRepaymentExtend::find()
            ->where(['option_status' => 0])
            ->andFilterWhere(['<=', 'create_time', $optionTime])
            ->limit(100)
            ->asArray()
            ->all();

        foreach ($data as $key => $value) {
            $reqData['requestNo'] = $value['sn'];
            $result = $hkyh->createPostParam($serviceName, $reqData);
            //这里根据业务逻辑自行处理，如果是直连则根据$result数据做处理，如果是网关则不返回数据，

            if (trim(strtoupper($result['status'])) == 'SUCCESS') {
                $data = json_decode($result['data']);
                $orderRepaymentExtendModel = QfbOrderRepaymentExtend::findOne($value['id']);
                if ($data->code === '0') {
                    if (trim(strtoupper($data->records[0]->status)) === 'FREEZED') {

                        $res = new \stdClass();
                        $res->requestNo = $value['sn'];
                        $hkyhService = new HkyhService();
                        if($value['type'] == 0){
                            $hkyhResult = $hkyhService->hkyhRepayment($res);

                        }else{
                            $hkyhResult = $hkyhService->hkyhOverdueRepayment($res);
                        }

                        if($hkyhResult['code'] == 200){
                            $msg = '流水号' . $value['sn'] . '还款成功';
                            $orderRepaymentExtendModel->option_status = 1;
                        }else{
                            $msg = '流水号' . $value['sn'] . '还款异常';
                            $orderRepaymentExtendModel->option_status = 9;
                        }
                    } elseif(trim(strtoupper($data->records[0]->status)) == 'FAIL') {
                        if($value['type'] == 0){
                            $model = QfbOrderRepayment::findOne($value['order_id']);
                        }else{
                            $model = QfbOrderOverdue::findOne($value['order_id']);
                        }
                        $model->status = 3;
                        $model->save();
                        $orderRepaymentExtendModel->option_status = 2;
                        $msg = '流水号' . $value['sn'] . '银行处理失败---错误代码：' . $data->records[0]->status;
                    }elseif(trim(strtoupper($data->records[0]->status)) == 'ERROR'){
                        if($value['type'] == 0){
                            $model = QfbOrderRepayment::findOne($value['order_id']);
                        }else{
                            $model = QfbOrderOverdue::findOne($value['order_id']);
                        }
                        $model->status = 9;
                        $model->save();
                        $orderRepaymentExtendModel->option_status = 9;
                        $msg = '流水号' . $value['sn'] . '银行处理失败---错误代码：' . $data->records[0]->status;
                    }elseif(trim(strtoupper($data->records[0]->status)) == 'ERROR'){
                        $msg = '流水号' . $value['sn'] . '银行处理失败---错误代码：' . $data->records[0]->status;
                    }else{
                        $msg = '流水号' . $value['sn'] . '银行处理失败---错误代码：' . $data->records[0]->status;
                    }

                } else {
                    if($value['type'] == 0){
                        $model = QfbOrderRepayment::findOne($value['order_id']);
                    }else{
                        $model = QfbOrderOverdue::findOne($value['order_id']);
                    }
                    if ($data->errorCode == '100007') {
                        $model->status = 9; //异常
                        $orderRepaymentExtendModel->option_status = 9;
                    } else {
                        $model->status = 3; //失败
                        $orderRepaymentExtendModel->option_status = 2;
                    }

                    $model->save();
                    $msg = '流水号' . $value['sn'] . '银行处理失败---错误代码：' . $data->code . ',' . $data->status;
                }

                $orderRepaymentExtendModel->save();
            } else {
                $msg = '流水号' . $value['sn'] . '银行处理失败---错误信息：' . $result['message'];
            }

            $content .= $msg."\r\n银行请求数据：\r\n" . var_export($result, true) . "\r\n";

        }

        $end_time = microtime(true);
        $time = $end_time-$start_time;

        // 记录日志
        $fileName = 'HK_'.$serviceName . "_DIRECT.log";
        $content = $content."------------结束时间：".date("Y-m-d H:i:s",time())."-------本次执行耗时：".$time." 秒-------\r\n\r\n";
        LogService::hkyh_write_log($fileName, $content);

        $this->delLockFunc($funcName);
        echo "end";
        exit;

    }

    /**
     * 确认还款
     * @author
     */
    public function actionConfirmRepayment()
    {
        $start_time = time();
        $funcName = 'confirm_repayment_commutation';
        if ($this->getLockFunc($funcName) === 1)
            exit;

        $this->setLockFunc($funcName);

        $hkyh = \Yii::$app->Hkyh;

        //还款交易确认
        $serviceName = 'SYNC_TRANSACTION';

        // 脚本时间差
        $option_time = time() - 60 * $this->time_limit;

        // 通过时间差取出还款待确定订单--还款操作
        $orderRepayment = QfbOrderRepayment::find()
            ->select('id,status,product_id,sn,member_id,money,repay_money,interest,repay_money,invest_day,is_commutation,is_end')
            ->where('status=1 and create_time<=:create_time', [':create_time' => $option_time])
            ->orderBy('complete_time asc')
            ->limit(50)
            ->asArray()
            ->all();

        // 遍历还款订单
        foreach ($orderRepayment as $k => $v) {

            // 初始化 --默认是借款直接还款投资人 -- 还款类型
            $repayment_type = 1;
            $remark = '借款人还款';

            $order_repayment_id = $v['id'];
            // 借款人预处理号
            $sn = $v['sn'];
            $product_id = $v['product_id'];
            $is_commutation = $v['is_commutation'];
            $is_end = $v['is_end'];
            $invest_day = $v['invest_day'];
            $form_user_id = $v['member_id'];

            // 订单总应还金额  -本金+利息
            $total_repay_money = $v['money'] + $v['interest'];
            // 已经还款金额
            $repay_money = $v['repay_money'];

            // 判断是否已经锁过了
            $luck_count = QfbOrderFix::find()->where('product_id=:product_id and lock_status=1', ['product_id' => $product_id])->count();

            //  未上锁，上锁操作
            if (empty($luck_count)) {
                // 还款操作锁   --- 临时关闭
                $update = QfbOrderFix::updateAll(['lock_status' => 1], ['product_id' => $product_id]);
                if (!$update)
                    throw new \Exception('投资订单还款锁定有误');
            }

            // 平台代偿
            if ($is_commutation == 1) {

                // -还款类型
                $repayment_type = 2;
                $remark = '平台代偿还款';

                $pt_account_id = 2;
                // 获取平台账户余额  -- 获取营销账户
                $pt_account = QfbPtAccount::find()->select('id,name,money,frozen,commutation_money')->where(['id' => $pt_account_id])->asArray()->one();

                // 平台账户代偿金额
                $pt_account_commutation_money = $pt_account['commutation_money'];
                // 代偿账户用户编号
                $form_user_id = $pt_account['name'];

                // 订单总本金+订单总利息-已还金额 > 平台代偿账户金额
                if (($v['repay_money'] + $v['interest'] - $v['repay_money']) > $pt_account_commutation_money)
                    throw new \Exception('平台代偿账户冻结金额不足');

                $plat_form_income = QfbPlatformIncome::find()->where('platform_name=:platform_name', [':platform_name' => $pt_account['name']])->orderBy('id desc')->asArray()->one();

                // 平台当前收益金额
                $balance = $plat_form_income['balance'];
            }

            // 产品
            $product_data = QfbProduct::find()->where(['id' => $product_id])->asArray()->one();
            // 标的已还款金额
            $actual_repayment_money = $product_data['actual_repayment_money'];

            // 查询投资订单--已收益中的订单
            $order_fix = QfbOrderFix::find()->where(['product_id' => $product_id, 'status'=>'2'])->limit(100)->asArray()->all();

            foreach ($order_fix as $of_key => $of_val) {

                // 本金
                $principal = 0;
                // 还款总额
                $total_acount = 0;
                // 每期利息=每天利息*天数
                $income = Tool::moneyCalculate($of_val['day_interest'] * $invest_day);

                // 判断是否最后一期还款  -到期还本付息，先息后本 都是最后一个订单还本金
                if ($is_end == 1)
                    $principal = $of_val['money'];

                // 本金+利息
                $total_acount = $principal + $income;

                $sn_type_str = $is_commutation == 1 ? 'HK_P' : 'HK';
                $sn = $this->getBindSn($sn_type_str);

                unset($details);
                unset($reqData);
                // 默认--代偿还款
                // 业务类型
                $details['bizType'] = 'COMPENSATORY';
                // 交易类型
                $reqData['tradeType'] = 'COMPENSATORY';

                if ($is_commutation != 1) {
                    // 业务类型
                    $details['bizType'] = 'REPAYMENT';
                    // 交易类型
                    $reqData['tradeType'] = 'REPAYMENT';
                    // 代偿没有流水号
                    $details['freezeRequestNo'] = $v['sn'];
                }
                // 出款用户编号
                $details['sourcePlatformUserNo'] = $form_user_id;
                // 收款用户编号
                $details['targetPlatformUserNo'] = $of_val['member_id'];
                // 总额包含利息
                $details['amount'] = $total_acount;
                // 利息
                $details['income'] = $income;
                // 请求流水号
                $reqData['requestNo'] = $sn;
                // 标的号
                $reqData['projectNo'] = $product_data['sn'];
                // 业务明细
                $reqData['details'][] = $details;

                $result = $hkyh->createPostParam($serviceName, $reqData);

                $tran = \Yii::$app->db->beginTransaction();

                try {

                    if (trim(strtoupper($result['status'])) != 'SUCCESS')
                        throw new \Exception($result['message']);

                    $data = json_decode($result['data'], true);

                    if (trim(strtoupper($data['status'])) != 'SUCCESS')
                        throw new \Exception($data['errorMessage']);

                    if (trim(strtoupper($data['transactionStatus'])) != 'SUCCESS')
                        throw new \Exception('还代偿款失败');

                    if (trim(strtoupper($data['transactionStatus'])) == 'SUCCESS') {

                        ////////////////////////////////////////////////////////////////////////////////////////////////
                        // 平台代偿
                        if ($is_commutation == 1) {

                            // 添加平台收益信息有误
                            $update = QfbPtAccount::updateAll(['commutation_money' => ($pt_account_commutation_money - $total_acount)], ['id' => $pt_account_id]);
                            if (!$update)
                                throw new \Exception('扣减平台账户金额有误');

                            // 添加平台收支记录
                            unset($plat_form_income_model);
                            $plat_form_income_model = new QfbPlatformIncome();
                            $plat_form_income_model->platform_name = $pt_account['name'];
                            $plat_form_income_model->sn = $sn;
                            $plat_form_income_model->product_name = '产品id：' . $product_id;
                            $plat_form_income_model->member_id = $of_val['member_id'];
                            $plat_form_income_model->remark = '平台代偿还款';
                            $plat_form_income_model->complete_time = time();
                            $plat_form_income_model->amount = $total_acount;
                            $plat_form_income_model->balance = $pt_account_commutation_money - $total_acount;
                            $plat_form_income_model->ls_sn = $sn;
                            $plat_form_income_model->type = 4;

                            $pt_account_commutation_money = $pt_account_commutation_money - $total_acount;

                            if (!$plat_form_income_model->save())
                                throw new \Exception('添加平台收益信息有误');

                        } // 非代偿
                        else {
                            ////////////////////////////////////////////////////////////////////////////////////////////
                            // 借款人账户冻结金额扣减
                            // 扣减冻结金额
                            unset($investMoneyModel);
                            // 扣减借款用户零钱
                            $investMoneyModel = QfbMemberMoney::findOne(['member_id' => $v['member_id']]);
                            $investMoneyModel->lock_money = $investMoneyModel->lock_money - $total_acount;
                            if (!$investMoneyModel->save())
                                throw new \Exception('扣减借款人账户零钱有误');

                            ////////////////////////////////////////////////////////////////////////////////////////////
                            // 更新标的金额
                            // 标的已还金额 = 原已还+单个还投资人的总额   不包含违约金
                            $actual_repayment_money = $actual_repayment_money + $total_acount;

                            // 更新标的已还款金额
                            $update = QfbProduct::updateAll(['actual_repayment_money' => $actual_repayment_money], ['id' => $v['product_id']]);
                            if (!$update)
                                throw new \Exception('更新标的已还款金额有误');
                        }
                        ////////////////////////////////////////////////////////////////////////////////////////////////

                        ////////////////////////////////////////////////////////////////////////////////////////////////
                        // 增加投资人账户金额
                        $moneyModel = QfbMemberMoney::findOne(['member_id' => $of_val['member_id']]);
                        $old_money = $moneyModel->money;

                        //增加投资人的金额处理 -- 零钱
                        $moneyModel->money += $total_acount;
                        // 定期投资金额
                        $moneyModel->fix_money -= $principal;

                        if (!$moneyModel->save())
                            throw new \Exception('变更投资人零钱订单转换有误');

                        // 添加本金返还日志
                        unset($params);
                        $params['member_id'] = $of_val['member_id'];
                        $params['type'] = 1;
                        $params['money_type'] = 1;
                        $params['money'] = $principal;
                        $params['create_time'] = time();
                        $params['old_money'] = $old_money;
                        $params['action'] = 12;
                        $params['remark'] = '投资人本金返还';
                        $createMoneyLog = $this->createMoneyLog($params);

                        if ($createMoneyLog['status'] != true)
                            throw new \Exception($createMoneyLog['message']);

                        unset($params);
                        $params['member_id'] = $of_val['member_id'];
                        $params['type'] = 1;
                        $params['money_type'] = 3;
                        $params['money'] = $income;
                        $params['create_time'] = time();
                        $params['old_money'] = $old_money + $of_val['money'];
                        $params['action'] = 16;
                        $params['remark'] = '投资人收益返还';
                        $createMoneyLog = $this->createMoneyLog($params);
                        if ($createMoneyLog['status'] != true)
                            throw new \Exception($createMoneyLog['message']);
                        //////////////////////////////////////////////////////////////////

                        ////////////////////////////////////////////////////////////////////////////////////////////////
                        // 更新投资订单
                        unset($update);
                        $update['lock_status'] = 0;
                        $update['actual_repayment_money'] = $of_val['actual_repayment_money'] + $total_acount;

                        $product_total_actual_repayment = (string)trim(($of_val['money'] + Tool::moneyCalculate($of_val['day_interest'] * $product_data['invest_day'])));

                        // 投资订单是否完成还款 本金+投资期限*每天利息 = 已还金额
                        if ( (string)trim(($of_val['money'] + Tool::moneyCalculate($of_val['day_interest'] * $product_data['invest_day']))) == (string)trim($update['actual_repayment_money']) )
                            $update['status'] = 3;

                        $update = QfbOrderFix::updateAll($update, ['id' => $of_val['id']]);

                        if (!$update)
                            throw new \Exception('更新投资订单信息有误');

                        ////////////////////////////////////////////////////////////////////////////////////////////////
                        // 还款订单已还金额
                        $repay_money += $total_acount;
                        // 应还款金额
                        $order_reapayment_original = $v['money'] + $v['interest'];

                        // 变更还款订单信息
                        unset($update);
                        $update_order_repayment['repay_money'] = $repay_money;

                        // 本金+利息 = 已经还款金额
                        if (trim($order_reapayment_original) == trim($repay_money))
                            $update_order_repayment['status'] = 2;

                        $update_status = QfbOrderRepayment::updateAll($update_order_repayment, ['id' => $order_repayment_id]);
                        //  累加已还金额
                        if (!$update_status)
                            throw new \Exception('更新还款订单信息有误');

                        ////////////////////////////////////////////////////////////////////////////////////////////////
                        // 添加还款记录
                        if (!$this->createRepaymentLog($order_repayment_id, $sn, $repayment_type, $principal, $income, 0, $form_user_id, $of_val['member_id'], $remark))
                            throw new \Exception('添加还款记录有误');

                        $tran->commit();
                        $msg = '还款成功';
                    }

                } catch (\Exception $e) {
                    $tran->rollback();
                    $msg = $e->getMessage();
                }

                // 记录日志
                $fileName = "HK_" . $serviceName . "_DIRECT.log";
                $content = "还款操作     执行时间：" . date("Y-m-d H:i:s", time()) .
                    " 产品ID：{$product_id}   投资订单号:{$of_val['id']} 总应还：{$product_total_actual_repayment} 总已还金额：".($of_val['actual_repayment_money'] + $total_acount)."   现还：{$total_acount}  还款订单号：{$order_repayment_id}  还款订单总额：{$order_reapayment_original}  操作信息："
                    . $msg .
                    "  请求数据：" . json_encode($reqData) . "     响应数据:" . json_encode($result) . "\r\n";
                LogService::hkyh_write_log($fileName, $content);

            }// 遍历投资订单

            // 非平台代偿
            if ($is_commutation != 1)
                // 变更标的状态
                $this->change_product_status($v['product_id']);

        }// 遍历还款订单

        // 记录日志
        $fileName = "HK_" . $serviceName . "_DIRECT.log";
        $content = "------------结束时间：".date("Y-m-d H:i:s",time())."-------本次执行耗时：" . (time() - $start_time) . " 秒-------\r\n\r\n";
        LogService::hkyh_write_log($fileName, $content);

        $this->delLockFunc($funcName);
        echo "end";
        exit;
    }

    /**
     * 借款还逾期款
     * @author
     */
    public function actionConfirmOverdue()
    {
        $start_time = time();
        $funcName = 'confirm_overdue_commutation';
        if($this->getLockFunc($funcName) === 1)
            exit;
        $this->setLockFunc($funcName);

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // 获取平台账户余额  -- 获取营销账户
        $pt_account_id = 2;

        $pt_account = QfbPtAccount::find()->select('id,name,money')->where(['id'=>$pt_account_id])->asArray()->one();

        // 平台账户余额
        $pt_account_money = $pt_account['money'];
        // 代偿账户用户编号
        $platform_user_id = $pt_account['name'];

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $plat_form_income = QfbPlatformIncome::find()->where('platform_name=:platform_name', [':platform_name'=>$pt_account['name']])->orderBy('id desc')->asArray()->one();
        // 平台当前收益金额
        $balance = $plat_form_income['balance'];

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $hkyh = \Yii::$app->Hkyh;

        //还款交易确认
        $serviceName = 'SYNC_TRANSACTION';

        // 脚本时间差
        $option_time = time()-60*$this->time_limit;

        // 逾期还款操作
        $order_overdue = QfbOrderOverdue::find()
            ->select('*')
            ->where('status=1 and create_time<=:create_time', [':create_time'=>$option_time])
            ->orderBy('complete_time asc')
            ->limit(100)
            ->asArray()
            ->all();

        // 遍历还款订单
        foreach ($order_overdue as $k => $v) {

            // 获取标的
            $product_data = QfbProduct::find()->where(['id'=>$v['product_id']])->asArray()->one();

            $total_acount = $v['money']+$v['interest']+$v['overdue_money'];
            $income = $v['interest']+$v['overdue_money'];

            // 初始化 --默认是借款直接还款投资人 -- 还款类型
            $repayment_type = 3;
            $remark = '借款人还逾期款';
            // 逾期还款订单id
            $order_repayment_id = $v['id'];

            $sn = $this->getBindSn('HK_O_');

            unset($details);
            unset($reqData);
            $details['bizType'] = 'COMPENSATORY_REPAYMENT';
            $details['freezeRequestNo'] = $v['sn'];
            // 出款用户编号
            $details['sourcePlatformUserNo'] = $v['to_member_id'];
            // 收款用户编号
            $details['targetPlatformUserNo'] = $platform_user_id;
            // 总额包含利息
            $details['amount'] = $total_acount;
            // 利息
            $details['income'] = $income;
            // 请求流水号
            $reqData['requestNo'] = $sn;
            // 交易类型
            $reqData['tradeType'] = 'REPAYMENT';
            // 标的号
            $reqData['projectNo'] = $product_data['sn'];
            // 业务明细
            $reqData['details'][] = $details;

            $result = $hkyh->createPostParam($serviceName, $reqData);

            $tran = \Yii::$app->db->beginTransaction();

            try {

                if (trim(strtoupper($result['status'])) != 'SUCCESS')
                    throw new \Exception($result['message']);

                $data = json_decode($result['data'], true);

                if (trim(strtoupper($data['status'])) != 'SUCCESS')
                    throw new \Exception($data['errorMessage']);

                if (trim(strtoupper($data['transactionStatus'])) != 'SUCCESS')
                    throw new \Exception('还代偿款失败');

                if (trim(strtoupper($data['transactionStatus'])) == 'SUCCESS'){

                    // $repay_money += $total_acount;

                    // 添加平台收益信息有误
                    QfbPtAccount::updateAll(['money' => $pt_account_money+$total_acount], ['id' => $pt_account_id]);

                    // 添加平台收支记录
                    unset($plat_form_income_model);
                    $plat_form_income_model = new QfbPlatformIncome();
                    $plat_form_income_model->platform_name = $pt_account['name'];
                    $plat_form_income_model->sn = $sn;
                    $plat_form_income_model->product_name = '产品id：'.$v['product_id'];
                    $plat_form_income_model->member_id = $v['member_id'];
                    $plat_form_income_model->remark = '平台代偿还款';
                    $plat_form_income_model->complete_time = time();
                    $plat_form_income_model->amount = $total_acount;
                    $plat_form_income_model->balance = $balance+$total_acount;
                    $plat_form_income_model->ls_sn = $sn;
                    $plat_form_income_model->type = 4;

                    if(!$plat_form_income_model->save())
                        throw new \Exception('添加平台收益信息有误');

                    ////////////////////////////////////////////////////////////////////////////////////////////////////
                    //扣减冻结金额
                    unset($investMoneyModel);
                    // 扣减借款用户零钱
                    $investMoneyModel = QfbMemberMoney::findOne(['member_id' => $v['to_member_id']]);
                    $investMoneyModel->lock_money -= $total_acount;

                    if (!$investMoneyModel->save())
                        throw new \Exception('扣减借款人账户零钱有误');

                    // 变更逾期订单状态
                    $update = QfbOrderOverdue::updateAll(['status'=>2], ['id'=>$v['id']]);
                    if (!$update)
                        throw new \Exception('变更逾期订单状态有误');

                    // 添加还款记录
                    if( !$this->createRepaymentLog($order_repayment_id,$sn,$repayment_type,$v['money'],$income,0, $v['to_member_id'],$platform_user_id,$remark))
                        throw new \Exception('添加还款记录有误');

                    // 更新标的已还款金额
                    $update = QfbProduct::updateAll(['actual_repayment_money'=>($product_data['actual_repayment_money']+$v['money']+$v['interest'])], ['id'=>$v['product_id']]);
                    if( !$update )
                        throw new \Exception('更新已还款金额有误');

                    $pt_account_money += $total_acount;
                    $balance += $total_acount;

                    $tran->commit();

                    $msg = '还款成功';
                }

            } catch (\Exception $e) {
                $tran->rollback();
                $msg = $e->getMessage();
            }

            // 变更标的状态
            $this->change_product_status($v['product_id']);

            // 记录日志
            $fileName = "HK__O_".$serviceName . "_DIRECT.log";
            $content = "还逾期款操作     执行时间：".date("Y-m-d H:i:s", time())."   逾期订单ID：{$v['id']}   产品ID：{$v['product_id']}   操作信息：".$msg.
                "     请求数据：".json_encode($reqData)."     响应数据:".json_encode($result)."\r\n";
            LogService::hkyh_write_log($fileName, $content);

        }// 遍历还款订单

        // 记录日志
        $fileName = "HK_".$serviceName . "_DIRECT.log";
        $content = "------------结束时间：".date("Y-m-d H:i:s",time())."-------本次执行耗时：".(time()-$start_time)." 秒-------\r\n\r\n";
        LogService::hkyh_write_log($fileName, $content);

        $this->delLockFunc($funcName);
        echo "end";
        exit;
    }

    /*
     * 变革标的状态
     * */
    public function change_product_status($product_id){

        $order_overdue_count = QfbOrderOverdue::find()->where('product_id=:product_id and status != 2', [':product_id'=>$product_id])->count();
        if($order_overdue_count > 0)
            return false;

        $order_repayment_count = QfbOrderRepayment::find()->where('product_id=:product_id and status != 2', [':product_id'=>$product_id])->count();
        if($order_repayment_count > 0)
            return false;

        QfbProduct::updateAll(['status'=>8], ['id'=>$product_id]);

    }


    /**
     * 投标对账
     * @author lijunwei
     */
     public function actionObject()
    {
        $start_time = time();

        $funcName = 'object';

        if($this->getLockFunc($funcName) === 1){
            exit;
        }
        $this->setLockFunc($funcName);

        $hkyh = \Yii::$app->Hkyh;
        $serviceName = 'QUERY_TRANSACTION';
        $reqData['transactionType'] = 'PRETRANSACTION';

        // 脚本时间差
        $option_time = time()-60*$this->time_limit;
        //查询已投标的数据
        $data = QfbOrderFix::find()
            ->where('option_status=30 and option_time<=:option_time',[':option_time'=>$option_time])
            ->limit(100)
            ->asArray()
            ->all();

        foreach ($data as $key => $value) {

            $reqData['requestNo'] = $value['sn'];
            $result = $hkyh->createPostParam($serviceName, $reqData);

            try {

                if (trim(strtoupper($result['status'])) != 'SUCCESS')
                    throw new \Exception('投标对账异常');

                $data = json_decode($result['data'], true);

                if (trim(strtoupper($data['status'])) == 'INIT'){

                    // 订单不存在
                    if($data['errorCode'] == '100007'){
                        // 变更支付失败
                        QfbOrderFix::updateAll(['status'=>4, 'option_status'=>39], ['id'=>$value['id']]);
                    }
                    throw new \Exception($data['errorMessage']);
                }

                /*if (trim(strtoupper($data['records'][0]['status'])) != 'FREEZED')
                    throw new \Exception($data['errorMessage']);*/

                // 支付失败的表更，  INIT的还未处理
                if (trim(strtoupper($data['records'][0]['status'])) == 'FAIL' || trim(strtoupper($data['records'][0]['status'])) == 'ERROR' ){

                    // 变更支付失败
                    QfbOrderFix::updateAll(['status'=>4, 'option_status'=>39], ['id'=>$value['id']]);
                    throw new \Exception($data['errorMessage']);
                }

                // 处理 数据
                unset($respData);
                $respData['bizType'] = $data['records'][0]['bizType'];
                $respData['requestNo'] = $value['sn'];
                $respData['code'] = '0';
                $respData['status'] = 'SUCCESS';

                unset($temp_data);
                $temp_data['status'] = 'SUCCESS';
                $temp_data['respData'] = json_encode($respData);

                // 处理用户处理成功回调的业务
                $hkyh_service = new HkyhService();
                $result = $hkyh_service->userPreTransaction($temp_data);

                // 成功与失败都会返回msg
                $msg = $result['msg'];

            } catch (\Exception $e) {
                $msg = $e->getMessage();
            }

            // 记录日志
            $fileName = "TB_".$serviceName . "_DIRECT.log";
            $content = "投标对账操作     执行时间：".date("Y-m-d H:i:s", time())."     产品id:".$value['product_id']." - 订单预处理流水号：".$value['sn']."     操作信息：".$msg.
                "     请求数据：".json_encode($reqData)."     响应数据:".json_encode($result)."\r\n";
            LogService::hkyh_write_log($fileName, $content);
        }

        // 记录日志
        $fileName = "TB_".$serviceName . "_DIRECT.log";
        $content = "-------结束时间：".date("Y-m-d H:i:s",time())."-------本次执行耗时：".(time()-$start_time)." 秒-------\r\n\r\n";
        LogService::hkyh_write_log($fileName, $content);

        $this->delLockFunc($funcName);
        echo "end";
        exit;
    }

    /**
     * 用户查询对账
     * @author lijunwei
     */
    public function actionMemQuery()
    {
        $start_time = time();

        $funcName = 'mem_query';

        if($this->getLockFunc($funcName) === 1){
            exit;
        }
        $this->setLockFunc($funcName);

        // 脚本时间差
        $option_time = time()-60*$this->time_limit;
        //获取所有注册虚拟账户异常的用户
        $data = QfbMember::find()
            ->select('id')
            ->where('is_dredge=9 and option_time<=:option_time', [':option_time'=>$option_time])
            ->limit(100)
            ->asArray()->all();

        foreach ($data as $key => $value) {
            $memberService = new MemberService();

            // 调用接口查询，确定是否开户
            $getHkyhUser = MemberService::getHkyhUser($value['id']);

            $json_de_data = json_decode($getHkyhUser['data']['data'], true);

            // 错误代码-用户不存在--变更用户未开过户
            if (isset($json_de_data['errorCode']) && $json_de_data['errorCode'] == '100009')
                QfbMember::updateAll(['is_dredge' => 0], ['id' => $value['id']]);

            if ($json_de_data['status'] == 'INIT') {
                $msg = '用户编号：' . $value['id'] . ',' . $json_de_data['errorMessage'];
            } else {
                $json_de_data ['realName'] = $json_de_data ['name'];
                unset ($json_de_data ['name']);

                $params['respData'] = json_encode($json_de_data);

                // 已在银行系统开户且未在平台做标识，处理平台标识处理
                $hkyhService = new HkyhService();

                $result = $hkyhService->hkyhRester($params);

                $msg = '用户编号：' . $value['id'] . ',' . $result['msg'];
            }

            // 记录日志
            $fileName = "CU_QUERY_USER_INFORMATION_DIRECT.log";
            $content = "用户查询操作     执行时间：" . date("Y-m-d H:i:s", time()) . " ，结果：" . $msg . "   响应数据：" . json_encode($getHkyhUser) . "\r\n";
            LogService::hkyh_write_log($fileName, $content);

            // \Yii::info($msg, 'memquery');
        }

        // 记录日志
        $fileName = "CU_QUERY_USER_INFORMATION_DIRECT.log";
        $content = "-------结束时间：".date("Y-m-d H:i:s",time())."-------本次执行耗时：" . (time() - $start_time) . " 秒-------\r\n\r\n";
        LogService::hkyh_write_log($fileName, $content);

        $this->delLockFunc($funcName);
        echo "end";
        exit;
    }

    /**
     * 设置流水号
     */
    public function getBindSn($type = '')
    {
        //生成随机字母+数字
        $str = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $code = "";
        $len = strlen($str);
        for ($i = 0; $i < 6; $i++) {
            $code .= $str{rand(0, $len - 1)};
        }
        return $type . date('YmdHis') . $code;
    }

    /**
     * 添加金额日志
     */
    public function createMoneyLog($params){

        if(!isset($params['member_id'])
            || !isset($params['type'])
            || !isset($params['money_type'])
            || !isset($params['money'])
            || !isset($params['old_money'])
            || !isset($params['action'])
            || !isset($params['remark'])  )
            return ['status'=>false, 'message'=>'参数有误'];

        //日志 --- 添加投资本金返还日志
        $moneyLogModel = new QfbMoneyLog($params['member_id']);
        $moneyLogModel->member_id = $params['member_id'];
        //1收入，2支出
        $moneyLogModel->type = $params['type'];
        //金额类型 1零钱 2活期 3定期
        $moneyLogModel->money_type = $params['money_type'];
        // 添加的金额
        $moneyLogModel->money = $params['money'];
        $moneyLogModel->create_time = time();
        $moneyLogModel->old_money = $params['old_money'];
        $moneyLogModel->action = $params['action'];
        $moneyLogModel->remark = $params['remark'];

        if (!$moneyLogModel->save())
            return ['status'=>false, 'message'=>'添加用户日志有误'];

        return ['status'=>true, 'message'=>''];
    }

    /**
     * 对账文件下载
     **/
    public function actionDonload(){
        set_time_limit(0);
        $hkyh = \Yii::$app->Hkyh;

        $funcName = 'download';

        if($this->getLockFunc($funcName, 60) === 1){
            exit;
        }
        $this->setLockFunc($funcName);

        $fileArr = ['COMMISSION', 'RECHARGE', 'TRANSACTION', 'WITHDRAW'];

        $serviceName = 'DOWNLOAD_CHECKFILE';
        $date = date("Ymd", time()-24*3600*1);
        $reqData['fileDate'] = $date;
        $result = $hkyh->createPostParam($serviceName,$reqData);

        if(strtoupper(trim($result['status'])) == 'SUCCESS'){

            $path = \Yii::$app->BasePath.'/../common/extension/hkyh/hkyhfile/'.$date.'/';

            if(!is_dir($path)) mkdir($path, 0777);

            $filename = $date.".zip";
            if(file_exists($path.$filename))
                exit;

            $myfile = fopen($path.$filename, "w") or die("创建文件失败");
            fwrite($myfile, $result['data']);
            fclose($myfile);

            $file = new File();

            $file->unzip($path, $filename);

            foreach($fileArr as $key=>$value){
                $txtName = $date.'_'.$hkyh->platformNo.'_'.$value.'.txt';
                $this->downloadModel($value, $path.$txtName);
            }
        }

        $this->delLockFunc($funcName);
        echo "end";
        exit;
    }

    /**
     * 充值文件对账
     */
    public function actionFindUserPay()
    {
        $all_status = true;

        //判断对账文件下载是否完成
        if($this->getLockFunc('download', 30) === 1) exit;

        $funcName = 'find_user_pay';

        if($this->getLockFunc($funcName) === 1){
            exit;
        }
        $this->setLockFunc($funcName);

        $file_name = date("Ymd", time()-24*3600*1);

        // 取出昨天充值业务 对账记录
        $tr_data = QfbTemporaryReconciliation::find()->where(['file_name'=>$file_name, 'file_type'=>0])->asArray()->one();

        $reconciliation_id = $tr_data['id'];

        if (empty($tr_data)) {
            // 开始跑脚本
            $tr = new QfbTemporaryReconciliation();
            $tr->file_name = $file_name;
            $tr->file_type = 0;
            $tr->withhold_time = time();
            $tr->end_time = 0;
            $tr->remark = '正在对账中.....';
            $tr->save();
            $reconciliation_id = Yii::$app->db->getLastInsertID();
        }

        $connection = \Yii::$app->db;

        //取出充值临时表数据
        $data = QfbTemporaryRecharge::find()->select('id,ls_sn,money')->where(['date'=>$file_name])->asArray()->all();

        if($data){
            foreach ($data as $key => $value) {

                $status = true;
                $msg = '';

                 // 查询充值订单
                $code = substr($value['ls_sn'],0,2);
                if ($code == 'PT') {
                    $order_data = QfbPtOrder::find()->where(['sn'=>$value['ls_sn']])->one();
                } else {
                    $order_data = QfbOrder::find()->where(['sn'=>$value['ls_sn']])->one();
                }

                if(empty($order_data)) {

                    $msg .= "数据库订单不存在\r\n";
                    $status = false;
                    $all_status = false;
                }else{

                    if($order_data->is_check != 1){
                        $msg .= "订单状态不匹配\r\n";
                        $status = false;
                        $all_status = false;
                    }

                    if($order_data->money != $value['money']) {
                        $msg .= '数据库金额'.$order_data->money.'元，对账返回金额'.$value['money'].'元'."\r\n";
                        $status = false;
                        $all_status = false;
                    }
                }

                // 记录异常日志
                if ($status != true) {
                    $rl = new QfbReconciliationLog();

                    $rl->ls_sn = $value['ls_sn'];
                    $rl->r_id = $reconciliation_id;
                    $rl->platform_money = empty($order_data->money) ? 0 : $order_data->money;
                    $rl->account_money = empty($value['money']) ? 0 : $value['money'];
                    $rl->type = 2;
                    $rl->create_time = time();
                    $rl->remark = $msg;
                    $rl->save();

                }else{
                    // 删除临时表记录
                    $connection ->createCommand('DELETE FROM '.QfbTemporaryRecharge::tableName().' where id='.$value['id'])->execute();
                }
            }
        }

        //初始化状态
        $status = 2;
        $remark = '对账无误';
        $end_time = time();
        if (!$all_status) {
            $status = 1;
            $remark = '对账异常';
        }

        //更新临时对账表记录
        $update_reconciliation['status'] = $status;
        $update_reconciliation['remark'] = $remark;
        $update_reconciliation['end_time'] = $end_time;
        QfbTemporaryReconciliation::updateAll($update_reconciliation, ['id'=>$reconciliation_id]);

        // 验证昨天的业务是否全部成功
        $tr_count = QfbTemporaryReconciliation::find()->where(['file_name'=>$file_name, 'status'=>2])->count();

        if($tr_count == 4) {
            $this->ConfirmCheckfile($file_name);
        }

        $this->delLockFunc($funcName);
        echo 'end';
        exit;
    }

    /**
     * 佣金对账
     **/
    public function actionCommissionReconciliation(){

        // 初始化
        $all_status = true;
        $remark = '';

        //判断对账文件下载是否完成
        if($this->getLockFunc('download', 30) === 1) exit;

        $funcName = 'commission_reconciliation';

        if($this->getLockFunc($funcName) === 1){
            exit;
        }
        $this->setLockFunc($funcName);

        $file_name = date("Ymd", time()-24*3600*1);

        // 获取佣金对账记录
        $tc_data = QfbTemporaryCommission::find()->where(['date'=>$file_name])->limit(9999999)->orderBy('id asc')->all();

        $tr_data = QfbTemporaryReconciliation::find()->where(['file_name'=>$file_name, 'file_type'=>3])->asArray()->one();

        $reconciliation_id = $tr_data['id'];

        if(empty($tr_data)){

            // 开始跑脚本
            $tr = new QfbTemporaryReconciliation();
            $tr->file_name = $file_name;
            $tr->file_type = 3;
            $tr->withhold_time = time();
            $tr->end_time = 0;
            $tr->remark = '正在对账中.....';
            $tr->save();
            $reconciliation_id = $tr->id;
        }

        $connection = \Yii::$app->db;

        if($tc_data){
            // 遍历对账订单
            foreach($tc_data as $tc_key=>$tc_val) {

                // 初始化
                $msg = '';
                $status = true;

                // 对账文件内容
                $sn = $tc_val->product_sn;
                $ls_sn = $tc_val->ls_sn;
                $initiator_platform_id = $tc_val->initiator_platform_id;
                $money = $tc_val->money;

                $type_str = substr($ls_sn, 0, 2);

                if($type_str == 'FK'){
                    // 平台收益表
                    $data = QfbPlatformIncome::find()->where('ls_sn=:ls_sn', [':ls_sn'=>$ls_sn])->asArray()->one();

                    $amount = trim($data['amount']);
                }else if($type_str == 'TX'){
                    $data = QfbOrder::find()->where(['sn' => $ls_sn])->asArray()->one();
                    $amount = trim($data['fee']);
                }else{
                    continue;
                }

                if (empty($data)) {
                    $msg = '未找到收益订单';
                    $status = false;
                    $all_status = false;
                    // $remark .= $msg;
                }

                if ($money != $amount && !empty($data)) {
                    $msg = '对账文件发生金额：' . $money . ', 平台数据库金额：' . $amount . ', 相差: ' . ($money - $amount);
                    $status = false;
                    $all_status = false;
                    // $remark .= $msg;
                }

                switch ($type_str) {
                    case 'TX' :
                        $type = 0;
                        break;
                    case 'FK' :
                        $type = 1;
                        break;
                    default :
                        $type = 99;
                        break;
                }

                // 记录异常日志
                if ($status != true) {
                    $rl = new QfbReconciliationLog();

                    $rl->ls_sn = $ls_sn;
                    $rl->r_id = $reconciliation_id;
                    $rl->platform_money = empty($amount) ? 0 : $amount;
                    $rl->account_money = $money;
                    $rl->type = $type;
                    $rl->create_time = time();
                    $rl->remark = $msg;
                    $rl->save();

                }else{
                    // 删除临时表记录
                    $connection ->createCommand('DELETE FROM '.QfbTemporaryCommission::tableName().' where id='.$tc_val['id'])->execute();
                }

            }// 遍历对账订单
        }

        // 已对账完成
        $status = 2;
        $remark = '对账无误';
        $end_time = time();
        // 对账失败
        if($all_status == false){
            $status = 1;
            $remark = '对账异常';
        }

        $update_reconciliation['status'] = $status;
        $update_reconciliation['remark'] = $remark;
        $update_reconciliation['end_time'] = $end_time;
        QfbTemporaryReconciliation::updateAll($update_reconciliation, ['id'=>$reconciliation_id]);

        //////////////////////////////////////////////////////////////////////////////////////////////////
        // 验证昨天的业务是否全部成功
        $tr_count = QfbTemporaryReconciliation::find()->where(['file_name'=>$file_name, 'status'=>2])->count();

        if($tr_count == 4)
            $this->ConfirmCheckfile($file_name);

        $this->delLockFunc($funcName);
        echo 'end';
        exit;
    }

    /**
     * 前N天对账确认  time()-24*3600*1 自定义修改
     **/
    public function actionConfirmFile(){

        //  前一天
        $file_name = date("Ymd", time()-24*3600*1);

        $this->ConfirmCheckfile($file_name);
    }

    /**
     * 对账确认
     **/
    public function ConfirmCheckfile($file_name){

        // 对账确认
        $hkyh = \Yii::$app->Hkyh;
        $serviceName = 'CONFIRM_CHECKFILE';
        $detail['fileType'] = 'TRANSACTION';
        $reqData['requestNo'] = $this->getBindSn('DZ');
        $reqData['fileDate'] = $file_name;
        $reqData['detail'] = [
            ['fileType'=>'RECHARGE'],
            ['fileType'=>'WITHDRAW'],
            ['fileType'=>'COMMISSION'],
            ['fileType'=>'TRANSACTION'],
            ['fileType'=>'BACKROLL_RECHARGE'],
        ];

        $result = $hkyh->createPostParam($serviceName,$reqData);

        // 初始化
        $msg = '未知错误';
        $update_tr['affirm_status'] = '1';

        if(strtoupper(trim($result['status'])) == 'SUCCESS'){
            $data = json_decode($result['data'], true);
            if(strtoupper(trim($data['status'])) == 'SUCCESS'){
                $affirm_status = 2;
                $msg = '对账确认成功';
            }else{
                $affirm_status = 1;
                $msg = $data['errorMessage'];

                if($data['errorCode'] == '100008' && trim($data['errorMessage']) == '对账文件已确认，请勿重复确认。' ){
                    $affirm_status = 2;
                }
            }
        }

        // 更新对账确认状态
        $update_tr['affirm_status'] = $affirm_status;
        $update_tr['remark'] = $msg;
        QfbTemporaryReconciliation::updateAll($update_tr, ['file_name'=>$file_name]);

        // 记录日志
        $fileName = "DZ_".$serviceName . "_DIRECT.log";
        $content = "文件对账确认操作     执行时间：".date("Y-m-d H:i:s", time())."     请求流水号:".$reqData['requestNo']."     操作信息：".$msg.
            "     请求数据：".json_encode($reqData)."     响应数据:".json_encode($result)."\r\n";
        LogService::hkyh_write_log($fileName, $content);

        echo 'end';
        exit;
    }

    private function downloadModel($name, $filename)
    {
        switch($name){
            case 'COMMISSION':
                $model = new QfbTemporaryCommission();
                $attributes = $model->attributes();
                break;
            case 'RECHARGE':
                $model = new QfbTemporaryRecharge();
                $attributes = $model->attributes();
                break;
            case 'BACKROLL_RECHARGE':
//                $model = new QfbTemporaryRecharge();
//                $attributes = $model->attributes();
                break;
            case 'TRANSACTION':
                $model = new QfbTemporaryTransaction();
                $attributes = $model->attributes();
                break;
            case 'USER':
//                $model = new QfbTemporaryRecharge();
//                $attributes = $model->attributes();
                break;
            case 'WITHDRAW':
                $model = new QfbTemporaryWithdraw();
                $attributes = $model->attributes();
                break;
        }

        $file = new File();

        array_shift($attributes);
        $data = $file->read($filename, $attributes); //读取txt文件内容

        $date = date("Ymd", time()-24*3600*1);
        if(empty($data)) return false;

        if(empty($data)) return false;
        foreach($data as &$value)
            $value['date'] = $date;

        return Yii::$app->db->createCommand()->batchInsert($model->tableName(), $attributes, $data)->execute();
    }

    /**
     * 提现文件对账
     * @return bool
     * @throws yii\db\Exception
     */
    public function actionCheckWithdraw()
    {

        $all_status = true;

        $date = date("Ymd", time()-24*3600*1);

        //判断对账文件下载是否完成
        if($this->getLockFunc('download', 30) === 1) exit;

        $funcName = 'check_with_draw';

        if($this->getLockFunc($funcName) === 1){
            exit;
        }
        $this->setLockFunc($funcName);

        $reconciliation = new QfbTemporaryReconciliation();
        $recon = $reconciliation->findOne(['file_name'=>$date, 'file_type'=>1]);

        if(!$recon){
            $reconciliation->file_name = $date;
            $reconciliation->file_type = 1;
            $reconciliation->status = 0;
            $reconciliation->affirm_status = 0;
            $reconciliation->withhold_time = time();
            $reconciliation->end_time = 0;
            $reconciliation->remark = '正在提现对账...';
            $reconciliation->save();
            $reconciliation_id = $reconciliation->id;
        }else{
            $reconciliation_id = $recon->id;
        }

        $model = new QfbTemporaryWithdraw();

        $data = $model->find()->where(['date'=>$date])->orderBy('id')->limit(100000)->asArray()->all();

        if($data){
            foreach($data as $key=>$value){

                $reconciliationLog = new QfbReconciliationLog();
                $trans = Yii::$app->db->beginTransaction();

                try{

                    // 查询提现订单
                    $code = substr($value['ls_sn'],0,2);
                    if ($code == 'PT') {
                        $order = QfbPtOrder::find()->where(['sn'=>$value['ls_sn']])->one();
                    } else {
                        $order = QfbOrder::find()->where(['sn'=>$value['ls_sn']])->one();
                    }

                    if(empty($order)){
                        $all_status = false;
                        $reconciliationLog->r_id = $value['id'];
                        $reconciliationLog->ls_sn = $value['ls_sn'];
                        $reconciliationLog->platform_money = 0;
                        $reconciliationLog->account_money = $value['account_money'];
                        $reconciliationLog->type = 0;
                        $reconciliationLog->create_time = time();
                        $reconciliationLog->remark = '提现订单缺失';
                        if(!$reconciliationLog->save()){
                            throw new \Exception('日志异常表入库失败');
                        }

                    }else if($order->is_check == 2){
                        $all_status = false;
                        $reconciliationLog->r_id = $value['id'];
                        $reconciliationLog->ls_sn = $value['ls_sn'];
                        $reconciliationLog->platform_money = $order->price;
                        $reconciliationLog->account_money = $value['account_money'];
                        $reconciliationLog->type = 0;
                        $reconciliationLog->create_time = time();
                        $reconciliationLog->remark = '提现订单状态异常';
                        if(!$reconciliationLog->save()){
                            throw new \Exception('日志异常表入库失败');
                        }
                    }else{
                        if($order->price != $value['money'] && $order->money != $value['account_money']){
                            $all_status = false;
                            $reconciliationLog->r_id = $value['id'];
                            $reconciliationLog->ls_sn = $value['ls_sn'];
                            $reconciliationLog->platform_money = $order->price;
                            $reconciliationLog->account_money = $value['money'];
                            $reconciliationLog->type = 0;
                            $reconciliationLog->create_time = time();
                            $reconciliationLog->remark = '提现订单金额不对';
                            if(!$reconciliationLog->save()){
                                throw new \Exception('日志异常表入库失败');
                            }
                        }else{
                            if(!$model->deleteAll(['id'=>$value['id']])){
                                throw new \Exception('删除提现对账表数据失败');
                            }
                        }
                    }
                    $trans->commit();
                }catch(\Exception $e){
                    if($msg = $e->getMessage()){
                        $trans->rollBack();
                    }
                }
            }
        }

        // 已对账完成
        $status = 2;
        $remark = '对账无误';
        $end_time = time();
        // 对账失败
        if($all_status != true){
            $status = 1;
            $remark = '对账异常';
        }

        $update_reconciliation['status'] = $status;
        $update_reconciliation['remark'] = $remark;
        $update_reconciliation['end_time'] = $end_time;

        QfbTemporaryReconciliation::updateAll($update_reconciliation, ['id'=>$reconciliation_id]);

        // 验证昨天的业务是否全部成功
        $tr_count = QfbTemporaryReconciliation::find()->where(['file_name'=>$date, 'status'=>2])->count();

        if($tr_count == 4)
            $this->ConfirmCheckfile($date);

        $this->delLockFunc($funcName);
        echo 'end';
        exit;
    }

    /**
     * 交易文件对账
     * @return bool
     * @throws yii\db\Exception
     */
    public function actionCheckTranscation()
    {
        $date = date("Ymd", time()-24*3600*1);

        //判断对账文件下载是否完成
        if($this->getLockFunc('download', 30) === 1) exit;

        $funcName = 'check_transcation';

        if($this->getLockFunc($funcName) === 1){
            exit;
        }
        $this->setLockFunc($funcName);

        $reconciliation = new QfbTemporaryReconciliation();
        $recon = $reconciliation->findOne(['file_name'=>$date, 'file_type'=>2]);
        if(!$recon){
            $reconciliation->file_name = $date;
            $reconciliation->file_type = 2;
            $reconciliation->status = 0;
            $reconciliation->affirm_status = 0;
            $reconciliation->withhold_time = time();
            $reconciliation->end_time = 0;
            $reconciliation->remark = '正在交易对账...';
            $reconciliation->save();
            $reconciliation_id = $reconciliation->id;
        }else{
            $reconciliation_id = $recon->id;
        }

        // 初始化
        $msg = '';
        $all_status = true;
        $option_status_arr = [11,21];

        $model = new QfbTemporaryTransaction();
        $data = $model->find()->where(['date'=>$date])->orderBy('id asc')->limit(1000000)->asArray()->all();

        if($data){

            $connection = \Yii::$app->db;

            // 遍历交易订单
            foreach($data as $key=>$value){

                $snStr = substr($value['ls_sn'], 0, 2);

                $reconciliationLog = new QfbReconciliationLog();

                // 还款
                if( strtoupper($snStr) == 'HK' || strtoupper($snStr) == 'HK_O' || strtoupper($snStr) == 'HK_P') {

                    try{
                        //还款对账
                        $trans = Yii::$app->db->beginTransaction();

                        // 订单记录
                        unset($where_arr);
                        $where_arr['sn'] = $value['ls_sn'];
                        $repayment_log_model = new QfbOrderRepaymentLog('order_repayment_log');

                        // 订单记录
                        $order_repayment_log = $repayment_log_model->get_log($where_arr);

                        // 订单不存在
                        if(empty($order_repayment_log)){

                            $all_status = false;
                            $remark = '还款交易订单不存在';

                            $reconciliationLog->r_id = $value['id'];
                            $reconciliationLog->ls_sn = $value['ls_sn'];
                            $reconciliationLog->platform_money = 0;
                            $reconciliationLog->account_money = $value['money'] + $value['interest_money'];
                            $reconciliationLog->type = 3;
                            $reconciliationLog->create_time = time();
                            $reconciliationLog->remark = $remark;

                            if (!$reconciliationLog->save()) {
                                throw new \Exception('日志异常表入库失败');
                            }

                        }// 金额有误
                        else if(trim($value['money']) != trim($order_repayment_log['total_money'])){

                            $all_status = false;
                            $reconciliationLog->r_id = $value['id'];
                            $reconciliationLog->ls_sn = $value['ls_sn'];
                            $reconciliationLog->platform_money = $order_repayment_log['total_money'];
                            $reconciliationLog->account_money = $value['money'] + $value['interest_money'];
                            $reconciliationLog->type = 3;
                            $reconciliationLog->create_time = time();
                            $reconciliationLog->remark = '对账文件发生金额：' . ($value['money'] + $value['interest_money']) . ', 平台数据库金额：' . $order_repayment_log['total_money'] . ', 相差: ' . ($value['money'] + $value['interest_money'] - $order_repayment_log['total_money']);

                            if (!$reconciliationLog->save()) {
                                throw new \Exception('日志异常表入库失败');
                            }

                        }//删除对账无误数据
                        else{
                            $connection->createCommand('DELETE FROM '.QfbTemporaryTransaction::tableName().' where id='.$value['id'])->execute();
                        }

                        $trans->commit();

                    } catch (\Exception $e) {
                        if ($msg = $e->getMessage()) {
                            $trans->rollBack();
                        }
                    }

                }else if(strtoupper($snStr) == 'FK'){

                    //放款对账
                    $trans = Yii::$app->db->beginTransaction();

                    try{

                        unset($where_arr);
                        $where_arr['credit_sn'] = $value['ls_sn'];
                        // 订单记录
                        $orderFix = QfbOrderFix::find()->where($where_arr)->asArray()->one();

                        // 订单不存在
                        if(empty($orderFix)){

                            $remark = '放款交易订单不存在';

                            $all_status = false;
                            $reconciliationLog->r_id = $value['id'];
                            $reconciliationLog->ls_sn = $value['ls_sn'];
                            $reconciliationLog->platform_money = 0;
                            $reconciliationLog->account_money = $value['money'] + $value['interest_money'];
                            $reconciliationLog->type = 3;
                            $reconciliationLog->create_time = time();
                            $reconciliationLog->remark = $remark;

                            if (!$reconciliationLog->save()) {
                                throw new \Exception('日志异常表入库失败');
                            }
                        }
                        // 订单状态异常
                        else if( !in_array($orderFix['option_status'], $option_status_arr) ){

                            if(strtoupper($snStr) == 'FK') {
                                $remark = '放款订单状态异常';
                            }else if(strtoupper($snStr) == 'HK'){
                                $remark = '还款订单状态异常';
                            }

                            $all_status = false;
                            $reconciliationLog->r_id = $value['id'];
                            $reconciliationLog->ls_sn = $value['ls_sn'];
                            $reconciliationLog->platform_money = 0;
                            $reconciliationLog->account_money = $value['money'] + $value['interest_money'];
                            $reconciliationLog->type = 3;
                            $reconciliationLog->create_time = time();
                            $reconciliationLog->remark = '放款订单状态异常';

                            if (!$reconciliationLog->save()) {
                                throw new \Exception('日志异常表入库失败');
                            }
                        }
                        // 金额有误
                        else if(trim($value['money']) != trim($orderFix['money']) && strtoupper($snStr) == 'FK'){

                            $all_status = false;
                            $reconciliationLog->r_id = $value['id'];
                            $reconciliationLog->ls_sn = $value['ls_sn'];
                            $reconciliationLog->platform_money = $orderFix['money'];
                            $reconciliationLog->account_money = $value['money'] + $value['interest_money'];
                            $reconciliationLog->type = 3;
                            $reconciliationLog->create_time = time();
                            $reconciliationLog->remark = '对账文件发生金额：' . ($value['money'] + $value['interest_money']) . ', 平台数据库金额：' . $orderFix['money'] . ', 相差: ' . ($value['money'] + $value['interest_money'] - $orderFix['money']);

                            if (!$reconciliationLog->save()) {
                                throw new \Exception('日志异常表入库失败');
                            }
                        }
                        // 金额有误
                        else if(trim($value['interest_money']) != trim($orderFix['interest']) && strtoupper($snStr) == 'HK'){

                            $all_status = false;
                            $reconciliationLog->r_id = $value['id'];
                            $reconciliationLog->ls_sn = $value['ls_sn'];
                            $reconciliationLog->platform_money = $orderFix['interest'];
                            $reconciliationLog->account_money = $value['interest_money'];
                            $reconciliationLog->type = 3;
                            $reconciliationLog->create_time = time();
                            $reconciliationLog->remark = '对账文件发生金额：' . ($value['interest_money']) . ', 平台数据库金额：' . $orderFix['interest'] . ', 相差: ' . ($value['interest_money'] - $orderFix['interest']);

                            if (!$reconciliationLog->save()) {
                                throw new \Exception('日志异常表入库失败');
                            }
                        }
                        //删除对账无误数据
                        else{
                            $connection->createCommand('DELETE FROM '.QfbTemporaryTransaction::tableName().' where id='.$value['id'])->execute();
                        }

                        $trans->commit();
                    } catch (\Exception $e) {
                        if ($msg = $e->getMessage()) {
                            $trans->rollBack();
                        }
                    }
                }else{
                    $all_status = false;
                    $reconciliationLog->r_id = $value['id'];
                    $reconciliationLog->ls_sn = $value['ls_sn'];
                    $reconciliationLog->platform_money = 0;
                    $reconciliationLog->account_money = $value['money']+$value['interest_money'];
                    $reconciliationLog->type = 3;
                    $reconciliationLog->create_time = time();
                    $reconciliationLog->remark = '未知其他类型订单';
                    if(!$reconciliationLog->save()){
                        throw new \Exception('日志异常表入库失败');
                    }
                }
            }

        }

        // 已对账完成
        $status = 2;
        $remark = '对账无误';
        $end_time = time();

        // 对账失败
        if($all_status != true){
            $status = 1;
            $remark = '对账异常';
        }

        $update_reconciliation['status'] = $status;
        $update_reconciliation['remark'] = $remark;
        $update_reconciliation['end_time'] = $end_time;
        QfbTemporaryReconciliation::updateAll($update_reconciliation, ['id'=>$reconciliation_id]);

        // 验证昨天的业务是否全部成功
        $tr_count = QfbTemporaryReconciliation::find()->where(['file_name'=>$date, 'status'=>2])->count();

        if($tr_count == 4)
            $this->ConfirmCheckfile($date);

        $this->delLockFunc($funcName);
        echo 'end';
        exit;
    }

    /**
     * 判断标是否逾期
     */
    public function actionCheckOverdue()
    {
        $start_time = time();

        $funcName = 'overdue';

        if($this->getLockFunc($funcName) === 1){
            exit;
        }
        $this->setLockFunc($funcName);
        
        //获取待还款的标的
        $productList = QfbProduct::find()->where(['status'=>6, 'option_status'=>21])->asArray()->all();

        foreach($productList as $key=>$value){
            $orderRepayment = QfbOrderRepayment::find()->where(['product_id'=>$value['id'], 'is_overdue'=>0, 'status'=>0])->orderBy('periods')->asArray()->all();

            foreach($orderRepayment as $r_key=>$r_val){


                if($r_val['periods'] == 1 && $r_val['is_end'] == 1){
                    if($value['profit_day'] == 20){
                        $profitDay = $value['finish_time']+$r_val['invest_day']*Tool::$dayTime;
                        $startTime = $value['finish_time'];
                    }else{
                        $profitDay = $value['finish_time']+($r_val['invest_day']+1)*Tool::$dayTime;
                        $startTime = $value['finish_time']+Tool::$dayTime;
                    }
                }else{
                    if($value['profit_day'] == 20){
                        $profitDay = $value['finish_time']+Tool::$dayTime*(($r_val['periods']-1)*Tool::$periodsDay+$r_val['invest_day']);
                        $startTime = $value['finish_time']+(Tool::$periodsDay)*Tool::$dayTime*($r_val['periods']-1);
                    }else{
                        $profitDay = $value['finish_time']+Tool::$dayTime*(($r_val['periods']-1)*Tool::$periodsDay+$r_val['invest_day']+1);
                        $startTime = $value['finish_time']+(Tool::$periodsDay*($r_val['periods']-1)+1)*Tool::$dayTime;
                    }
                }

                $profitTime = Tool::endTime($profitDay)-24*3600;

                if($start_time > $profitTime && $r_val['is_overdue'] == 0){
                    $overdue = QfbOrderOverdue::findOne(['product_id'=>$value['id'], 'status'=>0]);
                    if($overdue){
                        $overdue->money += $r_val['money'];
                        $overdue->interest += $r_val['interest'];
                        $overdue->save();
                    }else{
                        $overdueModel = new QfbOrderOverdue();
                        $overdueModel->product_id = $value['id'];
                        $overdueModel->member_id = Yii::$app->params['platform_id'];
                        $overdueModel->to_member_id = $r_val['member_id'];
                        $overdueModel->money = $r_val['money'];
                        $overdueModel->interest = $r_val['interest'];
                        $overdueModel->overdue_money = 0;
                        $overdueModel->create_time = time();
                        $overdueModel->save();
                    }

                    $orderRepaymentModel = QfbOrderRepayment::findOne(['id'=>$r_val['id']]);
                    $orderRepaymentModel->is_overdue = 1;
                    $orderRepaymentModel->save();
                }
            }
        }

        // 记录日志
        $fileName = "YQ_CHECK_OVERDUE_INFORMATION_DIRECT.log";
        $content = "------------结束时间：".date("Y-m-d H:i:s",time())."---------本次执行耗时：" . (time() - $start_time) . " 秒-------\r\n\r\n";
        LogService::hkyh_write_log($fileName, $content);

        $this->delLockFunc($funcName);
        echo "end";
        exit;
        
    }

    /**
     * 每日逾期利息
     */
    public function actionOverdueInterest()
    {
        $start_time = time();

        $todayDate = date('Ymd', $start_time);

        $funcName = 'overdue_interest';

        if($this->getLockFunc($funcName) === 1){
            exit;
        }
        $this->setLockFunc($funcName);

        $orderOverdue = QfbOrderOverdue::find()
            ->where(['status'=>0])
            ->andFilterWhere(['<>', 'option_date', $todayDate])
            ->limit(100)
            ->orderBy('id')
            ->asArray()
            ->all();

        foreach($orderOverdue as $key=>$value){
            $orderOverdueModel = QfbOrderOverdue::findOne(['id'=>$value['id'], 'status'=>0]);
            $orderOverdueModel->overdue_money += Tool::moneyPlatform(($orderOverdueModel->money+$orderOverdueModel->interest)*Yii::$app->params['overdue_interest']);
            $orderOverdueModel->overdue_day += 1;
            $orderOverdueModel->option_date = $todayDate;
            $orderOverdueModel->save();
        }

        // 记录日志
        $fileName = "YQ_CHECK_OVERDUE_INFORMATION_DIRECT.log";
        $content = "-------------结束时间：".date("Y-m-d H:i:s", time())."--------本次执行耗时：" . (time() - $start_time) . " 秒-------\r\n\r\n";
        LogService::hkyh_write_log($fileName, $content);

        $this->delLockFunc($funcName);
        echo "end";
        exit;

    }

    /**
     * 设置方法锁
     * @param $funcName
     * @return mixed
     */
    private function setLockFunc($funcName)
    {
        $hkyhMongoService = new HkyhMongoService($funcName);
        $condition = ['key'=>1];
        $params = ['value'=>1, 'time'=>time()];

        if(!$hkyhMongoService->update($condition, $params)){
            $params['key'] = 1;
            $hkyhMongoService->insert($params);
        }
        return true;
    }

    /**
     * 获取方法锁
     * @param $funcName 方法名
     * @param $minute 时间（分钟）
     * @return mixed
     */
    private function getLockFunc($funcName, $minute=15)
    {
        $hkyhMongoService = new HkyhMongoService($funcName);
        $result = $hkyhMongoService->findOne([], ['value', 'time']);
        $nowTime = time();
        if(!empty($result['time']) && $result['value'] == 1){
            if($nowTime-$result['time'] >= $minute*60){
                $result['value'] = 0;
            }
        }else{
            $result['value'] = 0;
        }
        return  $result['value'];
    }

    /**
     * 销毁方法锁
     * @param $funcName
     * @return mixed
     */
    private function delLockFunc($funcName)
    {
        $hkyhMongoService = new HkyhMongoService($funcName);
        $condition = ['key'=>1];
        $params = ['value'=>0];
        return $hkyhMongoService->update($condition, $params);
    }

    /**
     *
     * $order_repayment_id: 还款订单编号
     * $repayment_type：还款类型:1 用户直接还款 2：平台代偿 3 用户还平台代偿金
     * $money：本金
     * $interest_money：利息
     * $other_money；其他费用（逾期金额）
     * $out_account_id：出款账户编号
     * $in_account_id：入款账户编号
     * $remark：备注
     *
     */
    private function createRepaymentLog($order_repayment_id, $sn, $repayment_type=1, $money=0, $interest_money=0, $other_money=0, $out_account_id, $in_account_id, $remark=''){

        // 还款记录
        $order_repayment_log = new QfbOrderRepaymentLog($order_repayment_id);

        $order_repayment_log->order_repayment_id = $order_repayment_id;
        $order_repayment_log->sn = $sn;
        $order_repayment_log->repayment_type = $repayment_type;
        $order_repayment_log->money = $money;
        $order_repayment_log->interest_money = $interest_money;
        $order_repayment_log->other_money = $other_money;
        $order_repayment_log->total_money = $money+$interest_money+$other_money;
        $order_repayment_log->out_account_id = $out_account_id;
        $order_repayment_log->in_account_id = $in_account_id;
        $order_repayment_log->remark = $remark;
        $order_repayment_log->create_time = time();

        if($order_repayment_log->save()){
            return true;
        }

        return false;
    }
}