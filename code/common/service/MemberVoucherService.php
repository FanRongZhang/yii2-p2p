<?php 
namespace common\service;
use common\models\QfbMemberVouchers;
use yii;
use Exception;
class MemberVoucherService extends BaseService{
	public $_className = 'common\models\QfbMemberVouchers';

	public function cost($id,$product_id){
		try{
            $command = yii::$app->db;
            $arr=$command->createCommand(sprintf("select product_id,status from qfb_member_vouchers where id = %d for update",$id))->queryOne(); 
            if($arr['status']==0){
                $row = $command->createCommand(sprintf("UPDATE qfb_member_vouchers SET status=1,product_id =%d ,invalid_time = %d WHERE id = %d;",$product_id,$this->getTime(),$id))->execute();
                return true;
            }else{
                throw new Exception("代金券已失效");
            }
        }catch(Exception $e){
            $this->addMessage('error',$e->getMessage());
            return false;
        }
	}


	/**
	 * 获取详情
	 * [findDetail description]
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function findDetail($id){
		if($this->model) return $this->model;
		$model = new $this->_className;
		return $this->model = $model::find()
		// ->select("
		// 	qfb_member_vouchers.id,qfb_member_vouchers.member_id,qfb_member_vouchers.status,qfb_member_vouchers.invalid_time,
		// 	vouchers.id,vouchers.money,vouchers.use_money,vouchers.use_type,vouchers.status as vstatus,vouchers.start_time,vouchers.end_time
		// ")
		->joinWith('vouchers')->where(['=','qfb_member_vouchers.id',$id])->one();
	}
	/**
	 * 判断是否已买过此产品
	 * [validHasBuy description]
	 * @param  [type] $product_id [description]
	 * @return [type]             [description]
	 */
	public function validHasBuy($product_id){
		if($this->findModelByProduct($product_id)){
			$this->addMessage('id','此产品已使用过代金券');
			return false;
		}
		return true;
	}

	public function findModelByProduct($product_id){
		$modelName = $this->_className;
		return $modelName::find()->where(['=','product_id',$product_id])->andWhere(['=','member_id',$this->getMemberID()])->one();
	}
	/**
	 * [checkVoucher 验证代金券]
	 * @param  [type] $member_id [description]
	 * @return [type]            [description]
	 */
	public function validVoucher($member_id){
		if($this->model==null) {
			$this->addMessage('id','代金券不存在');
			return false;
		}else if($this->model->member_id!= $member_id){
			$this->addMessage('member_id','代金券用户错误');
			return false;
		} else if($this->model->status ==2||$this->model->status==3 || $this->model->invalid_time<time()) {
			$this->addMessage('status','代金券已失效');
			return false;
		}
		return true;
	}
	/**
	 * 判断产品类型是否符合
	 * [checkProduct description]
	 * @param  [type] $product_type [description]
	 * @return [type]               [description]
	 * @auther li
	 */
	public function validProduct($product_type){
		if($this->model==null) return $this->returnError('代金券不存在');
		switch ($this->model->vouchers->use_type) {
			case 1://
				if($product_type!=$this->model->vouchers->use_type)
					return $this->returnError('代金券不适用此产品');
				break;
			case 2://
				if($product_type!=$this->model->vouchers->use_type)
					return $this->returnError('代金券不适用此产品');
				break;
			default:
				break;
		};
		return true;
	}
	/**
	 * 判断代金券金额是否符合
	 * [checkMoney description]
	 * @param  [type] $money [description]
	 * @return [type]        [description]
	 */
	public function validMoney($money,$id=0){
		$this->model = $this->findDetail($id);
		if($this->model==null) return $this->returnError('代金券不存在');
		if($money<$this->model->vouchers->use_money){
			return $this->returnError('代金券不适用此产品');
		}
		return true;
	}

	/**
	 * 判断产品是否已经使用过代金券
	 * @param
	 * @return
	 */
	public static function productIsUse($member_id=0,$product_id=0){
	    $result = QfbMemberVouchers::findOne(['member_id'=>$member_id,'product_id'=>$product_id]);
	    if ($result){
	        return true;
	    }else{
	        return false;
	    }
	}

	/**
	 * 把代金券插入qfb_member_vouchers
	 * @param
	 * @return
	 */
	public static function vouchersIn($data) {

	   	$membervouchers = new QfbMemberVouchers();
	   	$membervouchers->vouchers_id = $data['vouchers_id'];
	   	$membervouchers->member_id = $data['member_id'];
	   	$membervouchers->status = $data['status'];
	   	$membervouchers->receive_time = $data['receive_time'];
	   	$membervouchers->invalid_time = $data['invalid_time'];
	   	$membervouchers->sn = $data['sn'];
	   	if ($membervouchers->save()) {
	   		return true;
	   	} else {
	   		return false;
	   	}

	}

}