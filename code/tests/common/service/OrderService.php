<?php
namespace common\service;

use api\common\helpers\ReseponseCode as Code;
use common\enum\ChannelEnum;
use common\enum\MoneyEnum;
use common\enum\OrderEnum;
use common\enum\ProductEnum;
use common\models\QfbBankLimit;
use common\models\QfbMember;
use common\models\QfbMemberInfo;
use common\models\QfbMemberMoney;
use common\models\QfbMoneyLog;
use common\models\QfbOrder;
use common\models\QfbOrderFix;
use common\models\QfbProfitSettings;
use common\service\PasswordService;
use Yii;
use yii\base\Exception;
use yii\mongodb\ActiveRecord;

/**
 * 订单业务逻辑
 *
 */
class OrderService extends BaseService
{
    protected $_className = "common\models\QfbOrder";
    protected $productService;
    protected $moneyLogService;
    /**
     * 零钱购买活期
     * [createLive description]
     * @param  [type] $params [description] product_id
     * @return [type]         [description]
     */
    public function createLive($params)
    {
        $member_id = $this->getMemberID();
        //1.判断是否可转入
        if (SystemService::isCanMoney() == false) {
            $this->addMessage('id', '数钱中');
            return false;
        }
        //2,判断产品
        if ($this->validProduct($params['product_id']) == false || $this->validMemberMoney($params['money']) == false) {
            return false;
        }

        //3.加入订单
        $data = [
            'sn' => ToolService::SetSn('HQ'),
            'price' => $params['money'],
            'is_check' => OrderEnum::ALREADY_PAYMENT,
            'remark' => '购买' . $this->productService->model->product_name,
            'complete_time' => $this->getTime(),
            'type' => OrderEnum::MONEY_IN,
            'sorts' => OrderEnum::FLOW_MONEY,
            'bank_id' => 0,
            'fee' => 0,
            'money' => $params['money'],
            'bank_type' => 3,
            'out_type' => 0,
        ];
        if ($this->create($data) == false) {
            return false;
        }

        //4.修改金额
        if ($this->memberMoneyService->buyLive($member_id, $this->model->price) == false) {
            $this->messages = $this->memberMoneyService->getMessages();
            return false;
        }

        //5.记录日志
        $this->moneyLogService = new MoneyLogService($member_id);
        $logData = $this->getMoneyLogData($member_id);
        if ($this->moneyLogService->createList($logData) == false) {
            $this->messages = $this->moneyLogService->getMessages();
            return false;
        }

        return true;
    }
    /**
     * 零钱购买活期
     * [createLive description]
     * @param  [type] $params [description] product_id
     * @return [type]         [description]
     */
    public function zlCreateLiveOrder($params)
    {
        $member_id = $this->getMemberID();
        //1.判断是否可转入
        if (SystemService::isCanMoney() == false) {
            $this->addMessage('id', '数钱中');
            return false;
        }
        //2,判断产品
        if ($this->validProduct($params['product_id']) == false) {
            return false;
        }

        //3.加入订单
        $data = [
            'sn' => ToolService::SetSn('HQ'),
            'price' => $params['money'],
            'is_check' => OrderEnum::WAITING_PAYMENT,
            'remark' => '购买' . $this->productService->model->product_name,
            'complete_time' => $this->getTime(),
            'type' => OrderEnum::MONEY_IN,
            'sorts' => OrderEnum::FLOW_MONEY,
            'bank_id' => $params['bank_id'],
            'fee' => 0,
            'money' => $params['money'],
            'bank_type' => $params['payment'],
            'out_type' => 0,
        ];
        if ($this->create($data) == false) {
            return false;
        }

        $memInfoModel = QfbMemberInfo::findOne(["member_id" => $member_id]);
        $data = [
            'pay_money' => $this->model->price,
            'bindId' => strval($memInfoModel->bindId),
            'id' => strval($this->model->id),
            'sn' => $this->model->sn,
        ];
        $zlRes = \Yii::$app->ZlPay->createOrdertn($data);
        if ($zlRes->respCode == "00") {
            $this->model->bank_sn = $zlRes->tn;
            if ($this->model->save()) {
                $smsData['bindId'] = strval($memInfoModel->bindId);
                $smsData['tn'] = $this->model->bank_sn;
                //发送支付手机验证
                $zlSms = \Yii::$app->ZlPay->smsVerify($smsData);
                if ($zlSms->respCode == "00") {
                    return true;
                } else {
                    $this->addMessage('zlsms', $zlSms->respMsg . "错误码:" . $zlRes->respCode);
                    return false;
                }
            } else {
                $this->addMessage('zltn', "保存受理订单失败");
            }

        } else {
            $this->addMessage('zlorder', $zlRes->respMsg . "错误码:" . $zlRes->respCode);
            return false;

        }

        /*  //5.记录日志
        $this->moneyLogService = new MoneyLogService($member_id);
        $logData= [
        'member_id'=>$this->getMemberID(),
        'type'=>2,
        'money_type'=>1,
        'create_time'=>$this->getTime(),
        'money'=>$this->model->price,
        'old_money'=>0,
        'action'=>19,
        'remark'=>sprintf('购买%s',$this->productService->model->product_name)
        ];
        if($this->moneyLogService->create($logData)==false){
        $this->messages=$this->moneyLogService->getMessages();
        return false;
        }*/

        return true;
    }
    protected function getMoneyLogData()
    {
        $logData = [
            [
                'member_id' => $this->getMemberID(),
                'type' => 2,
                'money_type' => 1,
                'create_time' => $this->getTime(),
                'money' => $this->model->price,
                'old_money' => $this->memberMoneyService->model->money,
                'action' => 11,
                'remark' => sprintf('购买%s', $this->productService->model->product_name),
            ],
            [
                'member_id' => $this->getMemberID(),
                'type' => 1,
                'money_type' => 2,
                'create_time' => $this->getTime(),
                'money' => $this->model->price,
                'old_money' => $this->memberMoneyService->model->fix_money,
                'action' => 11,
                'remark' => sprintf('购买%s', $this->productService->model->product_name),
            ],
        ];
        return $logData;
    }

    public function create($params)
    {
        $this->model = $this->newModel();
        $this->model->member_id = $this->getMemberID();
        $this->model->load(['QfbOrder' => $params]);
        $this->model->create_time = $this->getTime();
        $this->model->bank_sn = '';
        if ($this->model->validate() && $this->model->save()) {
            return true;
        } else {
            $this->messages = $this->model->getErrors();
            return false;
        }
    }

    /**
     * 验证用户金额
     * [validMemberMoney description]
     * @param  [type] $member_id [description]
     * @param  [type] $money     [description]
     * @return [type]            [description]
     */
    protected function validMemberMoney($money)
    {

        if ($money <= 0) {
            $this->addMessage('money', '金额错误');
            return false;
        }
        $this->memberMoneyService = new MemberMoneyService();
        $this->memberMoneyService->findModel($this->getMemberID());
        $last_money = bcsub($this->memberMoneyService->model->money, $money, 2);
        if ($last_money >= 0) {
            return true;
        } else {
            $this->addMessage('money', '用户金额不足');
            return false;
        }
    }

    /**
     * 产品判断
     * [validProduct description]
     * @param  [type] $product_id [description]
     * @return [type]             [description]
     */
    private function validProduct($product_id)
    {
        $this->productService = new ProductService();
        if (
            //判断是否存在
            $this->productService->validExist($product_id) &&
            //判断是否是募集中
            $this->productService->validStatus(ProductEnum::STATUS_BUY) &&
            //判断是否是活期
            $this->productService->validLive()
        ) {
            return true;
        } else {
            $this->messages = $this->productService->getMessages();
            return false;
        }
    }

    /**
     *   @通过id查找用户信息
     *   @param id
     *   @param join array
     *   @return model
     */
    public static function findModelById($id, $join = null, $select = null)
    {
        $query = QfbOrder::find();
        $query->andWhere(['=', QfbOrder::tableName() . '.id', $id]);
        if (!empty($join)) {
            foreach ($join as $key => $value) {
                $query->joinWith($value);
            }
        }
        if ($select) {
            $query->select = $select;
        }
        return $query->one();
    }

    /*
     *获取今天已预提现金额
     */
    public static function getTodayTransferByMemberId($member_id)
    {
        $query = QfbOrder::find();
        $query->andWhere(['=', 'sorts', 1]);
        $query->andWhere(['=', 'member_id', $member_id]);
        $start_time = strtotime(date('Y-m-d 0:0:0'));
        $query->andWhere(['between', 'create_time', $start_time, ($start_time + 24 * 3600 - 1)]);
        $query->andWhere(['>', 'bank_id', 0]);
        $query->andWhere(['=', 'type', 2]);
        $query->andWhere(['in', 'is_check', [0, 1, 3, 5]]);

        $query->select('sum(price) as price');
        $all = $query->one();
        return $all['price'];
    }

    //生成随机字母+数字
    public function random_numbers($size = 6)
    {
        $str = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $code = "";

        $len = strlen($str) - 1;
        for ($i = 0; $i < $size; $i++) {
            $code .= $str{rand(0, $len)};
        }
        return date('YmdHis') . $code;
    }

    // /**
    //  *设置model模型
    //  */
    // public function getModel(){
    //     return $this->model = new QfbOrder();
    // }

    /**
     *创建提现记录
     * @param Int $member_id,用户ID
     * @param Decimal $money ,提现金额
     * @param Int $type 提现类型 1：工作日当日提现，2：1-3个工作日到账
     * @param Int $order_type 订单类型0银嘉,1易联,2支付宝,3零钱,5快钱,7华融
     */
    public function withdrawals($member_id, $bank_id, $money, $type, $order_type = 0)
    {
        $memberMoney = MemberMoneyService::getByMemberMoney($member_id);

        $trans = Yii::$app->db->beginTransaction();
        try {
            //根据银行卡id查出卡信息
            $bank = BankService::getBank($member_id, $bank_id, $order_type);
            if ($bank) {
                //根据通道&银行卡判断是否支持提现
                $is_support = QfbBankLimit::find()->where(['name' => $bank['name'], 'pt_type' => $bank['bankExtend']->channel_id])->select('is_support')->one();

                if ($is_support->is_support == 0 || $order_type == 0) {
                    throw new Exception('不支持此银行提现');
                }

                //提现手续费
                $setting = SystemService::getLastRate();
                if ($type == 1 && $money >= $setting['min_money']) {
                    $fee    = round(bcmul($money, $setting['fast_rate'], 3)/100,2);
                    //$fee = bcdiv(bcmul($money, $setting['fast_rate'], 2), 100, 2);
                } elseif ($type == 2 && $money >= $setting['min_money']) {
                    $fee    = round(bcmul($money, $setting['slow_rate'], 3)/100,2);
                    //$fee = bcdiv(bcmul($money, $setting['slow_rate'], 2), 100, 2);
                } else {
                    $fee = 2;
                }

                if ($memberMoney->money < $money) {
                    throw new Exception('金额不足');
                } else {
                    $pay_money = bcsub($money, $fee, 2); //提现实际金额

                    /*******提现 START*******/
                    if ($pay_money > 0) {
                        $model = $this->newModel();

                        if ($order_type == 1) {
                            $model->sn = 'YL' . $this->random_numbers(6); //易联订单
                        } elseif ($order_type == 7) {
                            $model->sn = 'HR' . $this->random_numbers(6); //证联订单
                        } elseif ($order_type == 5) {
                            $model->sn = 'KQ' . $this->random_numbers(6); //快钱订单
                        }

                        $model->member_id = $member_id;
                        $model->price = $money;
                        $model->is_check = OrderEnum::WAITING_PAYMENT;
                        $model->remark = "提现";
                        $model->create_time = time();
                        $model->type = OrderEnum::MONEY_OUT;
                        $model->sorts = OrderEnum::SMALL_MONEY;
                        $model->bank_id = $bank_id;
                        $model->fee = $fee;
                        $model->money = $pay_money;
                        $model->bank_type = $order_type; //易联 证联 快钱订单
                        $model->out_type = $type;

                        if ($model->save()) {
                            //保留所需要的银行订单信息
                            $this->list = [
                                'bank_no' => $bank->no,
                                'bank_name' => $bank->name,
                                'order_price' => $model->price,
                                'order_fee' => $model->fee,
                                'id' => $model->id,
                                'sn' => $model->sn,
                                'create_time' => $model->create_time,
                            ];

                            //修改用户金额
                            $old_money = $memberMoney->money;
                            $memberMoney->money = $old_money - $money; //零钱
                            $memberMoney->lock_money = $memberMoney->lock_money + $money; //冻结金额
                            if ($memberMoney->save() == 0) {
                                throw new Exception('修改金额失败');
                            }

                            //记录money_log
                            $result = self::changeMoneyLog($old_money, $money, $model);
                            if ($result['code'] != Code::HTTP_OK) {
                                throw new Exception("log");
                            }

                            $trans->commit();
                            return ApiService::success(Code::HTTP_OK, '创建提现订单成功', ['id' => $model->id, 'sn' => $model->sn, 'money' => $model->price, 'create_time' => $model->create_time]);
                        } else {
                            throw new Exception('创建提现订单失败');
                        }
                        /*******提现 END*******/

                    } else {
                        return [
                            'code' => Code::COMMON_ERROR_CODE,
                            'msg' => '提现金额不得小于' . $fee . '元',
                        ];
                    }
                }

            } else {
                return [
                    'code' => Code::COMMON_ERROR_CODE,
                    'msg' => '银行卡不存在',
                ];
            }
        } catch (Exception $e) {
            $trans->rollback();
            switch ($e->getMessage()) {
                case 'log':
                    $res = ApiService::error(Code::COMMON_ERROR_CODE, $result['msg']);
                    break;
                default:
                    $res = ApiService::error(Code::COMMON_ERROR_CODE, $e->getMessage());
                    break;
            }
            return $res;
        }
    }

    /**
     *创建预充值订单
     * @author  xiaoma <xiaomalover@gmail.com>
     * @param Int $member_id,用户ID
     * @param Decimal $money 金额
     * @param Int $bank_id 银行卡id
     * @param Int $pay_type 支付类型，是快钱还是其他
     * @param String $yilian_order 易联支付订单号不能太长
     * 所以会自己传订单号过来
     */
    public function recharge($member_id, $money, $pay_type,
        $bank_id = null, $password, $yilian_order = null) {

        //最少充值金额
        $min_money = yii::$app->params['recharge_min_money'];
        if ($min_money > $money) {
            return ApiService::error(Code::COMMON_ERROR_CODE, "金额不得低于{$min_money}元");
        }

        //校验支付密码
        $ckp = PasswordService::checkPassword($member_id,
            $password, PasswordService::PAY_PASSWORD);
        if (!$ckp) {
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '支付密码错误'];
        }

        //如果有传卡号校验卡是否有效
        if ($bank_id) {
            $bc = new BankService;
            $bankExtend = $bc->getCardByBankIdAndChannel($bank_id, $pay_type);
            if ($bankExtend) {
                $this->info['bankExtend'] = $bankExtend;
            } else {
                return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '无效卡'];
            }
        }

        //创建充值订单,并返回结果
        return $this->createRechargeOrder($member_id, $money,
            $bank_id, $pay_type, $yilian_order);
    }

    /**
     * 创建充值订单
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param  Int $member_id 用户id
     * @param  Decimal $money 充值金额
     * @param  Int $bank_id 银行卡卡号
     * @param  Int $pay_type 通道类型
     * @param String $yilian_order 易联支付订单号不能太长
     * 所以会自己传订单号过来
     * @return Array 创建结果
     */
    public function createRechargeOrder($member_id, $money, $bank_id, $pay_type, $yilian_order)
    {
        $trans = Yii::$app->db->beginTransaction();
        $res = [];
        try {
            $model = new QfbOrder;
            $model->sn = $yilian_order ?: $this->getRechargeSn();
            $model->member_id = $member_id;
            $model->price = $money;
            $model->is_check = OrderEnum::WAITING_PAYMENT;
            $model->remark = "充值";
            $model->sorts = 1; //目前充值只有充到零钱一种方式
            $model->bank_id = $bank_id ? $bank_id : "0";
            $model->type = OrderEnum::MONEY_IN;
            $model->create_time = time();
            $model->bank_type = $pay_type;
            if ($model->save()) {
                if ($pay_type == ChannelEnum::KUAIQIAN) {
                    $kq = $this->kqGetCodeBeforePay($model);
                    if ($kq) {
                        $res = ApiService::success(Code::HTTP_OK, '创建零钱充值订单成功',
                            [
                                'id' => $model->id,
                                'sn' => $model->sn,
                                'money' => $model->price,
                                'tips' => "零钱充值",
                            ]
                        );
                    } else {
                        $res = ApiService::error(Code::COMMON_ERROR_CODE, $this->message);
                        throw new Exception("kq");
                    }
                } else {
                    $res = ApiService::success(Code::HTTP_OK, '创建零钱充值订单成功',
                        ['id' => $model->id, 'sn' => $model->sn, 'money' => $model->price,
                            'create_time' => $model->create_time]);
                }
                $trans->commit();
            } else {
                $res = ApiService::error(Code::COMMON_ERROR_CODE, '创建零钱充值订单失败');
            }
            return $res;
        } catch (\Exception $e) {
            $trans->rollback();
            switch ($e->getMessage()) {
                case 'kq':
                    break;
                default:
                    $res = ApiService::error(Code::COMMON_ERROR_CODE, $e->getMessage());
                    break;
            }
            return $res;
        }
    }

    /**
     * 快钱充值前获取验证码
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param ActiveRecorder $order 充值订单数据库实例
     */
    private function kqGetCodeBeforePay($order)
    {
        $kq = Yii::$app->kuaiQian;
        $bankExtend = $this->info['bankExtend'];
        $res = $kq->getVerifyCodeBeforPay($order->member_id,
            $order->sn, $bankExtend->storable_pan,
            $bankExtend->bank->bank_abbr, $order->price);
        $bks = new BankService;
        if (isset($res['GetDynNumContent']) &&
            isset($res['GetDynNumContent']['responseCode'])) {
            if ($res['GetDynNumContent']['responseCode'] == "00") {
                //存token到bank记录里
                $bankExtend->token = $res['GetDynNumContent']['token'];
                return $bankExtend->save();
            } else {
                $code = $res['GetDynNumContent']['responseCode'];
                $message = $res['GetDynNumContent']['responseTextMessage'];
                $msg = $bks->getErrorMsg($code, ChannelEnum::KUAIQIAN);
                $this->message = $code . ":" . ($msg ?: $message);
                return false;
            }
        } else {
            if (isset($res['ErrorMsgContent'])) {
                $code = $res['ErrorMsgContent']['errorCode'];
                $message = $res['ErrorMsgContent']['errorMessage'];
                $msg = $bks->getErrorMsg($code, ChannelEnum::KUAIQIAN);
                $this->message = $code . ":" . ($msg ?: $message);
                return false;
            } else {
                $this->message = "快钱充值时获取验证码错误";
                return false;
            }
        }
    }

    /**
     * 生成绑卡订单号
     * @return String 订单号
     */
    public function getRechargeSn()
    {
        //生成随机字母+数字
        $str = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $code = "";
        $len = strlen($str);
        for ($i = 0; $i < 6; $i++) {
            $code .= $str{rand(0, $len - 1)};
        }
        return 'CZ' . date('YmdHis') . $code;
    }

    /**
     * 申请提现成功时记录日志
     * @param Decimal $money 支付金额
     * @param Decimal $old_money 原金额
     * @param obj $order
     */
    private function changeMoneyLog($old_money, $money, $order)
    {
        $model = new QfbMoneyLog($order['member_id']);
        $model->member_id = $order['member_id'];
        $model->type = 2;
        $model->money_type = 1;
        $model->money = $money;
        $model->create_time = time();
        $model->old_money = $old_money;
        $model->action = 7;
        $model->remark = '零钱提现';
        if ($model->save()) {
            return [
                'code' => Code::HTTP_OK,
                'msg' => '日志记录成功',
            ];
        } else {
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => '写入日志错误',
            ];
        }
    }

    /**
     * 根据member_id,order_id查询记录
     */
    public static function getOrder($member_id = 0, $order_id = 0)
    {
        $query = QfbOrder::find();

        $query->where([
            'id' => $order_id,
            'member_id' => $member_id,
        ]);

        $model = $query->asArray()->one();
        return $model;
    }

    /**
     * 根据member_id,order_id查询记录
     */
    public static function getFixOrder($member_id = 0, $order_id = 0)
    {
        $query = QfbOrderFix::find();

        $query->joinWith(['order_fix_extend', 'product']);

        $query->where([
            QfbOrderFix::tableName() . '.id' => $order_id,
            QfbOrderFix::tableName() . '.member_id' => $member_id,
        ]);

        $model = $query->asArray()->one();
        return $model;
    }

    /**
     *根据member_id查询活期、充值、提现记录
     */
    public static function getRechargeList($uid, $type, $page, $limit)
    {
        $query = QfbOrder::find();
        $query->select(['id', 'money', 'is_check', 'create_time', 'bank_type', 'out_type', 'remark']);
        $query->offset(($page - 1) * $limit);
        $query->limit($limit);
        $query->orderBy('create_time desc');
        $query->where(['=', 'member_id', $uid]);
        if ($type == 1) {
            //充值
            $query->andWhere(['=', 'type', 1]);
            $query->andWhere(['=', 'sorts', 1]);
            $query->andWhere(['=', 'is_check', 1]);
        } elseif ($type == 2) {
            //提现
            $query->andWhere(['=', 'type', 2]);
            $query->andWhere(['=', 'sorts', 1]);
        } elseif ($type == 4) {
            //活期
            $query->andWhere(['=', 'type', 2]);
            $query->andWhere(['=', 'sorts', 2]);
            $query->andWhere(['=', 'is_check', 1]);
        }

        $model = $query->all();
        return $model;
    }

    /**
     *根据member_id查询定期理财记录
     *@type  0 查找全部类型  1查找持有中和收益中，2查找已退款
     */
    public static function getRegularList($uid, $page, $limit, $type = 0)
    {
        $query = QfbOrderFix::find();
        $query->offset(($page - 1) * $limit);
        $query->limit($limit);
        $query->orderBy('qfb_order_fix.create_time desc');
        $query->joinWith('product');
        $query->where(['=', 'qfb_order_fix.member_id', $uid]);
        if ($type == 0) {
            $query->andWhere(['in', 'qfb_order_fix.status', [1, 2, 3]]);
        }
        if ($type == 1) {
            $query->andWhere(['in', 'qfb_order_fix.status', [1, 2]]);
        }
        if ($type == 2) {
            $query->andWhere(['=', 'qfb_order_fix.status', 3]);
        }
        $model = $query->all();
        return $model;
    }

    /**
     * 充值成功处理
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param  String $order_sn 订单号
     * @return Bool 结果
     */
    public function rechargeSuccessHandle($sn, $bank_sn = '')
    {
        $trans = Yii::$app->db->beginTransaction();
        try {
            $order = QfbOrder::find()->where(['sn' => $sn
                , 'is_check' => OrderEnum::WAITING_PAYMENT])->one();
            if ($order) {
                //修改订单状态
                $order->is_check = OrderEnum::ALREADY_PAYMENT;
                $order->complete_time = time();
                $order->bank_sn = $bank_sn;
                $order->money = $order->price;
                if ($order->save()) {
                    //修改用户等级
                    $member = QfbMember::find()->where(['id' => $order->member_id,
                        'level' => 15])->one();
                    if ($member) {
                        $member->level = 1;
                        if (!$member->save()) {
                            throw new Exception("level");
                        }
                    }
                    //修改用户零钱金额
                    $mm = QfbMemberMoney::find()->where(['member_id' => $order->member_id])->one();
                    $old_money = $mm->money;
                    $mm->money += $order->price;
                    if (!$mm->save()) {
                        throw new Exception("money");
                    }
                    //存moneylog
                    $ms = new MoneyLogService($order->member_id);
                    $data = [
                        'member_id' => $order->member_id,
                        'type' => MoneyEnum::MONEY_IN,
                        'money_type' => MoneyEnum::MONEY,
                        'create_time' => time(),
                        'money' => $order->price,
                        'old_money' => $old_money,
                        'action' => 2,
                        'remark' => '零钱充值',
                    ];
                    if (!$ms->create($data)) {
                        throw new Exception("moneyLog");
                    }
                    $trans->commit();
                    return ApiService::success(Code::HTTP_OK, '回调成功');
                } else {
                    throw new Exception("order");
                }
            } else {
                throw new Exception("notfound");
            }

        } catch (\Exception $e) {
            $trans->rollback();
            switch ($e->getMessage()) {
                case 'order':
                    $res = ApiService::error(Code::COMMON_ERROR_CODE,
                        "修改订单状态失败");
                    break;
                case 'level':
                    $res = ApiService::error(Code::COMMON_ERROR_CODE,
                        "修改用户等级失败");
                    break;
                case 'money':
                    $res = ApiService::error(Code::COMMON_ERROR_CODE,
                        "修改用户余额失败");
                    break;
                case 'moneyLog':
                    $res = ApiService::error(Code::COMMON_ERROR_CODE,
                        "保存moneylog失败");
                    break;
                case 'notfound':
                    $res = ApiService::error(Code::COMMON_ERROR_CODE,
                        "订单不存在，或已处理！");
                    break;
                default:
                    $res = ApiService::error(Code::COMMON_ERROR_CODE, $e->getMessage());
                    break;
            }
            return $res;
        }
    }

    /**
     * 修改订单状态
     * @param int $order_id         订单信息id
     * @return Boolean [ture,false]
     */
    public function changeOrderCheck($order_id = 0, $status = 1)
    {
        if (empty($order_id)) {
            return false;
        }

        $transfer = QfbOrder::findOne($order_id);
        $data['QfbOrder'] = [
            'is_check' => $status,
        ];
        if ($status == OrderEnum::PAY_NO) {
            $data['QfbOrder'] = [
                'is_check' => $status,
                'complete_time' => time(),
            ];
        }

        if ($transfer->load($data) && $transfer->save()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *申请提现失败时返回用户账户里的余额
     */
    public function changeReMoney($money, $member_id)
    {
        $trans = Yii::$app->db->beginTransaction();
        try {
            $member_money = QfbMemberMoney::find()->where(['member_id' => $member_id])->one();
            if (!$member_money) {
                throw new Exception('帐户不存在');
            }

            //冻结金额
            $member_money->lock_money = bcsub($member_money->lock_money, $money, 2);
            //零钱
            $old_money = $member_money->money;
            $member_money->money = bcadd($member_money->money, $money, 2);
            if (!$member_money->save()) {
                throw new Exception("member_money");
            }

            //写入日志
            $MoneyLogService = new MoneyLogService($member_id);
            $data = [
                'member_id' => $member_id,
                'type' => MoneyEnum::MONEY_IN,
                'money_type' => MoneyEnum::MONEY,
                'create_time' => time(),
                'money' => $money,
                'old_money' => $old_money,
                'action' => 9,
                'remark' => '提现未通过审核返还',
            ];
            if (!$MoneyLogService->create($data)) {
                throw new Exception("moneyLog");
            }

            $trans->commit();
            return ApiService::error(Code::HTTP_OK, '提现失败，退还成功');
        } catch (Exception $e) {
            $trans->rollback();
            switch ($e->getMessage()) {
                case 'member_money':
                    return ApiService::error(Code::COMMON_ERROR_CODE, "修改金额失败");
                    break;
                case 'moneyLog':
                    return ApiService::error(Code::COMMON_ERROR_CODE, "保存日志失败");
                    break;
                default:
                    return ApiService::error(Code::COMMON_ERROR_CODE, $e->getMessage());
                    break;
            }
        }
    }

    /**
     * 通用的活期订单产生
     * 支持所有支付通道，目前只有华融，没有做零钱支付，和证联支付的修改
     * 因为luo已写了，只不过没有通用
     * 以后要加支付方式的话，只需要根据payment参数做判断，
     * 大可不必在控制器判断支付方式了
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param int $member_id 用户ID
     * @param int $product_id 产品ID
     * @param float $money 购买金额
     * @param int $bank_id 银行卡ID
     * @param int $payment 支付方式
     * @return bool
     */
    public function current(
        $member_id,
        $product_id,
        $money,
        $bank_id,
        $payment
    ) {
        //1.判断是否可转入
        if (SystemService::isCanMoney() == false) {
            $this->message = '数钱中';
            return false;
        }

        //2,判断产品
        if ($this->validProduct($product_id) == false) {
            $this->message = '产品无效';
            return false;
        }

        //校验卡是否有效
        $bc = new BankService;
        $bankExtend = $bc->getCardByBankIdAndChannel($bank_id, $payment);
        if (!$bankExtend) {
            $this->message = '无效卡';
            return false;
        }
        $bank = $bankExtend->bank;

        //创建活期订单
        $model = $this->createCurrentOrder(
            $member_id,
            $money,
            $bank_id,
            $payment
        );

        if ($model) {
            //根据不同支付通道，处理订单生成后逻辑
            //一般是要获取验证码，以供以后支付使用
            if ($payment == ChannelEnum::HUARONG) { //华融支付
                //调用获取验证码接口
                $hr = Yii::$app->hrpay;
                $res = $hr->getVerifyCode (
                    $bank->mobile,
                    $model->hr_sn,
                    $money,
                    $model->remark
                );
                if (!$res) {
                    $this->message = '获取验证码失败';
                    return false;
                } else {
                    return true;
                }
            }elseif ($payment == ChannelEnum::YILIAN){
                //调用获取验证码接口
                $ylPay = Yii::$app->YiLian;
                //易联绑卡必须先发送短信
                $res_code = $ylPay->send_message(
                    [
                        'ACC_NO'=>$bank->no,//卡号
                        'ACC_NAME'=>$bank->username,//开户姓名
                        'ID_NO'=>'',
                        'MOBILE_NO'=>$bank->mobile,
                        //'AMOUNT'=>'',
                        //'CNY'=>'CNY',
                        'PAY_STATE'=>'',
                        'MER_ORDER_NO'=>$model->sn,
                        'TRANS_DESC'=>$model->remark
                    ]
                );

                if ($res_code['TRANS_STATE']=='0000' && $res_code['PAY_STATE']=='0000') {
                    return true;
                } else {
                    $this->message = $res_code['REMARK'];
                    return false;
                }
            }
        } else {
            $this->message = '创建订单失败';
            return false;
        }
    }

    /**
     * 创建活期订单
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param int $member_id 用户ID
     * @param float $money 购买金额
     * @param int $bank_id 银行卡ID
     * @param int $payment 支付方式
     * @return bool | ActiveRecord
     */
    private function createCurrentOrder(
        $member_id,
        $money,
        $bank_id,
        $payment
    ) {
        $model = $this->newModel();
        $model->member_id = $member_id;
        $model->create_time = $this->getTime();
        $model->bank_sn = '';
        $model->sn = ToolService::SetSn('HQ');
        $model->price = $money;
        $model->is_check = OrderEnum::WAITING_PAYMENT;
        $model->remark = '购买' . $this->productService->model->product_name;
        $model->complete_time = $this->getTime();
        $model->type = OrderEnum::MONEY_IN;
        $model->sorts = OrderEnum::FLOW_MONEY;
        $model->bank_id = $bank_id;
        $model->fee = 0;
        $model->money = $money;
        $model->bank_type = $payment;
        $model->out_type = 0;
        $model->hr_sn = ToolService::SetSn('HRC');
        return $this->model = $model->save() ? $model : false;
    }

    /**
     * 处理订单订单成功逻辑, 支付通道无关(所有支付通道通用)
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param ActiveRecorder $order 订单记录
     * @param string $order_type 订单类型(current活期，fix定期)
     * @param float $amount 金额
     * @param int $payment_type 支付通道
     * @param string $bank_sn 支付流水号
     */
    public function handleSuccess(
        $order,
        $order_type,
        $payment_type,
        $bank_sn = ''
    ) {
        $trans = Yii::$app->db->beginTransaction();
        try {
            if ($order_type == 'fix') {
                    $memMoneyModel = QfbMemberMoney::findOne(['member_id' => $order->member_id]);
                    $memMoneyModel->fix_money = bcadd($memMoneyModel->fix_money, $order->money, 2);
                    if ($memMoneyModel->save()) {
                        $memModel = QfbMember::findOne(['id' => $order->member_id]);
                        //如果是普通会员改变等级
                        if($memModel->level ==15){
                            $memModel->level = 1;
                        }

                        if ($memModel->save()) {
                            //如果存在代金券，处理代金券
                            if ($order->order_fix_extend) {
                                $memVouchModel = new MemberVoucherService();
                                $memVouchModel->validExist($order->order_fix_extend->money_ticket_id);
                                $memVouchModel->cost($order->order_fix_extend->money_ticket_id, $order->product_id);
                            }
                            $proServ = new ProductService();
                            $proServ->validExist($order->product_id);
                            $orderProfitTime = $proServ->setProfitDay();
                            $order->end_time = $orderProfitTime->end_time;
                            $order->next_profit_time = $orderProfitTime->next_profit_time;
                            $order->status = isset($orderProfitTime->status) ? $orderProfitTime->status : 0;
                            $order->bank_sn = $bank_sn;
                            if ($order->save()) {
                                if ($proServ->addHasMoney($order->product_id, $order->money)) {
                                    $memServ = new MemberService();
                                    $memServ->validExist($order->member_id);
                                    //如果是新手产品，购买之后修改用户新手状态
                                    if ($proServ->validIsNewer() == true) {
                                        if (!$memServ->updateNew()) {
                                            throw new Exception($memServ->findOneMessage());
                                        }

                                    }

                                    //成功记录定期代收奖励日志
                                    $settingModel = QfbProfitSettings::find()->where(['product_id'=>$order->product_id])->one();
                                    $fixLogService = new OrderFixLogService();
                                    if ($fixLogService->createList($order, $settingModel, $proServ->model) == false) {
                                        $this->messages = $fixLogService->getMessages();
                                        return false;
                                    }

                                    $orderFixServ = new OrderFixService();
                                    $orderFixServ->validExist($order->id);
                                    //记录moneylog的日志记录
                                    if ($orderFixServ->writeMoneyLog($order->member_id, $order->product_id) == true) {

                                        $trans->commit();
                                        //die("success");
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
            } else if ($order_type == 'current') {
                    $order->is_check = 1;
                    $order->bank_sn = $bank_sn;
                    if ($order->save()) {
                        $memMoneyModel = QfbMemberMoney::findOne(['member_id' => $order->member_id]);
                        $memMoneyModel->pre_live_money = bcadd($memMoneyModel->pre_live_money, $order->price, 2);
                        if ($memMoneyModel->save()) {
                            $memModel = QfbMember::findOne(['id' => $order->member_id]);
                            //如果是普通会员改变等级
                            if($memModel->level ==15){
                                $memModel->level = 1;
                            }

                            if ($memModel->save()) {
                                //5.记录日志
                                $moneyLogService = new MoneyLogService($order->member_id);
                                $logData = [
                                    'member_id' => $order->member_id,
                                    'type' => 1,
                                    'money_type' => 2,
                                    'create_time' => time(),
                                    'money' => $order->price,
                                    'old_money' => 0,
                                    'action' => 2,
                                    'remark' => sprintf('购买%s', "活期理财"),
                                ];
                                if ($moneyLogService->create($logData) == true) {
                                    $trans->commit();
                                    //die("success");
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
                throw new Exception("未知的订单类型");
            }
        } catch (\Exception $e) {
            $trans->rollBack();
            var_dump($e->getMessage());
            exit;
        }
    }
}
