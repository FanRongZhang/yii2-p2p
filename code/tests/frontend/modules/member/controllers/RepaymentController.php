<?php
namespace frontend\modules\member\controllers;

use frontend\controllers\WebController;
use common\service\MemberService;
use frontend\models\search\RepaymentSearch;

class RepaymentController extends WebController
{
    /**
     * 会员中心--还款
     */
    public function actionIndex()
    {
        $this->mid=0;
        //判断是投资人还是借款人
//        if($this->member_type == 1){
//            return $this->error(['您不是借款人']);
//        }
//
//        //判断用户是否开通账户
//        $member = MemberService::getHkyhUser($this->mid);
//
//        if($member['code'] != 200){
//            return $this->redirect('auth');
//        }

        $model = new RepaymentSearch();
        $dataProvider = $model->search(\Yii::$app->request->queryParams);
        return $this->render('index', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }
}