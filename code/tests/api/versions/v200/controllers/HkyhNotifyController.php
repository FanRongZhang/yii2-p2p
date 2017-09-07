<?php

namespace api\versions\v200\controllers;

use yii;
use yii\rest\Controller;
use common\enum\ChannelEnum;
use \yii\web\Response;

use common\models\QfbBindingBank;
use common\models\QfbBank;
use common\models\QfbBankExtend;
use common\models\QfbMember;
use api\common\helpers\ReseponseCode as Code;
use common\models\QfbMoneyLog;
use common\models\QfbMemberMoney;
use common\models\QfbOrder;

/**
 * 支付通道回调处理控制器
 * @author xiaomalover <xiaomalover@gmail.com>
 */
class HkyhNotifyController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * 绑卡注册回调
     */
    public function actionOtherCreate(){

        \Yii::$app->response->format = Response::FORMAT_JSON;

        $data = empty(\Yii::$app->request->get()) ? \Yii::$app->request->post() : \Yii::$app->request->get();

        if(isset($data['respData'])){

            // 返回结果
            $respData = json_decode($data['respData'], true);

            // 绑卡注册成功
            if($respData['status'] == 'SUCCESS'){

                $member_id = 1;//$respData['platformUserNo'];

                // 更新会员已经绑卡已绑卡状态
                $qfbMember =  QfbMember::findOne($member_id);

                $qfbMember->is_dredge = 1;
                $qfbMember->save();

                // 查询绑卡订单表
                $bind_order = QfbBindingBank::find()->where([
                    'member_id' => $member_id,
                    // 测试
                    'no' => '6221886400021521445',//$respData['bankcardNo'],
                    'channel_id' => ChannelEnum::HKYH,
                ])->one();

                if($bind_order){

                    // 添加绑卡
                    $bank = new QfbBank();
                    // 测试
                    $bank->member_id = 1;//$respData['platformUserNo'];
                    $bank->username = $respData['realName'];
                    $bank->name = $bind_order['name'];
                    $bank->no = $bind_order['no'];
                    $bank->mobile = $respData['mobile'];
                    $bank->create_time = time();

                    $bank->save();

                    if($result){

                        // 添加绑卡扩展表
                        $bankExtend = new QfbBankExtend();

                        $bankExtend->bank_id = $bank->id;
                        $bankExtend->channel_id = $bind_order->channel_id;
                        $bankExtend->create_time = time();

                        $bankExtend->save();
                    }
                }

                return ['code' => Code::HTTP_OK, 'msg' => '绑卡注册成功', 'data'=>$respData];
            }

            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '绑卡注册失败', 'data'=>$respData];
        }

        return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '绑卡注册失败', 'data'=>''];
    }


    //充值通道回调地址
    public function actionUserPay()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $data = empty(\Yii::$app->request->get()) ? \Yii::$app->request->post() : \Yii::$app->request->get();

        $respData = json_decode($data['respData'], true);

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

                            return ['code' =>  Code::HTTP_OK, 'msg' => '充值成功', 'data'=>$result];

                         } else {

                            return ['code' =>  Code::COMMON_ERROR_CODE, 'msg' => '充值成功，数据入库失败', 'data'=>''];

                         }
                    }

                    return ['code' =>  Code::COMMON_ERROR_CODE, 'msg' => $result['channelErrorMessage'], 'data'=>''];
                }

                return ['code' =>  Code::COMMON_ERROR_CODE, 'msg' => $result['errorMessage'], 'data'=>''];

            }
            
            return ['code' =>  Code::COMMON_ERROR_CODE, 'msg' => '请求失败', 'data'=>''];
        }

        return ['code' =>  Code::COMMON_ERROR_CODE, 'msg' => $reqData['errorMessage'], 'data'=>''];
    }


    

}
