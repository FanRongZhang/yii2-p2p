<?php

namespace common\service;

use common\models\QfbOrderRepayment;
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

        if($member['is_dredge'])
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
            $qfbMember =  QfbMember::findOne($member_id);
            $qfbMember->is_dredge = 1;

            if(!$qfbMember->save())
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
            $qfbMember->is_dredge = 99;  // 绑卡异常
            $qfbMember->save();
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => $e->getMessage(), 'data'=>$temp_data];
        }
    }

    //充值通道回调地址
    public function userPay($data)
    {

        $respData = json_decode($data['respData'], true);

        if(empty($respData))
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '数据格式有误', 'data'=>$respData];

        if(strtoupper(trim($respData['status'])) == 'SUCCESS'){

            //请求查询接口判断是否充值成功
            $hkyh = \Yii::$app->Hkyh;

            $serviceName = 'QUERY_TRANSACTION';

            //业务请求流水号  -必填
            $data['requestNo'] = $respData['requestNo'];
            // 查询类型 --必填
            $data['transactionType'] = 'RECHARGE';

            $result = $hkyh->createPostParam($serviceName,$data);

            //判断请求状态
            if ($result['status'] == 'success') {

                $result= json_decode($result['data'],true);
                //判断调用状态
                if ($result['status'] == 'SUCCESS') {

                    $result=$result['records'][0];

                    //判断交易成功
                    if ($result['status'] == 'SUCCESS' && $result['amount'] == $respData['amount']) {

                        $member_id= intval($result['platformUserNo']);

                        $status = true;

                        // 开启事务
                        $trans = yii::$app->db->beginTransaction();

                        $member_money=QfbMemberMoney::findOne($member_id);

                        //用户资金操作日志表操作
                        $money_log=new QfbMoneyLog($member_id);
                        $money_log->member_id=$member_id;
                        $money_log->money=$result['amount'];
                        $money_log->create_time=strtotime($result['createTime']);
                        $money_log->old_money=$member_money->money;
                        $money_log->action=2;
                        $money_log->remark='充值';

                        //用户资金表操作
                        $member_money->money+=$result['amount'];

                        //订单表操作
                        $order=QfbOrder::find()->where(['sn'=>$respData['requestNo']])->one();
                        $order->is_check=5;
                        $order->complete_time=strtotime($result['transactionTime']);
                        $order->bank_sn=(string)$result['payCompanyRequestNo'];

                        if(!$member_money->save()){
                            $status=false;
                        } 
                        if(!$money_log->save()){
                            $status=false;
                        } 
                        if(!$order->save()){
                            $status=false;
                        } 
                        if ($status==true) {

                            $trans->commit();

                            return ['code' =>  Code::HTTP_OK, 'msg' => '充值成功', 'data'=>$result];

                        } else {

                            $trans->rollBack();

                        }

                         return ['code' =>  Code::COMMON_ERROR_CODE, 'msg' => '充值成功，数据入库失败', 'data'=>''];
                         
                    }
                    return ['code' =>  Code::COMMON_ERROR_CODE, 'msg' => $result['channelErrorMessage'], 'data'=>''];

                }

                return ['code' =>  Code::COMMON_ERROR_CODE, 'msg' => $result['errorMessage'], 'data'=>''];
            }
            return ['code' =>  Code::COMMON_ERROR_CODE, 'msg' => '请求失败', 'data'=>''];
        }
         return ['code' =>  Code::COMMON_ERROR_CODE, 'msg' => $respData['errorMessage'], 'data'=>''];

    }

    /**
     * 提现回调
     * @param $data
     * @return array
     * @author panheng
     */
    public function WithdrawNotify($data){

        $orderModel = new QfbOrder();
        $result = json_decode($data['respData']);

        if($result && $result->code === '0'){

            $status = true;

            // 开启事务
            $trans = yii::$app->db->beginTransaction();

            //订单表写入
            $orderModel->sn = $result->requestNo;
            $orderModel->member_id = intval($result->platformUserNo);//$result->platformUserNo;
            $orderModel->price = $result->amount;
            $orderModel->is_check = $result->withdrawStatus === 'ACCEPT' ? 3 : 2;
            $orderModel->remark = '提现';
            $orderModel->create_time = strtotime($result->createTime);
            $orderModel->type = 2;  // 提现
            $orderModel->sorts = 2; // 提现
            $orderModel->fee = 0;
            $orderModel->money = $result->amount-$orderModel->fee;
            $orderModel->bank_sn = $data['platformNo'];
            $orderModel->bank_type = 3;  //零钱
            $orderModel->out_type = 2;

            $bankModel = new QfbBank();
            $bank = $bankModel::findOne(['no'=>$result->bankcardNo]);
            $orderModel->bank_id = $bank->id;

            if(!$orderModel->save())
                $status = false;

            //更新零钱表
            $memberMoneyModel = QfbMemberMoney::findOne($result->platformUserNo);
            $memberMoneyModel->lock_money = $memberMoneyModel->lock_money+$orderModel->money;
            $memberMoneyModel->money = $memberMoneyModel-$orderModel->money;

            if(!$memberMoneyModel->save())
                $status = false;

            if($status == true){
                $trans->commit();
                return ['code' => Code::HTTP_OK, 'msg' => '成功', 'data'=>['sn'=>$result->requestNo, 'money'=> $orderModel->money]];
            }else{
                $trans->rollBack();
            }

        }

        return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '失败', 'data'=>''];

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

        // 支付失败
        if(strtoupper(trim($respData['status'])) != 'SUCCESS'){
            $orderfix->status = 4;
            $orderfix->save();
            return ['code' =>  Code::COMMON_ERROR_CODE, 'msg' => $reqData['errorMessage'], 'data'=>$respData];
        }

        $product = QfbProduct::find()->where('id=:id', [':id'=>$orderfix->product_id])->asArray()->one();

        // 如满标起息，付款后是投资中，反之收益中
        if($product['profit_day'] == '10' || $product['profit_day'] == '11'){
            $orderfix->status = 2;
        }else if($product['profit_day'] == '20' || $product['profit_day'] == '21'){
            $orderfix->status = 1;
        }

        $orderfix->save();

        $data = [
            'product_name' => $product['product_name'],
            'end_time' => date('Y-m-d H:i:s', $product['end_time']),
            'pay_money' => $orderfix['pay_money'],
            'money' => $orderfix['money'],
            'bizType' => $respData['bizType'],
            'requestNo' => $respData['requestNo'],
            'code' => $respData['code'],
            'status' => $respData['status'],
        ];

        return ['code' =>  Code::HTTP_OK, 'msg' => '投标成功', 'data'=>$data];
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

        /**修改还款订单表的状态为还款待确认**/
        $orderRepayment = QfbOrderRepayment::findOne(['sn'=>$data['requestNo']]);
        $orderRepayment->status = 1;

        if(!$orderRepayment->save()){
            $status = false;
        }

        /**修改产品表的状态为还款待确认**/
        $product = QfbProduct::findOne(['sn'=>$data['sn']]);
        $product->status = 7;

        if(!$product->save()){
            $status = false;
        }

        //开始还款，冻结资金
        $memberMoney = QfbMemberMoney::findOne(['member_id'=>$data['platformUserNo']]);
        $memberMoney->money -= $data['amount'];
        $memberMoney->lock_money += $data['amount'];
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
     * 确认还款后
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
        $memberLogModel = new QfbMoneyLog();
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

    /**
     * 取消还款
     * @param $data
     */
    public function hkyhCancelRepay($data)
    {

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
            $msg = '处理成功';
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
            $data = ['result'=>status, 'money'=>'', 'create_time'=>'', 'name'=>'', 'tips'=>'流水不存在'];
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '订单流水不存在', 'data'=>$data];
        }

        if($order['sorts'] != '1'){
            $data = ['result'=>status, 'money'=>'', 'create_time'=>'', 'name'=>'', 'tips'=>'非充值订单'];
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '非充值订单', 'data'=>$data];
        }

        if($order['is_check'] == 1){
            $status = 1;
            $name = '成功';
        }else if($order['is_check'] == 2){
            $name = '失败';
        }else if($order['is_check'] == 3){
            $name = '处理中';
        }else if($order['is_check'] == 4){
            $name = '无此甲乙';
        }else if($order['is_check'] == 5){
            $name = '通过审核';
        }else{
            $name = '待提现';
        }

        $data = ['result'=>status, 'money'=>$order['money'], 'create_time'=>date("Y-m-d H:i:s", $order['create_time']), 'name'=>$name, 'tips'=>''];
        return ['code' => Code::HTTP_OK, 'msg' => '请求成功', 'data'=>$data];
    }

    /**
     * 处理回调业务后，返回信息给app 提现
     **/
    public function withdraw_info($params){

        // 请求流水号
        $requestNo = $params['order_id'];

        $status = 2;

        $order = QfbOrder::find()->select('money,create_time,sorts,is_check')->where('sn=:sn', [':sn'=>$requestNo])->asArray()->one();

        if(empty($order)){
            $data = ['result'=>status, 'money'=>'', 'create_time'=>'', 'name'=>'', 'tips'=>'流水不存在'];
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '订单流水不存在', 'data'=>$data];
        }

        if($order['sorts'] != '2'){
            $data = ['result'=>status, 'money'=>'', 'create_time'=>'', 'name'=>'', 'tips'=>'非提现订单'];
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '非提现订单', 'data'=>$data];
        }

        if($order['is_check'] == 1){
            $status = 1;
            $name = '成功';
        }else if($order['is_check'] == 2){
            $name = '失败';
        }else if($order['is_check'] == 3){
            $name = '处理中';
        }else if($order['is_check'] == 4){
            $name = '无此交易';
        }else if($order['is_check'] == 5){
            $name = '通过审核';
        }else{
            $name = '待提现';
        }

        $data = ['result'=>status, 'money'=>$order['money'], 'create_time'=>date("Y-m-d H:i:s", $order['create_time']), 'name'=>$name, 'tips'=>''];
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
                'result'=>$status, 'sn'=>$requestNo, 'money'=>'',
                'real_money'=>0, 'off_money'=>0,
                'create_time'=>0, 'name'=>'订单不存在', 'tips'=>'订单不存在'
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
            'result'=>status, 'sn'=>$requestNo, 'money'=>$order_fix['money'],
            'real_money'=>$order_fix['pay_money'], 'off_money'=>($order_fix['money']-$order_fix['pay_money']),
            'create_time'=>date("Y-m-d H:i:s", $order_fix['create_time']), 'name'=>$name, 'tips'=>$tips
        ];
        return ['code' => Code::HTTP_OK, 'msg' => '请求成功', 'data'=>$data];

    }
}
