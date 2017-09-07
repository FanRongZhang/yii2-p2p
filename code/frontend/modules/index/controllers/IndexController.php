<?php

namespace frontend\modules\index\controllers;

use Yii;
use common\models\QfbBanner;
use common\models\QfbNotice;
use common\service\ProductService;
use frontend\controllers\WebController;
use common\models\QfbPcImage;
use common\models\QfbProduct;
use common\models\QfbMember;
use common\models\QfbMemberMoney;
use common\models\QfbBank;
use yii\data\ActiveDataProvider; 
use frontend\models\search\OrderFixSearch;
use common\service\OrderFixService;
use common\helpers\AdCommon;
use common\models\QfbAgreement;
use yii\data\Pagination;
use common\models\QfbOrder;
use common\models\QfbAboutMe;
use common\models\QfbOrderFix;
use common\service\HkyhService;
use common\service\AssetService;
use common\models\QfbBorrowMoney;
use common\models\QfbBankLimit;
use common\models\QfbArticle;
use common\models\QfbBaseNavigation;
use common\service\LogService;
use common\models\QfbChannel;
use common\models\QfbWarranty;
use common\models\QfbOrderRepayment;
use mPDF;

/*
 * ---------------------------------------
 * PC端首页控制器 
 * @author phphome@qq.com 
 * ---------------------------------------
 */
class IndexController extends WebController
{
    public function actionTest1()
    {
        $name = Yii::$app->request->get('name');
        $hkyh = \Yii::$app->Hkyh;
        // 解绑卡
        $serviceName = 'UNBIND_BANKCARD';

        //平台用户编号  -必填
        $reqData['platformUserNo'] = $name;
        // 请求流水号  --流水号 --不允许重复
        $reqData['requestNo'] = 'JB'.time();
        $reqData['redirectUrl'] = 'http://www.qfbqt.com/index/index/test2';
        $data = $hkyh->createPostParam($serviceName,$reqData);

    }
    public function actionTest2()
    {
        $data = empty(\Yii::$app->request->get()) ? \Yii::$app->request->post() : \Yii::$app->request->get();
        echo '<pre/>';var_dump($data);exit;
    }
    /**
     * ---------------------------------------
     * 前端首页方法
     * @author lijunwei
     * ---------------------------------------
     */
    public function actionIndex()
    {
        //首页轮播图
        $banner = QfbPcImage::find()->where(['type'=>1,'status'=>1])->orderBy('sort asc')->asArray()->all();

        //合作方
        $partner = QfbPcImage::find()->where(['type'=>2,'status'=>1])->orderBy('sort asc')->asArray()->all();

        //平台公告
        $noticeModel = new QfbNotice();
        $notice = $noticeModel->find()->where(['>','show_end_time',time()])->orderBy('send_time desc')->asArray()->all();

        $proServ = new ProductService();
        //新手尊投
        $list = $proServ->getAllList($type = 0, $sort = 1, $page = 1, $limit = 1, $is_index = 1, $stock_money = 1, $category_id= 1);
        $xinshou = $proServ->getList($list);

        //定期抵押贷
        $list = $proServ->getAllList($type = 0, $sort = 1, $page = 1, $limit = 4, $is_index = 1, $stock_money = 1);
        $dingqi = $proServ->getList($list);

        return $this->render('index', [
            'xinshou' => $xinshou,
            'dingqi' => $dingqi,
            'notice' => $notice,
            'banner' => $banner,
            'partner' => $partner
        ]);
    }

    //公告详情
    public function actionNewsdetail()
    {
        $id=Yii::$app->request->get('id');
        $newsdata=QfbNotice::findOne($id);
        $newslist=QfbNotice::find()->where(['>','show_end_time',time()])->andWhere(['<>','id',$id])->orderBy('send_time desc')->limit(5)->asArray()->all();
        return $this->render('newsdetail',[
                'newsdata' => $newsdata,
                'newslist' => $newslist
            ]);
    }

    //公告列表
    public function actionNewslist()
    {
        $notice= new QfbNotice;
        //$newsdata=QfbNotice::find()->where(['>','show_end_time',time()])->orderBy('send_time desc')->limit(5)->asArray()->all();
        $newsdata = $this->query($notice,'',['>','show_end_time',time()],'send_time desc',10,true);
        //echo '<pre/>';var_dump($newsdata['data']);exit;
        return $this->render('newslist',[
                'newsdata' => $newsdata['data'],
                'page' => $newsdata['page'],
                'count' => $newsdata['count'],
                'pageSize' => $newsdata['pageSize'],
            ]);
    }


    /**
     * ---------------------------------------
     * 抵押贷列表
     * @author lijunwei
     * ---------------------------------------
     */
    public function actionMortgagelist()
    {
        $proServ = new QfbProduct();
        $sort = (int)Yii::$app->request->get('sort', 1);
        if ($sort == 1) {
            $orderby = 'qfb_product.create_time desc';
        } elseif ($sort == 2) {
            $orderby = 'qfb_product.year_rate desc';
        } elseif ($sort == 3) {
            $orderby = 'qfb_product.year_rate asc';
        } elseif ($sort == 4) {
            $orderby = 'qfb_product.invest_day desc';
        } elseif ($sort == 5) {
            $orderby = 'qfb_product.invest_day asc';
        }
        $type=0;
        $where='qfb_product.status in (1,2,5,6,7,8)';
        if ($type == 0) {
            $where .= ' and qfb_product.product_type in (1,2)';
        } else {
            $where .= ' and qfb_product.product_type ='.$type;
        }
        $where .= 'and qfb_product.category_id != 1';

        //抵押贷列表
        $list = $this->query($proServ,'product_detail',$where,$orderby,5,true);

        return $this->render('list', [
            'list' => $list['data'],
            'page' => $list['page'],
            'count' => $list['count'],
            'pageSize' => $list['pageSize'],
            'sort' => $sort
        ]);
    }


    /**
     * ---------------------------------------
     * 抵押贷详情
     * @author lijunwei
     * ---------------------------------------
     */
    public function actionDetail()
    {
        $id = intval(Yii::$app->request->get('id'));
        if($id <= 0) {
            $this->error('参数错误');
        }

        //查询产品详情
        $model=QfbProduct::find()->joinWith('product_agreement')->joinWith('product_detail')
        ->select("qfb_product.id,qfb_product.category_id,qfb_product.end_time,qfb_product.product_name,qfb_product.min_money,qfb_product.max_money,qfb_product.has_money,qfb_product.stock_money,qfb_product.step_money,qfb_product.profit_type,qfb_product.profit_day,qfb_product.start_time,
            qfb_product.year_rate,qfb_product.status,qfb_product.end_time,qfb_product.invest_day,qfb_product.is_newer,qfb_product.can_money_ticket,
            qfb_product_detail.detail,
            qfb_agreement.title,qfb_agreement.id agreement_id,qfb_agreement.pic_url")
        ->where(['=','qfb_product.id',$id])
        ->asArray()
        ->one();

        if($model == null) {
            $this->error('产品不存在');
        }

        //组装需要信息
        $model['residue'] = $model['stock_money'] - $model['has_money'];
        $model['percent'] = intval($model['has_money'] / $model['stock_money'] *100);
        $model['endtime'] =$model['end_time'] - time();
        $model['year_rate'] = rtrim(rtrim($model['year_rate'], '0'),'.');
        $member_data='';
        $member_money='';
        $member_bank='';
        $residue='';
        $bank_limit['one_trade']='';
        $bank_limit['day_trade']='';
        $ceiling=  '';

        //查询当前用户开户信息，资金信息
        $member_id=$this->mid;
        if (!empty($member_id)) {
            $member_data=QfbMember::findOne($member_id);
            $member_money=QfbMemberMoney::findOne($member_id);
            $member_money=$member_money['money'];

            //查询用户绑卡信息
            $member_bank=QfbBank::find()->where(['member_id'=>$member_id])->one();
            //查询默认通道信息
            $channel = QfbChannel::find()->where(['is_default'=>1])->one();
            //查询用户当前绑卡银行限额
            $bank_limit=QfbBankLimit::find()->where(['bank_abbr'=>$member_bank['bank_abbr'],'pt_type'=>$channel['id']])->one();
            //查询用户当日交易总金额
            $begintime=strtotime(date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d'),date('Y'))));
            $endtime=strtotime(date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1));
            $countmoney=QfbOrder::find()->where(['sorts'=>1,'type'=>1,'member_id'=>$member_id])->andFilterWhere(['in','is_check',[0,1,3,5]])->andFilterWhere(['between','create_time', $begintime, $endtime])->sum('price');
            //当日剩余交易额度
            $residue=$bank_limit['day_trade']-$countmoney;
            //查询当前用户剩余投资上限
            $count=QfbOrderFix::find()->where(['member_id'=>$member_id,'product_id'=>$model['id']])->andFilterWhere(['in','status',[0,1,2,3]])->sum('pay_money');
            $ceiling = $model['max_money'] - $count;
            if ($ceiling < 0) {
                $ceiling = 0;
            }
        }

        //查询投资记录
        $order_model = new OrderFixSearch();
        $dataProvider = $order_model->search(\Yii::$app->request->queryParams,$id);

        return $this->render('detail', [
            'model' => $model,
            'member_data' => $member_data,
            'member_money' => $member_money,
            'member_bank' => $member_bank,
            'order_model' => $order_model,
            'dataProvider' => $dataProvider,
            'residue' => $residue,
            'one_trade' => $bank_limit['one_trade'],
            'day_trade' => $bank_limit['day_trade'],
            'ceiling' => $ceiling
        ]);
    }


    /**
     * ---------------------------------------
     * 投资明细
     * @author lijunwei
     * ---------------------------------------
     */
    public function actionBuylist()
    {
        $product_id = intval(Yii::$app->request->get('product_id'));
        $page = intval(Yii::$app->request->get('page', 1));
        $service = new ProductService();
        $data = $service->buyProductList($product_id, $page, 10);
        if($service->getMessages() != null) {
            $this->error($service->findOneMessage());
        }

        return $this->render('buylist', [
            'data' => $data,
        ]);
    }

    //充值
    public function actionRecharge()
    {
        //接受参数
        $member_id=$this->mid ? $this->mid : 0;
        $money=Yii::$app->request->get('money');
        $bank_id=Yii::$app->request->get('bank_id');
        if (empty($member_id) || empty($money) || empty($bank_id)) {
            $this->error('参数错误');
        }
        if ($money < 1) {
            $this->error('单笔充值最小为1元');
        }
        //充值
        //获取当前用户绑卡所属银行信息
        $bankinfo=QfbBank::findOne($bank_id);

        //查询默认通道信息
        $channel = QfbChannel::find()->where(['is_default'=>1])->one();

        //查询用户当前绑卡银行限额
        $bank_limit=QfbBankLimit::find()->where(['bank_abbr'=>$bankinfo['bank_abbr'],'pt_type'=>$channel['id']])->one();

        if (empty($bank_limit)) {
            $this->error('未能识别的银行卡');
        }

        if ($money > $bank_limit['one_trade']) {
            $this->error('单笔交易限额为'.$bank_limit['one_trade'].'元');
        }
        
        //查询用户当日交易总金额
        $begintime=strtotime(date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d'),date('Y'))));
        $endtime=strtotime(date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1));
        $countmoney=QfbOrder::find()->where(['sorts'=>1,'type'=>1,'member_id'=>$member_id])->andFilterWhere(['in','is_check',[0,1,3,5]])->andFilterWhere(['between','create_time', $begintime, $endtime])->sum('price');
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

            $count=QfbOrder::find()->where(['sorts'=>1,'type'=>1,'member_id'=>$member_id])->andFilterWhere(['in','is_check',[0,1,3,5]])->andFilterWhere(['between','create_time', $BeginDate, $endThismonth])->count();

            if ($count >= $bank_limit['trade_num']) {

                $this->error('已超过当月限制交易次数');
            }
        }
        //单月交易限额
        if ($bank_limit['month_trade'] != 0) {
            $BeginDate=date('Y-m-01', strtotime(date("Y-m-d")));
            $endThismonth=strtotime(date('Y-m-d', strtotime("$BeginDate +1 month -1 day")).' 23:59:59');
            $BeginDate=strtotime($BeginDate);

            $countmoney=QfbOrder::find()->where(['sorts'=>1,'type'=>1,'member_id'=>$member_id])->andFilterWhere(['in','is_check',[0,1,3,5]])->andFilterWhere(['between','create_time', $BeginDate, $endThismonth])->sum('price');
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
        $reqData['platformUserNo'] = $member_id;
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
        $reqData['redirectUrl'] = $hkyh->RETURN_PC_URL;
        // 超过此时间即页面过期 --必填
        $reqData['expired'] = date('YmdHis', time()+5*60);
        // 非必填---快捷充值回调模式，如传入 DIRECT_CALLBACK，则订单支付不论成功、失败、处理中均直接同步、异步通知商户；未传入订单仅在支付成功时通知商户；
        // $reqData['callbackMode'] = 'DIRECT_CALLBACK';

        //创建用户充值订单记录
        $order=new QfbOrder();
        $order->sn=$reqData['requestNo'];
        $order->member_id=$member_id;
        $order->price=$money;
        $order->is_check=3;
        $order->create_time=time();
        $order->sorts=1;
        $order->bank_id=$bank_id;
        $order->money=$money;
        $order->bank_type=3;
        $order->remark='充值';
        if (!$order->save()) {
            $this->error('充值失败');
        }
        // 记录日志
        $fileName = $serviceName."-REQUEST".".log";
        $content = "执行时间：".date("Y-m-d H:i:s", time())."   请求数据：".json_encode($reqData)."\r\n";
        LogService::hkyh_write_log($fileName, $content);

        $result = $hkyh->createPostParam($serviceName,$reqData);
        //这里根据业务逻辑自行处理，如果是直连则根据$result数据做处理，如果是网关则不返回数据，
    } 

    //投资
    public function actionInvest()
    {

        //接受参数
        $member_id=$this->mid ? $this->mid : 0;
        $money=Yii::$app->request->get('money');
        $product_id=Yii::$app->request->get('product_id');

        if (empty($member_id) || empty($money) ||empty($product_id)) {
            $this->error('参数错误');
        }
        $orderFixService = new OrderFixService();

        $data=[
            'product_id' => intval($product_id),
            'money' => $money,
            'member_voucher_id' => 0,
            'member_id' => $member_id,
        ];

        /** 创建订单 */
        if ($orderFixService->doSaveByMoney($data) == false) { 
            $this->error('购买失败，请稍后重试');
        } else {
            
            $sn = QfbProduct::find()->select('sn')->where('id=:product_id', [':product_id'=>$product_id])->asArray()->one();

            $liushui = QfbOrderFix::find()->select('sn')->where('product_id=:product_id and member_id=:member_id', [':product_id'=>$product_id, ':member_id'=>$member_id])->orderBy('id desc')->limit(1)->asArray()->one();

            $hkyh = \Yii::$app->Hkyh;

            // 用户预处理
            $serviceName = 'USER_PRE_TRANSACTION';

            // 流水号
            // $liushui = $this->getBindSn('UPT');

            // 请求流水号
            $reqData['requestNo'] = $liushui['sn']; //$sn; 
            // 出款人平台用户编号
            $reqData['platformUserNo'] = $member_id;
            // 根据业务的不同，需要传入不同的值，见【预处理业务类型】。
            $reqData['bizType'] = 'TENDER';//'TENDER';'REPAYMENT'
            // 冻结金额
            $reqData['amount'] = $money;
            // 预备使用的红包金额，只记录不冻结，仅限投标业务类型
            // $reqData['preMarketingAmount'] = '';
            // 超过此时间即页面过期
            $reqData['expired'] = date('YmdHis', time()+5*60);
            // 备注
            // $reqData['remark'] = 'Dq20170606170522ERD9XT';
            // 页面回跳 URL
            $reqData['redirectUrl'] = $hkyh->RETURN_PC_URL; //页面回跳 URL --必填*/
            $reqData['projectNo'] = $sn['sn'];//$data['sn'];Dq20170606170522ERD9XT 标的号 --必填

            // 记录日志
            $fileName = "TB_".$serviceName."_GATEWAY".".log";
            $content = "投标操作   执行时间：".date("Y-m-d H:i:s", time())."   订单号：".$reqData['requestNo']."   请求数据：".json_encode($reqData)."\r\n";
            LogService::hkyh_write_log($fileName, $content);

            // 到银行页面投标
            $hkyh->createPostParam($serviceName,$reqData);
        }
    }

    //协议
    public function actionXy()
    {
        $xy_id=Yii::$app->request->get('id');
        $xy_data=QfbAgreement::findOne($xy_id);
        if (empty($xy_data)) {
            $this->error('该协议修改中，请稍后查看');
        }
        return $this->render('xy',['xy_data'=>$xy_data]);
    }

    //关于我们
    public function actionAbout()
    {
        $this->layout='../layouts/layouts';
        //横幅图片
        //$image=QfbPcImage::find()->where(['type'=>4,'status'=>1])->orderBy('sort asc')->asArray()->one();
        $data=QfbAboutMe::find()->where(['status'=>1])->orderBy('sort asc')->asArray()->all();
        return $this->render('about',[
            'data'=>$data,
            ]);
    }

    //我要借款
    public function actionLoan()
    {
        $model = new QfbBorrowMoney();

        if (Yii::$app->request->isPost) {
            $data=Yii::$app->request->post();
            $data['time']=time();
            $model->setAttributes($data);
            if ($model->save()) {
                return $this->render('notify',[
                    'status' => 1,
                    'msg' => '申请成功，请等待平台工作人员与您联系'
                    ]);
            } else {
                return $this->render('notify',[
                    'status' => 2,
                    'msg' => '申请失败'
                    ]);
            }
        } else {
            return $this->render('loan',['model'=>$model]);
        }
    }

    //安全保障
    public function actionSecurity()
    {
        return $this->render('security');
    }

    //下载APP
    public function actionDownload()
    {
        return $this->render('download');
    }

    //文章展示
    public function actionShow()
    {
        $id=Yii::$app->request->get('id');
        $data=QfbArticle::findOne($id);
        return $this->render('show',[
                'data'=>$data
            ]);

    }


    //二维码
    public function actionQuickmark()
    {
        $this->layout = false;

        return $this->render('quickmark');
    }


    //关于我们查看详情
    public function actionShowdetail()
    {
        $this->layout = "../layouts/layouts";
        $id=Yii::$app->request->get('id');
        if (empty($id)) {
            //关于我们数据
            $pdata = QfbBaseNavigation::find()->where(['name'=>'关于我们','status'=>0])->asArray()->one();
            $base_navigation_list = QfbBaseNavigation::find()->where(['pid'=>$pdata['id'],'status'=>0])->orderBy('sort asc')->asArray()->all();
            $data = $base_navigation_list[0];
            $_GET['url']=$data['url'];
            $_GET['title']=$data['name'];
            $id=$data['url'];
        }
        $data=QfbArticle::findOne($id);
        return $this->render('showdetail',[
                'data'=>$data
            ]);
    }


    public function actionLoanpdf()
    {
        $mpdf = new mPDF('zh-CN','A4'); //new mPDF('zh-CN','A4','','',23,23,20)创建mpdf对象，‘zh-CN’:对应中文，‘23，23‘,页眉和页脚的距离。
        $mpdf->useAdobeCJK = true;
        $mpdf->SetWatermarkText('钱富宝Pro',0.1);
        $mpdf->showWatermarkText = true;

        $id = Yii::$app->request->get('id');

        if (!empty($this->mid)) {
            //获取用户信息
            $memgerinfo = QfbMember::find()->joinWith('memberInfo')
                ->select(['qfb_member.member_type', 'qfb_member.account', 'qfb_member_info.realname', 'qfb_member_info.card_no'])
                ->where(['id'=>$this->mid])
                ->asArray()
                ->one();

            //获取该产品相关信息 QfbWarranty
            $product = QfbProduct::find()->where(['id' => $id])->asArray()->one();
            if($product['profit_day'] == 21){
                $endtime = $product['finish_time']+$product['invest_day']*3600*24+86400;
            }else{
                $endtime = $product['finish_time']+$product['invest_day']*3600*24;
            }

            $jiekuanjine = $this->get_amount($product['stock_money']);
            
            if (!empty($product) && $product['status'] >= '6') {
                if(!empty($memgerinfo['member_type']) && $memgerinfo['member_type'] == 1){ //投资人
                    $where = 'product_id = '.$id.' and member_id = '.$this->mid.' and status != 4';
                    //获取投资人员相关信息
                    $touzi = QfbOrderFix::find()->select(['sum(pay_money) as pay_money', 'member_id', 'create_time'])->where($where)->asArray()->one();

                    if(!isset($touzi['member_id'])){
                        $mpdf->SetHTMLHeader( '钱富宝Pro平台借款协议<hr/>' );
                        $mpdf->WriteHTML($this->renderPartial('template'));
                        $mpdf->Output('借款协议.pdf','I');
                        exit;
                    }
                    
                    $touziinfo = QfbMember::find()->joinWith('memberInfo')
                        ->select(['qfb_member.member_type', 'qfb_member.account', 'qfb_member_info.realname', 'qfb_member_info.card_no'])
                        ->where(['id'=>$touzi['member_id']])
                        ->asArray()
                        ->one();
                }else{  //借款人
                    $jkr = QfbProduct::find()->where(['id' => $id, 'member_id' => $this->mid])->asArray()->one();
                    // var_dump($jkr);die;
                    if(!$jkr){
                        $mpdf->SetHTMLHeader( '钱富宝Pro平台借款协议<hr/>' );
                        $mpdf->WriteHTML($this->renderPartial('template'));
                        $mpdf->Output('借款协议.pdf','I');
                        exit;
                    }

                    $wheretouzi = 'product_id = '.$id.' and status != 4';
                    //获取投资金额最多人员相关信息 
                    $touzi = QfbOrderFix::find()
                        ->select(['sum(pay_money) as pay_money', 'member_id', 'create_time'])
                        ->where($wheretouzi)
                        ->groupBy('member_id')
                        ->orderBy('pay_money desc')
                        ->asArray()
                        ->one();

                    $touziinfo = QfbMember::find()->joinWith('memberInfo')
                        ->select(['qfb_member.account', 'qfb_member_info.realname', 'qfb_member_info.card_no'])
                        ->where(['id'=>$touzi['member_id']])
                        ->asArray()
                        ->one();
                }
                
                //获取借款人相关信息
                $jiekuanren = QfbMember::find()->joinWith('memberInfo')
                    ->select(['qfb_member.account', 'qfb_member.mobile', 'qfb_member_info.realname', 'qfb_member_info.card_no'])
                    ->where(['id'=>$product['member_id']])
                    ->asArray()
                    ->one();
                $memberbank = QfbBank::find()->where(['member_id'=>$product['member_id']])->asArray()->one();

                if($memgerinfo['member_type'] == 2){ //如果是借款人看投资人信息则用*标识
                    $touziinfo['account'] = AdCommon::hidtel($touziinfo['account']);
                    $touziinfo['mobile'] = AdCommon::hidtel($touziinfo['mobile']);
                    $touziinfo['card_no'] = AdCommon::cut_str($touziinfo['card_no'], 6, 0).'**** ****'.AdCommon::cut_str($touziinfo['card_no'], 4, -4);
                }else{
                    $jiekuanren['account'] = AdCommon::hidtel($jiekuanren['account']);
                    $jiekuanren['mobile'] = AdCommon::hidtel($jiekuanren['mobile']);
                    $jiekuanren['card_no'] = AdCommon::cut_str($jiekuanren['card_no'], 6, 0).'**** ****'.AdCommon::cut_str($jiekuanren['card_no'], 4, -4);
                    $memberbank['no'] = '**** **** ****'.AdCommon::cut_str($memberbank['no'], 4, -4);
                }


                // echo "<pre>";
                // var_dump($touziinfo);
                // echo "<br/>";
                // var_dump($memberbank);die;
                //获取保证方式
                $baozheng = QfbWarranty::find()->where(['product_id'=>$product['id']])->asArray()->one();

                //还款计划表
                $huankuan = QfbOrderRepayment::find()->where(['product_id'=>$product['id']])->asArray()->all();
                foreach ($huankuan as $key => $value) {
                    $ben = $value['money'];
                    $total += $value['interest'];
                }

                //协议编号
                $bianhao = date("YmdHis");

                // var_dump($huankuan);die;
                $mpdf->SetHTMLHeader( '钱富宝Pro平台借款协议<hr/>' );
                $mpdf->WriteHTML($this->renderPartial('haha', [
                    'product' => $product,
                    'touzi' => $touzi,
                    'memgerinfo' => $memgerinfo,
                    'jiekuanren' => $jiekuanren,
                    'memberbank' => $memberbank,
                    'baozheng' => $baozheng,
                    'jiekuanjine' => $jiekuanjine,
                    'huankuan' => $huankuan,
                    'ben' => $ben,
                    'total' => $total,
                    'bianhao' => $bianhao,
                    'touziinfo' => $touziinfo,
                    'endtime' => $endtime,
                ]));
                $mpdf->Output('借款协议.pdf','I');
                exit;
            } else {  //如果标的没有满则用空的模板展示
                $mpdf->SetHTMLHeader( '钱富宝Pro平台借款协议<hr/>' );
                $mpdf->WriteHTML($this->renderPartial('template'));
                $mpdf->Output('借款协议.pdf','I');
                exit;
            }
        } else {  //如果不是登录状态则用空的模板展示
            $mpdf->SetHTMLHeader( '钱富宝Pro平台借款协议<hr/>' );
            $mpdf->WriteHTML($this->renderPartial('template'));
            $mpdf->Output('借款协议.pdf','I');
            exit;
        }
    }


    public function actionTest()
    {
        // include_once '../../vendor/phpoffice/phpword/samples/Sample_Header.php';

        require_once '../../vendor/phpoffice/phpword/bootstrap.php';

        // Creating the new document...
        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        $phpWord->setDefaultFontName('Tahoma'); 
        $phpWord->setDefaultFontSize(12);

        $section = $phpWord->addSection();

        // Adding Text element to the Section having font styled by default...
        $section->addText(
            '"Learn from yesterday, live for today, hope for tomorrow. '
                . 'The important thing is not to stop questioning." '
                . '(Albert Einstein)'
        );

        // Adding Text element with font customized inline...
        $section->addText(
            '"Great achievement_is_usually-born of great sacrifice, '
                . 'and is never the result of selfishness." '
                . '(Napoleon Hill)',
            array('name' => 'Tahoma', 'size' => 10)
        );

        // Adding Text element with font customized using named font style...
        $fontStyleName = 'oneUserDefinedStyle';
        $phpWord->addFontStyle(
            $fontStyleName,
            array('name' => 'Tahoma', 'size' => 10, 'color' => '1B2232', 'bold' => true)
        );
        $section->addText(
            '"The greatest accomplishment is not in never falling, '
                . 'but in rising again after you fall." '
                . '(Vince Lombardi)',
            $fontStyleName
        );
        $c = "地质灾害";
        $section->addText($c, 'rStyle', 'pStyle');
        $content="<p>根据市气象局未来24小时降雨预报和市水利局实时降雨数据，市国土资源局进行了地质灾害预报，
        请有关部门关注</p>
        <p>实时预警信息，做好地质灾害防范工作</p>";
        $section->addText($content);

        // Adding Text element with font customized using explicitly created font style object...
        $fontStyle = new \PhpOffice\PhpWord\Style\Font();
        $fontStyle->setBold(true);
        $fontStyle->setName('Tahoma');
        $fontStyle->setSize(13);
        $myTextElement = $section->addText('"Believe you can and you\'re halfway there." (Theodor Roosevelt)');
        $myTextElement->setFontStyle($fontStyle);

        $src = __DIR__.'/../../../web/image/404img.png';

        $imageStyle = array('width'=>350, 'height'=>350, 'align'=>'center');
        $section->addImage( $src, [$imageStyle] );
        // Saving the document as OOXML file...
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');
        $objWriter->save('helloWorld.docx');
        var_dump($objWriter);die;
        // echo write($phpWord, basename(__FILE__, '.php'), $writers);
        // if (!CLI) {
        //     include_once '../../vendor/phpoffice/phpword/samples/Sample_Footer.php';
        // }
    }

}
