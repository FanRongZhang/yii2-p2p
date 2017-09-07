<?php

namespace backend\modules\order\controllers;

use backend\modules\order\models\PtOrderSearch;
use common\models\QfbPtOrder;
use Yii;
use common\models\QfbChannel;
use common\models\QfbPlatformIncome;
use common\service\LogService;
use backend\modules\order\models\PtAccountSearch;
use common\models\QfbPtAccount;


class PtOrderController extends \yii\web\Controller
{
	public $enableCsrfValidation = false;
    public function actionIndex()
    {
        $searchModel = new PtOrderSearch();
        $params = Yii::$app->request->queryParams;

        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionList()
    {
        $searchModel = new PtAccountSearch();
        $params = Yii::$app->request->queryParams;

        $dataProvider = $searchModel->search($params);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionRecharge()
    {
    	$model = new QfbPtOrder;
    	if (Yii::$app->request->isPost) {
    		$data = Yii::$app->Request->post('QfbPtOrder');

    		if ($data['price'] < 1) {
    			$this->alert('最小充值金额为一元');
    		}
    		if (empty($data['pt_number'])) {
    			$this->alert('参数错误');
    		}
    		//前往充值
    		//查询默认通道信息
        	$channel = QfbChannel::find()->where(['is_default'=>1])->one();
        	//支付通道选择 -- 易宝通道
	        if ($channel['id'] == 8) {
	            $pt_name = 'YEEPAY';
	        }
	        // 块钱通道
	        else if($channel['id'] == 5) {
	            $pt_name = 'BILL99';
	        }else{
	            $this->alert('支付通道有误');
	        }
	        
	        $hkyh = \Yii::$app->Hkyh;

	        // 充值
	        $serviceName = 'RECHARGE';

	        //平台用户编号  -必填
	        $reqData['platformUserNo'] = $data['pt_number'];
	        // 请求流水号  --流水号 --不允许重复
	        $reqData['requestNo'] = $this->getBindSn('PT_CZ');
	        // 充值金额 --必填
	        $reqData['amount'] = $data['price'];
	        // 支付公司编码 - 见支付公司  --必填
	        $reqData['expectPayCompany'] = $pt_name;
	        // 支付方式 - 网银 WEB  快捷支付 SWIFT --必填
	        $reqData['rechargeWay'] = 'WEB';
	        // 页面回调url  --必填
	        $reqData['redirectUrl'] = $hkyh->RETURN_ADMIN_URL.'order/pt-order/return';
	        // 超过此时间即页面过期 --必填
	        $reqData['expired'] = date('YmdHis', time()+5*60);
	        // 非必填---快捷充值回调模式，如传入 DIRECT_CALLBACK，则订单支付不论成功、失败、处理中均直接同步、异步通知商户；未传入订单仅在支付成功时通知商户；
	        // $reqData['callbackMode'] = 'DIRECT_CALLBACK';

	        //创建用户充值订单记录
	        $order=new QfbPtOrder();
	        $order->sn=$reqData['requestNo'];
	        $order->pt_number='SYS_GENERATE_001';
	        $order->price=$data['price'];
	        $order->is_check=3;
	        $order->create_time=time();
	        $order->sorts=1;
	        $order->money=$data['price'];
	        $order->bank_type=$channel['id'];
	        if (!$order->save()) {
	            $this->alert('充值失败');
	        }
	        // 记录日志
	        $fileName = $serviceName."-REQUEST".".log";
	        $content = "执行时间：".date("Y-m-d H:i:s", time())."   请求数据：".json_encode($reqData)."\r\n";
	        LogService::hkyh_write_log($fileName, $content);

	        $result = $hkyh->createPostParam($serviceName,$reqData);
    	} else {
    		$name = Yii::$app->request->get('name');
    		if (empty($name)) {
    			$this->alert('参数错误');
    		}
    		$model->pt_number = $name;
    		return $this->render('recharge',
    				['model'=>$model]
    			);
    	}
    }

    //充值回调
    public function actionReturn()
    {
    	$data = empty(\Yii::$app->request->get()) ? \Yii::$app->request->post() : \Yii::$app->request->get();
    	
    	$respData = json_decode($data['respData'], true);

    	if (empty($respData)) {
    		$this->alert('充值失败');
    	}

    	if($respData['rechargeStatus'] == 'SUCCESS'){
    		//充值成功
    		//开启事务
            $tran = yii::$app->db->beginTransaction();

            try{

                $pt_income_data=QfbPlatformIncome::find()->where(['platform_name'=>$respData['platformUserNo']])->orderBy('complete_time desc')->one();

                $pt_income = new QfbPlatformIncome;
                //平台收益表操作
                $pt_income->platform_name = $respData['platformUserNo'];
                $pt_income->sn = $respData['requestNo'];
                $pt_income->remark = '平台账户充值';
                $pt_income->complete_time = strtotime($respData['transactionTime']);
                $pt_income->amount = $respData['amount'];
                $pt_income->balance =intval($pt_income_data['balance'] + $respData['amount']);
                $pt_income->ls_sn = $respData['requestNo'];
                $pt_income->type = 3;

                //订单表操作
                $order_data = QfbPtOrder::find()->where(['sn'=>$respData['requestNo']])->one();
                $order_data->is_check = 1;
                $order_data->complete_time = strtotime($respData['transactionTime']);

                //平台账户表操作
                $pt_money = QfbPtAccount::find()->where(['name'=>$respData['platformUserNo']])->one();
                $pt_money->money += $respData['amount'];

                // 平台账户表操作
                if(!$pt_money->save())
                    throw new \Exception('更新平台账户有误');

                // 更新平台收益记录
                if(!$pt_income->save())
                    throw new \Exception('更新平台收益记录有误');

                // 更改充值订单状态
                if(!$order_data->save())
                    throw new \Exception('更新充值订单状态有误');

                $tran->commit();
                return $this->redirect(['views','msg'=>'充值成功','code'=>'200']);

            } catch (\Exception $e) {
                $tran->rollback();
                return $this->redirect(['views','msg'=>'充值成功，平台处理数据失败','code'=>'500']);
            }
    	} else if($respData['rechargeStatus'] == 'PENDDING') {
            return $this->redirect(['views','msg'=>'充值中，等待银行处理','code'=>'300']);
        }
    	return $this->redirect(['views','msg'=>'充值失败','code'=>'500']);  	
    }

    public function actionViews()
    {
    	$data = empty(\Yii::$app->request->get()) ? \Yii::$app->request->post() : \Yii::$app->request->get();
    	if (empty($data)) {
    		$this->alert('充值失败','/order/pt-order/list');
    	}
    	$this->alert($data['msg'],'/order/pt-order/list');
    	
    }


	public function alert($msg, $url = NULL, $charset='utf-8')
	{
		header("Content-type: text/html; charset={$charset}");
		$alert_msg="alert('$msg');";
		if( empty($url) ) {
			$go_url = 'history.go(-1);';
		}else{
			$go_url = "window.location.href = '{$url}'";
		}
		echo "<script>$alert_msg $go_url</script>";
		exit;
	}

	/**
	 * 设置流水号
	 */
	public function getBindSn($type='')
	{
		//生成随机字母+数字
		$str = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$code = "";
		$len = strlen($str);
		for ($i = 0; $i < 6; $i++) {
			$code .= $str{rand(0, $len - 1)};
		}
		return $type . date('YmdHis') . $code;
	}

	//开户
	public function actionOpen()
	{
		$model = new QfbPtAccount;

		if (Yii::$app->request->isPost) {
			$data = Yii::$app->request->post('QfbPtAccount');
			if (empty($data['name']) || empty($data['bank']) || empty($data['bank_code'])) {
				$this->alert('参数错误');
			}

			$hkyh = \Yii::$app->Hkyh;

	        // 绑卡
	        $serviceName = 'ENTERPRISE_BIND_BANKCARD';

	        $reqData['requestNo'] = $this->getBindSn('PT_BK');
	        $reqData['redirectUrl'] = $hkyh->RETURN_ADMIN_URL.'order/pt-order/openreturn';
	        $reqData['platformUserNo'] = $data['name'];
	        $reqData['bankcardNo'] = $data['bank'];
	        $reqData['bankcode'] = $data['bank_code'];

	        // 记录日志
	        $fileName = $serviceName."-REQUEST".".log";
	        $content = "执行时间：".date("Y-m-d H:i:s", time())."   请求数据：".json_encode($reqData)."\r\n";
	        LogService::hkyh_write_log($fileName, $content);

	        $result = $hkyh->createPostParam($serviceName,$reqData);
			//var_dump($result);exit;
		} else {
			$name = Yii::$app->request->get('name');

			if (empty($name)) {
				$this->alert('参数错误');
			}

			$model->name = $name;

			return $this->render('open',['model'=>$model]);
		}

	}

	//绑卡回调
	public function actionOpenreturn()
	{
		$data = empty(\Yii::$app->request->get()) ? \Yii::$app->request->post() : \Yii::$app->request->get();
    	
    	$respData = json_decode($data['respData'], true);

    	if (empty($respData)) {
    		$this->alert('绑卡失败');
    	}
    	if ($respData['status'] == 'SUCCESS') {
    		//更改账户状态
    		$pt_money = QfbPtAccount::find()->where(['name'=>$respData['platformUserNo']])->one();
    		$pt_money->bank = $respData['bankcardNo'];
    		$pt_money->bank_code = $respData['bankcode'];
    		$pt_money->is_open = 1;
    		if ($pt_money->save()) {
    			return $this->redirect(['views','msg'=>'绑卡成功','code'=>'200']);
    		} else {
    			return $this->redirect(['views','msg'=>'绑卡失败','code'=>'500']);
    		} 		
    	}
    	return $this->redirect(['views','msg'=>'绑卡失败','code'=>'500']); 
	}

	//提现
	public function actionWithdraw()
	{
		$model = new QfbPtAccount;
		if (Yii::$app->request->isPost) {
			$data = Yii::$app->request->post('QfbPtAccount');
			if (empty($data['name']) || empty($data['money'])) {
				$this->alert('参数错误');
			}
			//查询账户余额
			$pt_money = QfbPtAccount::find()->where(['name'=>$data['name']])->one();
			if ($data['money'] > $pt_money['money']) {
				$this->alert('余额不足');
			}
			$hkyh = \Yii::$app->Hkyh;
			// 提现
	        $serviceName = 'WITHDRAW';

	        //平台用户编号  -必填
	        $reqData['platformUserNo'] = $data['name'];
	        // 请求流水号  --流水号 --不允许重复
	        $reqData['requestNo'] = $this->getBindSn('PT_TX');//"LQ". $this->member_id . time() . rand(10, 99);
	        $reqData['withdrawType'] = 'NORMAL'; //提现方式
	        $reqData['withdrawForm'] = 'IMMEDIATE'; //IMMEDIATE直接提现，CONFIRMED待确认提现
	        $reqData['amount'] = $data['money'];
	        $reqData['redirectUrl'] = $hkyh->RETURN_ADMIN_URL.'order/pt-order/withdrawreturn';
	        // 超过此时间即页面过期 --必填
	        $reqData['expired'] = date('YmdHis', time()+3600);

	        //创建提现订单记录
	        $order=new QfbPtOrder();
	        $order->sn=$reqData['requestNo'];
	        $order->pt_number=$reqData['platformUserNo'];
	        $order->price= $reqData['amount'];
	        $order->is_check=3;
	        $order->create_time=time();
	        $order->sorts=2;
	        $order->money=$reqData['amount'];
	        if (!$order->save()) {
	            $this->alert('提现失败');
	        }
	        // 记录日志
	        $fileName = $serviceName."-REQUEST".".log";
	        $content = "执行时间：".date("Y-m-d H:i:s", time())."   请求数据：".json_encode($reqData)."\r\n";
	        LogService::hkyh_write_log($fileName, $content);

	        $result = $hkyh->createPostParam($serviceName,$reqData);
			//var_dump($data);exit;
		} else {
			$name = Yii::$app->request->get('name');

			if (empty($name)) {
				$this->alert('参数错误');
			}

			$model->name = $name;

			return $this->render('withdraw',['model'=>$model]);
		}
	}

	//提现回调
	public function actionWithdrawreturn()
	{
		$data = empty(\Yii::$app->request->get()) ? \Yii::$app->request->post() : \Yii::$app->request->get();
    	
    	$respData = json_decode($data['respData'], true);

    	if (empty($respData)) {
    		$this->alert('提现失败');
    	}
    	if ($respData['status'] == 'SUCCESS') {
    		//开启事务
            $tran = yii::$app->db->beginTransaction();

            try{
            	$pt_income_data=QfbPlatformIncome::find()->where(['platform_name'=>$respData['platformUserNo']])->orderBy('complete_time desc')->one();
	    		//处理数据
	    		$pt_money = QfbPtAccount::find()->where(['name'=>$respData['platformUserNo']])->one();
	    		$pt_money->money -= $respData['amount'];
	    		$pt_money->frozen += $respData['amount'];

	    		if (!$pt_money->save()) {
	    			throw new \Exception('更新平台账户有误');
	    		}

	    		//平台收益表操作
	    		$pt_income = new QfbPlatformIncome;
	            $pt_income->platform_name = $respData['platformUserNo'];
	            $pt_income->sn = $respData['requestNo'];
	            $pt_income->remark = '平台账户提现';
	            $pt_income->complete_time = strtotime($respData['transactionTime']);
	            $pt_income->amount = $respData['amount'];
	            $pt_income->balance =intval($pt_income_data['balance'] - $respData['amount']);
	            $pt_income->ls_sn = $respData['requestNo'];
	            $pt_income->type = 1;
	            if (!$pt_income->save()) {
	    			throw new \Exception('更新平台收益记录有误');
	    		}
	    		$tran->commit();
                return $this->redirect(['views','msg'=>'提现成功，等待银行处理','code'=>'200']);
	    	} catch (\Exception $e) {
                $tran->rollback();
                return $this->redirect(['views','msg'=>$e->getMessage(),'code'=>'500']);
            }
    	}
    	return $this->redirect(['views','msg'=>'提现失败','code'=>'500']);
    	//echo '<pre/>';var_dump($respData);exit;
	}

}