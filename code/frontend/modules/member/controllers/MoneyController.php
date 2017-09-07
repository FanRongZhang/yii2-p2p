<?php

namespace frontend\modules\member\controllers;

use common\models\QfbBankLimit;
use common\models\QfbDayOff;
use common\service\BankService;
use common\service\MemberMoneyService;
use common\service\MemberService;
use common\models\QfbMember;
use common\models\QfbMemberMoney;
use common\models\QfbMoneyLog;
use common\models\QfbOrder;
use common\models\QfbBank;
use common\service\SystemService;
use common\service\LogService;
use common\toolbox\Tool;
use common\models\QfbChannel;

class MoneyController extends BaseController
{
    public $channel = 8; //通道id

    public function actionIndex()
    {
        //判断用户是否开通账户
        if($this->memberData['is_dredge'] != 1){
            return $this->redirect('/member/auth');
        }
        $type = \Yii::$app->request->get('type', 0);

        $money = MemberMoneyService::getByMemberMoney($this->mid);

        //获取银行卡信息
        $bankService = new BankService();
        $bank = $bankService->getCard($this->mid, $this->channel);

        if($type == 1){
            $system = SystemService::getLastRate(); //获取提现的额度
        }else{
            //获取当前用户绑卡所属银行信息
            $bankinfo=QfbBank::findOne(['member_id'=>$this->mid, 'is_del'=>0]);
            //查询默认通道信息
            $channel = QfbChannel::find()->where(['is_default'=>1])->one();
            //查询用户当前绑卡银行限额
            $bank_limit=QfbBankLimit::find()->where(['bank_abbr'=>$bankinfo['bank_abbr'], 'pt_type'=>$channel['id']])->one();
            $system['per_money'] = $bank_limit['one_trade'];
            $system['day_money'] = $bank_limit['day_trade'];
        }

        return $this->render('index',['type'=>$type, 'bank'=>$bank, 'money'=>$money, 'system'=>$system]);
    }

    /**
     * 会员中心--充值
     * @author panheng
     */
    public function actionRecharge()
    {
        $money = \Yii::$app->request->post('money', 0);

        //判断是否开户
        if($this->memberData['is_dredge'] != 1){
            return $this->redirect('/member/auth');
        }

        if($money < 1 || !preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $money)){
            $this->error("金额数据不正确");
        }

        //获取当前用户绑卡所属银行信息
        $bankinfo=QfbBank::findOne(['member_id'=>$this->mid, 'is_del'=>0]);
        //查询默认通道信息
        $channel = QfbChannel::find()->where(['is_default'=>1])->one();
        //查询用户当前绑卡银行限额
        $bank_limit=QfbBankLimit::find()->where(['bank_abbr'=>$bankinfo['bank_abbr'], 'pt_type'=>$channel['id']])->one();

        if (empty($bank_limit)) {
            $this->error('未能识别的银行卡');
        }

        if ($money > $bank_limit['one_trade']) {
            $this->error('单笔交易限额为'.$bank_limit['one_trade'].'元');
        }

        //查询用户当日交易总金额
        $begintime=strtotime(date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d'),date('Y'))));
        $endtime=strtotime(date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1));
        $countmoney=QfbOrder::find()->where(['sorts'=>1,'type'=>1,'member_id'=>$this->mid])->andFilterWhere(['in','is_check',[0,1,3,5]])->andFilterWhere(['between','create_time', $begintime, $endtime])->sum('price');
        //当日剩余交易额度
        $residue=$bank_limit['day_trade']-$countmoney;
        if ($money > $residue) {
            $this->error('当日剩余交易额度为'.$residue.'元');
        }

        if ($bank_limit['trade_num'] != 0) {
            //查询用户当月交易总次数
            $BeginDate=date('Y-m-01', strtotime(date("Y-m-d")));
            $endThismonth=strtotime(date('Y-m-d', strtotime("$BeginDate +1 month -1 day")).' 23:59:59');
            $BeginDate=strtotime($BeginDate);

            $count=QfbOrder::find()->where(['sorts'=>1,'type'=>1,'member_id'=>$this->mid])->andFilterWhere(['in','is_check',[0,1,3,5]])->andFilterWhere(['between','create_time', $BeginDate, $endThismonth])->count();
            if ($count >= $bank_limit['trade_num']) {
                $this->error('已超过当月限制交易次数');
            }
        }

        //单月交易限额
        if ($bank_limit['month_trade'] != 0) {
            $BeginDate=date('Y-m-01', strtotime(date("Y-m-d")));
            $endThismonth=strtotime(date('Y-m-d', strtotime("$BeginDate +1 month -1 day")).' 23:59:59');
            $BeginDate=strtotime($BeginDate);

            $countmoney=QfbOrder::find()->where(['sorts'=>1,'type'=>1,'member_id'=>$this->mid])->andFilterWhere(['in','is_check',[0,1,3,5]])->andFilterWhere(['between','create_time', $BeginDate, $endThismonth])->sum('price');
            $residue = $bank_limit['month_trade'] - $countmoney;
            if ($money > $residue) {
                $this->error('当月剩余交易额度为'.$residue.'元');
            }
        }

        //支付通道选择 -- 易宝通道
        if ($channel['id'] == 8) {
            $pt_name = 'YEEPAY';
        }
        // 块钱通道
        else if($channel['id'] == 5) {
            $pt_name = 'BILL99';
        }else{
            $this->error('支付通道有误');
        }

        $hkyh = \Yii::$app->Hkyh;

        // 充值
        $serviceName = 'RECHARGE';

        //平台用户编号  -必填
        $reqData['platformUserNo'] = $this->mid;
        // 请求流水号  --流水号 --不允许重复
        $reqData['requestNo'] = $this->getBindSn('CZ');
        // 充值金额 --必填
        $reqData['amount'] = $money;
        // 平台佣金 --非必填
        // $reqData['commission'] = '0';
        // 支付公司编码 - 见支付公司  --必填
        $reqData['expectPayCompany'] = $pt_name;
        // 支付方式 - 网银 WEB  快捷支付 SWIFT --必填
        $reqData['rechargeWay'] = 'SWIFT';
        // 非网银必填，银行编码  ，网银：填，转去银行页面，不填跳转支付公司收银台页面 --非必填
        $reqData['bankcode'] = $bankinfo->bank_abbr;
        // 页面回调url  --必填
        $reqData['redirectUrl'] = $hkyh->RETURN_PC_URL;
        // 超过此时间即页面过期 --必填
        $reqData['expired'] = date('YmdHis', time()+5*60);
        // 开启异步通知结果
        // $reqData['callbackMode'] = 'DIRECT_CALLBACK';

        /**********创建订单**********/
        $order=new QfbOrder();
        $order->sn=$reqData['requestNo'];
        $order->member_id=$this->mid;
        $order->price=$money;
        $order->is_check=3;
        $order->create_time=time();
        $order->sorts=1;
        $order->bank_id=$bankinfo->id;
        $order->money=$money;
        $order->bank_type=$channel['id'];
        $order->remark='充值';
        if (!$order->save()) {
            $this->error('充值失败');
        }

        // 记录日志
        $fileName = "CZ_".$serviceName."_GATEWAY.log";
        $content = "充值操作   执行时间：".date("Y-m-d H:i:s", time())."   订单号：".$reqData['requestNo']."   请求数据：".json_encode($reqData)."\r\n";
        LogService::hkyh_write_log($fileName, $content);

        $hkyh->createPostParam($serviceName,$reqData);
        //这里根据业务逻辑自行处理，如果是直连则根据$result数据做处理，如果是网关则不返回数据，
        return $this->redirect(['/hkyh-notify/hkyh-return','data'=>['msg'=>'银行系统错误'],'status'=>'error']);
    }

    /**
     * 会员中心--提现
     * @author
     */
    public function actionWithdraw()
    {
        $money = \Yii::$app->request->post('money', 0);

        $systemService = new SystemService();
        $system = $systemService->getLastRate();
        $time = time();
        $status = true;
        $hour = date('H', $time);

        if($system['open_start_time'] > $hour || $hour >= $system['open_end_time']){
            $status = false;
        }

        $days = QfbDayOff::find()->asArray()->all();

        foreach($days as $key=>$value){
            $offTime = date('Y-m-d', $value['time']);
            $nowDays = date('Y-m-d', $time);
            if($offTime == $nowDays){
                $status = false;
            }
        }

        if($status == false){
            $this->error('仅工作日'.$system['open_start_time'].':00-'.$system['open_end_time'].':00支持提现');
        }

        if($this->memberData['is_dredge'] != 1){
            return $this->redirect('/member/auth');
        }

        if(!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $money) || $money <= 0){
            $this->error('金额数据不正确');
        }

        $memberMoney = QfbMemberMoney::find()->where(['member_id'=>$this->mid])->asArray()->one();
        if($money > $memberMoney['money']){
            $this->error('余额不足');
        }

        //获取当前用户绑卡所属银行信息
        $bankinfo=QfbBank::findOne(['member_id'=>$this->mid, 'is_del'=>0]);
        //查询用户当前绑卡银行限额
        $bank_limit=QfbBankLimit::find()->where(['bank_abbr'=>$bankinfo['bank_abbr'], 'pt_type'=>8])->one();

        if (empty($bank_limit)) {
            $this->error('未能识别的银行卡');
        }

        if($money > $system['per_money']){
            $this->error('单笔最大提现金额为'.$system['per_money'].'元');
        }

        $begintime=strtotime(date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d'),date('Y'))));
        $endtime=strtotime(date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1));

        $nowMoney = QfbOrder::find()->where(['type'=>2, 'sorts'=>1, 'member_id'=>$this->mid])->andFilterWhere(['in', 'is_check', [1,3,5]])->andFilterWhere(['between','create_time', $begintime, $endtime])->sum('price');

        if(($nowMoney+$money) > $system['day_money']){
            $this->error('单日最大提现金额为'.$system['day_money'].'元');
        }

        if ($bank_limit['trade_num'] != 0) {

            //查询用户当月交易总次数
            $BeginDate=date('Y-m-01', strtotime(date("Y-m-d")));
            $endThismonth=strtotime(date('Y-m-d', strtotime("$BeginDate +1 month -1 day")).' 23:59:59');
            $BeginDate=strtotime($BeginDate);

            $count=QfbOrder::find()->where(['sorts'=>1,'type'=>1,'member_id'=>$this->mid])->andFilterWhere(['in','is_check',[0,1,3,5]])->andFilterWhere(['between','create_time', $BeginDate, $endThismonth])->count();

            if ($count >= $bank_limit['trade_num']) {

                $this->error('已超过当月限制交易次数');
            }
        }
        //单月交易限额
        if ($bank_limit['month_trade'] != 0) {
            $BeginDate=date('Y-m-01', strtotime(date("Y-m-d")));
            $endThismonth=strtotime(date('Y-m-d', strtotime("$BeginDate +1 month -1 day")).' 23:59:59');
            $BeginDate=strtotime($BeginDate);

            $countmoney=QfbOrder::find()->where(['sorts'=>1,'type'=>1,'member_id'=>$this->mid])->andFilterWhere(['in','is_check',[0,1,3,5]])->andFilterWhere(['between','create_time', $BeginDate, $endThismonth])->sum('price');
            $residue = $bank_limit['month_trade'] - $countmoney;
            if ($money > $residue) {
                $this->error('当月剩余交易额度为'.$residue.'元');
            }
        }

        $bank = QfbBank::findOne(['member_id'=>$this->mid]);

        $hkyh = \Yii::$app->Hkyh;

        //计算提现手续费
        if($money >= $system['min_money']){
            if($system['fast_rate'] > 0){
                $reqData['commission'] = Tool::moneyPlatform($money*$system['fast_rate']/100);
            }
        }else{
            if($system['money_fee'] != 0)
                $reqData['commission'] = Tool::moneyPlatform($system['money_fee']);

            if($money <= $system['money_fee']){
                $this->error("提现最小金额需大于".$system['money_fee'].'元');
            }
        }

        // 提现
        $serviceName = 'WITHDRAW';

        //平台用户编号  -必填
        $reqData['platformUserNo'] = $this->mid;//$this->member_id;
        // 请求流水号  --流水号 --不允许重复
        $reqData['requestNo'] = $this->getBindSn('TX');//"LQ". $this->member_id . time() . rand(10, 99);
        $reqData['withdrawType'] = 'NORMAL'; //提现方式
        $reqData['withdrawForm'] = 'IMMEDIATE'; //IMMEDIATE直接提现，CONFIRMED待确认提现
        $reqData['amount'] = $money;
        $reqData['redirectUrl'] = $hkyh->RETURN_PC_URL;
        // 超过此时间即页面过期 --必填
        $reqData['expired'] = date('YmdHis', time()+3600);

        /**********创建订单**********/
        $order=new QfbOrder();
        $order->sn=$reqData['requestNo'];
        $order->member_id=$this->mid;
        $order->price=$reqData['amount'];
        $order->fee = isset($reqData['commission']) ? $reqData['commission'] : 0;
        $order->create_time = time();
        $order->type=2;
        $order->is_check=0;
        $order->sorts=1;
        $order->bank_id=$bank->id;
        $order->bank_type=3;
        $order->out_type=2;
        $order->remark='提现';
        $order->money = isset( $reqData['commission'])? $reqData['amount']-$reqData['commission']:$reqData['amount'];
        $order->bank_sn = $hkyh->platformNo;

        if (!$order->save()) {
            $this->error('提现失败');
        }

        // 记录日志
        $fileName = "TX_".$serviceName."_GATEWAY.log";
        $content = "提现操作   执行时间：".date("Y-m-d H:i:s", time())."   订单号：".$reqData['requestNo']."   请求数据：".json_encode($reqData)."\r\n";
        LogService::hkyh_write_log($fileName, $content);

        $hkyh->createPostParam($serviceName,$reqData);
        //这里根据业务逻辑自行处理，如果是直连则根据$result数据做处理，如果是网关则不返回数据，

        return $this->redirect(['/hkyh-notify/hkyh-return','data'=>['msg'=>'银行系统错误'],'status'=>'error']);

    }
}
