<?php
namespace frontend\modules\member\controllers;

use common\service\LogService;
use common\service\MemberService;
use common\service\HkyhService;

class AuthController extends BaseController
{
    /**
     * 会员中心--认证（绑卡）
     */
    public function actionIndex()
    {
        //判断是否开户
        $member = MemberService::getHkyhUser($this->mid);

        if($member['code'] == 200){
            $json_de_data = json_decode($member['data']['data'], true);
            $json_de_data ["realName"] = $json_de_data ["name"];
            unset ( $json_de_data ["name"] );

            $params['respData'] = json_encode($json_de_data);

            // 已在银行系统开户且未在平台做标识，处理平台标识处理
            $hkyhService = new HkyhService();

            $data = $hkyhService->hkyhRester($params);

            if($data['code'] == 200){
                $this->memberData['member_type'] = 1;
                \Yii::$app->session->set('LOGIN', $this->memberData);
                return $this->redirect('/member/member/index');
            }
        }else{
            //未开户，注册开户，已开户，返回前一页面
            $hkyh = \Yii::$app->Hkyh;

            // 个人绑卡注册
            $serviceName = 'PERSONAL_REGISTER_EXPAND';

            if ($this->member_type==1) {
                $userRole='INVESTOR';
            } else {
                $userRole='BORROWERS';
            }

            // 流水号
            $sn = $this->getBindSn('RT');

            $reqData['platformUserNo'] = $this->mid; /*测试*/
            $reqData['requestNo'] = $sn;
            $reqData['idCardType'] = 'PRC_ID';
            $reqData['userRole'] = $userRole;
            $reqData['userLimitType'] = 'ID_CARD_NO_UNIQUE';
            $reqData['checkType'] = 'LIMIT';
            $reqData['redirectUrl'] =  $hkyh->RETURN_PC_URL;

            // 记录日志
            $fileName = "RT_".$serviceName."_GATEWAY".".log";
            $content = "绑卡注册操作   执行时间：".date("Y-m-d H:i:s", time())."   用户编号：".$this->mid."   请求数据：".json_encode($reqData)."\r\n";
            LogService::hkyh_write_log($fileName, $content);

            // 到银行页面注册
            $hkyh->createPostParam($serviceName,$reqData);
        }

        return $this->redirect('/member/member/index');
    }
}