<?php 
namespace common\models\orderFix;
/**
 * 项目投资记录
 */
class BuyDetail{

	public $money;
	public $vouchers_money;
	public $time;
	public function getMoney($value){
		$this->money = $value;
		return $this;
	}
	public function getVouchersMoney($value){
		$this->vouchers_money=$value;
		return $this;
	}
	public function getTime($value){
		$this->time = date('Y-m-d H:i:s',$value);
		return $this;
	}
}