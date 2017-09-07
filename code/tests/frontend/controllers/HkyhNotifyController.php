<?php 

namespace frontend\controllers;

use Yii;
use common\service\HkyhService;
use api\common\helpers\ReseponseCode as Code;
use yii\web\Response;

class HkyhNotifyController extends WebController
{
	    /**
     * 海口银行所有回调入口访问
     **/
    public function actionHkyh()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data=\Yii::$app->request->post() ? \Yii::$app->request->post() :\Yii::$app->request->get();

        // 无效回调操作 Invalid-request
        if(empty($data['serviceName']))
            return $this->error('未知请求','/index/index/index');

        $hkyhService = new HkyhService();

        //初始化
        $status = 'error';
        $name = '';
        $id_card = '';

        // 接口名称 区分方法处理不同回调
        switch($data['serviceName']){

            // 绑卡注册回调业务处理
            case 'PERSONAL_REGISTER_EXPAND':
                $result = $hkyhService->hkyhRester($data);
                if($result['code'] == code::HTTP_OK){//echo '<pre/>';var_dump($result);die;
                Yii::$app->response->format = Response::FORMAT_HTML;
                    //绑卡成功
                    return $this->render('notify',[
                        'msg'=>$result['msg'],
                        'time'=>time()
                        ]);
                }
            //充值
            case 'RECHARGE';
                $result = $hkyhService->userPay($data);
                if($result['code'] == code::HTTP_OK){ 
                Yii::$app->response->format = Response::FORMAT_HTML;                  
                    //充值成功
                    return $this->render('notify',[
                        'msg'=>$result['msg'],
                        'money'=>$result['data']['amount'],
                        'time'=>$result['data']['transactionTime']
                        ]);
                }

            //投资
            case 'USER_PRE_TRANSACTION';
                $result = $hkyhService->userPreTransaction($data);
                if($result['code'] == code::HTTP_OK){ 
                Yii::$app->response->format = Response::FORMAT_HTML;                  
                    //充值成功
                    return $this->render('notify',[
                        'msg'=>$result['msg'],
                        'money'=>$result['data']['pay_money'],
                        'time'=>time()
                        ]);
                }

        }

        return $this->render('notify-no');
    }
}