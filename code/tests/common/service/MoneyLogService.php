<?php 
namespace common\service;

use common\models\QfbMoneyLog;
use common\models\QfbMoneyDetail;
use common\models\moneyLog\MoneyLog;
use common\enum\MoneyEnum;
use common\models\QfbOrder;
use common\models\QfbOrderFix;

class MoneyLogService extends BaseService{
    protected $member_id;

    protected $_className = "common\models\QfbMoneyLog";

    public function __construct($member_id){
        $this->member_id=$member_id;
        $this->model = new QfbMoneyLog($member_id);
    }
    public function getList($page=1,$limit=10){
        $model = $this->newModel();
        $list = $model::find()->select(['create_time','type','money','remark'])
        ->where(['=','member_id',$this->member_id])
        //->andWhere(['=','money_type',1])
        ->offset(($page-1)*$limit)->limit($limit)->orderBy('create_time desc')
        ->all();
        $data = new MoneyLog();
        if(count($list)>0){
            foreach ($list as $value) {
                $data->add($value);
            }
        }
        return $data->show();
    }


    /**
     *累积$member_id的分润收益
     *
     */
    public static function getAllProfit($member_id = 0,$from_members = -1){
    
        $query = QfbMoneyDetail::find();
        $query->where(['member_id' => $member_id]);
        $query->andWhere([
            'type' => 3,
            'is_show' => 1,
        ]);

        if ($from_members != -1){
            $query->andWhere(['in','from_member_id',$from_members]);
        }
        
        $all_profit = $query->sum('money');
        $all_profit = empty($all_profit)? 0:$all_profit;
         
        return $all_profit;
    }

    public function createList($list){

        if(count($list)<1){
            return false;
        }
        foreach ($list as  $params) {
            $model = new QfbMoneyLog($this->member_id);
            $model->load(['QfbMoneyLog'=>$params]);
            if($model->validate()&&$model->save()){
                continue;
            }else{
                $this->messages = $model->getErrors();
                return false;
            }
        }
        return true;
    }

    public function create($params){
        $this->model->load(['QfbMoneyLog'=>$params]);
        if($this->model->validate()&&$this->model->save()){
            return true;
        }else{
            $this->messages = $this->model->getErrors();
            return false;
        }
    }

    /**
     *根据member_id获取收益
     */
    public static function getProfitByMemberId($member_id=null , $type = 'all'){
        if($member_id==null){
            return '0';
        }
        $query  = QfbMoneyLog::find();
        $query->where([
            'member_id' => $member_id,
            'type' => MoneyEnum::MONEY_IN,
        ]);
        if ($type == 'yesterday'){
            $query->andWhere(['TO_DAYS( NOW() ) - TO_DAYS( FROM_UNIXTIME(create_time) )' => 1]);
        }
        $query->andWhere(['in','action',[ 
            MoneyEnum::ACTION_MANAGE , MoneyEnum::ACTION_SHARE, MoneyEnum::ACTION_FOUR , MoneyEnum::ACTION_FIVE , MoneyEnum::ACTION_PROFIT ,
            MoneyEnum::ACTION16 , MoneyEnum::ACTION17, MoneyEnum::ACTION18 ,
        ]]);
        
        $profit = $query->sum('money');
        $profit = empty($profit)? 0:$profit;
         
        return strval($profit);
    }

    /**
     * @param $member_id
     * 获取用户累计总投资
     * @author lwj
     */
    public static function  getAllInvestment($member_id){
        return self::getLiveInvestment($member_id) + self::getFixInvestment($member_id);
    }

    /**
     * @param $member_id
     *获取用户累计活期总投资
     * @author lwj
     */
    public static function  getLiveInvestment($member_id){
        $query  = QfbOrder::find();
        $query->where([
            'member_id' => $member_id,
        ]);

        $query->andWhere(['=','sorts',2]);
        $query->andWhere(['=','is_check',1]);

        $profit = $query->sum('money');
        $profit = empty($profit)? 0:$profit;

        return strval($profit);
    }
    /**
     * @param $member_id
     *获取用户累计定期总投资
     * @author lwj
     */
    public static function  getFixInvestment($member_id){
        $query  = QfbOrderFix::find();
        $query->where([
            'member_id' => $member_id,
        ]);

        $profit = $query->sum('pay_money');
        $profit = empty($profit)? 0:$profit;

        return strval($profit);
    }
    /**
     * @param $member_id
     *获取用户累计定期总收益
     * @author lwj
     */
    public static function  getFixIncome($member_id){
        $query  = QfbMoneyLog::find();
        $query->where([
            'member_id' => $member_id,
            'type' => MoneyEnum::MONEY_IN,
        ]);

        $query->andWhere(['=','action',8]);
        $query->andWhere(['=','money_type',3]);

        $profit = $query->sum('money');
        $profit = empty($profit)? 0:$profit;

        return strval($profit);
    }
    /**
     * @param $member_id
     *获取用户累计活期总收益
     * @author lwj
     */
    public static function  getLiveIncome($member_id){
        $query  = QfbMoneyLog::find();
        $query->where([
            'member_id' => $member_id,
            'type' => MoneyEnum::MONEY_IN,
        ]);

        $query->andWhere(['=','action',8]);
        $query->andWhere(['=','money_type',2]);

        $profit = $query->sum('money');
        $profit = empty($profit)? 0:$profit;

        return strval($profit);
    }
    /**
     * @param $member_id
     *获取用户累计推荐奖
     * @author lwj
     */
    public static function  getRecommandProfit($member_id){
        $query  = QfbMoneyLog::find();
        $query->where([
            'member_id' => $member_id,
            'type' => MoneyEnum::MONEY_IN,
        ]);

        $query->andWhere(['=','action',5]);

        $profit = $query->sum('money');
        $profit = empty($profit)? 0:$profit;

        return strval($profit);
    }
    
    
}