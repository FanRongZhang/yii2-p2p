<?php

namespace frontend\modules\member\controllers;

use common\service\BankService;
use common\service\MemberMoneyService;
use frontend\controllers\WebController;
use common\Models\QfbMember;
use common\Models\QfbMemberMoney;
use common\Models\QfbMoneyLog;
use common\Models\QfbOrder;
use common\Models\QfbBank;

class MoneyController extends WebController
{
    public function actionIndex()
    {
        $this->mid = 65;
        $bankService = new BankService();
        $bank = $bankService->getCard($this->mid, 1);

        $money = MemberMoneyService::getByMemberMoney($this->mid);
        $type = \Yii::$app->request->get('type', 0);
        return $this->render('index',['type'=>$type, 'bank'=>$bank, 'money'=>$money]);
    }

    /**
     * 会员中心--充值
     * @author panheng
     */
    public function actionRecharge()
    {
        $this->mid = '1a13128829243';
        $money = \Yii::$app->request->post('money', 0);
        //最少充值金额
        $min_money = \Yii::$app->params['recharge_min_money'];
        if ($min_money > $money) {
            return $this->error(["金额不得低于{$min_money}元"]);
        }

        //判断是否开户
//        $memberModel = new QfbMember();
//        $member = $memberModel::find()->where(['id'=>$this->mid])->asArray()->one();
//        if($member['is_dredge'] === 1){
//            return $this->redirect('auth');
//        }elseif($member['is_dredge'] === 9){
//            return $this->error(['账户异常']);
//        }

        $hkyh = \Yii::$app->Hkyh;

        // 充值
        $serviceName = 'RECHARGE';

        //平台用户编号  -必填
        $reqData['platformUserNo'] = $this->mid;
        // 请求流水号  --流水号 --不允许重复
        $reqData['requestNo'] = $this->getBindSn('TX');
        // 充值金额 --必填
        $reqData['amount'] = $money;
        // 平台佣金 --非必填
        // $reqData['commission'] = '0';
        // 支付公司编码 - 见支付公司  --必填
        $reqData['expectPayCompany'] = 'TFTPAY';
        // 支付方式 - 网银 WEB  快捷支付 SWIFT --必填
        $reqData['rechargeWay'] = 'SWIFT';
        // 非网银必填，银行编码  ，网银：填，转去银行页面，不填跳转支付公司收银台页面 --非必填
        $reqData['bankcode'] = 'PSBC';
        // 页面回调url  --必填
        $reqData['redirectUrl'] = 'http://frontend.qfb.com:88/money/money/recharge-notify';
        // 超过此时间即页面过期 --必填
        $reqData['expired'] = date('YmdHis', time()+5*60);
        // 非必填---快捷充值回调模式，如传入 DIRECT_CALLBACK，则订单支付不论成功、失败、处理中均直接同步、异步通知商户；未传入订单仅在支付成功时通知商户；
        $reqData['callbackMode'] = 'DIRECT_CALLBACK';

        $result = $hkyh->createPostParam($serviceName,$reqData);
        //这里根据业务逻辑自行处理，如果是直连则根据$result数据做处理，如果是网关则不返回数据，
    }

    /**
     * 充值回调
     */
    public function actionRechargeNotify()
    {
        $data = empty($this->get()) ? $this->post() : $this->get();
        $respData = json_decode($data['respData'], true);

        if(empty($respData)){

            return $this->error('数据格式错误');

        }

        if($respData['status'] == 'SUCCESS'){

            //请求查询接口判断是否充值成功
            $hkyh = \Yii::$app->Hkyh;

            $serviceName = 'QUERY_TRANSACTION';

            //业务请求流水号  -必填
            $reqData['requestNo'] = $respData['requestNo'];
            // 查询类型 --必填
            $reqData['transactionType'] = 'RECHARGE';

            $result = $hkyh->createPostParam($serviceName,$reqData);

            //判断请求状态
            if ($result['status'] == 'success') {

                $result= json_decode($result['data'],true);
                //判断调用状态
                if ($result['status'] == 'SUCCESS') {

                    $result=$result['records'][0];

                    //判断交易成功
                    if ($result['status'] == 'SUCCESS' && $result['amount'] == $respData['amount']) {

                        $member_id=$result['platformUserNo'];
                        //测试
                        $member_id=1;
                        $member_money=QfbMemberMoney::findOne($member_id);

                        //用户资金操作日志表操作
                        $money_log=new QfbMoneyLog($member_id);
                        $money_log->member_id=$member_id;
                        $money_log->money=$result['amount'];
                        $money_log->create_time=time();
                        $money_log->old_money=$member_money->money;
                        $money_log->action=2;
                        $money_log->remark='充值';

                        //用户资金表操作
                        $member_money->money+=$result['amount'];

                        //订单表操作
                        $order=new QfbOrder();
                        $order->sn="LQ". $member_id . time() . rand(10, 99);    //测试···
                        //$order->sn="LQ". $this->member_id . time() . rand(10, 99);
                        $order->member_id=$member_id;
                        $order->price=$result['amount'];
                        $order->is_check=1;
                        $order->create_time=time();
                        $order->complete_time=time();
                        $order->sorts=1;
                        //查询银行卡id
                        $bank_id=QfbBank::find()->where(['member_id'=>$member_id])->one();
                        $order->bank_id=$bank_id->id;
                        $order->money=$result['amount'];
                        $order->bank_type=3;
                        $order->remark='充值';

                        if($member_money->save() && $money_log->save() && $order->save()) {

                            return $this->render('notify',['data'=>'']);

                        } else {

                            return $this->render('notify',['data'=>'']);

                        }
                    }

                    return $this->render('notify', ['data'=>$result['channelErrorMessage'], 'status'=>0]);
                }

                return $this->render('notify', ['data'=>$result['errorMessage'], 'status'=>0]);

            }

            return $this->render('notify', ['data'=>'请求失败', 'status'=>0]);
        }

        return $this->render('notify', ['data'=>$respData['status'], 'status'=>0]);
    }

    /**
     * 会员中心--提现
     * @author
     */
    public function actionWithdraw()
    {
        $this->mid = '1a13128829243';
        $money = \Yii::$app->request->post('money', 0);

        if($money <= 0){
            return $this->error("金额数据不正确");
        }

        $hkyh = \Yii::$app->Hkyh;

        // 提现
        $serviceName = 'WITHDRAW';

        //平台用户编号  -必填
        $reqData['platformUserNo'] = $this->mid;//$this->member_id;
        // 请求流水号  --流水号 --不允许重复
        $reqData['requestNo'] = "LQ". $this->mid . time() . rand(10, 99);//"LQ". $this->member_id . time() . rand(10, 99);
        $reqData['withdrawType'] = 'NORMAL'; //提现方式
        $reqData['withdrawForm'] = 'IMMEDIATE'; //IMMEDIATE直接提现，CONFIRMED待确认提现
        $reqData['amount'] = $money;
        $reqData['redirectUrl'] = 'http://frontend.qfb.com:88/money/money/withdraw-notify';
        // 超过此时间即页面过期 --必填
        $reqData['expired'] = date('YmdHis', time()+5*60);
        // 非必填---快捷充值回调模式，如传入 DIRECT_CALLBACK，则订单支付不论成功、失败、处理中均直接同步、异步通知商户；未传入订单仅在支付成功时通知商户；
        $reqData['callbackMode'] = 'DIRECT_CALLBACK';

        $hkyh->createPostParam($serviceName,$reqData);
        //这里根据业务逻辑自行处理，如果是直连则根据$result数据做处理，如果是网关则不返回数据，

    }

    /**
     * 提现回调
     * @author panheng
     */
    public function actionWithdrawNotify()
    {
        $data = empty($this->get()) ? $this->post() : $this->get();
        $orderModel = new QfbOrder();
        $result = json_decode($data['respData']);
        if($result && $result->code === '0'){
            $status = true;

            // 开启事务
            $trans = \yii::$app->db->beginTransaction();

            //订单表写入
            $orderModel->sn = $result->requestNo;
            $orderModel->member_id = $result->platformUserNo;//$result->platformUserNo;
            $orderModel->price = $result->amount;
            $orderModel->is_check = $result->withdrawStatus === 'ACCEPT' ? 3 : 2;
            $orderModel->remark = '提现';
            $orderModel->create_time = strtotime($result->createTime);
            $orderModel->type = 1;
            $orderModel->sorts = 1;
            $orderModel->fee = 0;
            $orderModel->money = $result->amount-$orderModel->fee;
            $orderModel->bank_sn = $data['platformNo'];
            $orderModel->bank_type = 3;  //零钱
            $orderModel->out_type = 2;

            $bankModel = new QfbBank();
            $bank = $bankModel::findOne(['no'=>$result->bankcardNo]);
            $orderModel->bank_id = $bank->id;//var_dump($orderModel);exit;
            if(!$orderModel->save()){
                $status = false;
            }

            //更新零钱表
            $memberMoneyModel = QfbMemberMoney::findOne($result->platformUserNo);
            $memberMoneyModel->lock_money = $memberMoneyModel->lock_money+$orderModel->money;
            if(!$memberMoneyModel->save()){
                $status = false;
            }

            if($status == true){
                $trans->commit();
                return $this->render('notify', ['data'=>$result]);
            }else{
                $trans->rollBack();
            }
        }

        return $this->error('');
    }
}
