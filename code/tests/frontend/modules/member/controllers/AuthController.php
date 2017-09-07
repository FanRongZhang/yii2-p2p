<?php
namespace frontend\modules\member\controllers;

use common\service\MemberService;
use common\service\HkyhService;
use frontend\controllers\WebController;

class AuthController extends WebController
{
    /**
     * 会员中心--认证（绑卡）
     */
    public function actionIndex()
    {
        $this->mid = 65;

        $hkyhService = new HkyhService();

        //判断是否开户
        $member = MemberService::getHkyhUser($this->mid);

        if($member['code'] != 200){
            $result = $hkyhService->hkyhUser($this->mid); //对比银行，用户是否开户
            if($result['code'] != 200){
                $hkyhService->hkyhRegister($this->mid); //未开户，注册开户，已开户，返回前一页面
            }
        }

        return $this->setForward();
    }
}