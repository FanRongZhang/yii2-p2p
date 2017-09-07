<?php

namespace common\service;

use common\enum\ChannelEnum;
use common\enum\ProductEnum;
use common\models\orderFix\BuyDetail;
use common\models\orderFix\MemberType;
use common\models\QfbMemberInfo;
use yii;

class OrderFixService extends BaseService
{

    protected $_className = "common\models\QfbOrderFix";
    protected $productService; //产品服务
    protected $memberVoucherService; //代金券服务
    protected $memberMoneyService; //用户金额服务
    protected $memberService;
    protected $orderfixExtendService; // 订单扩展表
    protected $moneyLogService; //金额日志
    protected $ProfitSettingService;
    /**
     * [buyMemberList description]
     * @param  [type]  $product_id [description]
     * @param  integer $page       [description]
     * @param  integer $limit      [description]
     * @return [type]              [description]
     */
    public function buyMemberList($product_id, $page = 1, $limit = 20)
    {
        $page = ToolService::setMin($page);
        $limit = ToolService::setMin($limit);
        $list = $this->findBySql(sprintf("
			SELECT `qfb_order_fix`.id,`qfb_order_fix`.create_time ,qfb_member.`level`,qfb_member.`mobile`,qfb_order_fix.`money`
			FROM `qfb_order_fix`
			LEFT JOIN `qfb_member` ON `qfb_member`.id = qfb_order_fix.`member_id`
			WHERE `qfb_order_fix`.`product_id` = %d AND `qfb_order_fix`.`status` IN (1,2,3) limit %d,%d", $product_id, ($page - 1) * $limit, $limit));
        $tmp = [];
        if (count($list) > 0) {
            foreach ($list as $key => $value) {
                if ($value['level']) {
                    $model = new MemberType();
                    $tmp[] = $model->getLevel($value['level'])->getMobile($value['mobile'])->getMoney($value['money'])->getTime($value['create_time']);
                }
            }
        }
        return $tmp;
    }

    /**
     * 用户购买某产品的记录
     * [buyProductList description]
     * @param  [type] $product_id [description]
     * @param  [type] $page       [description]
     * @param  [type] $limit      [description]
     * @return [type]             [description]
     */
    public function buyProductList($product_id, $page = 1, $limit = 20)
    {
        $page = ToolService::setMin($page);
        $limit = ToolService::setMin($limit);
        $model = new $this->_className;
        $list = $model::find()
            ->select(
                [
                    $model->tableName() . '.id',
                    $model->tableName() . '.money',
                    $model->tableName() . '.create_time',
                    'qfb_order_fix_extend.money_ticket_id',
                    'qfb_order_fix_extend.money_ticket_num',
                ]
            )
            ->joinWith('order_fix_extend')
            ->where(['=', $model->tableName() . '.product_id', $product_id])
            ->andWhere(['=', $model->tableName() . '.member_id', 1])  //self::getMemberID()
            ->andWhere(['IN',$model->tableName() . '.status',[1,2,3]])
            ->offset(($page - 1) * $limit)->limit($limit)
            ->all();
        $tmp = [];
        if (count($list) > 0) {
            foreach ($list as $key => $value) {
                $BuyDetail = new BuyDetail();
                $tmp[] = $BuyDetail->getMoney($value->money)->getVouchersMoney($value->order_fix_extend->money_ticket_num)->getTime($value->create_time);
                $tmp[$key]->vouchers_money = $tmp[$key]->vouchers_money ? $tmp[$key]->vouchers_money : '0';
            }
        }
        return $tmp;
    }

    /**
     * 零钱购买定期
     * [save description]
     * @param  [type] $params [description]::'product_id','money', 'member_voucher_id', 'member_id',
     * @return [type]         [description]
     */
    public function doSaveByMoney($params)
    {

        if (SystemService::isCanMoney() == false) {
            $this->addMessage('id', '数钱中');
            return false;
        }

        // 验证产品相关信息
        if ($this->verifyBeforsave($params)) {

            //验证用户零钱是否足够
            if ($this->validMemberMoney($params['member_id'], $params['money'])) {

                /** 分润设置 */
                $settingService = new ProfitSettingsService();
                $settingModel = $settingService->findModelByProduct($this->productService->model->id);
                if ($settingModel == null) {
                    $this->addMessage('profit_type', '产品利率配置不存在');
                    return false;
                }
                /** 创建定期订单 */
                if ($this->create($params)) {
                    // 0.创建定期订单扩展数据
                    if (!$this->createFixOrderExtend($settingModel)) {
                        return false;
                    }

                    // 1.如果有代金券 记录代金券
                    if ($this->memberVoucherService->model != null) {
                        if (!$this->createVoucher()) {
                            return false;
                        }

                    }
                    // 2.用户金额转移
                    if ($this->memberMoneyService->buyFix($this->memberMoneyService->model->member_id, $this->model->pay_money, $this->model->money) == false) {
                        $this->messages = $this->memberMoneyService->getMessages();
                        return false;
                    }
                    // 3.产品金额增加
                    if (!$this->addProductHasMoney()) {
                        return false;
                    }

                    // 4.如果是新手
                    if ($this->productService->model->is_newer == 1 && $this->memberService->updateNew() == false) {
                        $this->messages = $this->memberService->getMessages();
                        return false;
                    }
                    // 5.创建购买定期订单日志
                    if (!$this->createMoneyLogData($params['member_id'])) {
                        return false;
                    }

                    return true;
                } else {
                    return false;
                }

            } else {
                return false;
            }

        }
        return false;
    }

    /**
     * @return bool
     * 证联支付定期订单
     * luo 写， xiaomalover 加华融支付
     */
    public function payOrder($params)
    {
        if (SystemService::isCanMoney() == false) {
            $this->addMessage('id', '数钱中');
            return false;
        }

        //验证产品信息
        if ($this->verifyBeforsave($params)) {
            /** 分润设置 */
            $settingService = new ProfitSettingsService();
            $settingModel = $settingService->findModelByProduct($this->productService->model->id);

            if ($settingModel == null) {
                $this->addMessage('profit_type', '产品利率配置不存在');
                return false;
            }
            /** 创建定期订单 */
            if ($this->create($params)) {

                $memInfoModel = QfbMemberInfo::findOne(['member_id' => $params['member_id']]);
                $data = [
                    'pay_money' => $this->model->pay_money,
                    'bindId' => $memInfoModel->bindId,
                    'id' => $this->model->id,
                    'sn' => $this->model->sn,
                ];

                if ($params['payment'] == ChannelEnum::ZHENGLIAN) {
                    //创建证联支付订单
                    $zlRes = \Yii::$app->ZlPay->createOrdertn($data);
                    if ($zlRes->respCode == "00") {
                        $this->model->bank_sn = $zlRes->tn;
                        $this->model->bank_id = $params['bank_id'];
                        $this->model->bank_type = 6;
                        if ($this->model->save()) {
                            $smsData['bindId'] = strval($memInfoModel->bindId);
                            $smsData['tn'] = $this->model->bank_sn;
                            //发送支付手机验证
                            $zlSms = \Yii::$app->ZlPay->smsVerify($smsData);
                            if ($zlSms->respCode == "00") {
                                // 0.创建定期订单扩展数据
                                if (!$this->createFixOrderExtend($settingModel)) {
                                    return false;
                                }
                                return true;
                            } else {
                                $this->addMessage('zlsms', $zlSms->respMsg . "错误码:" . $zlSms->respCode);
                                return false;
                            }
                        } else {
                            $this->addMessage('zltn', "保存受理订单失败");
                        }

                    } else {
                        $this->addMessage('zlorder', $zlRes->respMsg . "错误码:" . $zlRes->respCode);
                        return false;
                    }
                } else if ($params['payment'] == ChannelEnum::HUARONG) {
                    //待支付订单，写的好绕，搞不太懂
                    $order = $this->model;
                    //华融支付组件
                    $hr = Yii::$app->hrpay;

                    //校验卡是否有效
                    $bc = new BankService;
                    $bankExtend = $bc->getCardByBankIdAndChannel(
                        $params['bank_id'],
                        ChannelEnum::HUARONG
                    );
                    if (!$bankExtend) {
                        $this->message = '无效卡';
                        return false;
                    }
                    $bank = $bankExtend->bank;

                    //华融支付每次都要不同的订单号，所以每次要生成订单号
                    $order->hr_sn = ToolService::SetSn('HRF');
					$this->model->bank_id = $params['bank_id'];
					$this->model->bank_type = ChannelEnum::HUARONG;
                    if ($order->save()) {
                        $res = $hr->getVerifyCode(
                            $bank->mobile,
                            $order->hr_sn,
                            $order->pay_money,
                            $order->product->product_name,
                            $bank->no,
                            $bank->username,
                            $memInfoModel->card_no
                        );
                        if (!$res) {
                            $this->message = '获取验证码失败';
                            return false;
                        } else {
                            return true;
                        }
                    } else {
                        $this->message = '生成华融支付订单号失败';
                        return false;
                    }
                }else if(ChannelEnum::YILIAN){//易联支付
                    $order = $this->model;
                    //校验卡是否有效
                    $bc = new BankService;
                    $bankExtend = $bc->getCardByBankIdAndChannel(
                        $params['bank_id'],
                        ChannelEnum::YILIAN
                    );
                    if (!$bankExtend) {
                        $this->addMessage('bank_extend', '无效卡');
                        return false;
                    }
                    $bank = $bankExtend->bank;
                    $this->model->bank_id = $params['bank_id'];
                    $this->model->bank_type = ChannelEnum::YILIAN;

                    //调用获取验证码接口
                    $ylPay = Yii::$app->YiLian; 
                    //易联支付必须先发送短信
                    $res_code = $ylPay->send_message(
                        [
                            'ACC_NO'=>$bank->no,//卡号
                            'ACC_NAME'=>$bank->username,//开户姓名
                            'ID_NO'=>'',
                            'MOBILE_NO'=>$bank->mobile,
                            //'AMOUNT'=>'',
                            //'CNY'=>'CNY',
                            'PAY_STATE'=>'',
                            'MER_ORDER_NO'=>$order->sn,
                            'TRANS_DESC'=>'购买定期理财'
                        ]
                    );

                    if ($res_code['TRANS_STATE']=='0000' && $res_code['PAY_STATE']=='0000') {
                        // 0.创建定期订单扩展数据
                        if (!$this->createFixOrderExtend($settingModel,ChannelEnum::YILIAN)) {
                            $this->addMessage('fix_extend', '订单扩展数据保存失败');
                            return false;
                        }
                        return true;
                    } else {
                        $this->addMessage('sms_error', $res_code['REMARK']);
                        return false;
                    }
                }
            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    /**
     * 定期产品订单附加表
     * 创建定期订单附加表数据
     */
    public function createFixOrderExtend($settingModel,$type='')
    {
        $extendData = [
            'order_id' => $this->model->id,
            'admin_rate' => $settingModel->manage_rate,
            'share_rate' => $settingModel->recommond_rate,
        ];
        if ($this->memberVoucherService->model != null) {
            $extendData['money_ticket_num'] = $this->memberVoucherService->model->vouchers->money;
            $extendData['money_ticket_id'] = $this->memberVoucherService->model->id;
        }
        $this->orderfixExtendService = new OrderFixExtendService();
        if ($this->orderfixExtendService->create($extendData) == false) {
            $this->messages = $this->orderfixExtendService->getMessages();
            return false;
        }

        //如果订单没支付是不能写入定期日志表
        if(empty($type)){
            $fixLogService = new OrderFixLogService();
            if ($fixLogService->createList($this->model, $settingModel, $this->productService->model) == false) {
                $this->messages = $fixLogService->getMessages();
                return false;
            }
        }

        return true;
    }
    protected function verifyBeforsave($params)
    {
        //验证产品相关信息
        if ($this->validProduct($params['product_id'], $params['money'], $params['member_voucher_id'])) {

            //验证是否是新手产品
            if ($this->validNewer()) {

                //验证该产品还可投入的资金
                if ($this->validMax($params)) {
                    //代金券
                    if ($this->validVouchers($params['member_voucher_id'], $params['member_id'], $params['money'])) {
                        return true;
                    } else {
                        return false;
                    }

                } else {
                    return false;
                }

            } else {
                return false;
            }

        } else {
            return false;
        }

    }
    /**创建零钱购买定期日志*/
    public function createMoneyLogData($member_id)
    {
        //5.记录日志
        $this->moneyLogService = new MoneyLogService($member_id);
        $logData = $this->getMoneyLogData($member_id);
        if ($this->moneyLogService->createList($logData) == false) {
            $this->messages = $this->moneyLogService->getMessages();
            return false;
        }
        return true;
    }
    /**创建定期购买定期日志*/
    public function writeMoneyLog($member_id, $product_id)
    {
        //5.记录日志
        $this->moneyLogService = new MoneyLogService($member_id);
        $this->productService = new ProductService();
        $this->productService->validExist($product_id);
        $logData = [
            'member_id' => $member_id,
            'type' => 1,
            'money_type' => 3,
            'create_time' => $this->getTime(),
            'money' => $this->model->pay_money,
            'old_money' => 0,
            'action' => 2,
            'remark' => sprintf('购买%s', $this->productService->model->product_name),
        ];
        if ($this->moneyLogService->create($logData) == false) {
            $this->messages = $this->moneyLogService->getMessages();
            return false;
        }
        return true;
    }

    /**
     * @return bool
     * 记录代金券
     */
    public function createVoucher()
    {
        if ($this->memberVoucherService->cost($this->memberVoucherService->model->id, $this->productService->model->id) == false) {
            $this->messages = $this->memberVoucherService->getMessages();
            return false;
        }
        return true;
    }
    /**记录产品定增金额*/
    public function addProductHasMoney()
    {
        if ($this->productService->addHasMoney($this->productService->model->id, $this->model->money) == false) {
            $this->messages = $this->productService->getMessages();
            return false;
        }
        return true;
    }
    protected function getMoneyLogData($member_id)
    {
        $logData = [
            [
                'member_id' => $member_id,
                'type' => 2,
                'money_type' => 1,
                'create_time' => $this->getTime(),
                'money' => $this->model->pay_money,
                'old_money' => $this->memberMoneyService->model->money,
                'action' => 12,
                'remark' => sprintf('购买%s', $this->productService->model->product_name),
            ],
            [
                'member_id' => $member_id,
                'type' => 1,
                'money_type' => 3,
                'create_time' => $this->getTime(),
                'money' => $this->model->pay_money,
                'old_money' => $this->memberMoneyService->model->fix_money,
                'action' => 12,
                'remark' => sprintf('购买%s', $this->productService->model->product_name),
            ],
        ];
        return $logData;
    }

    /**
     * 创建定期
     * [create description]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function create($params)
    {
        $this->model = $this->newModel();
        $this->model->sn = ToolService::SetSn('DQ');
        $this->model->member_id = $params['member_id'];
        $this->model->product_id = $this->productService->model->id;
        $this->model->money = $params['money'];
        $this->model->pay_money = self::getPayMoney($params['money']);
        $this->model->number = 0;
        $this->model->status = 0;
        $this->model->create_time = $this->getTime();
        $this->setYearRate();
        $this->model->profit_money = bcdiv(bcmul(bcmul($this->model->money, $this->model->year_rate, 2), $this->productService->model->invest_day, 2), 36500, 2);
        $this->model->end_time = 0;
        $this->model->next_profit_time = 0;
        // $this->model->bank_type = $params['payment'];

        //如果不是零钱支付的情况下
        // if ($params['payment'] == ChannelEnum::YILIAN || $params['payment'] == ChannelEnum::HUARONG) {
        //     $this->model->status = 0;
        //     $this->model->end_time = 0;
        //     $this->model->next_profit_time = 0;
        //     $this->model->bank_id   = $params['bank_id'];
        // } else {
            $orderFix = $this->productService->setProfitDay($this->productService->model->profit_day);
            $this->model->status = $orderFix->status;
            $this->model->end_time = $orderFix->end_time;
            $this->model->next_profit_time = $orderFix->next_profit_time;
        // }
        if ($this->model->validate() && $this->model->save()) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * 设置年化收益率
     */
    protected function setYearRate()
    {
        $this->model->year_rate = $this->productService->model->year_rate;
        return $this;
    }

    /**
     * 设置产品的下次分润时间以及结束时间
     * [setProductDay description]
     */
    public function setProductDay($profit_day)
    {
        switch ($profit_day) {
            //起息日:(1)10:投资日(2)11:投资日+1(3)20:满标日(4)21:满标日+1
            case 10:
                $start_time = strtotime(date("Ymd"));
                $orderfix = $this->productService->setProductProfitType($start_time);
                $this->model->status = 2;
                break;
            case 11:
                $start_time = strtotime(date("Ymd")) + 24 * 3600;
                $orderfix = $this->productService->setProductProfitType($start_time);
                $this->model->status = 2;
                break;
            case 20:
                $orderfix = $this->productService->setProductProfitType(0);
                break;
            case 21:
                $orderfix = $this->productService->setProductProfitType(0);
                break;
            default:
                $this->addMessage('profit_day', '产品配置错误');
                return false;
                break;
        }
        $this->model->end_time = $orderfix->end_time;
        $this->model->next_profit_time = $orderfix->next_profit_time;
        return true;
    }
    /**
     * 计算应支付金额
     * [getPayMoney description]
     * @return [type] [description]
     */
    protected function getPayMoney($money)
    {
        $vouchersMoney = 0;
        if (isset($this->memberVoucherService->model->vouchers->money) && $this->memberVoucherService->model->vouchers->money > 0) {
            $vouchersMoney = $this->memberVoucherService->model->vouchers->money;
        }

        $pay_money = bcsub($money, $vouchersMoney, 2);
        return $pay_money > 0 ? $pay_money : 0;
    }
    /**
     * 验证用户金额
     * [validMemberMoney description]
     * @param  [type] $member_id [description]
     * @param  [type] $money     [description]
     * @return [type]            [description]
     */
    public function validMemberMoney($member_id, $money)
    {
        $this->memberMoneyService = new MemberMoneyService();
        $this->memberMoneyService->findModel($member_id);
        $vouchersMoney = 0;
        if (isset($this->memberVoucherService->model->vouchers->money) && $this->memberVoucherService->model->vouchers->money > 0) {
            $vouchersMoney = $this->memberVoucherService->model->vouchers->money;
        }

        $last_money = bcsub($this->memberMoneyService->model->money, $money-$vouchersMoney, 2);
        if ($last_money >= 0) {
            return true;
        } else {
            $this->addMessage('money', '用户金额不足');
            return false;
        }
    }

    /**
     * 代金券验证
     * [validVouchers description]
     * @param  [type] $member_voucher_id [description]
     * @param  [type] $member_id         [description]
     * @param  [type] $money             [description]
     * @return [type]                    [description]
     */
    public function validVouchers($member_voucher_id, $member_id, $money)
    {
        $this->memberVoucherService = new MemberVoucherService();
        if ($member_voucher_id > 0) {
            if (
                $this->memberVoucherService->validExist($member_voucher_id) &&
                $this->memberVoucherService->validVoucher($member_id) &&
                $this->memberVoucherService->validProduct(ProductEnum::FIX) &&
                $this->memberVoucherService->validMoney($money, $member_voucher_id) &&
                $this->memberVoucherService->validHasBuy($this->productService->model->id)
            ) {
                return true;
            } else {
                $this->messages = $this->memberVoucherService->getMessages();
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * 产品验证
     * [validProduct description]
     * @param  [type] $product_id [产品id]
     * @param  [type] $money      [支付金额]
     * @param  [type] $member_voucher_id      [代金券id]
     * @return [type]             [description]
     */
    public function validProduct($product_id, $money, $member_voucher_id)
    {

        $this->productService = new ProductService();
        if (

            //验证产品是否存在
            $this->productService->validExist($product_id) &&
            //验证是否是定期产品
            $this->productService->validFix() && //定期
            //验证产品状态
            $this->productService->validStatus(ProductEnum::STATUS_BUY) &&
            //验证购买金额
            $this->productService->validMoney($money) &&
            //是否支持加息卷
            $this->productService->validRateTicket() && //加息券
            //是否支持代金券
            $this->productService->validMoneyTicket($member_voucher_id) && //代金券
            //验证产品有效期
            $this->productService->validTime()
        ) {
            return true;
        } else {
            $this->messages = $this->productService->getMessages();
            return false;
        }
    }

    public function validNewer($productModel = null)
    {
        if ($productModel == null) {
            $productModel = $this->productService->model;
        }
        if (!isset($productModel['is_newer'])) {
            $this->addMessage('status', '产品配置错误');
            return false;
        }
        if ($productModel['is_newer'] != 1) {
            return true;
        }

        if ($this->isBuy()) {
            $this->addMessage('status', '新手才能购买');
            return false;
        }
        return true;
    }

    public function validMax($params)
    {
        $productModel = $this->productService->model;
        $findMoney = $this->findMoneys($productModel['id'], $params['member_id']);
        if ($params['money'] + $findMoney > $productModel['stock_money']) {
            $this->addMessage('stock_money', '该产品还可购买' . ($productModel['stock_money'] - $findMoney) . '元');
            return false;
        } else {
            return true;
        }
    }

    public function findMoneys($product_id, $member_id)
    {
        $model = new $this->_className;
        $model = $model::find()
            ->filterWhere(['=', 'product_id', $product_id])
            ->andWhere(['=', "member_id", $member_id])
            ->andWhere(['IN', "status", [1,2,3]])
            ->select(['sum(money) as money'])
            ->one();
        return isset($model['money']) ? $model['money'] : 0;
    }

    public function findMoney($product_id)
    {
        $model = new $this->_className;
        $model = $model::find()
            ->filterWhere(['=', 'product_id', $product_id])
            ->andWhere(['=', "member_id", $this->getMemberID()])  //
            ->andWhere(['IN', "status", [1,2,3]])
            ->select(['sum(money) as money'])
            ->one();
        return isset($model['money']) ? $model['money'] : 0;
    }

    public function findPriceAndProfit($product_id)
    {
        $model = new $this->_className;
        $model = $model::find()
            ->filterWhere(['=', 'product_id', $product_id])
            ->andWhere(['=', "member_id", $this->getMemberID()])
            ->andWhere(['IN','status',[1,2,3]])
            ->select(['sum(pay_money) as pay_money', 'sum(profit_money) as profit_money'])
            ->one();
        return $model;
    }

    public function findPrice($product_id = null)
    {
        $model = new $this->_className;
        $model = $model::find()
            ->filterWhere(['=', 'product_id', $product_id])->andWhere(['=', "member_id", $this->getMemberID()])
            ->select(['sum(pay_money) as pay_money'])
            ->one();
        return isset($model['pay_money']) ? $model['pay_money'] : 0;
    }

    public function isBuy()
    {
        $this->memberService = $this->memberService === null ? new MemberService() : $this->memberService;
        return !$this->memberService->isNewer();
        $model = new $this->_className;
        $model = $model::find()->joinWith('product')
            ->where(['=', 'qfb_product.is_newer', 1])->andWhere(['=', "qfb_order_fix.member_id", $this->getMemberID()])
            ->select(['qfb_order_fix.money'])
            ->one();
        return isset($model['money']) ? $model['money'] : 0;
    }

    /**
     * 计算今年是否是闰年
     * @return int
     */
    public static function yearDay()
    {
        $year = date('Y', time());
        $day = 365;
        if(($year%4 == 0 && $year%100 != 0) || $year%400 == 0){
            $day = 366;
        }

        return $day;
    }

}
