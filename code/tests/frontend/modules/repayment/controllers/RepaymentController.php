<?php

namespace frontend\modules\repayment\controllers;

use common\models\QfbOrder;
use common\models\QfbOrderRepayment;
use common\models\QfbProduct;
use common\service\AssetService;
use common\service\MemberMoneyService;
use frontend\controllers\WebController;

class RepaymentController extends WebController
{

    public function init()
    {

        parent::init();
        $this->mid = 10;
//        if(empty($this->mid)){
//            return $this->error('未登录');
//        }
//
//        if($this->member_type != 2){
//            return $this->error('您不是投资人');
//        }
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionRepayment()
    {
        $productId = $this->get('id');

        $memberMoney = MemberMoneyService::getByMemberMoney($this->mid);

        $product = QfbProduct::find()->where(['id'=>$productId])->asArray()->one();

//        if($product['status'] != 4){
//            return $this->error('您无需还款');
//        }

        $interest = $product['stock_money']*($product['year_rate']/100)*$product['invest_day']/$this->yearDay();  //利息

        $repaymentMoney = $product['stock_money']+$interest;   //待还款金额
        $repaymentMoney = sprintf("%.2f",substr(sprintf("%.3f", $repaymentMoney), 0, -1));

        if($memberMoney->money < $repaymentMoney){
            return $this->error('您的余额不足，无法还款');
        }

        /***********创建还款订单**************/
        $orderModel = new QfbOrderRepayment();

        $result['liushui'] = $product['sn'];
        $result['money'] = $repaymentMoney;
        $result['type'] = 'REPAYMENT';
        $result['sn'] = $orderModel->sn = $this->getBindSn('Hk');
        $orderModel->member_id = $this->mid;
        $orderModel->money = $product['stock_money'];
        $orderModel->interest = $interest;
        $orderModel->create_time = time();

        if($orderModel->save()){
            $service = new AssetService();
            $res = $service->preTransaction($result);
            if ($res['status'] == 'error') {
                return $this->error('还款失败');
            }else{
                return $this->error('成功');
            }
        }

        return $this->error('系统异常');


    }
}
