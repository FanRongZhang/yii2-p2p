<?php
namespace common\service;
use common\models\DwalletBank;
use common\models\DwalletMember;
use common\models\DwalletOrder;
use common\extension\yilian\src\payeco\tools\Tools;
use common\extension\yilian\src\payeco\tools\Xml;
use common\extension\yilian\src\payeco\tools\Log;
use common\extension\yilian\src\payeco\client\TransactionClient;
use common\extension\yilian\src\merchant\config\Constants;
use yii\base\Exception;
use common\walletService\ActivityService;
use Yii;
use common\enum\MoneyEnum;
use common\walletService\OrderPaymentService;
use common\enum\OrderEnum;
use common\service\MemberService as DmMemberService;

use common\walletService\RegularService;



/**
 * Class YiService
 * @package common\service
 */
class YiService {
    private static $_instance;

    //创建__clone方法防止对象被复制克隆
    public function __clone(){
        trigger_error('Clone is not allow!',E_USER_ERROR);
    }
    public static function getInstance(){
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * 易联支付下单参数，参数参考易联文档
     * @return  返回易联支付插件所需要的参数
     * @param $amount
     * @param $miscData
     * @param string $orderDesc
     * @param null $extData
     * @return array
     * @throws \common\extension\yilian\src\payeco\client\Exception
     */
    public function Yiorder($amount,$miscData,$merchOrderId=null,$orderDesc="易联支付",$extData=null){
        //下订单处理自动设置的参数
        $merchOrderId = !empty($merchOrderId) ?$merchOrderId:Tools::currentTimeMillis();  //订单号；本例子按时间产生； 商户请按自己的规则产生
        $merchantId = Constants::getMerchantId();
        $notifyUrl = Constants::getMerchantNotifyUrl();  //需要做URLEncode
        $tradeTime =  Tools::getSysTime();
        $expTime = ""; //采用系统默认的订单有效时间
        $notifyFlag = "0";
        // 调用下单接口
        $retXml = new Xml();
        $retMsgJson = "";
        $bOK = true;
        try {
            Log::setLogFlag(true);
            $ret = TransactionClient::MerchantOrder($merchantId,
                $merchOrderId, $amount, $orderDesc, $tradeTime, $expTime,
                $notifyUrl, $extData, $miscData, $notifyFlag,
                Constants::getMerchantRsaPrivateKey(),Constants::getPayecoRsaPublicKey(),
                Constants::getPayecoUrl(), $retXml);
            if(strcmp("0000", $ret)){
                $bOK=false;
                return ApiService::success("205", $msg = "验证失败,请核对您的账户信息" );
            }
            if("T480"== $ret){
                $bOK=false;
                return ApiService::success("205", $msg = "该银行卡已绑定，请更换银行卡" );
            }
        } catch (\Exception $e) {
            $bOK=false;
            $errCode  = $e->getMessage();
            if(strcmp("E101", $errCode) == 0){
                return ApiService::success("205", $msg = "下订单接口无返回数据" );
            }else if(strcmp("E102", $errCode) == 0){
                return ApiService::success("205", $msg = "验证签名失败" );
            }else if(strcmp("E103", $errCode) == 0){
                return ApiService::success("205", $msg = "进行订单签名失败" );
            }else{
                return ApiService::success("205", $msg = "下订单通讯失败" );
            }
        }

        //设置返回给手机Json数据
        if($bOK) {
            $retMsgJson = [
                "Version" => $retXml->getVersion(),
                "MerchOrderId" => $retXml->getMerchOrderId(),
                "MerchantId" => $retXml->getMerchantId(),
                "Amount" => $retXml->getAmount(),
                "TradeTime" => $retXml->getTradeTime(),
                "OrderId" => $retXml->getOrderId(),
                "Sign" => $retXml->getSign(),

            ];
            return ApiService::success("200", $msg = "下单成功", $retMsgJson);
        }
        //输出数据
    }

    /**
     * @param array $data
     * 易联下单回调结果处理
     */
    public function Yinotify($data=array()){
        $verifyMac = [
            $data['MERCHANT_NO'], $data['ORDER_NO'], $data['YL_BATCH_NO'],
            $data['SN'], $data['AMOUNT'], $data['CURRENCY'],
            $data['ACCOUNT_NO'], $data['MOBILE_NO'], $data['RESP_CODE'],
            $data['SETT_AMOUNT'], $data['MER_ORDER_NO'], Constants::$MERCHANT_KEY
        ];

        try {
            Log::setLogFlag(true);
            $verifyMacStr = implode(" ",$verifyMac);
            Log::logFile("PublicKey=".$verifyMacStr);
                /** * 校验MAC值 */
             if(strtoupper(md5($verifyMacStr)) == $data['MAC']) {
                 //订单类型q为钱富宝  l为理财账户
                 $rechargeType = substr($data['MER_ORDER_NO'], 0, 2);
                 if($rechargeType != "RZ" ){
                     if( $data['RESP_CODE'] !="0000")
                     return ApiService::error(201,"交易失败");
                 }else{
                     //(0000:交易成功;T425:订单已退款;T212:订单退款失败;0051:余额不足;U011:您卡上的余额不足)
                     $status = array("0000","T425","T212","0051","U011");
                     if(!in_array($data['RESP_CODE'],$status)){
                         return ApiService::error(201,"认证失败!");
                     }
                 }
                 if ($rechargeType == "LQ") {
                     /**充值零钱订单回调处理*/
                     self::linqianOrderComplete($data['MER_ORDER_NO'], $data['SN']);

                 } elseif ($rechargeType == "LC") {
                     /**充值理财账户逻辑处理*/
                     self::preMoneyOrderComplete($data['MER_ORDER_NO'], $data['SN']);

                 } elseif ($rechargeType == "RZ") {
                     /**认证订单回调处理*/

                     self::rzOrderComplete($data['MER_ORDER_NO'], $data['ACCOUNT_NO']);

                 } elseif($rechargeType == "CF"){
                    $ActivityService=new ActivityService();
                    $ActivityService->NofityWealth($data['MER_ORDER_NO'],$data['SN']);
                 }elseif($rechargeType == "DQ") {
                    $service = new RegularService();
                    $service->notifySuccess($data['MER_ORDER_NO'],$data['SN']);
                 }else{
                     throw new Exception("错误的订单信息!");
                 
                 }
             }else{
                 throw new Exception("MAC验证失败!");
             }

        } catch (\Exception $e) {
            return $e->getMessage();
        }

        //返回数据
    }

    /**
     * 充值零钱成功/钱富宝，订单结果处理
     * @string $sn 订单号
     * @string $trade_no 流水号
     */
    protected function linqianOrderComplete($sn,$trade_no=null){
        $tran = Yii::$app->db->beginTransaction();
        try {
        //充值零钱
                $recharge_arr = DwalletOrder::find()->where(['orderstatus'=>1,'sn'=>$sn])->one();
                if($recharge_arr) {
                    //修改充值订单表为成功
                    $data['DwalletOrder'] = array(
                        'orderstatus' => OrderEnum::ALREADY_PAYMENT,
                        'is_check' => MoneyEnum::CHECK,
                        'complete_time' => time(),
                    );
                    if ($recharge_arr->load($data)) {
                        $recharge_arr->save();
                    }

                    //修改支付表
                    OrderPaymentService::orderStatus($recharge_arr->id, $trade_no);

                    /*****修改当前用户等级******/
                    $member = new DmMemberService();
                    $level = $member::findModelById($recharge_arr->member_id);
                    if ($level->level == 15) {
                        $member->recharge($recharge_arr->member_id, 1, 'level');
                    }

                    //会员相关金钱
                    $memberMoney = new MemberMoneyService();
                    $member_res = $memberMoney->getByMemberId($recharge_arr->member_id);

                    //增加零钱
                    $combine_money = bcadd($member_res['money'], $recharge_arr->price, 2);
                    $memberMoney->recharge($recharge_arr->member_id, $combine_money, 'money');

                    //记录日志
                    $moneyLog = new MoneyLogService();
                    $params['MoneyLog'] = [
                        'member_id' => $recharge_arr->member_id,
                        'type' => MoneyEnum::INCOME,
                        'money_type' => MoneyEnum::MONEY,
                        'money' => $recharge_arr->price,
                        'remark' => '零钱充值',
                        'create_time' => time(),
                        'old_money' => $member_res['money'],
                        'from' => MoneyEnum::FROM_RECHARGE,
                        'new_money' => $combine_money,
                        'source' => MoneyEnum::SOURCE_QFB,
                        'detail' => $recharge_arr->member_id . ':' . $recharge_arr->price

                    ];

                       $moneyLog->create($params);
                       $tran->commit();
                    return true;
                }else{
                    throw new Exception("没有找到相应的订单");
                }
            }catch (\Exception $e){
                $tran->rollback();
                return $e->getMessage();
        }

    }

    /**
     * 充值理财账户，订单结果处理
     * @string $sn 订单号
     * @string $trade_no 流水号
     */
    protected function preMoneyOrderComplete($sn,$trade_no=null){
        $tran = Yii::$app->db->beginTransaction();
        try {
            //充值理财账户
            $recharge_arr = DwalletOrder::find()->where(['orderstatus' => 1, 'sn' => $sn])->one();

            if ($recharge_arr) {
                //修改充值订单表为成功
                $data['DwalletOrder'] = array(
                    'orderstatus' => OrderEnum::ALREADY_PAYMENT,
                    'is_check' => MoneyEnum::CHECK,
                    'complete_time' => time(),
                );
                if ($recharge_arr->load($data)) {
                    $recharge_arr->save();
                }

                //修改支付表
                OrderPaymentService::orderStatus($recharge_arr->id, $trade_no);

                /*****修改当前用户等级******/
                $member = new DmMemberService();
                $level = $member::findModelById($recharge_arr->member_id);
                if ($level->level == 15) {
                    $member->recharge($recharge_arr->member_id, 1, 'level');
                }

                //会员相关金钱
                $memberMoney = new MemberMoneyService();
                $member_res = $memberMoney->getByMemberId($recharge_arr->member_id);

                //增加理财账户金钱
                $combine_money = bcadd($member_res['qfb_pre_money'], $recharge_arr->price, 2);
                $memberMoney->recharge($recharge_arr->member_id, $combine_money, 'qfb_pre_money');

                //记录日志
                $moneyLog = new MoneyLogService();
                $params['MoneyLog'] = [
                    'member_id' => $recharge_arr->member_id,
                    'type' => MoneyEnum::INCOME,
                    'money_type' => MoneyEnum::QFB_MONEY,
                    'money' => $recharge_arr->price,
                    'remark' => '钱富宝充值',
                    'create_time' => time(),
                    'old_money' => $member_res['qfb_pre_money'],
                    'from' => MoneyEnum::FROM_RECHARGE,
                    'new_money' => $combine_money,
                    'source' => MoneyEnum::SOURCE_QFB,
                    'detail' => $recharge_arr->member_id . ':' . $recharge_arr->price

                ];
                 $moneyLog->create($params);
                $tran->commit();
                return true;
            }else{
                throw new Exception("没有找到相应的订单");
            }
        }catch (\Exception $e){
            return $e->getMessage();

        }

    }

    /**
     * 认证订单逻辑处理
     * @param $sn
     */
    public function rzOrderComplete($sn,$bank_no){
        if(isset($sn) && isset($bank_no)) {
            $dMemberModel = DwalletMember::findByVerifyCode($sn);
           /* $memberInfoServ = new MemberInfoService();
            $memberInfoModel = $memberInfoServ->getMemberInfo($dMemberModel->member_id);
            if($memberInfoModel->is_YLverify!=1){*/
            if ($dMemberModel) {
                $dMemberModel->verify_order = "";
                if ($dMemberModel->save()) {
                    $bankModel = DwalletBank::findAll(['member_id' => $dMemberModel->member_id]);
                    if ($bankModel) {
                        foreach ($bankModel as $v) {
                            $v->is_del = 1;
                            $v->save();
                        }
                    } else {
                        throw new Exception("修改银行卡信息失败");
                    }
                    $bankModel = DwalletBank::findOne(['no' => $bank_no]);
                    $bankModel->is_del = 0;
                    if ($bankModel->save()) {
                        $memberInfoServ = new MemberInfoService();
                        $memberInfoModel = $memberInfoServ->getMemberInfo($dMemberModel->member_id);
                        $memberInfoModel->is_YLverify = 1;
                        var_dump($memberInfoModel->save());
                        exit;
                    } else
                        throw new \Exception("修改银行卡信息失败");
                } else
                    throw new \Exception("修改用户信息失败");
            } else
                throw new \Exception("用户不存在");
        /*}else
            throw new \Exception("用户已实名");*/

        }
    }
}