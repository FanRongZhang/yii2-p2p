<?php

namespace frontend\modules\member\controllers;

use common\models\QfbOrderOverdue;
use common\models\UMember;
use common\service\MemberInfoService;
use common\service\MemberService;
use common\toolbox\Tool;
use frontend\models\search\OrderSearch;
use common\models\QfbMoneyLog;
use common\models\QfbProduct;
use common\models\QfbOrderFix;
use common\models\QfbOrderRepayment;
use common\enum\ProductEnum;
use common\service\CommonService;
use common\service\MoneyLogService;
use common\extension\middleware\EncryptService;

class MemberController extends BaseController
{

    public $terminally_day = 30;

    /**
     * 会员中心--总览
     * @return string
     * @author
     */
    public function actionIndex()
    {

        $model = new OrderSearch();
        $dataProvider = $model->search(\Yii::$app->request->queryParams, $this->mid, $this->member_type);
        $memberService = new MemberService();
        $member = $memberService->getMemberInfo($this->mid);

        $member['all_money'] =strval( $member['money'] + $member['live_money'] + $member['fix_money'] +
            $member['pre_live_money'] + $member['lock_money'] );  //总资产

        $member['all_credit'] = MoneyLogService::getCreditByCount($this->mid);  //累计放款

        $member['yesterday_profit'] = MoneyLogService::getProfitByMemberId($this->mid,'yesterday');  //昨日收益

        $member['yesterday_credit'] = MoneyLogService::getCreditByMemberId($this->mid,'yesterday');  //昨日放款
        $product_where = "status in (6,7) and member_id = ".$this->mid;
        $result = QfbProduct::find()->select(['sum(stock_money) as dai_money, sum(actual_repayment_money) as actual_repayment_money'])->where($product_where)->asArray()->one();  //待还金额

        //借款人利息（投资人利息）
        $product = QfbProduct::find()->where($product_where)->asArray()->all();
        $interest = '0.00';
        foreach ($product as $key => $value) {
            $orderFix = QfbOrderFix::find()->where('product_id=:product_id and status in (2,3)', [':product_id'=>$value['id']])->asArray()->all();

            foreach($orderFix as $k=>$v) {

                if($value['profit_type'] == 1){
                    $interest += Tool::moneyCalculate($v['day_interest']*$value['invest_day']);
                }else{
                    $term = ceil($value['invest_day']/$this->terminally_day);
                    for($i=1; $i<=$term; $i++){
                        if($i == $term){
                            $investDay = $value['invest_day']-($i-1)*$this->terminally_day;
                        }else{
                            $investDay = $this->terminally_day;
                        }
                        $interest += Tool::moneyCalculate($investDay*$v['day_interest']);
                    }
                }
            }
        }

        //获取违约金
        $overdue = QfbOrderOverdue::find()->where(['to_member_id'=>$this->mid])->andFilterWhere(['in', 'status', [0,1]])->select(['sum(overdue_money) as overdue_money'])->asArray()->one();

        $overdueMoney = !empty($overdue['overdue_money']) ? $overdue['overdue_money']:0;

        if (isset($result['dai_money']) && !empty($result['dai_money'])) {
            $dai_money = isset($result['dai_money']) ? $result['dai_money'] : '0.00';

            $member['dai_money'] = $dai_money+$interest-$result['actual_repayment_money']+$overdueMoney;  //借款人借款金额+利息-已还款金额=待还款金额
        } else {
            $member['dai_money'] = '0.00';
        }

        $plan_profit = 0;
        $plan = QfbMoneyLog::find()->select('money')
            ->where(['type'=>1,'money_type'=>1,'action'=>14])
            ->andWhere(['=','member_id',$this->mid])
            ->asArray()->all();
        if ($plan) {
            foreach ($plan as $value) {
                $plan_profit += $value['money'];
            }
        }
        $member['all_profit'] = (string) (MoneyLogService::getProfitByMemberId($this->mid) + $plan_profit);  //累计收益

        return $this->render('index', [
            'model' => $model,
            'member' => array_merge($member, $this->memberData),
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 会员中心--基本信息
     * @return string
     */
    public function actionMemberInfo()
    {
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
        if(!empty($post)){
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
        $password = \Yii::$app->request->post('password', '');
        $length = strlen($password);
        if($length<6 || $length>18){
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

}
