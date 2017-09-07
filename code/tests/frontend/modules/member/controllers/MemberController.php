<?php

namespace frontend\modules\member\controllers;

use common\models\UMember;
use common\service\MemberInfoService;
use common\service\MemberService;
use frontend\models\search\OrderSearch;
use common\models\QfbMoneyLog;
use frontend\controllers\WebController;

use common\service\CommonService;
use common\service\MoneyLogService;
use common\extension\middleware\EncryptService;

class MemberController extends WebController
{
    /**
     * 会员中心--总览
     * @return string
     * @author
     */
    public function actionIndex()
    {
        $this->mid = 65;
        //判断用户是否开通账户
//        $member = MemberService::getHkyhUser($this->mid);
//
//        if($member['code'] != 200){
//            return $this->redirect('/member/auth');
//        }

        $model = new OrderSearch();
        $dataProvider = $model->search(\Yii::$app->request->queryParams);
        $memberService = new MemberService();
        $member = $memberService->getMemberInfo($this->mid);

        $member['all_money'] =strval( $member['money'] + $member['live_money'] + $member['fix_money'] +
            $member['pre_live_money'] + $member['lock_money'] );

        $member['yesterday_profit'] = MoneyLogService::getProfitByMemberId($this->mid,'yesterday');

        $plan_profit = 0;
        $plan = QfbMoneyLog::find()->select('money')->where(['type'=>1,'money_type'=>1,'action'=>14])->andWhere(['=','member_id',$this->mid])->asArray()->all();
        if ($plan) {
            foreach ($plan as $value) {
                $plan_profit += $value['money'];
            }
        }
        $member['all_profit'] = (string) (MoneyLogService::getProfitByMemberId($this->mid) + $plan_profit);

        return $this->render('index', [
            'model' => $model,
            'member' => $member,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 会员中心--基本信息
     * @return string
     */
    public function actionMemberInfo()
    {
        $this->mid = 65;
        $memberService = new MemberService();
        $memberInfo = $memberService->getMemberInfo($this->mid);

        return $this->render('member_info', ['memberInfo'=>$memberInfo]);
    }

    /**
     * 更新密码
     */
    public function actionMemberPassword()
    {
        $model = UMember::findOne($this->mid);
        $errorMsg = '';
        $post = \Yii::$app->request->post();
        if($post){
            $oldPassword = $post['UMember']['old_password'];
            $newPassword = $post['UMember']['new_password'];

            if(!empty($newPassword)){
                if($oldPassword != $newPassword){
                    $isTrue = UMember::find()->where(['id'=>$this->mid, 'password'=>EncryptService::twiceMd5($oldPassword)])->one();
                    if($isTrue){
                        $model->password = EncryptService::twiceMd5($newPassword);
                        if ($model->save()) {
                            return $this->redirect(['index']);
                        }else{
                            $errorMsg = '系统异常，请重新提交';
                        }
                    }else{
                        $errorMsg = '旧密码错误';
                    }
                }else{
                    $errorMsg = '新密码跟旧密码不能相同';
                }
            }else{
                $errorMsg = '密码不能为空';
            }
        }
        
        return $this->render('password', ['error_msg'=>$errorMsg]);



    }

    /**
     * 确认密码
     */
    public function actionConfirmPassword()
    {
        $password = \Yii::$app->request->post('old_password', '');
        $length = strlen($password);
        if($length<6 || $length>12){
            echo 0;exit;
        }

        $isTrue = UMember::find()->where(['id'=>$this->mid, 'password'=>EncryptService::twiceMd5($password)])->one();

        if($isTrue){
            echo 1;
        }else{
            echo 0;
        }

        exit;
    }


    /**
     * ---------------------------------------
     * 忘记密码
     * @author lijunwei
     * ---------------------------------------
     */
    public function actionResetPassword()
    {
        $params = $this->post('UMember');
        foreach ($params as $value) {
            if ($value == null) {
                $this->error('参数缺失!');
            }
        }

        $memberService = new MemberService();
        $mobile = $memberService->findMemberByMobile($params);
        if (!$mobile) {
            $this->error('手机账号不存在!');
        }

        $member_id = $mobile->id;
        if (!CommonService::checkVerify($params['code'], $params['mobile'],CommonService::VERIFY_TYPE_FORGET)) {
            $this->error('验证码错误!');
        }
        $member = new MemberService();
        $result = $member->updateLoginPassword($member_id,$params);
        if ($result) {
            return $this->redirect(['/login/login/login']);
            // $this->success('修改成功!');
        } else {
            $this->error('修改失败!');
        }
    }


}
