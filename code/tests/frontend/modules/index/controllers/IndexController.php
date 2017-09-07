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
use common\models\QfbAgreement;
use yii\data\Pagination;
use common\models\QfbOrder;


use common\models\QfbOrderFix;
use common\service\HkyhService;
use common\service\AssetService;


/*
 * ---------------------------------------
 * PC端首页控制器 
 * @author phphome@qq.com 
 * ---------------------------------------
 */
class IndexController extends WebController
{
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
        //echo '<pre/>';var_dump($list);die;
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
        $newsdata=QfbNotice::find()->where(['>','show_end_time',time()])->orderBy('send_time desc')->limit(5)->asArray()->all();
        return $this->render('newslist',[
                'newsdata' => $newsdata,
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

        //抵押贷列表
        $list = $this->query($proServ,'product_detail',$where,$orderby,5,true);
        // var_dump($list['data']);die;

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
            qfb_product.year_rate,qfb_product.status,qfb_product.end_time,qfb_product.invest_day,qfb_product.is_newer,
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
        $model['year_rate'] = intval($model['year_rate']);

        //查询当前用户开户信息，资金信息
        $member_id=0;
        $member_data=\Yii::$app->session->get('LOGIN');
        if (!empty($member_data)) {
            $member_id=$member_data['id'];
        }
        $member_data=QfbMember::findOne($member_id);

        $member_money=QfbMemberMoney::findOne($member_id);
        $member_money=$member_money['money'];

        //查询用户绑卡信息
        $member_bank=QfbBank::find()->where(['member_id'=>$member_id])->one();

        //查询投资记录
        $order_model = new OrderFixSearch();
        $dataProvider = $order_model->search(\Yii::$app->request->queryParams,$id);

        //echo '<pre/>';var_dump($dataProvider);die;
        return $this->render('detail', [
            'model' => $model,
            'member_data' => $member_data,
            'member_money' => $member_money,
            'member_bank' => $member_bank,
            'order_model' => $order_model,
            'dataProvider' => $dataProvider
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

        var_dump($data);die;
        return $this->render('buylist', [
            'data' => $data,
        ]);
    }

    //充值
    public function actionRecharge()
    {
        //接受参数
        $member_id=Yii::$app->request->get('member_id');
        $money=Yii::$app->request->get('money');
        $bank_id=Yii::$app->request->get('bank_id');
        if (empty($member_id) || empty($money) || empty($bank_id)) {

            $this->error('参数错误');
        }
        //充值
        //获取当前用户绑卡所属银行信息
            $bankinfo=QfbBank::findOne($bank_id);

            $hkyh = \Yii::$app->Hkyh;

            // 充值
            $serviceName = 'RECHARGE';

            //平台用户编号  -必填
            $reqData['platformUserNo'] = $member_id;
            // 请求流水号  --流水号 --不允许重复
            $reqData['requestNo'] = "LQ". $member_id . time() . rand(10, 99);
            // 充值金额 --必填
            $reqData['amount'] = $money;
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
            $reqData['redirectUrl'] = $hkyh->RETURN_PC_URL;
            // 超过此时间即页面过期 --必填
            $reqData['expired'] = date('YmdHis', time()+5*60);
            // 非必填---快捷充值回调模式，如传入 DIRECT_CALLBACK，则订单支付不论成功、失败、处理中均直接同步、异步通知商户；未传入订单仅在支付成功时通知商户；
            $reqData['callbackMode'] = 'DIRECT_CALLBACK';

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

            $result = $hkyh->createPostParam($serviceName,$reqData);
            //这里根据业务逻辑自行处理，如果是直连则根据$result数据做处理，如果是网关则不返回数据，
    } 

    //投资
    public function actionInvest()
    {

        //接受参数
        $member_id=Yii::$app->request->get('member_id');
        $money=Yii::$app->request->get('money');
        $product_id=Yii::$app->request->get('product_id');
        //var_dump($_GET);die;

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

            // 到银行页面投标
            $hkyh->createPostParam($serviceName,$reqData);

        }
    }


    public function actionXy()
    {
        $xy_id=Yii::$app->request->get('id');
        $xy_data=QfbAgreement::findOne($xy_id);
        if (empty($xy_data)) {
            $this->error('该协议修改中，请稍后查看');
        }
        return $this->render('xy',['xy_data'=>$xy_data]);
    }

}
