<?php

namespace common\service;

use common\models\QfbOrderOverdue;
use common\models\QfbOrderRepayment;
use common\models\QfbOrderRepaymentExtend;
use yii;
use common\enum\ChannelEnum;
use api\common\helpers\ReseponseCode as Code;
use common\service\BaseService;
use common\service\BankService;
use common\models\QfbProduct;
use common\models\QfbBindingBank;
use common\models\QfbBank;
use common\models\QfbOrderFix;
use common\models\QfbMember;
use common\models\QfbMoneyLog;
use common\models\QfbMemberMoney;
use common\models\QfbOrder;
use common\models\QfbMemberInfo;
use League\Flysystem\Exception;
use common\service\OrderFixService;
use common\service\MoneyLogService;

/**
 * 支付通道回调处理控制器
 * @author xiaomalover <xiaomalover@gmail.com>
 */
class HkyhService extends BaseService
{

    /**
     * $data json 格式数据
     * 用户绑卡注册回调
     */
    public function hkyhRester($data){

        $temp_data = json_decode($data['respData'], true);

        /*
        // 记录回调日志
        /*$fileName = "hkyh_register.log";
        $content = "执行日期 ".date('Y-m-d H:i:s',time())."  平台用户编号：".$temp_data['platformUserNo']
                    ."  请求流水号：".$temp_data['requestNo']."  状态：".$temp_data['status']."\n";
        $this->serviceWirteLog($fileName, $content);*/

        if(empty($temp_data))
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '数据格式有误', 'data'=>$temp_data];

        if(strtoupper(trim($temp_data['status'])) != 'SUCCESS')
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '绑卡注册银行虚拟账户有误', 'data'=>$temp_data];

        $no = $temp_data['bankcardNo'];
        $name = $temp_data['realName'];
        $id_card = $temp_data['idCardNo'];
        $mobile = $temp_data['mobile'];

        /*测试*/
        $member_id = intval($temp_data['platformUserNo']) > 0 ? intval($temp_data['platformUserNo']) : 1;

        // 判断用户是否开通银行存管虚拟账户
        $member = QfbMember::find()->where('id=:member_id', [':member_id'=>$member_id])->asArray()->one();

        if(empty($member))
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '平台账号不匹配', 'data'=>$temp_data];

        if($member['is_dredge'] == 1)
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '用户已开通银行虚拟账户', 'data'=>$temp_data];

        // 开启事务
        $tran = yii::$app->db->beginTransaction();

        try{

            // 创建绑卡订单
            $BankService = new BankService();
            $bindingCard = $BankService->bindingCard($temp_data, $member_id);

            if($bindingCard['code'] != Code::HTTP_OK){
                $tran->rollback();
                return $bindingCard;
            }

            // 更新会员已绑卡注册标识
            $update = QfbMember::updateAll(['is_dredge'=>1], ['id'=>$member_id]);
            if(empty($update))
                throw new \Exception('标识用户已开户绑卡有误');

            //存实名认证信息
            $member_info = QfbMemberInfo::findOne(['member_id' => $member_id]);
            $member_info->realname = $name;
            $member_info->is_verify = 1;
            $member_info->card_no = $id_card;

            if(!$member_info->save())
                throw new \Exception('标识已绑卡实名认证有误');

            $tran->commit();
            return ['code' => Code::HTTP_OK, 'msg' => '绑卡注册成功', 'data'=>$temp_data];
        } catch (\Exception $e) {
            $tran->rollback();
            // 更新会员绑卡注册标识异常
            $qfbMember =  QfbMember::findOne($member_id);
            // 绑卡异常
            $qfbMember->is_dredge = 9;
            $qfbMember->save();
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => $e->getMessage(), 'data'=>$temp_data];
        }
    }

    //充值通道回调地址
    public function userPay($data)
    {

        $respData = json_decode($data['respData'], true);

         /* // 记录回调日志
        $fileName = "hkyh_userPay.log";
        $content = "执行日期 ".date('Y-m-d H:i:s',time())."  平台用户编号：".$respData['platformUserNo']
            ."  请求流水号：".$respData['requestNo']."  状态：".$respData['status']."\n";
        $this->serviceWirteLog($fileName, $content);*/

        if(empty($respData))
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '数据格式有误', 'data'=>$respData];

        // 充值成功
        if($respData['rechargeStatus'] == 'SUCCESS'){

            $member_id= intval($respData['platformUserNo']);

            // 开启事务
            $tran = yii::$app->db->beginTransaction();

            try{

                $member_money=QfbMemberMoney::findOne($member_id);

                $time = strtotime($respData['transactionTime']);

                //用户资金操作日志表操作
                $money_log=new QfbMoneyLog($member_id);

                $money_log->member_id=$member_id;
                $money_log->money=$respData['amount'];
                $money_log->create_time=$time;
                $money_log->old_money=$member_money->money;
                $money_log->action=2;
                $money_log->remark='充值';
                
                //用户资金表操作
                $member_money->money+=$respData['amount'];

                //订单表操作
                $order=QfbOrder::findOne(['sn'=>$respData['requestNo']]);
                $order->is_check=1;
                $order->complete_time=$time;
                //$order->bank_sn=(string)$respData['payCompanyRequestNo'];

                // 更新零钱
                if(!$member_money->save())
                    throw new \Exception('充值有误');

                // 添加充值日志
                if(!$money_log->save())
                    throw new \Exception('添加充值日志有误');

                // 更改充值订单状态
                if(!$order->save())
                    throw new \Exception('更新充值订单状态有误');

                $tran->commit();
                return ['code' =>  Code::HTTP_OK, 'msg' => '充值成功', 'data'=>$respData];

            } catch (\Exception $e) {
                $tran->rollback();
                return ['code' => Code::COMMON_ERROR_CODE, 'msg' => $e->getMessage(), 'data'=>$respData];
            }
        } else if($respData['rechargeStatus'] == 'PENDDING') {
            return ['code' => Code::HTTP_OK, 'msg' => '充值中,等待银行处理', 'data'=>$respData];
        }

        return ['code' =>  Code::COMMON_ERROR_CODE, 'msg' => $respData['errorMessage'], 'data'=>$respData];
    }

    /**
     * 提现回调
     * @param $data
     * @return array
     * @author panheng
     */
    public function WithdrawNotify($data){

        $result = json_decode($data['respData']);

        if($result && $result->code === '0'){

            $status = true;

            // 开启事务
            $trans = yii::$app->db->beginTransaction();

            $orderModel = QfbOrder::findOne(['sn'=>$result->requestNo]);

            //更新零钱表
            $memberMoneyModel = QfbMemberMoney::findOne($result->platformUserNo);

            $oldMoney = $memberMoneyModel->money;

            //订单表写入
            if(trim(strtoupper($result->withdrawStatus)) == 'ACCEPT'){
                $orderModel->is_check = 3;
                $memberMoneyModel->lock_money = ($memberMoneyModel->lock_money+$orderModel->price);
                $memberMoneyModel->money = bcsub($memberMoneyModel->money, $orderModel->price, 2); // 左边减去右边 保留2位小数
            }elseif(trim(strtoupper($result->withdrawStatus)) == 'CONFIRMING'){
                $status = false;
            }elseif(trim(strtoupper($result->withdrawStatus)) == 'REMITING'){
                $orderModel->is_check = 3;
                $memberMoneyModel->lock_money = ($memberMoneyModel->lock_money+$orderModel->price);
                $memberMoneyModel->money = bcsub($memberMoneyModel->money, $orderModel->price, 2); // 左边减去右边 保留2位小数
            }elseif(trim(strtoupper($result->withdrawStatus)) == 'SUCCESS'){
                $orderModel->is_check = 1;
                $memberMoneyModel->money = bcsub($memberMoneyModel->money, $orderModel->price, 2); // 左边减去右边 保留2位小数
            }else{
                $orderModel->is_check = 2;
                $orderModel->save();
                $trans->commit();
                $status = false;
            }

            if($status == true){
                $bankModel = new QfbBank();
                $bank = $bankModel::findOne(['no'=>$result->bankcardNo]);
                $orderModel->bank_id = $bank->id;

                if(!$orderModel->save())
                    $status = false;

                if(!$memberMoneyModel->save())
                    $status = false;

                //提现金额写入日志
                $moneyLogModel = new QfbMoneyLog($result->platformUserNo);
                $moneyLogModel->member_id = $result->platformUserNo;
                $moneyLogModel->type = 2;  //1收入，2支出
                $moneyLogModel->money_type = 1; //金额类型 1零钱 2活期 3定期
                $moneyLogModel->money = $orderModel->price;
                $moneyLogModel->create_time = time();
                $moneyLogModel->old_money = $oldMoney;
                $moneyLogModel->action = 7; //行为类型 1活期管理奖,2充值,3分润,4活期推荐奖,5定期推荐奖,6转账,7提现,8活期收益,9退款,10兑换,11(零钱活期转换),12(零钱定期转换),13店铺押金,14财富计划购买失败返回及收益,16定期收益,17定期管理奖,18定期分润,19现金奖励,20购买财富计划
                $moneyLogModel->remark = '提现';
                if(!$moneyLogModel->save()){
                    $status = false;
                }

                if($status == true){
                    $trans->commit();
                    return ['code' => Code::HTTP_OK, 'msg' => '提现成功，等待银行处理', 'data'=>$result];
                }else{
                    $trans->rollBack();
                }
            }

        }

        return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '提现失败', 'data'=>''];

    }

    //用户预处理回调
    public function userPreTransaction($data)
    {
        $respData = json_decode($data['respData'], true);

        if(empty($respData))
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '数据格式有误', 'data'=>$respData];

        if(empty($respData['requestNo']) || !isset($respData['requestNo']))
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '请求流水号不存在', 'data'=>$respData];

        $order_sn = $respData['requestNo'];

        //获取订单表数据
        $orderfix = QfbOrderFix::find()->where('sn=:sn', [':sn'=>$order_sn])->one();

        if(empty($orderfix))
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '投资订单不匹配', 'data'=>$respData];

        // 产品：已投资的金额字段累加
        $product = QfbProduct::find()->where('id=:id', [':id'=>$orderfix->product_id])->one();

        if(empty($product))
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '理财产品不存在', 'data'=>$respData];

        // 开启事务
        $tran = yii::$app->db->beginTransaction();

        try{

            // 支付失败
            if(strtoupper(trim($respData['status'])) != 'SUCCESS'){
                $orderfix->status = 4;
                // 记录操作时间、状态
                $orderfix->option_time = time();
                $orderfix->option_status = 39;
                $msg = '投标失败';
            }
            // 支付成功
            else{

                // 如投标日起息方式，付款后是投资中，反之收益中
                if($product->profit_day == '10' || $product->profit_day == '11'){
                    // 收益中
                    $orderfix->status = 2;

                    // 退出时间 = 当前时间+投资期限天数
                    $orderfix->end_time = time() + ($product->invest_day*24*3600);

                    if($product->profit_day == '11')
                        $orderfix->end_time = time() + (($product->invest_day+1)*24*3600);

                }else if($product->profit_day == '20' || $product->profit_day == '21'){
                    // 投资中
                    $orderfix->status = 1;
                }

                // 记录操作时间、状态
                $orderfix->option_time = time();
                $orderfix->option_status = 31;

                // 产品到期时间
                // $orderfix->end_time = $product->end_time;

                $has_money = $product->has_money + $orderfix->money;
                
                // 判断是否满标
                if($has_money == $product->stock_money){
                    $product->status = 2;
                    // 筹集完成时间
                    $product->finish_time = time();
                }

                // 更新已购买了多少金额
                $product->has_money = $has_money;

                if(!$product->save())
                    throw new \Exception('记录已购买金额有误');

                $memberMoneyService = new MemberMoneyService();
                $update_member_moneny = $memberMoneyService->buyFix($orderfix->member_id, $orderfix->pay_money, $orderfix->money);

                if(!$update_member_moneny)
                    throw new \Exception('扣除零钱有误');

                // 创建购买定期订单日志
                $money_log_arr['product_name'] = $product->product_name;
                $money_log_arr['money'] = $orderfix['money'];

                if (!$this->createBuyMoneyLog($orderfix->member_id, $money_log_arr))
                    throw new \Exception('添加投资日志有误');

                // 变更不是新手
                if($product->is_newer == 1){
                    // 变更非新手
                    unset($member_arr);
                    $member_arr['is_newer'] = 0;
                    $update = QfbMember::updateAll($member_arr, ['id'=>$orderfix->member_id]);
                }

                $msg = '投标成功';
            }

            if(!$orderfix->save())
                throw new \Exception('标识订单支付状态有误');

            // 判断是否满标 --变更 投资中 标的为收益中
            if($has_money == $product->stock_money && ($product->profit_day == '20' || $product->profit_day == '21')){

                unset($order_fix_arr);
                $order_fix_arr['status'] = 2;
                $order_fix_arr['end_time'] = time() + ($product->invest_day*24*3600);
                if($product->profit_day == '21'){
                    $order_fix_arr['end_time'] = time() + (($product->invest_day+1)*24*3600);
                }
                $update = QfbOrderFix::updateAll($order_fix_arr, ['status'=>1, 'product_id'=>$orderfix->product_id]);

                if(empty($update))
                    throw new \Exception('标识订单支付状态有误');
            }

            $tran->commit();

        }catch (\Exception $e) {
            $tran->rollback();
            $msg = $e->getMessage();
        }

        $data = [
            'product_name' => $product->product_name,
            // 'end_time' => date('Y-m-d H:i:s', $product->end_time),
            'pay_money' => $orderfix['pay_money'],
            'money' => $orderfix['money'],
            'bizType' => $respData['bizType'],
            'requestNo' => $respData['requestNo'],
            'code' => $respData['code'],
            'status' => $respData['status'],
        ];

        return ['code' =>  Code::HTTP_OK, 'msg' => $msg, 'data'=>$data];
    }

    /**
     * 购买产品日志
     * @param $member_id
     * @param $params
     * @return bool
     */
    public function createBuyMoneyLog($member_id, $params){

        //5.记录日志
        $moneyLogService = new MoneyLogService($member_id);
        $member_money = QfbMemberMoney::findOne($member_id);

        $logData = [
            [
                'member_id' => $member_id,
                'type' => 2,
                'money_type' => 1,
                'create_time' => $this->getTime(),
                'money' => $params['money'],
                'old_money' => $member_money->money,
                'action' => 12,
                'remark' => sprintf('购买%s', $params['product_name']),
            ],
            [
                'member_id' => $member_id,
                'type' => 1,
                'money_type' => 3,
                'create_time' => $this->getTime(),
                'money' => $params['money'],
                // 定期旧总额 不包含当前的支付金额
                'old_money' => $member_money->fix_money,
                'action' => 12,
                'remark' => sprintf('购买%s', $params['product_name']),
            ],
        ];

        if ($moneyLogService->createList($logData) == false) {
            return false;
        }
        return true;
    }
    /**
     * 银行
     * @param $member_id
     * @return bool
     */
    public function hkyhRegister($member_id)
    {

        // 调用接口查询，确定是否开户
        $getHkyhUser = MemberService::getHkyhUser($member_id);

        // 未开户
        if($getHkyhUser['code'] != 200){

            $hkyh = \Yii::$app->Hkyh;

            // 个人绑卡注册
            $serviceName = 'PERSONAL_REGISTER_EXPAND';

            // 流水号
            $sn = "LQ". $member_id . time() . rand(10, 99);

            $reqData['platformUserNo'] = $member_id; /*测试*/
            $reqData['requestNo'] = $sn;
            $reqData['idCardType'] = 'PRC_ID';
            $reqData['userRole'] = 'INVESTOR';
            $reqData['userLimitType'] = 'ID_CARD_NO_UNIQUE';
            $reqData['checkType'] = 'LIMIT';
            $reqData['redirectUrl'] =  $hkyh->RETURN_URL;

            // 到银行页面注册
            $hkyh->createPostParam($serviceName,$reqData);
        }

        // 处理下数据
        unset($params);
        $json_de_data = json_decode($getHkyhUser['data']['data'], true);
        $json_de_data ["realName"] = $json_de_data ["name"];
        unset ( $json_de_data ["name"] );

        $params['respData'] = json_encode($json_de_data);

        // 已在银行系统开户且未在平台做标识，处理平台标识处理
        $hkyhService = new HkyhService();

        $result = $hkyhService->hkyhRester($params);

        if($result['code'] == code::HTTP_OK){
            return true;
        }else{
            return false;
        }

    }

    /**
     * 用户开始还款
     * @param $data
     * @return array
     * @throws yii\db\Exception
     */
    public function hkyhRepayment($data)
    {
        // 开启事务
        $trans = yii::$app->db->beginTransaction();

        $status = true;

        $orderRepaymentExtend = QfbOrderRepaymentExtend::findOne(['sn'=>$data->requestNo, 'type'=>0]);

        /**修改还款订单表的状态为还款待确认**/
        $orderRepayment = QfbOrderRepayment::findOne(['id'=>$orderRepaymentExtend->order_id]);
        $orderRepayment->status = 1;
        $orderRepayment->sn = $data->requestNo;

        if(!$orderRepayment->save()){
            $status = false;
        }

        $orderRepaymentExtend->option_status = 1;

        if(!$orderRepaymentExtend->save()){
            $status = false;
        }

        //遍历下单订单表
        $orderFix = QfbOrderFix::find()->where(['product_id'=>$orderRepayment->product_id, 'option_status'=>11])->asArray()->all();

        foreach($orderFix as $key=>$value){
            $orderFixModel = QfbOrderFix::findOne($value['id']);
            $orderFixModel->option_status = 20; //标的订单状态修改为还款中
            if(!$orderFixModel->save()){
                $status = false;
            }
        }

        /**修改产品表的状态为还款待确认**/
        $product = QfbProduct::findOne(['id'=>$orderRepayment->product_id]);
        $product->total_repayment_money = $orderRepayment->money+$orderRepayment->interest;

        if(!$product->save()){
            $status = false;
        }

        //开始还款，冻结资金
        $memberMoney = QfbMemberMoney::findOne(['member_id'=>$orderRepayment->member_id]);
        $money = $orderRepayment->money+$orderRepayment->interest;

        //写入金额日志表
        $moneyLog = new QfbMoneyLog($orderRepayment->member_id);
        $moneyLog->member_id = $orderRepayment->member_id;
        $moneyLog->type = 2;
        $moneyLog->money_type = 1;
        $moneyLog->money = $money;
        $moneyLog->create_time = time();
        $moneyLog->old_money = $memberMoney->money;
        $moneyLog->action = 21;
        $moneyLog->remark = '借款人还款';
        if(!$moneyLog->save()){
            $status = false;
        }

        $memberMoney->money -= $money;
        $memberMoney->lock_money += $money;
        if(!$memberMoney->save()){
            $status = false;
        }

        if($status == true){
            $trans->commit();

            return ['code' =>  Code::HTTP_OK, 'msg' => '处理成功', 'data'=>''];
        }else{
            $trans->rollBack();

            return ['code' =>  Code::COMMON_ERROR_CODE, 'msg' => '入库失败', 'data'=>''];
        }
    }

    /**
     * 逾期还款
     * @param $data
     * @return array
     * @throws yii\db\Exception
     */
    public function hkyhOverdueRepayment($data)
    {
        // 开启事务
        $trans = yii::$app->db->beginTransaction();

        $status = true;

        $orderRepaymentExtend = QfbOrderRepaymentExtend::findOne(['sn'=>$data->requestNo, 'type'=>1]);

        /**修改还款订单表的状态为还款待确认**/
        $orderOverdue = QfbOrderOverdue::findOne(['id'=>$orderRepaymentExtend->order_id]);
        $orderOverdue->status = 1;
        $orderOverdue->sn = $data->requestNo;

        if(!$orderOverdue->save()){
            $status = false;
        }

        $orderRepaymentExtend->option_status = 1;

        if(!$orderRepaymentExtend->save()){
            $status = false;
        }

        //遍历下单订单表
        $orderFix = QfbOrderFix::find()->where(['product_id'=>$orderOverdue->product_id, 'option_status'=>11])->asArray()->all();

        foreach($orderFix as $key=>$value){
            $orderFixModel = QfbOrderFix::findOne($value['id']);
            $orderFixModel->option_status = 20; //标的订单状态修改为还款中
            if(!$orderFixModel->save()){
                $status = false;
            }
        }

        $repaymentMoney = $orderOverdue->money+$orderOverdue->interest+$orderOverdue->overdue_money;//总的还款金额

        /**修改产品表的状态为还款待确认**/
        $product = QfbProduct::findOne(['id'=>$orderOverdue->product_id]);
        $product->total_repayment_money = $repaymentMoney;

        if(!$product->save()){
            $status = false;
        }

        //开始还款，冻结资金
        $memberMoney = QfbMemberMoney::findOne(['member_id'=>$orderOverdue->to_member_id]);

        //写入金额日志表
        $moneyLog = new QfbMoneyLog($orderOverdue->to_member_id);
        $moneyLog->member_id = $orderOverdue->to_member_id;
        $moneyLog->type = 2;
        $moneyLog->money_type = 1;
        $moneyLog->money = $repaymentMoney;
        $moneyLog->create_time = time();
        $moneyLog->old_money = $memberMoney->money;
        $moneyLog->action = 21;
        $moneyLog->remark = '借款人还逾期款';
        if(!$moneyLog->save()){
            $status = false;
        }

        $memberMoney->money -= $repaymentMoney;
        $memberMoney->lock_money += $repaymentMoney;
        if(!$memberMoney->save()){
            $status = false;
        }

        if($status == true){
            $trans->commit();

            return ['code' =>  Code::HTTP_OK, 'msg' => '处理成功', 'data'=>''];
        }else{
            $trans->rollBack();

            return ['code' =>  Code::COMMON_ERROR_CODE, 'msg' => '入库失败', 'data'=>''];
        }
    }


    /**
     * 确认还款后(目前不需要用到)
     * @param $data
     * @return array
     * @throws yii\db\Exception
     */
    public function hkyhConfirmRepay($data)
    {
        // 开启事务
        $trans = yii::$app->db->beginTransaction();

        $status = true;

        /**修改还款订单表的状态为确认已还款**/
        $orderRepayment = QfbOrderRepayment::findOne(['sn'=>$data['requestNo']]);
        $orderRepayment->status = 2;

        if(!$orderRepayment->save()){
            $status = false;
        }

        /**修改产品表的状态为确认已还款**/
        $product = QfbProduct::findOne(['sn'=>$data['sn']]);
        $product->status = 8;

        if(!$product->save()){
            $status = false;
        }

        //确认还款，扣除冻结资金
        $memberMoney = QfbMemberMoney::findOne(['member_id'=>$data['platformUserNo']]);
        $memberMoney->lock_money -= $data['amount'];
        if(!$memberMoney->save()){
            $status = false;
        }

        //还款记录写入日志表
        $memberLogModel = new QfbMoneyLog($data['platformUserNo']);
        $memberLogModel->member_id = $data['platformUserNo'];
        $memberLogModel->type = 2;
        $memberLogModel->money_type = 1;
        $memberLogModel->money = $data['amount'];
        $memberLogModel->create_time = time();
        $memberLogModel->old_money = $memberMoney->money+$data['amount'];
        $memberLogModel->action = 21;
        $memberLogModel->remark = '还款';

        if(!$memberLogModel->save()){
            $status = false;
        }


        if($status == true){
            $trans->commit();

            return ['code' =>  Code::HTTP_OK, 'msg' => '处理成功', 'data'=>''];
        }else{
            $trans->rollBack();

            return ['code' =>  Code::COMMON_ERROR_CODE, 'msg' => '入库失败', 'data'=>''];
        }

    }

    public function hkyhTransaction($data){
        switch($data['bizType']){
            case 'REPAYMENT':
                return $this->hkyhConfirmRepay($data);
                break;
        }
    }

    /**
     * 将银行的类型参数转化为平台可识别参数
     * @param $type 银行证件类型参数
     * @return int
     * @author
     */
    public function cardType($type)
    {
        switch($type){
            case 'PRC_ID':
                $result = 1;
                break;
            case 'PASSPORT':
                $result = 3;
                break;
            case 'COMPATRIOTS_CARD':
                $result = 2;
                break;
            case 'PERMANENT_RESIDENCE':
                $result = 4;
                break;
        }

        return $result;

    }

    /**
     * 重置银行交易密码 --回调
     */
    public function resetPassword($data){

        $temp_data = json_decode($data['respData'], true);

        /* // 记录回调日志
        $fileName = "hkyh_register.log";
        $content = "执行日期 ".date('Y-m-d H:i:s',time())." 银行平台编号：".$data['platformNo']."  平台用户编号：".$temp_data['platformUserNo']
            ."  请求流水号：".$temp_data['requestNo']."  状态：".$temp_data['status']."\n";
        $this->serviceWirteLog($fileName, $content);*/

        if(empty($temp_data))
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '数据格式有误', 'data'=>$data];

        if(strtoupper(trim($temp_data['status'])) != 'SUCCESS')
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '修改交易密码有误', 'data'=>$data];

        return ['code' => Code::HTTP_OK, 'msg' => '修改交易密码成功', 'data'=>$data];
    }

    /**
     * 处理回调业务后，返回信息给app 绑卡注册
     **/
    public function register_info($params){

        $member_id = $params['order_id'];

        $member_data = QfbMemberInfo::find()->select('realname, card_no')->where('member_id=:member_id and is_verify=:is_verify',[':member_id'=>$member_id, ':is_verify'=>'1'])->asArray()->one();

        if(empty($member_data)){
            $msg = '用户不存在';
            $code = Code::COMMON_ERROR_CODE;
            $data = ['result'=>2, 'name'=>'', 'id_card'=>''];
        }else{
            $msg = '绑卡开户成功';
            $code = Code::HTTP_OK;
            $data = ['result'=>1, 'name'=>$member_data['realname'], 'id_card'=>$member_data['card_no']];
        }

        return ['code' => $code, 'msg' => $msg, 'data'=>$data];
    }

    /**
     * 处理回调业务后，返回信息给app 充值
     **/
    public function user_pay_info($params){

        // 请求流水号
        $requestNo = $params['order_id'];

        $status = 2;

        $order = QfbOrder::find()->select('money,create_time,sorts,is_check')->where('sn=:sn', [':sn'=>$requestNo])->asArray()->one();

        if(empty($order)){
            $data = ['result'=>$status, 'money'=>'', 'create_time'=>'', 'name'=>'订单流水不存在', 'tips'=>'流水不存在'];
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '订单流水不存在', 'data'=>$data];
        }

        if($order['sorts'] != '1'){
            $data = ['result'=>$status, 'money'=>'', 'create_time'=>'', 'name'=>'非充值订单', 'tips'=>'非充值订单'];
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '非充值订单', 'data'=>$data];
        }

        if($order['is_check'] == 1){
            $status = 1;
            $name = '充值成功';
        }else if($order['is_check'] == 2){
            $name = '充值失败';
        }else if($order['is_check'] == 3){
            $status = 1;
            $name = '充值中,等待银行处理';
        }else if($order['is_check'] == 4){
            $name = '无此交易';
        }else if($order['is_check'] == 5){
            $name = '通过审核';
        }else{
            $name = '待支付';
        }

        $data = ['result'=>$status, 'sn'=>$requestNo,'money'=>$order['money'], 'create_time'=>date("Y-m-d H:i:s", $order['create_time']), 'name'=>$name, 'tips'=>''];
        return ['code' => Code::HTTP_OK, 'msg' => '请求成功', 'data'=>$data];
    }

    /**
     * 处理回调业务后，返回信息给app 提现
     **/
    public function withdraw_info($params){

        // 请求流水号
        $requestNo = $params['order_id'];

        $status = 2;
        $tips = '';

        $order = QfbOrder::find()->select('money,price,create_time,sorts, type, is_check')->where('sn=:sn', [':sn'=>$requestNo])->asArray()->one();

        if(empty($order)){
            $data = ['result'=>$status, 'sn'=>$requestNo, 'money'=>'', 'create_time'=>'', 'name'=>'订单流水不存在', 'tips'=>'流水不存在'];
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '订单流水不存在', 'data'=>$data];
        }

        if($order['sorts'] != '1' && $order['type'] == 2){
            $data = ['result'=>$status,'sn'=>$requestNo, 'money'=>'', 'create_time'=>'', 'name'=>'非提现订单', 'tips'=>'非提现订单'];
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '非提现订单', 'data'=>$data];
        }

        if($order['is_check'] == 1){
            $status = 1;
            $name = '提现成功';
            $tips = '次日到账';
        }else if($order['is_check'] == 2){
            $name = '提现失败';
            $tips = '提现失败';
        }else if($order['is_check'] == 3){
            $status = 1;
            $name = '申请提现成功';
            $tips = '次日到账';
        }else if($order['is_check'] == 4){
            $name = '无此交易';
            $tips = '提现失败';
        }else if($order['is_check'] == 5){
            $name = '通过审核';
        }else{
            $name = '待提现';
        }

        $data = ['result'=>$status, 'sn'=>$requestNo, 'money'=>$order['price'], 'create_time'=>date("Y-m-d H:i:s", $order['create_time']), 'name'=>$name, 'tips'=>$tips];
        return ['code' => Code::HTTP_OK, 'msg' => '请求成功', 'data'=>$data];
    }

    /**
     * 处理回调业务后，返回信息给app 投标
     **/
    public function userPre_transaction_info($params){

        $tips = '';
        $status = 2;

        // 请求流水号
        $requestNo = $params['order_id'];

        $order_fix = QfbOrderFix::find()->where('sn=:sn',[':sn'=>$requestNo])->with('product')->asArray()->one();

        if(empty($order_fix)){

            $data = [
                'result'=>$status,
                'sn'=>$requestNo,
                'money'=>'',
                'real_money'=>0,
                'off_money'=>0,
                'create_time'=>0,
                'name'=>'订单不存在',
                'tips'=>'订单不存在'
            ];
            return ['code' => Code::HTTP_OK, 'msg' => '订单不存在', 'data'=>$data];
        }

        if($order_fix['status'] == 1){
            $status = 1;
            $end_time = $order_fix['product']['end_time'];
            $tips = '注：截止到'.date("Y-m-d H:i:s", $end_time).'，若该项目募款未达成，则您所投资资金将返还到您的零钱账户中';
            $name = '投资中';
        }else if($order_fix['status'] == 2){
            $status = 1;
            $end_time = $order_fix['product']['end_time'];
            $tips = '注：截止到'.date("Y-m-d H:i:s", $end_time).'，若该项目募款未达成，则您所投资资金将返还到您的零钱账户中';
            $name = '收益中';
        }else if($order_fix['status'] == 3){
            $name = '已到期';
        }else if($order_fix['status'] == 4){
            $name = '支付失败';
        }else{
            $name = '待支付';
        }

        $data = [
            'result'=>$status,
            'sn'=>$requestNo,
            'money'=>$order_fix['money'],
            'real_money'=>$order_fix['pay_money'],
            'off_money'=>($order_fix['money']-$order_fix['pay_money']),
            'create_time'=>date("Y-m-d H:i:s",
                $order_fix['create_time']),
            'name'=>$name,
            'tips'=>$tips
        ];
        return ['code' => Code::HTTP_OK, 'msg' => '请求成功', 'data'=>$data];

    }


    public function hkyhUserQuery($member_id)
    {
        $hkyh = \Yii::$app->Hkyh;

        $serviceName = 'QUERY_USER_INFORMATION';

        $reqData['platformUserNo'] = $member_id;  //平台用户编号

        $result = $hkyh->createPostParam($serviceName,$reqData);

        if($result['code'] === '0'){
            if(isset($result['bankcardNo']) && !empty($result['bankcardNo'])){
                return $result;
            }
        }

        return false;

    }

    /**
     * 确认提现
     * @param $sn
     * @return mixed
     */
    public function confirmWithdraw($sn)
    {
        $hkyh = \Yii::$app->Hkyh;

        $assetService = new AssetService();

        // 提现确认
        $serviceName = 'CONFIRM_WITHDRAW';

        //提现待确认流水号
        $reqData['preTransactionNo'] = $sn;
        // 请求流水号  --流水号 --不允许重复
        $reqData['requestNo'] = $assetService->getBindSn('TXQR');
        return $hkyh->createPostParam($serviceName,$reqData);
    }
}
