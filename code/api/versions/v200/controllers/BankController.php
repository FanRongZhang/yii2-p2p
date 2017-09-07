<?php

namespace api\versions\v200\controllers;

use api\common\BaseController;
use api\common\helpers\ReseponseCode as Code;
use common\enum\ChannelEnum;
use common\models\QfbBank;
use common\models\QfbMemberInfo;
use common\service\BankService;
use common\service\CommonService;
use common\service\ToolService;
use Yii;

/**
 * @author xiaoma <xiaomalover@gmail.com>
 * @since 2.0
 */
class BankController extends BaseController
{
    /**
     * 第三方绑卡
     * @param String $this->params['no'] 银行卡卡号
     * @param Int $this->params['type'] 支付通道类型
     * @return  Array $res 结果数组
     * @no 银行卡号
     * @mobile 手机号
     * @type 支付类型
     * @name 用户名
     * @id_card 身份证
     */
    public function actionOtherCreate()
    {
        $params = $this->getParams();
        if (isset($params['no']) && isset($params['type']) && isset($params['mobile'])) {
            $bks = new BankService;
            //快钱绑卡
            if ($params['type'] == ChannelEnum::KUAIQIAN) {
                //中国银行与招商银行需要传入开户行所在省市
                $province = isset($params['province']) ? trim($params['province']) : '';
                $city = isset($params['city']) ? trim($params['city']) : '';
                //快钱绑卡生成绑卡订单
                $result = $bks->kqCreate(trim($params['no']), $this->member_id, trim($params['mobile']), $province, $city);
                if ($result) {
                    return [
                        'code' => '200',
                        'msg' => '创建绑卡订单成功',
                        'data' => (object) [
                            'id' => $bks->info['order']['id'],
                            'sn' => $bks->info['order']['sn'],
                        ],
                    ];
                } else {
                    $tmp = explode("<>", $bks->message);
                    $code = isset($tmp[1]) ? $tmp[0] : Code::COMMON_ERROR_CODE;
                    $message = isset($tmp[1]) ? $code . ':' . $tmp[1] : $tmp[0];
                    return ['code' => Code::COMMON_ERROR_CODE, 'msg' => $message];
                }
                //易联实名认证绑卡
            }elseif ($params['type'] == ChannelEnum::YILIAN){
                    $result = $bks->ylBind(
                        $this->member_id =1,
                        $params['no'],
                        $params['mobile'],
                        $params['name'],
                        $params['id_card'],
                        $params['bankName']
                    );
                    if ($result) {
                        return [
                            'code' => '200',
                            'msg' => '绑卡短信发送成功!',
                            'data' => (object) [
                                'id' => $bks->info['order']['id'],
                                'sn' => $bks->info['order']['sn'],
                            ],
                        ];
                    } else {
                        return [
                            'code' => Code::COMMON_ERROR_CODE,
                            'msg' => $bks->message,
                        ];
                    }
                //证联实名认证绑卡
            } else if ($params['type'] == ChannelEnum::ZHENGLIAN) {
                $params = $bks->checkParams($params, $this->member_id);
                if ($params) {
                    $result = $bks->ZlCreateBinding($this->member_id, $params);
                    if ($result) {
                        return [
                            'code' => '200',
                            'msg' => '创建绑卡订单成功',
                            'data' => (object) [
                                'id' => $bks->info['order']['id'],
                                'sn' => $bks->info['order']['sn'],
                            ],
                        ];
                    } else {
                        $tmp = explode("<>", $bks->message);
                        $code = isset($tmp[1]) ? $tmp[0] : Code::COMMON_ERROR_CODE;
                        $message = isset($tmp[1]) ? $code . ':' . $tmp[1] : $tmp[0];
                        return ['code' => Code::COMMON_ERROR_CODE, 'msg' => $message];
                    }
                } else {
                    return ['code' => Code::COMMON_ERROR_CODE, 'msg' => $bks->message];
                }
            } else if ($this->params['type'] == ChannelEnum::HUARONG) { //华融支付
                if (isset($params['name']) && isset($params['id_card']) && isset($params['bankName'])) {
                    $result = $bks->hrBind(
                        $this->member_id,
                        $params['no'],
                        $params['mobile'],
                        $params['name'],
                        $params['id_card'],
                        $params['bankName']
                    );
                    if ($result) {
                        return [
                            'code' => '200',
                            'msg' => '绑卡成功',
                        ];
                    } else {
                        return [
                            'code' => Code::COMMON_ERROR_CODE,
                            'msg' => $bks->message,
                        ];
                    }
                } else {
                    return [
                        'code' => Code::COMMON_ERROR_CODE,
                        'msg' => '请求参数缺失',
                    ];
                }
            } else {
                return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '不支持的通道'];
            }
        } else {
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => '请求参数缺失',
            ];
        }
    }

    /**
     * 快钱再次获取验证码
     * @author xiaomalover <xiaomalover@gmail.com>
     */
    public function actionGetCode()
    {
        $bks = new BankService();
        $result = $bks->getOrderBySn($this->params['sn']);
        if ($this->params['payment'] == ChannelEnum::KUAIQIAN) {
            if (isset($this->params['sn'])) {
                $bks = new BankService;
                $res = $bks->kqReGetCode($this->params['sn']);
                if ($res) {
                    return ['code' => Code::HTTP_OK, 'msg' => '获取验证码成功'];
                } else {
                    $tmp = explode("<>", $bks->message);
                    $code = isset($tmp[1]) ? $tmp[0] : Code::COMMON_ERROR_CODE;
                    $message = isset($tmp[1]) ? $code . ':' . $tmp[1] : $tmp[0];
                    return ['code' => Code::COMMON_ERROR_CODE, 'msg' => $message];
                }
            } else {
                return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '请求参数缺失'];
            }
            //证联再次发送验证码
        } elseif ($this->params['payment'] == ChannelEnum::ZHENGLIAN) {
            //绑卡再次获取验证码，通过自己渠道获取
            if ($result['type'] == "bind") {
                $service = new CommonService();
                $smsResult = $service->sendMobileVerifyCode($result['order']->mobile, CommonService::VERIFY_TYPE_BANK);
                if ($smsResult) {
                    $result['order']->token = \Yii::$app->session[$service::getType(CommonService::VERIFY_TYPE_BANK) . $result['order']->mobile];
                    if ($result['order']->save()) {
                        return ['code' => Code::HTTP_OK, 'msg' => '获取验证码成功'];
                    } else {
                        return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '获取验证码失败'];
                    }

                } else {
                    return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '获取验证码失败'];
                }

            } else {
                $memInfoModel = QfbMemberInfo::findOne(['member_id' => $result['order']->member_id]);
                //下单再次获取验证码,证联通道获取
                $smsData['bindId'] = strval($memInfoModel->bindId);
                $smsData['tn'] = $result['order']->bank_sn;
                //发送支付手机验证
                $zlSms = \Yii::$app->ZlPay->smsVerify($smsData);
                if ($zlSms->respCode == "00") {
                    return ['code' => Code::HTTP_OK, 'msg' => '获取验证码成功'];
                } else {
                    return ['code' => Code::COMMON_ERROR_CODE, 'msg' => $zlSms->respMsg . '错误码:' . $zlSms->respCode];
                }
            }
        } else if ($this->params['payment'] == ChannelEnum::HUARONG) { //华融
            if (isset($this->params['sn'])) {
                $order = $result['order'];
                //根据订单类型获取产品名
                if ($result['type'] == 'liveorder') {
                    $product_name = $order->remark;
                    $hr_sn = ToolService::SetSn('HRC');
                    $money = $order->money;
                } else if ($result['type'] == 'fixorder') {
                    $product_name =  $order->product->product_name;
                    $hr_sn = ToolService::SetSn('HRF');
                    $money = $order->pay_money;
                } else {
                    $product_name = $hr_sn = $money = '';
                }
                //通过订单查出银行卡，从而得到手机号
                $bank = QfbBank::findOne($order->bank_id);
                //得到用户身份证
                $memInfoModel = QfbMemberInfo::findOne(['member_id' => $result['order']->member_id]);
                //华融支付每次都要不同的订单号，所以重新生成华融对应的订单号
                $order->hr_sn = $hr_sn;
                if ($order->save()) {
                    //发送短信
                    $res = Yii::$app->hrpay->getVerifyCode(
                        $bank->mobile,
                        $order->hr_sn,
                        $money,
                        $product_name,
                        $bank->no,
                        $bank->username,
                        $memInfoModel->card_no
                    );
                    if ($res) {
                        return ['code' => Code::HTTP_OK, 'msg' => '获取验证码成功'];
                    } else {
                        return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '获取验证码失败'];
                    }
                } else {
                    return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '重新生成订单号出错'];
                }
            } else {
                return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '请求参数缺失'];
            }
        }
    }

    /**
     * @author xiaomalover <xiaomalover@gmail.com>
     * 快钱绑卡和充值提交处理
     */
    public function actionOtherCommit()
    {
        $params = $this->getParams();
        if (isset($params['code']) && isset($params['sn'])) {
            //根据不同支付方式，调用不同的支付提交方法
            $bks = new BankService();
            if ($params['payment'] == ChannelEnum::YILIAN) {
                $res = $bks->YlCommit($params['sn'], $params['code']);
            }else if ($params['payment'] == ChannelEnum::ZHENGLIAN) {
                $res = $bks->ZlCommit($params['sn'], $params['code']);
            } else if ($params['payment'] == ChannelEnum::KUAIQIAN) {
                $res = $bks->kqCommit($params['sn'], $params['code']);
            } else if ($params['payment'] == ChannelEnum::HUARONG) {
                $res = $bks->hrCommit($params['sn'], $params['code']);
            } else {
                $res = false;
            }
            //根据支付结果，组织返回数据
            if ($res) {
                $tmp = explode("<>", $bks->message);
                //充值有通过<>串金额
                if (isset($tmp[1])) {
                    $message = $tmp[0];
                    $money = $tmp[1];
                    return [
                        'code' => Code::HTTP_OK,
                        'msg' => $message,
                        'data' => (object) [
                            'result' => true,
                            'tips' => "恭喜您," . $message,
                            'error' => "",
                            'money' => $money,
                        ],
                    ];
                } else { //绑卡没有串金额
                    return [
                        'code' => Code::HTTP_OK,
                        'msg' => $bks->message,
                        'data' => (object) [
                            'result' => true,
                            'tips' => "恭喜您," . $bks->message,
                            'error' => "",
                        ],
                    ];
                }
            } else {
                $tmp = explode("<>", $bks->message);
                $code = isset($tmp[1]) ? $tmp[0] : Code::COMMON_ERROR_CODE;
                $message = isset($tmp[1]) ? $tmp[1] : $tmp[0];
                return [
                    'code' => Code::HTTP_OK,
                    'msg' => $message,
                    'data' => (object) [
                        'result' => false,
                        'tips' => $message,
                        'error' => "错误码:" . $code,
                    ],
                ];
            }
        } else {
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '请求参数缺失'];
        }
    }

    /**
     * 查询支持的银行卡列表
     * @return array
     */
    public function actionSupport()
    {
        $params = $this->getParams();
        $bankService = new BankService();
        $result = $bankService->findSupportBankList($params);
        if ($result) {
            return [
                "code" => Code::HTTP_OK,
                "msg" => "请求成功",
                "data" => $result,
            ];
        } else {
            return [
                'code' => Code::HTTP_OK,
                'msg' => '未查到任何数据',
                'data' => [],
            ];
        }
    }
}
