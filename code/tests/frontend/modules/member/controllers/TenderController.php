<?php
namespace frontend\modules\member\controllers;

use frontend\controllers\WebController;
use frontend\models\search\TenderSearch;
use common\service\MemberService;

class TenderController extends WebController
{
    /**
     * 会员中心--放款
     */
    public function actionIndex()
    {
        $this->mid = 65;
        //判断是投资人还是借款人
//        if($this->member_type == 1){
//            return $this->error('您不是借款人');
//        }

        //判断用户是否开通账户
//        $member = MemberService::getHkyhUser($this->mid);
//
//        if($member['code'] != 200){
//            return $this->redirect('auth');
//        }

        $model = new TenderSearch();
        $dataProvider = $model->search(\Yii::$app->request->queryParams);
        return $this->render('index', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);

    }
}