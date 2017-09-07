<?php 
namespace common\models\orderFix;
use common\enum\LevelEnum;
use common\service\ToolService;
/**
 * 用户类型
 */
class MemberType{
	public $level;
	public $mobile;
	public $time;
	public $money;

	public function getLevel($value){
		$this->level = LevelEnum::getName($value);
		return $this;
	}
	public function getMobile($value){
		$this->mobile = ToolService::setEncrypt($value,3,4,'*',4);
		return $this;
	}
	public function getTime($value){
		$this->time = date("Y-m-d H:i:s",$value);
		return $this;
	}
	public function getMoney($value){
		$this->money = "￥{$value}";
		return $this;
	}
}