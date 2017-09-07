<?php
namespace api\versions\v200\controllers;

use common\models\QfbBank;
use common\models\QfbDayOff;
use common\models\QfbMemberMoney;
use common\models\QfbOrder;
use common\service\ApiService;
use common\service\BankService;
use common\service\CommonService;
use common\service\MemberService;
use common\service\MemberMoneyService;
use common\service\SystemService;
use common\service\ToolService;
use api\common\helpers\ReseponseCode as Code;
use api\common\BaseController;
use common\models\QfbArticle;
use common\service\OrderService;
use common\service\MoneyLogService;
use common\service\IndexService;
use common\service\VouchersService;
use common\service\MemberInfoService;
use common\service\ChannelService;
use common\service\ProductService;
use common\service\MemberVoucherService;
use common\enum\ChannelEnum;
use common\service\YiService;
use common\service\OrderFixService;
use common\models\QfbMoneyLog;
use yii;
use common\service\HkyhService;

class MoneyController extends BaseController{


    //资金托管充值接口
    public function actionUserPay(){

        //获取传递参数
        $params = $this->getParams();

        $memberService = new MemberService();
        $member_data = $memberService->findMemberIdByToken($params['access_token']);

        $member_id = $member_data->id;

        $is_dredge = $member_data->is_dredge;

        if(empty($member_id) )
            return $this->redirect(['/v200/notify/hkyh-return','status'=>'nologin','type'=>'hkyh-usePay','msg'=>'未登录']);

        if (empty($params['money']) || empty($params['bank_id']))
            return $this->redirect(['/v200/notify/hkyh-return','status'=>'error', 'order_id'=>'0','type'=>'hkyh-usePay', 'msg'=>'参数有误']);

        //已开户，充值
        if ($is_dredge==1) {

            //获取当前用户绑卡所属银行信息
            $bankinfo=QfbBank::findOne($params['bank_id']);

            $hkyh = \Yii::$app->Hkyh;

            // 充值
            $serviceName = 'RECHARGE';

            //平台用户编号  -必填
            $reqData['platformUserNo'] = $member_id;
            // 请求流水号  --流水号 --不允许重复
            $reqData['requestNo'] = "LQ". $member_id . time() . rand(10, 99);
            // 充值金额 --必填
            $reqData['amount'] = $params['money'];
            // 平台佣金 --非必填
            // $reqData['commission'] = '0';
            // 支付公司编码 - 见支付公司  --必填
            $reqData['expectPayCompany'] = 'TFTPAY';
            // 支付方式 - 网银 WEB  快捷支付 SWIFT --必填
            $reqData['rechargeWay'] = 'SWIFT';
            // 非网银必填，银行编码  ，网银：填，转去银行页面，不填跳转支付公司收银台页面 --非必填
            $reqData['bankcode'] = $bankinfo['bank_abbr'];

            // 网银类型，如对银行编码填写了，这里必须填写，反之不填  --非必填
            // $reqData['payType'] = '';
            // 授权交易类型  如要实现充值+投标单次授权，填固定TENDER --非必填
            // $reqData['authtradeType'] = '99999999999998';
            // 授权投标金额，如果授权类型已填，这里必填 --非必填
            // $reqData['authtenderAmount'] = '99999999999998';
            // 标的号，授权类型已经填了，这里是必填  --非必填
            // $reqData['projectNo'] = '99999999999998';
            // 页面回调url  --必填
            $reqData['redirectUrl'] = $hkyh->RETURN_URL;
            // 超过此时间即页面过期 --必填
            $reqData['expired'] = date('YmdHis', time()+5*60);
            // 非必填---快捷充值回调模式，如传入 DIRECT_CALLBACK，则订单支付不论成功、失败、处理中均直接同步、异步通知商户；未传入订单仅在支付成功时通知商户；
            $reqData['callbackMode'] = 'DIRECT_CALLBACK';


            //创建用户充值订单记录
            $order=new QfbOrder();
            $order->sn=$reqData['requestNo'];
            $order->member_id=$member_id;
            $order->price=$params['money'];
            $order->is_check=3;
            $order->create_time=time();
            $order->sorts=1;
            $order->bank_id=$params['bank_id'];
            $order->money=$params['money'];
            $order->bank_type=3;
            $order->remark='充值';
            if (!$order->save()) {
                return  ['code' =>  Code::COMMON_ERROR_CODE, 'msg' => '创建订单失败', 'data'=>''];
            }

            $result = $hkyh->createPostParam($serviceName,$reqData);
            //这里根据业务逻辑自行处理，如果是直连则根据$result数据做处理，如果是网关则不返回数据，

        } else {

            // 调用接口查询，确定是否开户
            $getHkyhUser = MemberService::getHkyhUser($member_id);

            // 未开户
            if($getHkyhUser['code'] != code::HTTP_OK){

                $hkyh = \Yii::$app->Hkyh;

                // 个人绑卡注册
                $serviceName = 'PERSONAL_REGISTER_EXPAND';

                // 流水号
                $sn = $this->getBindSn('RT');

                $reqData['platformUserNo'] = $member_id.'a'.time(); /*测试*/
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

            //更新用户数据成功,继续充值
            if($result['code'] == code::HTTP_OK){
                //获取当前用户绑卡所属银行信息
                $bankinfo=QfbBank::findOne($params['bank_id']);

                $hkyh = \Yii::$app->Hkyh;

                // 充值
                $serviceName = 'RECHARGE';

                //平台用户编号  -必填
                $reqData['platformUserNo'] =$member_id;
                // 请求流水号  --流水号 --不允许重复
                $reqData['requestNo'] = "LQ". $member_id . time() . rand(10, 99);
                // 充值金额 --必填
                $reqData['amount'] = $params['money'];
                // 平台佣金 --非必填
                // $reqData['commission'] = '0';
                // 支付公司编码 - 见支付公司  --必填
                $reqData['expectPayCompany'] = 'TFTPAY';
                // 支付方式 - 网银 WEB  快捷支付 SWIFT --必填
                $reqData['rechargeWay'] = 'SWIFT';
                // 非网银必填，银行编码  ，网银：填，转去银行页面，不填跳转支付公司收银台页面 --非必填
                $reqData['bankcode'] = $bankinfo['bank_abbr'];
                // 网银类型，如对银行编码填写了，这里必须填写，反之不填  --非必填
                // $reqData['payType'] = '';
                // 授权交易类型  如要实现充值+投标单次授权，填固定TENDER --非必填
                // $reqData['authtradeType'] = '99999999999998';
                // 授权投标金额，如果授权类型已填，这里必填 --非必填
                // $reqData['authtenderAmount'] = '99999999999998';
                // 标的号，授权类型已经填了，这里是必填  --非必填
                // $reqData['projectNo'] = '99999999999998';
                // 页面回调url  --必填
                $reqData['redirectUrl'] = $hkyh->RETURN_URL;
                // 超过此时间即页面过期 --必填
                $reqData['expired'] = date('YmdHis', time()+5*60);
                // 非必填---快捷充值回调模式，如传入 DIRECT_CALLBACK，则订单支付不论成功、失败、处理中均直接同步、异步通知商户；未传入订单仅在支付成功时通知商户；
                $reqData['callbackMode'] = 'DIRECT_CALLBACK';

                $result = $hkyh->createPostParam($serviceName,$reqData);
                //这里根据业务逻辑自行处理，如果是直连则根据$result数据做处理，如果是网关则不返回数据，

            }else{

                return $this->redirect(['/v200/notify/hkyh-return','status'=>'error', 'order_id'=>'0','type'=>'hkyh-usePay', 'msg'=>$result['msg']]);
            }
        }

    }


    /**
     * 58定期活期投资规则获取v200
     *
     * int type //1表示活期，2表示定期
     * int product_id;//产品id
     * @author jin
     *
     */
    public function actionBuyRule()
    {
        $member_id=$this->member_id;

        $params = $this->getParams();
        $product_id = isset($params['product_id']) ? $params['product_id'] : null;

        $type = isset($params['type']) ? $params['type'] : null;

        if ( !is_numeric($product_id) || !is_numeric($type) )
        {
            return ApiService::error(Code::COMMON_ERROR_CODE,'参数错误');
        }

        //根据member_id获取金额
        $member_money = MemberMoneyService::getByMemberMoney($member_id);
        $data['loose'] = $member_money['money'];

        //获取产品
        $product = ProductService::getProduct($product_id);
        $data['min_money'] = $product['min_money'];//起投金额
        $data['max_money'] = $product['max_money'];//投资上线

        if ($type == 2){
            if ( !$product['can_money_ticket']){//是否可用代金券
                $data['coupon'] = 0;
                $data['money_coupon'] = '0';
            }else{

                $data['coupon'] = intval(VouchersService::getVouchersNumsByMemberId($member_id));
                if ($data['coupon'] == 0){
                    $data['money_coupon'] = '0';
                }else{
                    $data['money_coupon'] = VouchersService::getVouchersMoneysByMemberId($member_id);
                }

            }
        }elseif($type == 1){
            $data['coupon'] = 0;
            $data['money_coupon'] = '0';
        }

        return ApiService::success(Code::HTTP_OK,Code::$statusTexts[Code::HTTP_OK],$data);
    }

    /**
     * 59定期理财投资结果页面v200
     * int order_id;//订单id，
     * int type //1表示活期，2表示定期
     *
     * @author jin
     *
     */
    public function actionBuyDetail()
    {
        $member_id=$this->member_id;

        $params = $this->getParams();
        $order_id = isset($params['order_id']) ? $params['order_id'] : null;
        $product_id = isset($params['product_id']) ? $params['product_id'] : null;
        $type = isset($params['type']) ? $params['type'] : null;

        if ( !is_numeric($order_id) || !is_numeric($type) || !is_numeric($product_id))
        {
            return ApiService::error(Code::COMMON_ERROR_CODE,'参数错误');
        }

        $member_money = MemberMoneyService::getByMemberMoney($member_id);
        if ($type == 2){
            $order_fix = new OrderFixService();
            $data['total'] = $order_fix->findMoney($product_id);
            $order = OrderService::getFixOrder($member_id,$order_id);
            $data['title'] = $order['product']['product_name'];
            $data['money'] = $order['money'];
            $data['money_off'] = $order['order_fix_extend']['money_ticket_num'];
            $data['money_real'] = $order['pay_money'];

            date_default_timezone_set('PRC');
            $data['tips'] = '注：截止到'.date('Y-m-d H:i:s',$order['product']['end_time']).'，若该项目募款未达成，则您所投资金将返还到您的零钱账户中。';
        }elseif($type == 1){
            $data['total'] = sprintf( '%.2f',( $member_money['live_money'] + $member_money['pre_live_money'] ) );
            $order = OrderService::getOrder($member_id,$order_id);
            $data['title'] = ProductService::getLiveProduct()['product_name'];
            $data['money'] = $order['price'];
            $data['money_off'] = '0';
            $data['money_real'] = $order['price'];

            $data['tips'] = '';
        }

        return ApiService::success(Code::HTTP_OK,Code::$statusTexts[Code::HTTP_OK],$data);
    }

    /**
     * 我的人脉
     * int type;//0表示全部，1表示一度人脉，2表示2度人脉
     *
     * @author jin
     *
     */
    public function actionContacts()
    {
        $member_id=$this->member_id;

        $params = $this->getParams();
        $type = isset($params['type']) ? $params['type'] : null;
        $limit = isset($params['limit']) ? $params['limit'] : 10;
        $page = isset($params['page']) ? $params['page'] : 1;

        if ( !is_numeric($type) || !is_numeric($limit) || !is_numeric($page) )
        {
            return ApiService::error(Code::COMMON_ERROR_CODE,'参数错误');
        }

        $members = MemberService::getCountByRMemberId($member_id,$type);

        $from_members   = array_column($members,'id');//二维数组转一维数组

        $data['contacts'] = count($members);

        date_default_timezone_set('PRC');
        if ($data['contacts'] > 0){
            $data['money'] = strval(MoneyLogService::getAllProfit($member_id,$from_members));

            $members_info = MemberInfoService::getInfoByMemberId($from_members,$page,$limit);//分页用户信息

            foreach ($members_info as $val){
                $data['list'][] = [
                    'realname' => '*'.mb_substr($val['realname'], 1,4,'utf-8') ,
                    'time' => date('Y-m-d',$val['create_time']),
                    'money' => strval(MoneyLogService::getAllProfit($member_id,[ $val['member_id'] ])),
                    'tips' => self::setTips($member_id, $type,$val['member_id']),
                ];
            }

        }else{
            $data['money'] = '0';
            $data['list'] = array();
        }

        return ApiService::success(Code::HTTP_OK,Code::$statusTexts[Code::HTTP_OK],$data);
    }

    public function setTips($member_id,$type,$find_member_id){
        if ($type == 1){
            return '一度人脉';
        }elseif ($type == 2){
            return '二度人脉';
        }elseif($type == 0){
            $members = MemberService::getCountByRMemberId($member_id,1);

            foreach ($members as $val){
                $from_members[] = $val["id"];
            }

            if (in_array($find_member_id, $from_members)){
                return '一度人脉';
            }else{
                return '二度人脉';
            }
        }
    }

    /**
     *我的财富
     *
     */
    public function actionMymoney(){

        $data = array();

        $member_id = $this->member_id;

        // 查询绑卡
        $card_no = 0;
        $bank_count = QfbBank::find()->where(['member_id'=>$member_id, 'is_del'=>'0'])->count();
        if($bank_count > 0) $card_no = 1;
        $data['card_no'] = $card_no;

        $member_money = MemberMoneyService::getByMemberMoney($member_id);
        $data['all_money'] =strval( $member_money['money'] + $member_money['live_money'] + $member_money['fix_money'] +
            $member_money['pre_live_money'] + $member_money['lock_money'] );

        $data['yesterday_profit'] = MoneyLogService::getProfitByMemberId($member_id,'yesterday');
        //金秋计划收益
        $plan_profit = 0;
        $plan = QfbMoneyLog::find()->select('money')->where(['type'=>1,'money_type'=>1,'action'=>14])->andWhere(['=','member_id',$member_id])->asArray()->all();
        if ($plan) {
            foreach ($plan as $value) {
                $plan_profit += $value['money'];
            }
        }
        $data['all_profit'] = (string) (MoneyLogService::getProfitByMemberId($member_id) + $plan_profit);

        $data['money'] = $member_money['money'];
        $data['invest'] = strval( $member_money['live_money'] + $member_money['fix_money'] + $member_money['pre_live_money']);
        $data['lock_money'] = $member_money['lock_money'];

        $guarantee = \yii::$app->params['guarantee'];
        $data['tips'] = $guarantee[0]['tips'];          //担保方
        $data['tips_url'] = $guarantee[0]['tips_url'];  //担保方logo，属于图片url
        $data['my_data'] = IndexService::getAll();
        foreach ($data['my_data'] as &$val)
        {
            $val['type'] = intval($val['type']);
            $val['click'] = $val['click']==1?true:false;
        }
        return [ 'code' => Code::HTTP_OK, 'msg' => '成功', 'data' => $data ];
    }

    /**
     * 60定期活期输入金额返回优惠券v200
     * @author jin
     *
     */
    public function actionBuyBack(){
        $member_id = $this->member_id;

        $params = $this->getParams();
        $money = isset($params['money']) ? $params['money'] : null;
        $product_id = isset($params['product_id']) ? $params['product_id'] : null;
        $type = isset($params['type']) ? $params['type'] : null;

        if ( !is_numeric($money) || !is_numeric($product_id) || !is_numeric($type) || $type != 2 )
        {
            return ApiService::error(Code::COMMON_ERROR_CODE,'参数错误');
        }

        $product = ProductService::getProduct($product_id);
        if( !$product['can_money_ticket']){
            return ApiService::error(Code::HTTP_OK,'该产品不能使用代金券');
        }

        if ( MemberVoucherService::productIsUse($member_id,$product_id) ){
            $data = (object)null;
        }else{
            $result = VouchersService::getVouchersByMemberId($member_id,$type,$money);

            if (empty($result)){
                $data = (object)$result;
            }
            else{
                foreach ($result as $val){
                    $data['member_voucher_id'] = $result['id'];
                    $data['money'] = intval($result['vouchers']['money']);
                }
            }
        }

        return ApiService::success(Code::HTTP_OK,Code::$statusTexts[Code::HTTP_OK],$data);
    }

    /**
     * 从余额提现
     * @author steve
     */
    public function actionWithdrawals()
    {
        if (isset($this->params['bank_id']) && isset($this->params['money']) && isset($this->params['zf_pwd']) && isset($this->params['type']) && isset($this->params['payment'])) {
            $money = floatval($this->params['money']); //验证金额

            //控制提现单笔5W
            $setting    = SystemService::getLastRate();
            if ($money > $setting['per_money']) {
                return [
                    'code' => Code::COMMON_ERROR_CODE,
                    'msg' => '单笔提现金额最高'.$setting['per_money'].'万'
                ];
            }

            if ($money <= 0) {
                return [
                    'code' => Code::COMMON_ERROR_CODE,
                    'msg' => '金额错误'
                ];
            }

            if (MemberService::checkZfPwd($this->member_id, $this->params['zf_pwd'])) { //判断支付密码
                $memberModel = MemberService::findModelById($this->member_id); //获取用户信息
                $todayMoney = OrderService::getTodayTransferByMemberId($this->member_id);//获取今天已预提现金额

                //控制零钱单日10W
                if (($todayMoney+$money) > $setting['day_money']) {
                    return [
                        'code' => Code::COMMON_ERROR_CODE,
                        'msg' => '单日提现金额最高'.$setting['day_money'].'万'
                    ];
                }

                //处理提现申请以及扣除用户金额
                $OrderService = new OrderService();
                $res = $OrderService->withdrawals($this->member_id, $this->params['bank_id'], $this->params['money'], $this->params['type'], $this->params['payment']);

                //订单生成成功发送短信
                $orderlist  = $OrderService->list;
                $order  = ['id'=>$orderlist['id'],'sn'=>$orderlist['sn'],'money'=>$orderlist['order_price'],'create_time'=>date('Y-m-d H:i:s',$orderlist['create_time'])];

                if ($res['code'] == Code::HTTP_OK) {
                    $data = [
                        'price' => $orderlist['order_price'],
                        'fee' => $orderlist['order_fee'],
                        'no' => $orderlist['bank_no'],
                        'name' => $orderlist['bank_name'],
                        'out_type' => $this->params['type'] //提现方式
                    ];

                    //发送短信提醒
                    $index = 0;
                    do {
                        $sendMsg = CommonService::sendMobileMsg($memberModel['mobile'], $data);
                        $index++;
                    } while ($index < 3 && $sendMsg == false);

                    return ApiService::success(Code::HTTP_OK,'申请成功',$order);
                } else {
                    return $res;
                }

            } else {
                return [
                    'code' => Code::COMMON_ERROR_CODE,
                    'msg' => '支付密码错误'
                ];
            }
        } else {
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => '参数缺失'
            ];
        }
    }

    /**
     * 新的提现接口
     * @author panheng
     */
    public function actionWithdraw()
    {
        $params = $this->getParams();

        if(!isset($params['money']) || $params['money'] <= 0)
            return $this->redirect(['/v200/notify/hkyh-return','status'=>'error', 'order_id'=>'0', 'type'=>'hkyh-withdraw', 'msg'=>'余额不足']);

        // 可以获取
        // $member_id = $this->member_id;

        $memberService = new MemberService();
        $member_data = $memberService->findMemberIdByToken($params['access_token']);

        $member_id = $member_data->id;
        $is_dredge = $member_data->is_dredge;

        if(empty($member_data) || empty($params['access_token']))
            return $this->redirect(['/v200/notify/hkyh-return','status'=>'nologin','type'=>'hkyh-withdraw']);

        if(empty($is_dredge))
            return $this->redirect(['/v200/notify/hkyh-return','status'=>'error' , 'order_id'=>'0', 'type'=>'hkyh-withdraw', 'msg'=>'未开通银行账户']);

        $hkyh = Yii::$app->Hkyh;

        // 提现
        $serviceName = 'WITHDRAW';

        //平台用户编号  -必填
        $reqData['platformUserNo'] = $member_id;
        // 请求流水号  --流水号 --不允许重复
        $reqData['requestNo'] = "LQ". $this->member_id . time() . rand(10, 99);
        $reqData['withdrawType'] = 'NORMAL'; //提现方式
        $reqData['withdrawForm'] = 'IMMEDIATE'; //IMMEDIATE直接提现，CONFIRMED待确认提现
        $reqData['amount'] = $params['money'];
        $reqData['redirectUrl'] = $hkyh->RETURN_URL;
        // 超过此时间即页面过期 --必填
        $reqData['expired'] = date('YmdHis', time()+5*60);
        // 非必填---快捷充值回调模式，如传入 DIRECT_CALLBACK，则订单支付不论成功、失败、处理中均直接同步、异步通知商户；未传入订单仅在支付成功时通知商户；
        $reqData['callbackMode'] = 'DIRECT_CALLBACK';

        $hkyh->createPostParam($serviceName,$reqData);
        //这里根据业务逻辑自行处理，如果是直连则根据$result数据做处理，如果是网关则不返回数据，
    }

    /**
     * 常见问题
     * @author wang
     */
    public function actionQuestion() {
        $params = $this->getParams();
        $title = $params['title'];
        if (empty($title)) {
            return [
                'code' => Code::HTTP_NO_CONTENT,
                'msg' => '标题缺失'
            ];
        }
        $model = QfbArticle::find()
            ->select('content')
            ->where(['=','title',$title])
            ->one();
        return [
            'code' => Code::HTTP_OK,
            'msg' => Code::$statusTexts[Code::HTTP_OK],
            'data' => $model
        ];

    }

    /*
     *赎回规则
     */
    public function actionBackRule(){
        $params = \Yii::$app->params;

        //查询可赎回金额
        $memberMoney = MemberMoneyService::getByMemberMoney($this->member_id);

        $data   = [
            'tips'      => '1.活期产品赎回之后的投资金额，将存放于零钱账户；<br>2.赎回后的资金不再计收益；<br>3.只能按所投产品进行赎回；',
            'money'     => $memberMoney->live_money,
            'time'      => '预计当日到账',
            'assure'    => $params['guarantee'][0]['tips'],
            'assure_pic'=> $params['guarantee'][0]['tips_url']
        ];

        return [
            'code' =>  Code::HTTP_OK,
            'msg'  =>  '请求成功',
            'data' =>  $data
        ];
    }

    /**
     * 提现规则
     * @author steve
     */
    public function actionWithdrawRule(){
        //查询可提现金额
        $memberMoney = MemberMoneyService::getByMemberMoney($this->member_id);
        //提现手续费
        $setting   = SystemService::getLastRate();

        //得到休息日
        $result   = QfbDayOff::find()->select('time')->asArray()->all();
        $day_off  = array_column($result,'time');
        $day_time = strtotime(date('Y-m-d',time()));//今天零点时间戳
        $is_close = in_array($day_time,$day_off);//当前时间是否在休息日里

        //判断是否支持提现
        date_default_timezone_set( 'Asia/Shanghai');//时区设置
        $h = intval(date('H',time()));          //取当前的小时
        if ($is_close){
            $is_support = false;
        }elseif ($h<$setting['open_start_time'] || $h>=$setting['open_end_time']){
            $is_support = false;
        }else{
            $is_support = true;
        }

        $data  = [
            'money'    =>  $memberMoney->money,
            'tips'     =>  '1.'.$setting['close_content'].'；<br>2.工作日当日到账手续费率'.$setting['fast_rate'].'%，1-3个工作日到账手续费率'.$setting['slow_rate'].'%；<br>'.
                '3.每笔提现低于'.$setting['min_money'].'，固定收取'.$setting['money_fee'].'元手续费。大于等于'.$setting['min_money'].'元的按费率收取手续费，每日最高可提现'.$setting['day_money'].'元。',
            'min'      => $setting['money_fee'],
            'max'      => $setting['per_money'],
            'limit'    => $setting['min_money'],
            'limit_money'  => $setting['money_fee'],
            'support'  => $is_support,
            'list'     => [
                [
                    'rate' => $setting['fast_rate'],
                    'title'=> '工作日当日到账',
                    'type' => 1
                ],
                [
                    'rate' => $setting['slow_rate'],
                    'title'=> '1-3个工作日到账',
                    'type' => 2
                ]
            ]
        ];

        return [
            'code' =>  Code::HTTP_OK,
            'msg'  =>  '请求成功',
            'data' =>  $data
        ];
    }

    /**
     * 充值、提现规则
     * @author steve
     */
    public function actionRule(){

        //判断是否支持提现
        date_default_timezone_set( 'Asia/Shanghai');//时区设置
        // 初始化
        $is_support = true;

        $member_id = $this->member_id;

        $params = $this->getParams();

        // 1 充值 2 提现
        $rule_arr = [1,2];

        if( !in_array($params['type'], $rule_arr) && !isset($params['type']))
            return ApiService::error(Code::COMMON_ERROR_CODE,'参数错误');

        //查询可提现金额
        $memberMoney = MemberMoneyService::getByMemberMoney($member_id);
        //提现手续费
        $setting   = SystemService::getLastRate();

        //得到休息日
        $result   = QfbDayOff::find()->select('time')->asArray()->all();

        $day_off  = array_column($result,'time');
        $day_time = strtotime(date('Y-m-d',time()));//今天零点时间戳
        $is_close = in_array($day_time,$day_off);//当前时间是否在休息日里

        $h = intval(date('H',time()));          //取当前的小时

        // 提现规则
        if($params['type'] == 2){

            // 是休息日
            if ($is_close){
                $is_support = false;
            }elseif ($h<$setting['open_start_time'] || $h>=$setting['open_end_time']){
                $is_support = false;
            }else{
                $is_support = true;
            }

            $min = empty($setting['money_fee']) ? 0 : $setting['money_fee'];
            $max = empty($setting['per_money']) ? 0 : $setting['per_money'];
            $limit = empty($setting['min_money']) ? 0 : $setting['min_money'];
            $limit_money = empty($setting['money_fee']) ? 0 : $setting['money_fee'];

            $tips = '1.'.$setting['close_content'].'；<br>2.工作日当日到账手续费率'.$setting['fast_rate'].'%，1-3个工作日到账手续费率'.$setting['slow_rate'].'%；<br>'.
                '3.每笔提现低于'.$setting['min_money'].'，固定收取'.$setting['money_fee'].'元手续费。大于等于'.$setting['min_money'].'元的按费率收取手续费，每日最高可提现'.$setting['day_money'].'元。';
            $list = [
                [
                    'rate' => $setting['fast_rate'],
                    'title'=> '工作日当日到账',
                    'type' => 1
                ],
                [
                    'rate' => $setting['slow_rate'],
                    'title'=> '1-3个工作日到账',
                    'type' => 2
                ]
            ];

        }
        // 充值规则
        else{

            $min = 0;
            $max = 0;
            $limit = 0;
            $limit_money = 0;
            $tips = '1.单张卡最多充值5w元';
            $list = [];

        }

        $data  = [
            // 可用零钱
            'money'    =>  $memberMoney->money,
            // 提示语
            'tips'     =>  $tips,
            // 最小操作金额
            'min'      => $min,
            // 做大操作金额
            'max'      => $max,
            // 设置是否参考值
            'limit'    => $limit,
            // 小于参考值收固定值
            'limit_money'  => $limit_money,
            'support'  => $is_support,
            'list'     => $list
        ];

        return [
            'code' =>  Code::HTTP_OK,
            'msg'  =>  '请求成功',
            'data' =>  $data
        ];
    }

    /**
     * 支付通道选择列表
     * @author xiaomalover <xiaomalover@gmail.com>
     * 场景，充值，提现
     * @return Array 支付通道列表
     */
    public function actionPayment()
    {
        $list = ChannelService::getChannelList($this->member_id, $this->params['type']);
        return ['code' => Code::HTTP_OK,
            'msg' => '请求成功',
            'data' => $list,
        ];
    }

    /**
     * 充值订单生成接口
     * @author xiaomalover <xiaomalover@gmail.com>
     */
    public function actionRecharge()
    {
        if(isset($this->params['money']) && isset($this->params['pay_type']) && isset($this->params['zf_pwd'])){
            $bank_id = isset($this->params['bank_id']) ? $this->params['bank_id'] : '';
            //创建支付订单
            $ors = new OrderService();
            $res = $ors->recharge($this->member_id, $this->params['money'],
                $this->params['pay_type'], $bank_id, $this->params['zf_pwd']);
            return $res;
        }else{
            return [ 'code' => Code::COMMON_ERROR_CODE, 'msg' => '请求参数缺失'];
        }
    }

    /**
     * 赎回接口
     * @author lwj
     * @return array
     */
    public function actionBack(){
        if(!SystemService::isCanMoney()){
            return ['code'=>Code::COMMON_ERROR_CODE,'msg'=>"数钱中暂时不能提现"];
        }
        //当前登录用户的member_id
        $uid=$this->member_id;
        $params= $this->getParams();
        //获取传入的参数
        $money= isset($params['money']) ? $params['money'] : false;
        $zf_pwd = isset($params['zf_pwd']) ? $params['zf_pwd'] : false;
        $memberService = new MemberService();
        $member= $memberService->findModelById($uid);

        $memberMoney = MemberMoneyService::getByMemberMoney($uid);
        if($money>$memberMoney->live_money) return ['code'=>Code::COMMON_ERROR_CODE,'msg'=>"金额不足"];
        //zf_pwd是否正确
        if($member->zf_pwd != $zf_pwd) return ApiService::error(10001,'账户的支付密码错误');
        $tran = \Yii::$app->db->beginTransaction();
        try{
            //更改用户金钱数据
            $old_money=$memberMoney->live_money ;
            $memberMoney->live_money = bcsub($old_money, $money, 2);
            $memberMoney->money = bcadd($memberMoney->money,$money,2);
            $memberMoney->save();
            $orderServ = new OrderService();
            $params = [
                'sn'=>ToolService::SetSn('HQ'),
                'price'=>$money,
                'is_check'=>1,
                'remark'=>"活期赎回",
                'complete_time'=>time(),
                'type'=>2,
                'sorts'=>2,
                'bank_id'=>0,
                'fee'=>0,
                'money'=>$money,
                'bank_type'=>3,
                'out_type'=>1
            ];
            if($orderServ->create($params)) {
                $moneyLogModel = new MoneyLogService($uid);
                $data = [];
                $data = [
                    [
                        'member_id' => $this->member_id,
                        'type' => 2,
                        'money_type' => 2,
                        'money' => $money,
                        'remark' => "活期赎回",
                        'create_time' => time(),
                        'old_money' => $old_money,
                        'action' => 7,
                    ],
                    //增加零钱记录
                    [
                        'member_id' => $this->member_id,
                        'type' => 1,
                        'money_type' => 1,
                        'money' => $money,
                        'remark' => "活期赎回",
                        'create_time' => time(),
                        'old_money' => $memberMoney->money,
                        'action' => 12,
                    ],
                ];
                $moneyLogModel->createList($data);
                $tran->commit();
                return ['code' => Code::HTTP_OK,
                    'msg' => '请求成功',
                ];
            }else{
                return ['code' => Code::COMMON_ERROR_CODE,
                    'msg' => '请求失败',
                ];

            }
        }catch (\Exception $e) {
            $e->getMessage();
            $tran->rollback();
        }

    }

    /**
     * 易联支付下单
     * @author 原作者 luo, 修改 xiaomalover <xiaomalover@gmail.com>
     */
    public function actionYiorder(){
        $params = $this->getParams();
        if (isset($params['money']) && isset($params['zf_pwd'])
            && isset($params['id'])) {

            //创建充值订单
            $ors = new OrderService();
            $sn = "LQ". $this->member_id . time() . rand(10, 99);
            $res = $ors->recharge($this->member_id, $params['money']
                , ChannelEnum::YILIAN, $params['id'], $params['zf_pwd'], $sn);

            //如果创建充值订单失败则返回失败信息
            if ($res['code'] != Code::HTTP_OK) {
                return $res;
            }

            //设置订单数据；商户在实际使用情况会有部分数据为手机端提交的数据
            $bankExtend = $ors->info['bankExtend'];
            $trade_num = $res['data']['sn'];
            $mobile = $bankExtend->bank->mobile;
            $level_sige = 0;
            $user_id = "";
            $member_info = MemberInfoService::getMemberInfo($this->member_id);
            $realname = $member_info['realname'];
            $idcard = $member_info['card_no'];
            $bank = $bankExtend->bank->no;
            $bank_address = "";
            $name = "";
            $order_type = "2";

            $miscData = implode("|",compact("mobile", "level_sige",
                "user_id", "realname", "idcard", "bank", "bank_address",
                "trade_num", "name", "order_type"));
            //调取易联支付
            $yiResult = YiService::getInstance()->Yiorder($res['data']['money'],
                $miscData, $res['data']['sn']);
            //加入订单ID
            $yiResult['data']['id'] = $res['data']['id'];
            return $yiResult;
        } else {
            return ApiService::error(Code::COMMON_ERROR_CODE, "请求参数缺失");
        }
    }

    /**
     * 易联回调
     */
    public function actionYinotify(){
        $data = $this->getParams();
        $res  = YiService::getInstance()->Yinotify($data);
        return $res;
    }

    public function actionResult(){
        
        $type_arr = [
            '1'=>'register_info',
            '2'=>'user_pay_info',
            '3'=>'withdraw_info',
            '4'=>'userPre_transaction_info',
            '5'=>'reset_password_info',
        ];

        $params = $this->getParams();

        if(empty($params['type']) || empty($params['order_id']))
            return ['code'=>Code::COMMON_ERROR_CODE,'msg'=>"参数有误"];

        if(empty($type_arr[$params['type']]))
            return ['code'=>Code::COMMON_ERROR_CODE,'msg'=>"请求业务不存在"];

        $method = $type_arr[$params['type']];

        $hkyh_service = new HkyhService();

        if(!method_exists($hkyh_service, $method))
            return ['code'=>Code::COMMON_ERROR_CODE,'msg'=>"业务处理有误"];

        $result = $hkyh_service->$method($params);

        return $result;
    }

}
