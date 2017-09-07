<?php

namespace frontend\modules\tender\controllers;

use Yii;
use common\models\QfbMember;
use common\models\QfbProduct;
use common\models\QfbOrderFix;
use common\service\AssetService;
use common\service\MemberService;
use common\service\OrderFixService;
use frontend\controllers\WebController;
use api\common\helpers\ReseponseCode as Code;

/*
 * ---------------------------------------
 * PC端用户投标控制器 
 * @author phphome@qq.com 
 * ---------------------------------------
 */
class TenderController extends WebController
{
	/**
     * ---------------------------------------
     * 绑卡注册银行虚拟账户
     * @author lijunwei
     * ---------------------------------------
     */
    public function actionHkyhRegister()
    {
        $member_data = QfbMember::find()->where(['id' => $this->mid])->one();
        $member_id = $this->mid;
        $is_dredge = $member_data->is_dredge;

        if(empty($member_data) || empty($params['access_token']))
            return $this->redirect(['/v200/notify/hkyh-return','status'=>'nologin','type'=>'hkyh-register']);

        if(!empty($is_dredge))
            return $this->redirect(['/v200/notify/hkyh-return','status'=>'already','type'=>'hkyh-register']);

        // 调用接口查询，确定是否开户
        $getHkyhUser = MemberService::getHkyhUser($member_id);

        // 未开户
        if($getHkyhUser['code'] != Code::HTTP_OK){

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

        if($result['code'] == Code::HTTP_OK){
            $status = 'success';
        }else{
            $status = 'already';
        }
        return $this->redirect(['/v200/notify/hkyh-return','status'=>$status,'type'=>'hkyh-register']);
    }


	/**
     * ---------------------------------------
     * 前端投标方法---用户预处理
     * @author lijunwei
     * ---------------------------------------
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;
        if ($request->isPost) {

            $params = $request->post();
            if($params['money'] <= 0) {
                $this->error('请输入金额');
            }
            if($params['id']=='') {
                $this->error('产品不存在');
            }

            $member_data = QfbMember::find()->where(['id' => $this->mid])->one();
            $member_id = $this->mid;

            if(empty($member_data))
                return $this->redirect(['/v200/notify/hkyh-return','status'=>'nologin','type'=>'hkyh-register']);

            $orderFixService = new OrderFixService();
            $data=[
                'product_id' => intval($params['id']),
                'money' => $params['money'],
                'member_voucher_id' => intval($params['member_voucher_id']),
                'member_id' => $member_id,
            ];

            /** 创建订单 */
            if ($orderFixService->doSaveByMoney($data) == false) {
                throw new Exception($orderFixService->findOneMessage());
            } else {
                $sn = QfbProduct::find()->select('sn')->where('id=:product_id', [':product_id'=>$params['id']])->asArray()->one();
                $liushui = QfbOrderFix::find()->select('sn')->where('product_id=:product_id', [':product_id'=>$params['id']])->asArray()->one();
                $result = [
                    'member_id' => $member_id,
                    'money' => $params['money'],
                    'sn' => $sn['sn'],
                    'liushui' => $liushui['sn'],
                ];
                $service = new AssetService;
                $res = $service->preTransaction($result);

                if ($res['status'] == 'error') {
                    $this->error($res['message']);
                }
            }
        }

        return $this->render('index');
    }

}