<?php
namespace frontend\modules\member\controllers;

use common\models\QfbOrderOverdue;
use common\models\QfbOrderRepayment;
use common\models\QfbOrderRepaymentExtend;
use common\models\QfbProduct;
use common\service\AssetService;
use common\service\MemberMoneyService;
use frontend\models\search\OverdueSearch;
use common\service\MemberService;

class OverdueController extends BaseController
{
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

        $model = new OverdueSearch();
        $dataProvider = $model->search(\Yii::$app->request->queryParams, $this->mid);
        return $this->render('index', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionOption()
    {
        if(\Yii::$app->request->post()) {
            $overdueId = $this->post('id');

            $orderOverdue = QfbOrderOverdue::find()->where(['id'=>$overdueId, 'status'=>0])->asArray()->one();

            $repaymentMoney = $orderOverdue['money']+$orderOverdue['interest']+$orderOverdue['overdue_money'];

            $data = ['repayment_money'=>$repaymentMoney, 'product_id'=>$orderOverdue['product_id']];
            echo json_encode(['status'=>1, 'msg'=>'成功', 'data'=>$data]);
            exit;
        }
        exit;
    }

    public function actionRepayment()
    {
        if(!\Yii::$app->request->isAjax){
            $overdueId = $this->get('id');

            $memberMoney = MemberMoneyService::getByMemberMoney($this->mid);

            $orderOverdue = QfbOrderOverdue::find()->where(['id'=>$overdueId, 'status'=>0])->asArray()->one();

            $isCommutation = \common\models\QfbOrderRepayment::find()
                ->where(['product_id'=>$orderOverdue['product_id'], 'is_overdue'=>1])
                ->andFilterWhere(['in', 'status', [0,1]])->one();
            if($orderOverdue['status'] != 0 || $isCommutation){
                $this->alert('您无法还款');
            }

            $product = QfbProduct::find()->where(['id'=>$orderOverdue['product_id']])->asArray()->one();

            $repaymentMoney = $orderOverdue['money']+$orderOverdue['interest']+$orderOverdue['overdue_money'];

            if($memberMoney->money < $repaymentMoney){
                $this->alert('您的余额不足，无法还款');
            }

            $result['member_id'] = $this->mid;
            $result['liushui'] =  $this->getBindSn('DC');;
            $result['money'] = $repaymentMoney;
            $result['type'] = 'REPAYMENT';
            $result['sn'] = $product['sn'];
            $result['pc'] = 1;

            $orderOverdueExtendModel = new QfbOrderRepaymentExtend();

            $orderOverdueExtendModel->order_id = $overdueId;
            $orderOverdueExtendModel->sn = $result['liushui'];
            $orderOverdueExtendModel->type = 1;
            $orderOverdueExtendModel->create_time = time();

            if($orderOverdueExtendModel->save()){
                $service = new AssetService();
                $service->preTransaction($result);
                return $this->redirect(['/hkyh-notify/hkyh-return','data'=>['msg'=>'银行系统错误'],'status'=>'error']);
            }
        }

        return $this->redirect(['/hkyh-notify/hkyh-return','data'=>['msg'=>'平台系统异常'],'status'=>'error']);

    }
}