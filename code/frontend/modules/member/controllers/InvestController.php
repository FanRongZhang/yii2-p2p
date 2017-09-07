<?php
namespace frontend\modules\member\controllers;

use common\service\MemberService;
use frontend\models\search\InvestSearch;

class InvestController extends BaseController
{
    /**
     * 我的投资
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {

        //判断是投资人还是借款人
        if($this->member_type != 1){
            return $this->error('您不是投资人');
        }

        //判断是否开户
        if($this->memberData['is_dredge'] != 1){
            return $this->redirect('/member/auth');
        }

        $model = new InvestSearch();
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