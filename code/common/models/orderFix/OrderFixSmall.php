<?php 
namespace common\models\orderFix;
use common\enum\ProductEnum;
class OrderFixSmall{
	public $id;
	public $sn;
	public $money;
	public $tips;
	public function getTips($index){
		$this->tips = ProductEnum::getTip($index);
		return $this;
	}
	public function load($params){
		foreach ($params as $key => $value) {
			$this->$key = $value;
		}
		return $this;
	}
}