<?php
namespace frontend\modules\member\controllers;

use frontend\controllers\WebController;
use common\service\MemberService;
use frontend\models\search\InvestSearch;

class InvestController extends WebController
{
    /**
     * 我的投资
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        $this->mid = 65;

        //判断是投资人还是借款人
//        if($this->member_type != 1){
//            return $this->error(['您不是投资人']);
//        }
//
//        //判断是否开户
//        $member = MemberService::getHkyhUser($this->mid);
//
//        if($member['code'] != 200){
//            return $this->redirect('auth');
//        }

        $model = new InvestSearch();
        $dataProvider = $model->search(\Yii::$app->request->queryParams);
        return $this->render('index', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }
}