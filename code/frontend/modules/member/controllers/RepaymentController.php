<?php
namespace frontend\modules\member\controllers;

use common\models\QfbOrderFix;
use common\models\QfbOrderRepaymentExtend;
use common\service\MemberService;
use common\toolbox\Tool;
use frontend\models\search\RepaymentSearch;
use common\models\QfbOrderRepayment;
use common\models\QfbProduct;
use common\service\AssetService;
use common\service\MemberMoneyService;
use League\Flysystem\Exception;

class RepaymentController extends BaseController
{
    /**
     * 会员中心--还款
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

        $model = new RepaymentSearch();
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

    public function actionOption()
    {
        if(\Yii::$app->request->post()) {
            $repayId = $this->post('repay_id');

            $memberMoney = MemberMoneyService::getByMemberMoney($this->mid);

           $orderRepayment = QfbOrderRepayment::find()->where(['id'=>$repayId, 'status'=>0, 'is_commutation'=> 0])->asArray()->one();

            if(!$orderRepayment){
                echo json_encode(['status'=>0, 'msg'=>'您无需还款']);
                exit;
            }

            $repaymentMoney = $orderRepayment['money']+$orderRepayment['interest'];   //待还款金额

            if($memberMoney->money < $repaymentMoney){
                echo json_encode(['status'=>0, 'msg'=>'您的余额不足，无法还款']);
                exit;
            }
            $data = ['repayment_money'=>$repaymentMoney, 'repay_id'=>$orderRepayment['id']];
            echo json_encode(['status'=>1, 'msg'=>'成功', 'data'=>$data]);
            exit;
        }
        exit;
    }

    /**
     * 还款操作
     */
    public function actionRepayment()
    {
        if(!\Yii::$app->request->isAjax){
            $repayId = $this->get('repay_id');

            $memberMoney = MemberMoneyService::getByMemberMoney($this->mid);

            $orderRepayment = QfbOrderRepayment::find()->where(['id'=>$repayId, 'status'=>0, 'is_commutation'=> 0])->asArray()->one();

            $product = QfbProduct::find()->where(['id'=>$orderRepayment['product_id']])->asArray()->one();

            $repaymentMoney = $orderRepayment['money']+$orderRepayment['interest'];   //待还款金额

            if($memberMoney->money < $repaymentMoney){
                $this->alert('您的余额不足，无法还款');
            }

            $result['member_id'] = $this->mid;
            $result['liushui'] =  $this->getBindSn('HK');
            $result['money'] = $repaymentMoney;
            $result['type'] = 'REPAYMENT';
            $result['sn'] = $product['sn'];
            $result['pc'] = 1;

            $orderRepaymentExtendModel = new QfbOrderRepaymentExtend();

            $orderRepaymentExtendModel->order_id = $repayId;
            $orderRepaymentExtendModel->sn = $result['liushui'];
            $orderRepaymentExtendModel->create_time = time();

            if($orderRepaymentExtendModel->save()){
                $service = new AssetService();
                $service->preTransaction($result);
                return $this->redirect(['/hkyh-notify/hkyh-return','data'=>['msg'=>'银行系统错误'],'status'=>'error']);
            }

        }

        return $this->redirect(['/hkyh-notify/hkyh-return','data'=>['msg'=>'平台系统异常'],'status'=>'error']);

    }
}