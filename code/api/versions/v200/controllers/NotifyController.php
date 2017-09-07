<?php

namespace api\versions\v200\controllers;

use common\enum\ChannelEnum;
use common\extension\yilian\lib\Log;
use common\models\QfbMember;
use common\models\QfbMemberMoney;
use common\models\QfbOrder;
use common\models\QfbOrderFix;
use common\service\LogService;
use common\service\MemberService;
use common\service\MemberVoucherService;
use common\service\MoneyLogService;
use common\service\OrderFixService;
use common\service\OrderService;
use common\service\ProductService;
use League\Flysystem\Exception;
use yii;
use yii\rest\Controller;
use api\common\BaseController;

use common\service\HkyhService;
use api\common\helpers\ReseponseCode as Code;
use yii\web\Response;


/**
 * 支付通道回调处理控制器
 * @author xiaomalover <xiaomalover@gmail.com>
 */
class NotifyController extends BaseController
{
    public $enableCsrfValidation = false;
    /**
     * 快钱回调方法
     */
    public function actionKuaiqian()
    {
        $content = $this->receiveStreamFile();
        $kq = Yii::$app->kuaiQian;
        $res = $kq->handleNotify($content);
        if ($res) {
            $ors = new OrderService;
            $r = $ors->rechargeSuccessHandle($res['sn'], $res['ref']);
            // print_r($r); die;
            // print_r($res);die;
            echo $res['returnData'];
            // $this->notifyLog("returnData.log",$res['returnData']);
        }

    }

    /**
     * 快钱回调读取文件流
     * @return 字符串
     */
    private function receiveStreamFile()
    {
        $streamData = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
        if (empty($streamData)) {
            $streamData = file_get_contents('php://input');
        }
        if ($streamData != '') {
            return $streamData;
        } else {
            return false;
        }
    }
    public function actionZlCallback()
    {
        $answer = \Yii::$app->request->post();
        foreach($answer as $key => $value){
            if($key !='data'){
                $answer[$key] =json_decode($value, true);
            }
        }
        $data = \Yii::$app->ZlPay->decryKey($answer);
        $fixModel = QfbOrderFix::find()->joinWith("order_fix_extend")->where(['bank_sn' => $data['tn'], "status" => 0])->one();
        $trans = \Yii::$app->db->beginTransaction();
        try {
            if ($fixModel) {
                //查询订单
                if (!strncmp($fixModel->sn, 'DQ', 2)) {
                    $memMoneyModel = QfbMemberMoney::findOne(['member_id' => $fixModel->member_id]);
                    $memMoneyModel->fix_money = bcadd($memMoneyModel->fix_money, $fixModel->money, 2);
                    if ($memMoneyModel->save()) {
                        $memModel = QfbMember::findOne(['id' => $fixModel->member_id]);
                        $memModel->level = 1;
                        if ($memModel->save()) {
                            //如果存在代金券，处理代金券
                            if ($fixModel->order_fix_extend) {
                                $memVouchModel = new MemberVoucherService();
                                $memVouchModel->validExist($fixModel->order_fix_extend->money_ticket_id);
                                $memVouchModel->cost($fixModel->order_fix_extend->money_ticket_id, $fixModel->product_id);
                            }
                            $proServ = new ProductService();
                            $proServ->validExist($fixModel->product_id);
                            $orderProfitTime = $proServ->setProfitDay();
                            $fixModel->end_time = $orderProfitTime->end_time;
                            $fixModel->next_profit_time = $orderProfitTime->next_profit_time;
                            $fixModel->status = isset($orderProfitTime->status) ? $orderProfitTime->status : 0;
                            if ($fixModel->save()) {
                                if ($proServ->addHasMoney($fixModel->product_id, $fixModel->money)) {
                                    $memServ = new MemberService();
                                    $memServ->validExist($fixModel->member_id);
                                    //如果是新手产品，购买之后修改用户新手状态
                                    if ($proServ->validIsNewer() == true) {
                                        if (!$memServ->updateNew()) {
                                            throw new Exception($memServ->findOneMessage());
                                        }

                                    }
                                    $orderFixServ = new OrderFixService();
                                    $orderFixServ->validExist($fixModel->id);
                                    if ($orderFixServ->writeMoneyLog($fixModel->member_id, $fixModel->product_id) == true) {

                                        $trans->commit();
                                        die("success");
                                    } else {
                                        throw new Exception($orderFixServ->findOneMessage());
                                    }

                                } else {
                                    throw new Exception("增加产品已投资金额失败");
                                }

                            } else {
                                throw new Exception("更新订单数据失败");
                            }

                        }
                    }
                }
            } else {
                $liveModel = QfbOrder::findOne(['bank_sn' => $data['tn'], 'is_check' => 0]);
                if ($liveModel) {
                    $liveModel->is_check = 1;
                    if ($liveModel->save()) {
                        $memMoneyModel = QfbMemberMoney::findOne(['member_id' => $liveModel->member_id]);
                        $memMoneyModel->pre_live_money = bcadd($memMoneyModel->pre_live_money, $liveModel->price, 2);
                        if ($memMoneyModel->save()) {
                            $memModel = QfbMember::findOne(['id' => $liveModel->member_id]);
                            $memModel->level = 1;
                            if ($memModel->save()) {
                                //5.记录日志
                                $moneyLogService = new MoneyLogService($liveModel->member_id);
                                $logData = [
                                    'member_id' => $liveModel->member_id,
                                    'type' => 2,
                                    'money_type' => 1,
                                    'create_time' => time(),
                                    'money' => $liveModel->price,
                                    'old_money' => 0,
                                    'action' => 19,
                                    'remark' => sprintf('购买%s', "活期理财"),
                                ];
                                if ($moneyLogService->create($logData) == true) {
                                    $trans->commit();
                                    die("success");
                                } else {
                                    throw new Exception("记录订单日志失败");
                                }

                            } else {
                                throw new Exception("更新用户等级");
                            }

                        } else {
                            throw new Exception("更新活期余额失败");
                        }

                    } else {
                        throw new Exception("更新订单数据失败");
                    }

                } else {
                    throw new Exception("没有找到相应的单号");
                }

            }
        } catch (\Exception $e) {
            $trans->rollBack();
            var_dump($e->getMessage());
            exit;
        }
        echo "success";
        die("fail");
    }
    /**
     * 记录回调日志
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param String $fileName 要存入的文件名
     * @param String $content 要存入的内容
     */
    private function notifyLog($fileName, $content)
    {
        $notifyLog = Yii::$app->getRuntimePath() . '/logs/' . $fileName;
        if (!file_exists($notifyLog)) {
            touch($notifyLog);
        }
        $fp = fopen($notifyLog, "w");
        fwrite($fp, $content);
        fclose($fp);
    }

    /*
     * 易联异步回调
     * */
    public function actionYiLian()
    {
        $data = Yii::$app->request->get();
        $ylPay = Yii::$app->YiLian;

        $verifyMac = [
            $data['MERCHANT_NO'], $data['ORDER_NO'], $data['YL_BATCH_NO'],
            $data['SN'], $data['AMOUNT'], $data['CURRENCY'],
            $data['ACCOUNT_NO'], $data['MOBILE_NO'], $data['RESP_CODE'],
            $data['SETT_AMOUNT'], $data['MER_ORDER_NO'], $ylPay->MERCHANT_KEY
        ];

        Log::setLogFlag(true);
        $verifyMacStr = implode(" ",$verifyMac);
        Log::logFile("PublicKey=".$verifyMacStr);

        /** * 校验MAC值 */
        if(strtoupper(md5($verifyMacStr)) == $data['MAC']) {
            $sn = $data['MER_ORDER_NO'];

            //成功时处理订单逻辑
            if ($data['RESP_CODE'] == '0000') {
                //处理订单逻辑
                $ors = new OrderService;
                $result = $this->getOrderBySn($sn);
                if ($result['order']) {
                    $ors->handleSuccess(
                        $result['order'],
                        $result['type'],
                        ChannelEnum::YILIAN,
                        $data['SN']
                    );
                }
            }
        }
        echo "success";
        exit;
    }

    /**
     * 华融支付回调
     * @author xiaomalover <xiaomalover@gmail.com>
     */
    public function actionHr()
    {
        $params = Yii::$app->request->post();
        if (isset($params['Prdordno'])) {
            //获取回调数据
            $hrSn = $params['Prdordno']; //华融支付订单号
            $payOrdNo = $params['payOrdNo']; //支付流水号
            $status = $params['ordStatus']; //支付状态
            //$ordAmt = $params['ordAmt'] / 100; //支付金额

            //成功时处理订单逻辑
            if ($status == 1) {
                //处理订单逻辑
                $ors = new OrderService;
                $result = $this->getOrderByHrSn($hrSn);
                if ($result['order']) {
                    $ors->handleSuccess(
                        $result['order'],
                        $result['type'],
                        ChannelEnum::HUARONG,
                        $payOrdNo
                    );
                }
            }

            //获取响应数据;
            $replyData = Yii::$app->hrpay->getNotifyData($params);
            //输出xml响应华融支付
            echo $replyData;
            exit;
        }
    }

    /**
     * 根据订华融订单号获取订单
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param string $hr_sn 华融订单号
     * @return mixed
     */
    private function getOrderByHrSn($hr_sn)
    {
        //前辍为HRF的订单，是定期，HRC是活期
        if (!strncmp($hr_sn, 'HRF', 3)) {
            $order = QfbOrderFix::find()
                ->joinWith("order_fix_extend")
                ->where(['hr_sn' => $hr_sn, "status" => 0])
                ->one();
            $res['order'] = $order;
            $res['type'] = "fix";
        } else if (!strncmp($hr_sn, 'HRC', 3)) {
            $order = QfbOrder::find()
                ->where(['hr_sn' => $hr_sn, "is_check" => 0])
                ->one();
            $res['order'] = $order;
            $res['type'] = "current";
        } else {
            $res = false;
        }
        return $res;
    }

    /*
     * 根据订单号获取订单
     * */
    public function getOrderBySn($sn)
    {
        //前辍为HRF的订单，是定期，HRC是活期
        if (!strncmp($sn, 'DQ', 2)) {
            $order = QfbOrderFix::find()
                ->joinWith("order_fix_extend")
                ->where(['sn' => $sn, "status" => 0])
                ->one();
            $res['order'] = $order;
            $res['type'] = "fix";
        } else if (!strncmp($sn, 'HQ', 2)) {
            $order = QfbOrder::find()
                ->where(['sn' => $sn, "is_check" => 0])
                ->one();
            $res['order'] = $order;
            $res['type'] = "current";
        } else {
            $res = false;
        }

        return $res;
    }

    /**
     * 海口银行所有回调入口访问
     **/
    public function actionHkyh()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $data = $this->getParams();

        // 无效回调操作 Invalid-request
        if(!isset($data['serviceName']))
            return $this->redirect(['/v200/notify/hkyh-return','status'=>'error','type'=>'Invalid-request','msg'=>'未知请求']);

        $hkyhService = new HkyhService();

        //初始化
        $status = 'error';
        $name = '';
        $id_card = '';

        $fileName = $data['serviceName'].'_DIRECT.log';
        $content = '响应时间：'.date("Y-m-d H:i:s", time());

        // 接口名称 区分方法处理不同回调
        switch($data['serviceName']){

            // 绑卡注册回调业务处理
            case 'PERSONAL_REGISTER_EXPAND':
                $result = $hkyhService->hkyhRester($data);

                if($result['code'] == code::HTTP_OK)
                    $status = 'success';

                // 写入日志
                $content .= '   开户回调   平台用户编号：'.$result['data']['platformUserNo'].'    平台处理信息：'.$result['msg'].'    响应数据：'.json_encode($data)."\r\n";
                LogService::hkyh_write_log($fileName, $content);

                return $this->redirect([ '/v200/notify/hkyh-return', 'status'=>$status, 'order_id'=>$result['data']['platformUserNo'], 'type'=>'hkyh-register', 'msg'=>$result['msg']]);
                break;
            // 充值回调业务处理
            case 'RECHARGE':
                $result = $hkyhService->userPay($data);
                if($result['code'] == code::HTTP_OK)
                    $status = 'success';

                // 写入日志
                $content .= '   充值回调   请求流水号：'.$result['data']['requestNo'].'    平台处理信息：'.$result['msg'].'    响应数据：'.json_encode($data)."\r\n";
                LogService::hkyh_write_log($fileName, $content);

                return $this->redirect(['/v200/notify/hkyh-return','status'=>$status, 'order_id'=>$result['data']['requestNo'], 'type'=>'hkyh-userPay','msg'=>$result['msg']]);                    
                break;
            // 用户预处理
            case 'USER_PRE_TRANSACTION':
                $result = $hkyhService->userPreTransaction($data);
                if($result['code'] == code::HTTP_OK)
                    $status = 'success';
                // 写入日志
                $content .= '   投标回调   请求流水号：'.$result['data']['requestNo'].'    平台处理信息：'.$result['msg'].'    响应数据：'.json_encode($data)."\r\n";
                LogService::hkyh_write_log($fileName, $content);

                return $this->redirect(['/v200/notify/hkyh-return','status'=>$status, 'order_id'=>$result['data']['requestNo'],'type'=>'hkyh-userPreTransaction','msg'=>$result['msg']]);                       // 充值回调业务处理
                break;
            //提现回调业务处理
            case 'WITHDRAW':
                $result = $hkyhService->WithdrawNotify($data);
                $withdraw_data = $result['data'];
                if($result['code'] == code::HTTP_OK)
                    $status = 'success';

                // 写入日志
                $content .= '  提现回调    请求流水号：'.$withdraw_data->requestNo.'    平台处理信息：'.$result['msg'].'    响应数据：'.json_encode($data)."\r\n";
                LogService::hkyh_write_log($fileName, $content);
                return $this->redirect(['/v200/notify/hkyh-return','status'=>$status,  'order_id'=>$withdraw_data->requestNo, 'type'=>'hkyh-withdraw','msg'=>$result['msg']]);
                break;
            // 修改交易密码
            case 'RESET_PASSWORD':
                $result = $hkyhService->resetPassword($data);
                if($result['code'] == code::HTTP_OK)
                    $status = 'success';

                // 写入日志
                $content .= '    修改密码回调    平台处理信息：'.$result['msg'].'    响应数据：'.json_encode($data)."\r\n";
                LogService::hkyh_write_log($fileName, $content);

                return $this->redirect(['/v200/notify/hkyh-return','status'=>$status,'type'=>'hkyh-resetPassword','msg'=>$result['msg']]);
                break;
            case 'ASYNC_TRANSACTION':
                $result = $hkyhService->hkyhTransaction($data);
                if($result['code'] == code::HTTP_OK)
                    $status = 'success';

                // 写入日志
                $content .= '    平台处理信息：'.$result['msg'].'    响应数据：'.json_encode($data)."\r\n";
                LogService::hkyh_write_log($fileName, $content);

                return $this->redirect(['/v200/notify/hkyh-return','status'=>$status,'type'=>'hkyh-withdraw']);
            break;
        }

        return $this->redirect(['/v200/notify/hkyh-return','status'=>'error','type'=>'Invalid-request','msg'=>'未知请求']);
    }

    /**
     * 处理海口银行回调业务，重定向新地址--app使用
     */
    public function actionHkyhReturn(){

        $params = $this->getParams();

        echo "<pre>";
        var_dump(($params));
        exit;
    }

}
