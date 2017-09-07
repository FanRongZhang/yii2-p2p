<?php 
namespace common\service;
use common\models\QfbMemberMoney;
use yii;
use Exception;
class MemberMoneyService extends BaseService{
	protected $_className = "common\models\QfbMemberMoney";

    /**
     *根据member_id获取金额
     */
    public static function getByMemberMoney($member_id=null){
        if($member_id==null){
            return false;
        }
        $money  = QfbMemberMoney::findOne($member_id);
        return $money;
    }
    /**
     * 购买活期
     * [buyLive description]
     * @param  [type] $member_id [description]
     * @param  [type] $money     [description]
     * @return [type]            [description]
     */
    public function buyLive($member_id,$money){
        if($money>0){
            try{
                $command = yii::$app->db;
                $arr=$command->createCommand(sprintf("select money from qfb_member_money where member_id = %d for update",$member_id))->queryOne(); 

                if($arr['money']>=$money){
                    $command->createCommand(sprintf("UPDATE qfb_member_money SET money=money-%.2f , pre_live_money = pre_live_money+%.2f WHERE member_id = %d;",$money,$money,$member_id))->execute();
                }else{
                    throw new Exception("零钱余额不足");
                }
            }catch(Exception $e){
                $this->addMessage('error',$e->getMessage());
                return false;
            }
        }
        return true;
    }

    /**
     * 购买定期
     * [buyFix description]
     * @param  [type] $member_id [description]
     * @param  [type] $pay_money [description]
     * @param  [type] $money     [description]
     * @return [type]            [description]
     */
    public function buyFix($member_id,$pay_money,$money){
        if($money>0&& $pay_money>0){
            try{
                $command = yii::$app->db;
                $arr=$command->createCommand(sprintf("select money from qfb_member_money where member_id = %d for update",$member_id))->queryOne(); 

                if($arr['money']>=$pay_money){
                    $command->createCommand(sprintf("UPDATE qfb_member_money SET money=money-%.2f , fix_money = fix_money+%.2f WHERE member_id = %d;",$pay_money,$money,$member_id))->execute();
                }else{
                    throw new Exception("零钱余额不足");
                }
            }catch(Exception $e){
                $this->addMessage('error',$e->getMessage());
                return false;
            }
        }
        return true;
    }

    /**
     * 消费资金
     * [addLockMoney description]
     * @param [type] $member_id [description]
     * @param [type] $money     [description]
     */
    public function subMoney($member_id,$money){
        if($money>0){
            try{
                $command = yii::$app->db;
                $arr=$command->createCommand(sprintf("select money from qfb_member_money where member_id = %d for update",$member_id))->queryOne(); 

                if($arr['money']>$money){
                    $command->createCommand(sprintf("UPDATE qfb_member_money SET money=money-%.2f WHERE member_id = %d;",$money,$member_id))->execute();
                }else{
                    throw new Exception("零钱余额不足");
                }
            }catch(Exception $e){
                $this->addMessage('error',$e->getMessage());
                return false;
            }
        }
        return true;
    }
    /**
     * 加入冻结资金
     * [addLockMoney description]
     * @param [type] $member_id [description]
     * @param [type] $money     [description]
     */
    public function addLockMoney($member_id,$money){
        if($money>0){
            try{
                $command = yii::$app->db;
                $arr=$command->createCommand(sprintf("select money from qfb_member_money where member_id = %d for update",$member_id))->queryOne(); 

                if($arr['money']>$money){
                    $command->createCommand(sprintf("UPDATE qfb_member_money SET money=money-%.2f,lock_money =lock_money+%.2f  WHERE member_id = %d;",$money,$money,$member_id))->execute();
                }else{
                    throw new Exception("零钱余额不足");
                }
            }catch(Exception $e){
                $this->addMessage('error',$e->getMessage());
                return false;
            }
        }
        return true;
    }

}