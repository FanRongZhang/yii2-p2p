<?php 

namespace frontend\controllers;

use Yii;
use common\service\HkyhService;
use api\common\helpers\ReseponseCode as Code;
use yii\web\Response;
use common\service\LogService;

class HkyhNotifyController extends WebController
{

    /**
     * 海口银行所有回调入口访问
     **/
    public function actionHkyh()
    {
        //\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->format = Response::FORMAT_HTML;
        $data=\Yii::$app->request->post() ? \Yii::$app->request->post() :\Yii::$app->request->get();
        $respData = json_decode($data['respData']);
        if(!$this->check($respData->requestNo)){
            $this->error('请勿重复提交表单','/member/member/index');
        }

        // 无效回调操作 Invalid-request
        if(empty($data['serviceName']))
            return $this->error('未知请求','/index/index/index');

        $hkyhService = new HkyhService();

        //初始化
        $status = 'error';
        $name = '';
        $id_card = '';

        $fileName = $data['serviceName'].'_DIRECT.log';
        $content = '响应时间：'.date("Y-m-d H:i:s", time());

        // 接口名称 区分方法处理不同回调
        switch($data['serviceName']){

            // 绑卡注册回调业务处理
            case 'PERSONAL_REGISTER_EXPAND':
                $result = $hkyhService->hkyhRester($data);

                // 写入日志
                $content .= '   开户回调   平台用户编号：'.$result['data']['platformUserNo'].'    平台处理信息：'.$result['msg'].'    响应数据：'.json_encode($data)."\r\n";
                LogService::hkyh_write_log($fileName, $content);

                if($result['code'] == code::HTTP_OK){
                    $this->memberData['is_dredge'] = 1;
                    \Yii::$app->session->set('LOGIN', $this->memberData);
                    //绑卡成功
                    $data=[
                        'msg'=>$result['msg'],
                        'time'=>time(),
                        'title' => '注册成功'
                        ];
                    return $this->redirect(['/hkyh-notify/hkyh-return','data'=>$data,'status'=>'success']);
                }
                break;
            //充值
            case 'RECHARGE';
                $result = $hkyhService->userPay($data);
                // 写入日志
                $content .= '   充值回调   请求流水号：'.$result['data']['requestNo'].'    平台处理信息：'.$result['msg'].'    响应数据：'.json_encode($data)."\r\n";
                LogService::hkyh_write_log($fileName, $content);

                if($result['code'] == code::HTTP_OK){             
                    //充值成功
                    $data=[
                        'msg'=>$result['msg'],
                        'money'=>$result['data']['amount'],
                        'time'=>time(),
                        'title' => '充值金额'
                        ];
                    return $this->redirect(['/hkyh-notify/hkyh-return','data'=>$data,'status'=>'success']);
                }
                break;

            //提现
            case 'WITHDRAW';
                $result = $hkyhService->WithdrawNotify($data);

                // 写入日志
                $content .= '   提现回调   请求流水号：'.$result['data']->requestNo.'    平台处理信息：'.$result['msg'].'    响应数据：'.json_encode($data)."\r\n";
                LogService::hkyh_write_log($fileName, $content);

                if($result['code'] == code::HTTP_OK){
                    //提现成功成功
                    $data=[
                        'msg'=>$result['msg'],
                        'money'=>$result['data']->amount,
                        'time'=>time(),
                        'title' => '提现金额'
                    ];
                    return $this->redirect(['/hkyh-notify/hkyh-return','data'=>$data,'status'=>'success']);
                }
            break;

            //投资
            case 'USER_PRE_TRANSACTION';
                //还款
                $respData = json_decode($data['respData']);
                if($respData->bizType == 'REPAYMENT'){
                    $type = substr($respData->requestNo, 0, 2);
                    if($type == 'HK'){
                        $result = $hkyhService->hkyhRepayment($respData);
                    }else{
                        $result = $hkyhService->hkyhOverdueRepayment($respData);
                    }

                    if($result['code'] == code::HTTP_OK){
                        $data=[
                            'msg'=>$result['msg'],
                            'time'=>time(),
                            'title' => '还款成功'
                        ];
                    }
                    return $this->redirect(['/hkyh-notify/hkyh-return','data'=>$data,'status'=>'success']);
                }else{
                    $result = $hkyhService->userPreTransaction($data);

                    // 写入日志
                    $content .= '   投标回调   请求流水号：'.$result['data']['requestNo'].'    平台处理信息：'.$result['msg'].'    响应数据：'.json_encode($data)."\r\n";
                    LogService::hkyh_write_log($fileName, $content);

                    if($result['code'] == code::HTTP_OK){
                        //投资成功
                        $data=[
                            'msg'=>$result['msg'],
                            'money'=>$result['data']['pay_money'],
                            'time'=>time(),
                            'title' => '投资金额'
                        ];
                        return $this->redirect(['/hkyh-notify/hkyh-return','data'=>$data,'status'=>'success']);
                    }
                }

                break;

            //修改密码
            case 'RESET_PASSWORD';
                $result=json_decode($data['respData']);
                if($result->status == 'SUCCESS'){
                    $data=[
                        'msg'=>'修改成功',
                        'time'=>time()
                    ];
                    return $this->redirect(['/hkyh-notify/hkyh-return','data'=>$data,'status'=>'success']);
                }
            break;

        }

        return $this->redirect(['/hkyh-notify/hkyh-return','data'=>'','status'=>'error']);
    }

    //海口银行页面重定向
    public function actionHkyhReturn()
    {
        $data=\Yii::$app->request->post() ? \Yii::$app->request->post() :\Yii::$app->request->get();

        if ($data['status']=='success') {
            return $this->render('notify',['data'=>$data['data']]);
        } else {
            return $this->render('notify-no');
        }
    }

    /**
     * 海口银行异步回调
     **/
    public function actionAsync()
    {
        exit;
    }
}