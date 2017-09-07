<?php
namespace frontend\modules\member\controllers;

use frontend\models\search\TenderSearch;
use common\service\MemberService;

class TenderController extends BaseController
{
    /**
     * 会员中心--借款
     */
    public function actionIndex()
    {
        //判断是投资人还是借款人
        if($this->member_type == 1){
            return $this->error('您不是借款人');
        }

        //判断是否开户
        if($this->memberData['is_dredge'] != 1){
            return $this->redirect('/member/auth');
        }

        $model = new TenderSearch();
        $dataProvider0 = $model->search(\Yii::$app->request->queryParams, $this->mid);
        $dataProvider1 = $model->search(\Yii::$app->request->queryParams, $this->mid, 1);
        $dataProvider2 = $model->search(\Yii::$app->request->queryParams, $this->mid, 2);
        return $this->render('index', [
            'model' => $model,
            'dataProvider0' => $dataProvider0,
            'dataProvider1' => $dataProvider1,
            'dataProvider2' => $dataProvider2,
        ]);

    }
}